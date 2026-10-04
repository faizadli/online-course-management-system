<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Chapter;
use App\Models\ChapterContent;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class CourseBuilderTest extends TestCase
{
    use BuildsFixtures, RefreshDatabase;

    public function test_instructor_creates_a_draft_course_with_thumbnail(): void
    {
        Storage::fake('public');
        $instructor = $this->makeUser(UserRole::Instructor);
        $category = $this->makeCategory();

        $response = $this->actingAs($instructor)->post('/dashboard/instructor/courses', [
            'title' => 'My New Course',
            'category_id' => $category->id,
            'price' => 25,
            'level' => 'Beginner',
            'language' => 'English',
            'short_description' => 'Short',
            'description' => 'Long description',
            'thumbnail' => UploadedFile::fake()->createWithContent('thumb.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')),
        ]);

        $course = Course::query()->where('title', 'My New Course')->first();
        $this->assertNotNull($course);
        $this->assertSame('draft', $course->status);
        $this->assertSame($instructor->id, $course->instructor_id);
        Storage::disk('public')->assertExists($course->thumbnail);
        $response->assertRedirect(route('dashboard.instructor.builder.show', $course));
    }

    public function test_course_cannot_be_published_until_requirements_are_met(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourse($instructor, ['status' => 'draft', 'thumbnail' => null]);

        $this->actingAs($instructor)->patch("/dashboard/instructor/courses/{$course->id}/toggle")
            ->assertSessionHasErrors('course');
        $this->assertSame('draft', $course->fresh()->status);

        // Thumbnail + chapter + content satisfy the checklist.
        $course->update(['thumbnail' => 'thumbnails/x.jpg']);
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'title' => 'C1', 'sort_order' => 1]);

        $this->actingAs($instructor)->patch("/dashboard/instructor/courses/{$course->id}/toggle")
            ->assertSessionHasErrors('course');

        ChapterContent::query()->create(['chapter_id' => $chapter->id, 'title' => 'L1', 'type' => 'text', 'body' => '<p>x</p>', 'sort_order' => 1]);

        $this->actingAs($instructor)->patch("/dashboard/instructor/courses/{$course->id}/toggle")
            ->assertSessionHas('success');
        $this->assertSame('published', $course->fresh()->status);

        // Unpublish and archive
        $this->actingAs($instructor)->patch("/dashboard/instructor/courses/{$course->id}/toggle");
        $this->assertSame('draft', $course->fresh()->status);
        $this->actingAs($instructor)->patch("/dashboard/instructor/courses/{$course->id}/archive");
        $this->assertSame('archived', $course->fresh()->status);
    }

    public function test_chapters_can_be_added_renamed_reordered_and_deleted(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourse($instructor, ['status' => 'draft']);
        $base = "/dashboard/instructor/builder/{$course->id}";

        $this->actingAs($instructor)->post("$base/chapters", ['title' => 'First']);
        $this->actingAs($instructor)->post("$base/chapters", ['title' => 'Second']);
        $first = Chapter::query()->where('title', 'First')->first();
        $second = Chapter::query()->where('title', 'Second')->first();
        $this->assertSame([1, 2], [$first->sort_order, $second->sort_order]);

        $this->actingAs($instructor)->post("$base/chapters/{$second->id}/move", ['direction' => 'up']);
        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);

        $this->actingAs($instructor)->put("$base/chapters/{$first->id}", ['title' => 'Renamed']);
        $this->assertSame('Renamed', $first->fresh()->title);

        $this->actingAs($instructor)->delete("$base/chapters/{$second->id}");
        $this->assertDatabaseMissing('chapters', ['id' => $second->id]);
        $this->assertSame(1, $first->fresh()->sort_order, 'Remaining chapters are renumbered.');
    }

    public function test_video_blog_and_pdf_content_can_be_added(): void
    {
        Storage::fake('public');
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourse($instructor, ['status' => 'draft']);
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'title' => 'C1', 'sort_order' => 1]);
        $url = "/dashboard/instructor/builder/{$course->id}/chapters/{$chapter->id}/contents";

        // Video by URL
        $this->actingAs($instructor)->post($url, [
            'title' => 'Video lesson', 'type' => 'video', 'url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'duration_minutes' => 7,
        ])->assertSessionHasNoErrors();

        // Blog with unsafe markup is sanitised
        $this->actingAs($instructor)->post($url, [
            'title' => 'Blog lesson', 'type' => 'text',
            'body' => '<h2>Title</h2><p onclick="alert(1)">Hi</p><script>alert(2)</script><a href="javascript:alert(3)">x</a>',
        ])->assertSessionHasNoErrors();

        // PDF upload
        $this->actingAs($instructor)->post($url, [
            'title' => 'Handout', 'type' => 'pdf', 'file' => UploadedFile::fake()->create('handout.pdf', 200, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $contents = $chapter->contents()->get()->keyBy('type');
        $this->assertCount(3, $contents);
        $this->assertSame('https://www.youtube.com/embed/aqz-KE-bpKQ', $contents['video']->embed_url);
        $this->assertStringContainsString('<h2>Title</h2>', $contents['text']->body);
        $this->assertStringNotContainsString('script', $contents['text']->body);
        $this->assertStringNotContainsString('onclick', $contents['text']->body);
        $this->assertStringNotContainsString('javascript:', $contents['text']->body);
        Storage::disk('public')->assertExists($contents['pdf']->file_path);
    }

    public function test_content_validation_errors(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourse($instructor, ['status' => 'draft']);
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'title' => 'C1', 'sort_order' => 1]);
        $url = "/dashboard/instructor/builder/{$course->id}/chapters/{$chapter->id}/contents";

        $this->actingAs($instructor)->post($url, ['title' => '', 'type' => 'video'])->assertSessionHasErrors(['title', 'url']);
        $this->actingAs($instructor)->post($url, ['title' => 'T', 'type' => 'text', 'body' => '  '])->assertSessionHasErrors('body');
        $this->actingAs($instructor)->post($url, ['title' => 'T', 'type' => 'pdf'])->assertSessionHasErrors('file');
    }

    public function test_content_can_be_reordered_edited_and_removed(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourseWithContent($instructor);
        $chapter = $course->chapters()->first();
        [$video, $text] = $chapter->contents()->get()->all();
        $base = "/dashboard/instructor/builder/{$course->id}/chapters/{$chapter->id}/contents";

        $this->actingAs($instructor)->post("$base/{$text->id}/move", ['direction' => 'up']);
        $this->assertSame(1, $text->fresh()->sort_order);

        $this->actingAs($instructor)->put("$base/{$text->id}", [
            'title' => 'Updated title', 'type' => 'text', 'body' => '<p>New body</p>', 'duration_minutes' => 9,
        ])->assertRedirect(route('dashboard.instructor.builder.show', $course));
        $this->assertSame('Updated title', $text->fresh()->title);

        $this->actingAs($instructor)->delete("$base/{$video->id}");
        $this->assertDatabaseMissing('chapter_contents', ['id' => $video->id]);
    }

    public function test_add_content_forms_are_never_rendered_in_edit_mode(): void
    {
        // Regression: a chapter that already has lessons must still render POST (create) forms.
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourseWithContent($instructor);
        $chapter = $course->chapters()->first();

        $html = $this->actingAs($instructor)->get("/dashboard/instructor/builder/{$course->id}")->getContent();

        foreach (['video', 'text', 'pdf'] as $type) {
            $this->assertMatchesRegularExpression('/<form[^>]*id="form-add-'.$chapter->id.'-'.$type.'"[^>]*>(?:(?!<\/form>).)*?<input type="hidden" name="_form"/s', $html);
            preg_match('/<form[^>]*id="form-add-'.$chapter->id.'-'.$type.'".*?<\/form>/s', $html, $m);
            $this->assertStringNotContainsString('name="_method"', $m[0], "Add-{$type} form must not spoof a PUT request.");
        }
    }

    public function test_instructor_cannot_edit_someone_elses_course(): void
    {
        $owner = $this->makeUser(UserRole::Instructor);
        $other = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourse($owner);

        $this->actingAs($other)->get("/dashboard/instructor/builder/{$course->id}")->assertForbidden();
        $this->actingAs($other)->post("/dashboard/instructor/builder/{$course->id}/chapters", ['title' => 'X'])->assertForbidden();
    }

    public function test_builder_page_shows_course_level_report(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourseWithContent($instructor);
        $this->enrol($this->makeUser(), $course);

        $this->actingAs($instructor)->get("/dashboard/instructor/builder/{$course->id}")
            ->assertOk()
            ->assertSee('Students enrolled')
            ->assertSee('Avg. score')
            ->assertSee('Chapter One');
    }

    public function test_course_with_enrolments_cannot_be_deleted(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourse($instructor);
        $this->enrol($this->makeUser(), $course);

        $this->actingAs($instructor)->delete("/dashboard/instructor/courses/{$course->id}")->assertSessionHasErrors('course');
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }
}
