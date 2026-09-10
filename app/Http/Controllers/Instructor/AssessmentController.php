<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreAssessmentRequest;
use App\Http\Requests\Instructor\StoreQuestionRequest;
use App\Http\Requests\Instructor\UpdateSubmissionRequest;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentSubmission;
use App\Models\Course;
use App\Support\AdminPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $status = $request->query('status');
        $search = $request->query('search');
        $perPage = AdminPagination::perPage($request);
        $instructorId = auth()->id();

        $assessments = Assessment::query()
            ->with(['course', 'chapter'])
            ->withCount(['questions', 'submissions'])
            ->whereHas('course', fn ($q) => $q->where('instructor_id', $instructorId))
            ->when($type && in_array($type, ['mcq', 'essay'], true), fn ($q) => $q->where('type', $type))
            ->when($status && in_array($status, ['draft', 'published'], true), fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $courseIds = Course::query()->where('instructor_id', $instructorId)->pluck('id');

        $counts = [
            'all' => Assessment::query()->whereIn('course_id', $courseIds)->count(),
            'mcq' => Assessment::query()->whereIn('course_id', $courseIds)->where('type', 'mcq')->count(),
            'essay' => Assessment::query()->whereIn('course_id', $courseIds)->where('type', 'essay')->count(),
            'pending' => AssessmentSubmission::query()
                ->whereHas('assessment', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->where('status', 'pending')
                ->count(),
        ];

        $courses = Course::query()
            ->where('instructor_id', $instructorId)
            ->orderBy('title')
            ->get();

        $pendingSubmissions = AssessmentSubmission::query()
            ->with(['student', 'assessment.course'])
            ->whereHas('assessment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->where('status', 'pending')
            ->latest('submitted_at')
            ->take(5)
            ->get();

        return view('dashboard.instructor.assessments.index', compact(
            'assessments',
            'counts',
            'type',
            'status',
            'search',
            'courses',
            'pendingSubmissions'
        ));
    }

    public function store(StoreAssessmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $course = Course::query()->findOrFail($validated['course_id']);
        $this->authorizeCourse($course);

        Assessment::query()->create([
            'course_id' => $course->id,
            'chapter_id' => $validated['chapter_id'] ?? null,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'pass_score' => $validated['pass_score'] ?? 70,
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'status' => 'draft',
        ]);

        return back()->with('success', 'Assessment created successfully.');
    }

    public function show(Assessment $assessment): View
    {
        $this->authorizeAssessment($assessment);

        $assessment->load(['course.chapters', 'chapter', 'questions', 'submissions.student']);

        return view('dashboard.instructor.assessments.show', compact('assessment'));
    }

    public function storeQuestion(StoreQuestionRequest $request, Assessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($assessment);

        $validated = $request->validated();
        $sortOrder = ((int) $assessment->questions()->max('sort_order')) + 1;

        $options = null;
        if ($assessment->type === 'mcq') {
            $options = collect($validated['options'] ?? [])
                ->filter(fn ($option) => filled($option['text'] ?? null))
                ->values()
                ->map(fn ($option, $index) => [
                    'text' => $option['text'],
                    'is_correct' => (int) ($validated['correct_option'] ?? 0) === $index,
                ])
                ->all();
        }

        $assessment->questions()->create([
            'question' => $validated['question'],
            'options' => $options,
            'model_answer' => $validated['model_answer'] ?? null,
            'points' => $validated['points'] ?? 1,
            'sort_order' => $sortOrder,
        ]);

        return back()->with('success', 'Question added successfully.');
    }

    public function destroyQuestion(Assessment $assessment, AssessmentQuestion $question): RedirectResponse
    {
        $this->authorizeAssessment($assessment);
        abort_unless($question->assessment_id === $assessment->id, 404);

        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    public function toggleStatus(Assessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($assessment);

        if ($assessment->status === 'published') {
            $assessment->update(['status' => 'draft']);
            $message = 'Assessment unpublished.';
        } else {
            if ($assessment->questions()->count() === 0) {
                return back()->withErrors(['assessment' => 'Add at least one question before publishing.']);
            }
            $assessment->update(['status' => 'published']);
            $message = 'Assessment published.';
        }

        return back()->with('success', $message);
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($assessment);
        $assessment->delete();

        return redirect()
            ->route('dashboard.instructor.assessments.index')
            ->with('success', 'Assessment deleted.');
    }

    public function gradeSubmission(UpdateSubmissionRequest $request, AssessmentSubmission $submission): RedirectResponse
    {
        $this->authorizeAssessment($submission->assessment);

        $validated = $request->validated();

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'] ?? null,
            'status' => 'graded',
            'graded_at' => now(),
        ]);

        return back()->with('success', 'Submission graded successfully.');
    }

    private function authorizeAssessment(Assessment $assessment): void
    {
        abort_unless($assessment->course?->instructor_id === auth()->id(), 403);
    }

    private function authorizeCourse(Course $course): void
    {
        abort_unless($course->instructor_id === auth()->id(), 403);
    }
}
