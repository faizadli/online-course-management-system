<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSubmission;
use App\Models\Category;
use App\Models\ChapterContent;
use App\Models\ContentCompletion;
use App\Models\Course;
use App\Models\Enrolment;
use App\Support\AdminPagination;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $perPage = AdminPagination::perPage($request);
        $studentId = auth()->id();

        $enrolments = Enrolment::query()
            ->with(['course.instructor', 'course.category'])
            ->where('student_id', $studentId)
            ->when($status && in_array($status, ['active', 'completed', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('course', fn ($query) => $query->where('title', 'like', "%{$search}%"));
            })
            ->latest('enrolled_at')
            ->paginate($perPage)
            ->withQueryString();

        $counts = [
            'all' => Enrolment::query()->where('student_id', $studentId)->count(),
            'active' => Enrolment::query()->where('student_id', $studentId)->where('status', 'active')->count(),
            'completed' => Enrolment::query()->where('student_id', $studentId)->where('status', 'completed')->count(),
        ];

        return view('dashboard.student.courses.index', compact('enrolments', 'counts', 'status', 'search'));
    }

    public function browse(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $perPage = AdminPagination::perPage($request);
        $studentId = auth()->id();

        $enrolledIds = Enrolment::query()
            ->where('student_id', $studentId)
            ->pluck('course_id');

        $courses = Course::query()
            ->with(['instructor', 'category'])
            ->withCount('chapters')
            ->where('status', 'published')
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('dashboard.student.courses.browse', compact('courses', 'categories', 'enrolledIds', 'search', 'categoryId'));
    }

    public function show(Course $course): View
    {
        $enrolment = $this->requireEnrolment($course);

        $course->load([
            'instructor',
            'category',
            'chapters.contents',
            'chapters.assessments' => fn ($q) => $q->where('status', 'published'),
            'assessments' => fn ($q) => $q->where('status', 'published'),
        ]);

        $completedIds = $enrolment->completions()->pluck('chapter_content_id');

        $submissions = AssessmentSubmission::query()
            ->where('student_id', auth()->id())
            ->whereIn('assessment_id', $course->assessments->pluck('id'))
            ->get()
            ->keyBy('assessment_id');

        // Assessments that are not tied to a chapter are listed separately.
        $looseAssessments = $course->assessments->whereNull('chapter_id');

        $nextLesson = $course->chapters
            ->flatMap(fn ($chapter) => $chapter->contents)
            ->first(fn ($content) => ! $completedIds->contains($content->id));

        return view('dashboard.student.courses.show', compact(
            'course',
            'enrolment',
            'submissions',
            'completedIds',
            'looseAssessments',
            'nextLesson'
        ));
    }

    public function lesson(Course $course, ChapterContent $content): View
    {
        $enrolment = $this->requireEnrolment($course);
        $this->ensureContentBelongsToCourse($course, $content);

        $course->load('chapters.contents');
        $lessons = $course->chapters->flatMap(fn ($chapter) => $chapter->contents)->values();
        $position = $lessons->search(fn ($item) => $item->id === $content->id);

        return view('dashboard.student.courses.lesson', [
            'course' => $course,
            'content' => $content,
            'enrolment' => $enrolment,
            'chapter' => $content->chapter,
            'isCompleted' => $enrolment->hasCompleted($content),
            'previous' => $position > 0 ? $lessons[$position - 1] : null,
            'next' => $position < $lessons->count() - 1 ? $lessons[$position + 1] : null,
            'safeBody' => $content->type === 'text' ? HtmlSanitizer::clean($content->body) : null,
            'position' => $position + 1,
            'totalLessons' => $lessons->count(),
        ]);
    }

    public function completeLesson(Request $request, Course $course, ChapterContent $content): RedirectResponse
    {
        $enrolment = $this->requireEnrolment($course);
        $this->ensureContentBelongsToCourse($course, $content);

        if ($request->boolean('undo')) {
            $enrolment->completions()->where('chapter_content_id', $content->id)->delete();
            $message = 'Lesson marked as not completed.';
        } else {
            ContentCompletion::query()->firstOrCreate(
                ['enrolment_id' => $enrolment->id, 'chapter_content_id' => $content->id],
                ['completed_at' => now()]
            );
            $message = 'Lesson completed. Great job!';
        }

        $enrolment->recalculateProgress();

        // Move on to the next lesson when requested and available.
        if (! $request->boolean('undo') && $request->boolean('go_next')) {
            $lessons = $course->chapters()->with('contents')->get()->flatMap(fn ($chapter) => $chapter->contents)->values();
            $position = $lessons->search(fn ($item) => $item->id === $content->id);
            $next = $lessons[$position + 1] ?? null;

            if ($next) {
                return redirect()
                    ->route('dashboard.student.courses.lesson', [$course, $next])
                    ->with('success', $message);
            }

            return redirect()->route('dashboard.student.courses.show', $course)->with('success', $message);
        }

        return back()->with('success', $message);
    }

    private function ensureContentBelongsToCourse(Course $course, ChapterContent $content): void
    {
        abort_unless($content->chapter && $content->chapter->course_id === $course->id, 404);
    }

    private function requireEnrolment(Course $course): Enrolment
    {
        $enrolment = Enrolment::query()
            ->where('course_id', $course->id)
            ->where('student_id', auth()->id())
            ->first();

        abort_unless($enrolment, 403, 'You are not enrolled in this course.');

        return $enrolment;
    }
}
