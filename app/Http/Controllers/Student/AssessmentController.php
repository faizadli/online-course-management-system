<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAssessmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Enrolment;
use App\Services\GeminiException;
use App\Services\GeminiScoringService;
use App\Support\AdminPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function __construct(private readonly GeminiScoringService $gemini)
    {
    }

    public function index(Request $request): View
    {
        $type = $request->query('type');
        $filter = $request->query('filter'); // available, completed
        $search = $request->query('search');
        $perPage = AdminPagination::perPage($request);
        $studentId = auth()->id();

        $enrolledCourseIds = Enrolment::query()
            ->where('student_id', $studentId)
            ->pluck('course_id');

        $assessments = Assessment::query()
            ->with(['course', 'questions'])
            ->withCount('questions')
            ->whereIn('course_id', $enrolledCourseIds)
            ->where('status', 'published')
            ->when($type && in_array($type, ['mcq', 'essay'], true), fn ($q) => $q->where('type', $type))
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($filter === 'available', function ($q) use ($studentId) {
                $q->whereDoesntHave('submissions', fn ($s) => $s->where('student_id', $studentId));
            })
            ->when($filter === 'completed', function ($q) use ($studentId) {
                $q->whereHas('submissions', fn ($s) => $s->where('student_id', $studentId));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $submissionMap = AssessmentSubmission::query()
            ->where('student_id', $studentId)
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->keyBy('assessment_id');

        $counts = [
            'all' => Assessment::query()->whereIn('course_id', $enrolledCourseIds)->where('status', 'published')->count(),
            'available' => Assessment::query()
                ->whereIn('course_id', $enrolledCourseIds)
                ->where('status', 'published')
                ->whereDoesntHave('submissions', fn ($s) => $s->where('student_id', $studentId))
                ->count(),
            'completed' => AssessmentSubmission::query()->where('student_id', $studentId)->count(),
        ];

        return view('dashboard.student.assessments.index', compact(
            'assessments',
            'submissionMap',
            'counts',
            'type',
            'filter',
            'search'
        ));
    }

    public function show(Assessment $assessment): View
    {
        $this->authorizeAssessment($assessment);

        $assessment->load(['course', 'questions']);

        $submission = AssessmentSubmission::query()
            ->where('assessment_id', $assessment->id)
            ->where('student_id', auth()->id())
            ->first();

        // Remember when the student opened the assessment so the time limit can be enforced.
        $startedAt = null;
        if (! $submission && $assessment->time_limit_minutes) {
            $key = "assessment_started.{$assessment->id}";
            if (! session()->has($key)) {
                session([$key => now()->timestamp]);
            }
            $startedAt = session($key);
        }

        $remainingSeconds = $startedAt
            ? max(0, ($assessment->time_limit_minutes * 60) - (now()->timestamp - $startedAt))
            : null;

        return view('dashboard.student.assessments.show', compact('assessment', 'submission', 'remainingSeconds'));
    }

    public function submit(SubmitAssessmentRequest $request, Assessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($assessment);

        $existing = AssessmentSubmission::query()
            ->where('assessment_id', $assessment->id)
            ->where('student_id', auth()->id())
            ->first();

        if ($existing) {
            return redirect()
                ->route('dashboard.student.assessments.show', $assessment)
                ->withErrors(['assessment' => 'You have already submitted this assessment.']);
        }

        // Enforce the time limit (with a 2-minute grace period for slow connections).
        $startedAt = session("assessment_started.{$assessment->id}");
        if ($assessment->time_limit_minutes && $startedAt
            && (now()->timestamp - $startedAt) > ($assessment->time_limit_minutes * 60 + 120)) {
            session()->forget("assessment_started.{$assessment->id}");

            return redirect()
                ->route('dashboard.student.assessments.show', $assessment)
                ->withErrors(['assessment' => 'The time limit for this assessment has expired. Please reopen it to start again.']);
        }

        $assessment->load('questions');
        $answers = $request->validated('answers') ?? [];

        $data = [
            'assessment_id' => $assessment->id,
            'student_id' => auth()->id(),
            'answers' => $answers,
            'submitted_at' => now(),
        ];

        if ($assessment->type === 'mcq') {
            [$score, $feedback, $details] = $this->scoreMcq($assessment, $answers);
            $data += [
                'status' => 'graded',
                'score' => $score,
                'feedback' => $feedback,
                'ai_details' => $details,
                'graded_at' => now(),
            ];
            $message = "Assessment submitted and auto-scored: {$score}%.";
        } else {
            try {
                $result = $this->gemini->scoreAssessment($assessment, $answers);

                $data += [
                    'status' => 'ai_graded',
                    'score' => $result['score'],
                    'feedback' => $result['feedback'],
                    'ai_details' => $result['details'],
                    'ai_model' => $result['model'],
                    'graded_at' => now(),
                ];
                $message = "Assessment submitted. AI scored your answer: {$result['score']}%.";
            } catch (GeminiException $e) {
                // Alternative flow in the sequence diagram: AI unavailable -> manual grading.
                Log::error('AI scoring failed, submission left pending', ['assessment' => $assessment->id, 'error' => $e->getMessage()]);

                $data += [
                    'status' => 'pending',
                    'ai_error' => $e->getMessage(),
                ];
                $message = 'Assessment submitted. AI scoring is temporarily unavailable, so your instructor will review it manually.';
            }
        }

        $submission = AssessmentSubmission::query()->create($data);
        session()->forget("assessment_started.{$assessment->id}");

        Enrolment::query()
            ->where('course_id', $assessment->course_id)
            ->where('student_id', auth()->id())
            ->first()
            ?->recalculateProgress();

        return redirect()
            ->route('dashboard.student.feedback.show', $submission)
            ->with('success', $message);
    }

    /**
     * @return array{0: float, 1: string, 2: array}
     */
    private function scoreMcq(Assessment $assessment, array $answers): array
    {
        $totalPoints = max(1, $assessment->questions->sum('points'));
        $earned = 0;
        $breakdown = [];

        foreach ($assessment->questions as $question) {
            $selected = $answers[(string) $question->id] ?? $answers[$question->id] ?? null;
            $options = $question->options ?? [];
            $correctIndex = collect($options)->search(fn ($option) => ! empty($option['is_correct']));
            $isCorrect = $correctIndex !== false && (string) $selected === (string) $correctIndex;

            if ($isCorrect) {
                $earned += $question->points;
            }

            $breakdown[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'selected_text' => $options[(int) $selected]['text'] ?? null,
                'correct_text' => $correctIndex !== false ? ($options[$correctIndex]['text'] ?? null) : null,
                'is_correct' => $isCorrect,
                'points' => $isCorrect ? (int) $question->points : 0,
                'max' => (int) $question->points,
                'explanation' => $question->explanation,
            ];
        }

        $score = round(($earned / $totalPoints) * 100, 1);
        $passed = $score >= $assessment->pass_score;
        $correct = collect($breakdown)->where('is_correct', true)->count();
        $feedback = $passed
            ? "Great job! You answered {$correct} of ".count($breakdown)." questions correctly ({$score}%) and passed this MCQ assessment."
            : "You answered {$correct} of ".count($breakdown)." questions correctly ({$score}%). Review the course materials and try to improve next time.";

        return [$score, $feedback, ['mcq' => $breakdown]];
    }
    private function authorizeAssessment(Assessment $assessment): void
    {
        abort_unless($assessment->status === 'published', 404);

        $enrolled = Enrolment::query()
            ->where('course_id', $assessment->course_id)
            ->where('student_id', auth()->id())
            ->exists();

        abort_unless($enrolled, 403);
    }
}
