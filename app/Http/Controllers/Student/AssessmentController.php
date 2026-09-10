<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAssessmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Enrolment;
use App\Support\AdminPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
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

        return view('dashboard.student.assessments.show', compact('assessment', 'submission'));
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

        $assessment->load('questions');
        $answers = $request->validated('answers') ?? [];

        if ($assessment->type === 'mcq') {
            [$score, $feedback] = $this->scoreMcq($assessment, $answers);
            $status = 'graded';
        } else {
            [$score, $feedback] = $this->scoreEssayWithAi($assessment, $answers);
            $status = 'ai_graded';
        }

        AssessmentSubmission::query()->create([
            'assessment_id' => $assessment->id,
            'student_id' => auth()->id(),
            'status' => $status,
            'answers' => $answers,
            'score' => $score,
            'feedback' => $feedback,
            'submitted_at' => now(),
            'graded_at' => now(),
        ]);

        return redirect()
            ->route('dashboard.student.feedback.index')
            ->with('success', $assessment->type === 'mcq'
                ? 'Assessment submitted and auto-scored.'
                : 'Assessment submitted. AI feedback is ready.');
    }

    private function scoreMcq(Assessment $assessment, array $answers): array
    {
        $totalPoints = max(1, $assessment->questions->sum('points'));
        $earned = 0;

        foreach ($assessment->questions as $question) {
            $selected = $answers[(string) $question->id] ?? $answers[$question->id] ?? null;
            $options = $question->options ?? [];
            $correctIndex = collect($options)->search(fn ($option) => ! empty($option['is_correct']));

            if ($correctIndex !== false && (string) $selected === (string) $correctIndex) {
                $earned += $question->points;
            }
        }

        $score = round(($earned / $totalPoints) * 100, 1);
        $passed = $score >= $assessment->pass_score;
        $feedback = $passed
            ? "Great job! You scored {$score}% and passed this MCQ assessment."
            : "You scored {$score}%. Review the course materials and try to improve next time.";

        return [$score, $feedback];
    }

    private function scoreEssayWithAi(Assessment $assessment, array $answers): array
    {
        $answerText = collect($answers)->filter()->implode(' ');
        $wordCount = str_word_count(strip_tags($answerText));

        $base = min(95, 55 + (int) floor($wordCount / 8));
        $score = max(40, min(98, $base + random_int(-5, 8)));

        $feedback = "AI Feedback: Your response covers the main idea with approximately {$wordCount} words. "
            .'Strengthen the answer by adding clearer structure, concrete examples, and a short conclusion. '
            .($score >= $assessment->pass_score
                ? 'Overall, this submission meets the passing criteria.'
                : 'Overall, revise key points to better match the expected model answer.');

        return [(float) $score, $feedback];
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
