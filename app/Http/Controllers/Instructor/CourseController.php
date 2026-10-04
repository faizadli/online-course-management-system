<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Requests\Instructor\UpdateCourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Support\AdminPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $perPage = AdminPagination::perPage($request);
        $instructorId = auth()->id();

        $courses = Course::query()
            ->where('instructor_id', $instructorId)
            ->with('category')
            ->withCount(['chapters', 'assessments', 'enrolments'])
            ->when($status && in_array($status, ['published', 'draft', 'archived'], true), fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $counts = [
            'all' => Course::query()->where('instructor_id', $instructorId)->count(),
            'published' => Course::query()->where('instructor_id', $instructorId)->where('status', 'published')->count(),
            'draft' => Course::query()->where('instructor_id', $instructorId)->where('status', 'draft')->count(),
            'archived' => Course::query()->where('instructor_id', $instructorId)->where('status', 'archived')->count(),
        ];

        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('dashboard.instructor.courses.index', compact('courses', 'counts', 'status', 'search', 'categories'));
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $thumbnail = $request->hasFile('thumbnail') ? $request->file('thumbnail')->store('thumbnails', 'public') : null;

        $course = Course::query()->create([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'category_id' => $validated['category_id'] ?? null,
            'instructor_id' => auth()->id(),
            'thumbnail' => $thumbnail,
            'status' => 'draft',
            'price' => $validated['price'] ?? 0,
            'level' => $validated['level'] ?? 'Beginner',
            'language' => $validated['language'] ?? 'English',
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('dashboard.instructor.builder.show', $course)
            ->with('success', 'Course draft created. Continue building content.');
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $validated = $request->validated();
        $status = $validated['status'] ?? $course->status;

        if ($status === 'published' && $course->status !== 'published') {
            $missing = $this->publishBlockers($course, $validated);

            if ($missing !== []) {
                return back()->withErrors(['course' => 'Cannot publish yet. Missing: '.implode(', ', $missing).'.'])->withInput();
            }
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $course->title !== $validated['title']
                ? $this->uniqueSlug($validated['title'], $course->id)
                : $course->slug,
            'category_id' => $validated['category_id'] ?? null,
            'status' => $status,
            'price' => $validated['price'] ?? 0,
            'level' => $validated['level'] ?? 'Beginner',
            'language' => $validated['language'] ?? 'English',
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->update($data);

        return back()->with('success', 'Course updated successfully.');
    }
    public function destroy(Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        if ($course->enrolments()->exists()) {
            return back()->withErrors(['course' => 'Cannot delete a course that already has enrolments. Archive it instead.']);
        }

        $course->delete();

        return redirect()
            ->route('dashboard.instructor.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    public function toggleStatus(Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        if ($course->status === 'published') {
            $course->update(['status' => 'draft']);
            $message = 'Course unpublished and set to draft.';
        } else {
            $missing = $course->missingPublishRequirements();

            if ($missing !== []) {
                return back()->withErrors(['course' => 'Cannot publish yet. Missing: '.implode(', ', $missing).'.']);
            }

            $course->update(['status' => 'published']);
            $message = 'Course published successfully.';
        }

        return back()->with('success', $message);
    }

    public function archive(Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        if ($course->status === 'archived') {
            $course->update(['status' => 'draft']);

            return back()->with('success', 'Course restored to draft.');
        }

        $course->update(['status' => 'archived']);

        return back()->with('success', 'Course archived. It is no longer listed in the catalogue.');
    }

    /** @return array<int, string> */
    private function publishBlockers(Course $course, array $incoming): array
    {
        $missing = $course->missingPublishRequirements();

        // The incoming form may fill in title / description in the same request.
        if (filled($incoming['description'] ?? null) || filled($incoming['short_description'] ?? null)) {
            $missing = array_values(array_diff($missing, ['Description']));
        }

        return $missing;
    }
    private function authorizeCourse(Course $course): void
    {
        abort_unless($course->instructor_id === auth()->id(), 403);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $counter = 1;

        while (
            Course::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
