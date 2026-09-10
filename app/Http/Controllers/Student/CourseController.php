<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSubmission;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrolment;
use App\Support\AdminPagination;
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

    public function enrol(Course $course): RedirectResponse
    {
        abort_unless($course->status === 'published', 404);

        $exists = Enrolment::query()
            ->where('course_id', $course->id)
            ->where('student_id', auth()->id())
            ->exists();

        if ($exists) {
            return redirect()
                ->route('dashboard.student.courses.show', $course)
                ->with('success', 'You are already enrolled in this course.');
        }

        Enrolment::query()->create([
            'course_id' => $course->id,
            'student_id' => auth()->id(),
            'status' => 'active',
            'amount_paid' => $course->price,
            'progress' => 0,
            'enrolled_at' => now(),
        ]);

        $course->increment('enrolments_count');

        return redirect()
            ->route('dashboard.student.courses.show', $course)
            ->with('success', 'Successfully enrolled. Start learning now!');
    }

    public function show(Course $course): View
    {
        $enrolment = $this->requireEnrolment($course);

        $course->load(['instructor', 'category', 'chapters.contents', 'assessments' => fn ($q) => $q->where('status', 'published')]);

        $submissions = AssessmentSubmission::query()
            ->where('student_id', auth()->id())
            ->whereIn('assessment_id', $course->assessments->pluck('id'))
            ->get()
            ->keyBy('assessment_id');

        return view('dashboard.student.courses.show', compact('course', 'enrolment', 'submissions'));
    }

    public function updateProgress(Request $request, Course $course): RedirectResponse
    {
        $enrolment = $this->requireEnrolment($course);

        $validated = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $progress = (int) $validated['progress'];
        $enrolment->update([
            'progress' => $progress,
            'status' => $progress >= 100 ? 'completed' : 'active',
            'completed_at' => $progress >= 100 ? now() : null,
        ]);

        return back()->with('success', 'Progress updated.');
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
