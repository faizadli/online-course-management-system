<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreChapterContentRequest;
use App\Http\Requests\Instructor\StoreChapterRequest;
use App\Http\Requests\Instructor\UpdateCourseRequest;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\ChapterContent;
use App\Models\Course;
use App\Models\Payment;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CourseBuilderController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->where('instructor_id', auth()->id())
            ->withCount(['chapters', 'assessments', 'contents', 'enrolments'])
            ->latest()
            ->get();

        return view('dashboard.instructor.builder.index', compact('courses'));
    }

    public function show(Course $course): View
    {
        $this->authorizeCourse($course);

        $course->load(['chapters.contents', 'chapters.assessments', 'category', 'assessments.chapter']);
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('dashboard.instructor.builder.show', [
            'course' => $course,
            'categories' => $categories,
            'report' => $this->courseReport($course),
            'checklist' => $course->publishChecklist(),
        ]);
    }

    public function updateInfo(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $validated = $request->validated();

        $data = [
            'title' => $validated['title'],
            'category_id' => $validated['category_id'] ?? null,
            'price' => $validated['price'] ?? 0,
            'level' => $validated['level'] ?? 'Beginner',
            'language' => $validated['language'] ?? 'English',
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('thumbnail')) {
            $this->deleteStoredFile($course->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->update($data);

        return back()->with('success', 'Course information saved.');
    }

    // ----- Chapters ---------------------------------------------------------

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

    public function updateChapter(StoreChapterRequest $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeCourse($course);
        abort_unless($chapter->course_id === $course->id, 404);

        $chapter->update(['title' => $request->validated('title')]);

        return back()->with('success', 'Chapter renamed.');
    }

    public function moveChapter(Request $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeCourse($course);
        abort_unless($chapter->course_id === $course->id, 404);

        $this->swapOrder($course->chapters()->get(), $chapter, $request->input('direction'));

        return back()->with('success', 'Chapter order updated.');
    }

    public function destroyChapter(Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeCourse($course);
        abort_unless($chapter->course_id === $course->id, 404);

        foreach ($chapter->contents as $content) {
            $this->deleteStoredFile($content->file_path);
        }

        $chapter->delete();
        $this->normaliseOrder($course->chapters()->get());

        return back()->with('success', 'Chapter deleted successfully.');
    }

    // ----- Content ----------------------------------------------------------

    public function storeContent(StoreChapterContentRequest $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeCourse($course);
        abort_unless($chapter->course_id === $course->id, 404);

        $data = $this->contentPayload($request);
        $data['sort_order'] = ((int) $chapter->contents()->max('sort_order')) + 1;

        $chapter->contents()->create($data);

        return back()->with('success', 'Content added to chapter.');
    }

    public function editContent(Course $course, Chapter $chapter, ChapterContent $content): View
    {
        $this->authorizeCourse($course);
        $this->ensureContentInChapter($course, $chapter, $content);

        return view('dashboard.instructor.builder.edit-content', compact('course', 'chapter', 'content'));
    }

    public function updateContent(StoreChapterContentRequest $request, Course $course, Chapter $chapter, ChapterContent $content): RedirectResponse
    {
        $this->authorizeCourse($course);
        $this->ensureContentInChapter($course, $chapter, $content);

        $data = $this->contentPayload($request, $content);
        unset($data['type']); // type cannot change after creation

        $content->update($data);

        return redirect()
            ->route('dashboard.instructor.builder.show', $course)
            ->with('success', 'Content updated.');
    }

    public function moveContent(Request $request, Course $course, Chapter $chapter, ChapterContent $content): RedirectResponse
    {
        $this->authorizeCourse($course);
        $this->ensureContentInChapter($course, $chapter, $content);

        $this->swapOrder($chapter->contents()->get(), $content, $request->input('direction'));

        return back()->with('success', 'Content order updated.');
    }

    public function destroyContent(Course $course, Chapter $chapter, ChapterContent $content): RedirectResponse
    {
        $this->authorizeCourse($course);
        $this->ensureContentInChapter($course, $chapter, $content);

        $this->deleteStoredFile($content->file_path);
        $content->delete();
        $this->normaliseOrder($chapter->contents()->get());

        return back()->with('success', 'Content removed.');
    }

    // ----- Assessments created from a chapter -------------------------------

    public function storeAssessment(Request $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorizeCourse($course);
        abort_unless($chapter->course_id === $course->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:mcq,essay'],
            'pass_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'chapter_id' => $chapter->id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'pass_score' => $validated['pass_score'] ?? 70,
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'status' => 'draft',
        ]);

        return redirect()
            ->route('dashboard.instructor.assessments.show', $assessment)
            ->with('success', 'Assessment created. Now add your questions.');
    }

    // ----- Helpers ----------------------------------------------------------

    /** @return array<string, mixed> */
    private function contentPayload(StoreChapterContentRequest $request, ?ChapterContent $existing = null): array
    {
        $validated = $request->validated();
        $type = $existing?->type ?? $validated['type'];

        $data = [
            'title' => $validated['title'],
            'type' => $type,
            'duration_minutes' => $validated['duration_minutes'] ?? 0,
            'is_free_preview' => (bool) ($validated['is_free_preview'] ?? false),
        ];

        if ($type === 'text') {
            $data['body'] = HtmlSanitizer::clean($validated['body'] ?? '');
            $data['url'] = null;
        } else {
            // Optional description shown under video lessons.
            $data['body'] = $type === 'video' ? ($validated['body'] ?? null) : null;

            if ($request->hasFile('file')) {
                if ($existing) {
                    $this->deleteStoredFile($existing->file_path);
                }
                $data['file_path'] = $request->file('file')->store($type === 'pdf' ? 'documents' : 'videos', 'public');
                $data['url'] = null;
            } elseif (filled($validated['url'] ?? null)) {
                if ($existing) {
                    $this->deleteStoredFile($existing->file_path);
                }
                $data['file_path'] = null;
                $data['url'] = $validated['url'];
            }
        }

        return $data;
    }

    private function swapOrder($siblings, $item, ?string $direction): void
    {
        $siblings = $siblings->values();
        $index = $siblings->search(fn ($row) => $row->id === $item->id);
        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! isset($siblings[$targetIndex])) {
            return;
        }

        // Re-number everything first so equal/zero sort orders never block a swap.
        $ordered = $siblings->all();
        [$ordered[$index], $ordered[$targetIndex]] = [$ordered[$targetIndex], $ordered[$index]];

        foreach ($ordered as $position => $row) {
            if ((int) $row->sort_order !== $position + 1) {
                $row->update(['sort_order' => $position + 1]);
            }
        }
    }

    private function normaliseOrder($siblings): void
    {
        foreach ($siblings->values() as $position => $row) {
            if ((int) $row->sort_order !== $position + 1) {
                $row->update(['sort_order' => $position + 1]);
            }
        }
    }

    /** Course-level report shown at the top of the builder (FR10). */
    private function courseReport(Course $course): array
    {
        $enrolments = $course->enrolments();
        $assessmentIds = $course->assessments()->pluck('id');

        $submissions = AssessmentSubmission::query()->whereIn('assessment_id', $assessmentIds);

        return [
            'enrolments' => (clone $enrolments)->count(),
            'revenue' => (float) Payment::query()
                ->where('status', 'paid')
                ->whereHas('enrolment', fn ($q) => $q->where('course_id', $course->id))
                ->sum('amount'),
            'completed' => (clone $enrolments)->where('status', 'completed')->count(),
            'avg_progress' => (int) round((float) (clone $enrolments)->avg('progress')),
            'submissions' => (clone $submissions)->count(),
            'pending' => (clone $submissions)->where('status', 'pending')->count(),
            'avg_score' => (clone $submissions)->whereNotNull('score')->avg('score'),
        ];
    }

    private function ensureContentInChapter(Course $course, Chapter $chapter, ChapterContent $content): void
    {
        abort_unless($chapter->course_id === $course->id, 404);
        abort_unless($content->chapter_id === $chapter->id, 404);
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function authorizeCourse(Course $course): void
    {
        abort_unless($course->instructor_id === auth()->id(), 403);
    }
}
