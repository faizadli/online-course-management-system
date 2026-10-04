<?php

namespace Tests\Concerns;

use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\ChapterContent;
use App\Models\Course;
use App\Models\Enrolment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait BuildsFixtures
{
    protected function makeUser(UserRole $role = UserRole::Student, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'password' => Hash::make('password'),
        ], $attributes));
    }

    protected function makeCategory(string $name = 'Tech & Programming'): Category
    {
        return Category::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'is_active' => true]);
    }

    protected function makeCourse(?User $instructor = null, array $attributes = []): Course
    {
        $instructor ??= $this->makeUser(UserRole::Instructor);
        $title = $attributes['title'] ?? 'Course '.Str::random(6);

        return Course::query()->create(array_merge([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'category_id' => $this->makeCategory()->id,
            'instructor_id' => $instructor->id,
            'status' => 'published',
            'price' => 50,
            'level' => 'Beginner',
            'language' => 'English',
            'thumbnail' => 'https://example.com/thumb.jpg',
            'short_description' => 'Short description',
            'description' => 'Full description',
        ], $attributes));
    }

    /** Course with one chapter and two lessons (text + video). */
    protected function makeCourseWithContent(?User $instructor = null, array $attributes = []): Course
    {
        $course = $this->makeCourse($instructor, $attributes);
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'title' => 'Chapter One', 'sort_order' => 1]);

        ChapterContent::query()->create([
            'chapter_id' => $chapter->id, 'title' => 'Intro video', 'type' => 'video',
            'url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'duration_minutes' => 5, 'sort_order' => 1,
        ]);
        ChapterContent::query()->create([
            'chapter_id' => $chapter->id, 'title' => 'Reading', 'type' => 'text',
            'body' => '<p>Hello <strong>world</strong></p>', 'duration_minutes' => 5, 'sort_order' => 2,
        ]);

        return $course->fresh();
    }

    protected function enrol(User $student, Course $course): Enrolment
    {
        return Enrolment::query()->create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'status' => 'active',
            'amount_paid' => $course->price,
            'progress' => 0,
            'enrolled_at' => now(),
        ]);
    }

    protected function makeMcq(Course $course, array $attributes = []): Assessment
    {
        $assessment = Assessment::query()->create(array_merge([
            'course_id' => $course->id,
            'chapter_id' => $course->chapters()->first()?->id,
            'title' => 'Quiz',
            'type' => 'mcq',
            'pass_score' => 70,
            'status' => 'published',
        ], $attributes));

        foreach ([1, 2] as $n) {
            AssessmentQuestion::query()->create([
                'assessment_id' => $assessment->id,
                'question' => "Question {$n}?",
                'options' => [
                    ['text' => 'Right', 'is_correct' => true],
                    ['text' => 'Wrong', 'is_correct' => false],
                ],
                'points' => 1,
                'sort_order' => $n,
            ]);
        }

        return $assessment->fresh('questions');
    }

    protected function makeEssay(Course $course, array $attributes = []): Assessment
    {
        $assessment = Assessment::query()->create(array_merge([
            'course_id' => $course->id,
            'chapter_id' => $course->chapters()->first()?->id,
            'title' => 'Essay',
            'type' => 'essay',
            'pass_score' => 60,
            'status' => 'published',
        ], $attributes));

        AssessmentQuestion::query()->create([
            'assessment_id' => $assessment->id,
            'question' => 'Explain the difference between a stack and a queue.',
            'model_answer' => 'Stack is LIFO, queue is FIFO.',
            'rubric' => [
                ['criteria' => 'Correctness of Concept', 'description' => 'LIFO vs FIFO', 'weight' => 50],
                ['criteria' => 'Use of Examples', 'description' => 'Real-world examples', 'weight' => 30],
                ['criteria' => 'Clarity', 'description' => 'Well written', 'weight' => 20],
            ],
            'points' => 10,
            'sort_order' => 1,
        ]);

        return $assessment->fresh('questions');
    }

    /** Gemini-style JSON payload for Http::fake(). */
    protected function geminiResponse(array $percents = [100, 80, 60], string $feedback = 'Nice work.'): array
    {
        $names = ['Correctness of Concept', 'Use of Examples', 'Clarity'];
        $criteria = [];
        foreach ($percents as $i => $percent) {
            $criteria[] = ['name' => $names[$i], 'percent' => $percent, 'comment' => "Comment {$i}"];
        }

        return [
            'candidates' => [[
                'content' => ['parts' => [['text' => json_encode(['criteria' => $criteria, 'feedback' => $feedback])]]],
                'finishReason' => 'STOP',
            ]],
        ];
    }
}
