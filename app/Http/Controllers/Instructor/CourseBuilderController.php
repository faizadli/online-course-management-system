<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreChapterContentRequest;
use App\Http\Requests\Instructor\StoreChapterRequest;
use App\Http\Requests\Instructor\UpdateCourseRequest;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\ChapterContent;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourseBuilderController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->where('instructor_id', auth()->id())
            ->withCount(['chapters', 'assessments'])
            ->latest()
            ->get();

        return view('dashboard.instructor.builder.index', compact('courses'));
    }

    public function show(Course $course): View
    {
        $this->authorizeCourse($course);

        $course->load(['chapters.contents', 'category', 'assessments']);
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('dashboard.instructor.builder.show', compact('course', 'categories'));
    }

    public function updateInfo(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $validated = $request->validated();

        $course->update([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'] ?? null,
            'status' => $validated['status'],
            'price' => $validated['price'] ?? 0,
            'level' => $validated['level'] ?? 'Beginner',
            'language' => $validated['language'] ?? 'English',
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Course information saved.');
    }

    public function storeChapter(StoreChapterRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $sortOrder = ((int) $course->chapters()->max('sort_order')) + 1;

        $course->chapters()->create([
            'title' => $request->validated('title'),
            'sort_order' => $sortOrder,
        ]);

        return back()->with('success', 'Chapter added successfully.');
    }

    public function destroyChapter(Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeCourse($course);
        abort_unless($chapter->course_id === $course->id, 404);

        $chapter->delete();

        return back()->with('success', 'Chapter deleted successfully.');
    }

    public function storeContent(StoreChapterContentRequest $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeCourse($course);
        abort_unless($chapter->course_id === $course->id, 404);

        $validated = $request->validated();
        $sortOrder = ((int) $chapter->contents()->max('sort_order')) + 1;

        $chapter->contents()->create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'body' => $validated['body'] ?? null,
            'url' => $validated['url'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? 0,
            'sort_order' => $sortOrder,
        ]);

        return back()->with('success', 'Content added to chapter.');
    }

    public function destroyContent(Course $course, Chapter $chapter, ChapterContent $content): RedirectResponse
    {
        $this->authorizeCourse($course);
        abort_unless($chapter->course_id === $course->id, 404);
        abort_unless($content->chapter_id === $chapter->id, 404);

        $content->delete();

        return back()->with('success', 'Content removed.');
    }

    private function authorizeCourse(Course $course): void
    {
        abort_unless($course->instructor_id === auth()->id(), 403);
    }
}
