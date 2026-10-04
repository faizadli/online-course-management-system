<?php

namespace App\Http\Controllers\Public;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ChapterContent;
use App\Models\Course;
use App\Models\Enrolment;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function show(Course $course): View
    {
        abort_unless($course->status === 'published', 404);

        return $this->render($course, preview: false);
    }

    /** Instructor-only preview of a draft/unpublished course. */
    public function preview(Course $course): View
    {
        abort_unless($course->instructor_id === auth()->id(), 403);

        return $this->render($course, preview: true);
    }

    /** Publicly viewable free-preview lesson. */
    public function freePreview(Course $course, ChapterContent $content): View
    {
        abort_unless($course->status === 'published' && $content->is_free_preview && $content->chapter?->course_id === $course->id, 404);

        return view('landing.lesson-preview', [
            'course' => $course->load('instructor'),
            'content' => $content,
            'safeBody' => $content->type === 'text' ? HtmlSanitizer::clean($content->body) : null,
        ]);
    }

    public function instructor(User $user): View
    {
        abort_unless($user->role === UserRole::Instructor && $user->is_active, 404);

        $courses = Course::query()
            ->with('category')
            ->withCount(['chapters', 'contents', 'enrolments'])
            ->where('instructor_id', $user->id)
            ->where('status', 'published')
            ->latest()
            ->get();

        $students = Enrolment::query()
            ->whereIn('course_id', Course::query()->where('instructor_id', $user->id)->pluck('id'))
            ->distinct('student_id')
            ->count('student_id');

        return view('landing.instructor-show', [
            'instructor' => $user,
            'courses' => $courses,
            'studentCount' => $students,
        ]);
    }

    private function render(Course $course, bool $preview): View
    {
        $course->load(['instructor', 'category', 'chapters.contents', 'chapters.assessments' => fn ($q) => $q->where('status', 'published')]);
        $course->loadCount('enrolments');

        $enrolment = null;
        if (auth()->check()) {
            $enrolment = Enrolment::query()
                ->where('course_id', $course->id)
                ->where('student_id', auth()->id())
                ->first();
        }

        return view('landing.course-show', [
            'course' => $course,
            'preview' => $preview,
            'enrolment' => $enrolment,
            'lessonCount' => $course->chapters->sum(fn ($chapter) => $chapter->contents->count()),
            'minutes' => $course->chapters->sum(fn ($chapter) => $chapter->contents->sum('duration_minutes')),
        ]);
    }
}
