<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentSubmission;
use App\Models\Chapter;
use App\Models\ChapterContent;
use App\Models\Course;
use App\Models\Enrolment;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorContentSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::query()->where('role', UserRole::Instructor)->first();
        $student = User::query()->where('role', UserRole::Student)->first();

        if (! $instructor) {
            return;
        }

        // Extra students for enrolment demos
        $extraStudents = collect([
            ['name' => 'Lisa Park', 'email' => 'lisa@skillforge.com'],
            ['name' => 'David Chen', 'email' => 'david@skillforge.com'],
            ['name' => 'Maya Putri', 'email' => 'maya@skillforge.com'],
        ])->map(function (array $data) {
            return User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                    'role' => UserRole::Student,
                    'is_active' => true,
                ]
            );
        });

        $students = collect([$student])->filter()->merge($extraStudents)->unique('id')->values();

        $courses = Course::query()
            ->where('instructor_id', $instructor->id)
            ->get();

        foreach ($courses as $index => $course) {
            $chapterCount = $course->status === 'published' ? 3 : 1;

            for ($i = 1; $i <= $chapterCount; $i++) {
                $chapter = Chapter::query()->updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'sort_order' => $i,
                    ],
                    ['title' => "Chapter {$i}: ".($i === 1 ? 'Introduction' : ($i === 2 ? 'Core Concepts' : 'Practice Project'))]
                );

                ChapterContent::query()->updateOrCreate(
                    [
                        'chapter_id' => $chapter->id,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Welcome video',
                        'type' => 'video',
                        'url' => 'https://example.com/videos/intro.mp4',
                        'duration_minutes' => 12,
                    ]
                );

                ChapterContent::query()->updateOrCreate(
                    [
                        'chapter_id' => $chapter->id,
                        'sort_order' => 2,
                    ],
                    [
                        'title' => 'Reading notes',
                        'type' => 'text',
                        'body' => 'Key concepts and explanations for this chapter.',
                        'duration_minutes' => 8,
                    ]
                );
            }

            $mcq = Assessment::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => $course->title.' — MCQ Quiz',
                ],
                [
                    'chapter_id' => $course->chapters()->first()?->id,
                    'type' => 'mcq',
                    'description' => 'Quick multiple choice quiz covering core topics.',
                    'pass_score' => 70,
                    'time_limit_minutes' => 20,
                    'status' => $course->status === 'published' ? 'published' : 'draft',
                ]
            );

            AssessmentQuestion::query()->updateOrCreate(
                [
                    'assessment_id' => $mcq->id,
                    'sort_order' => 1,
                ],
                [
                    'question' => 'Which statement best describes the main goal of this course?',
                    'options' => [
                        ['text' => 'Build strong fundamentals', 'is_correct' => true],
                        ['text' => 'Skip practice entirely', 'is_correct' => false],
                        ['text' => 'Avoid feedback', 'is_correct' => false],
                        ['text' => 'Ignore design principles', 'is_correct' => false],
                    ],
                    'points' => 1,
                ]
            );

            AssessmentQuestion::query()->updateOrCreate(
                [
                    'assessment_id' => $mcq->id,
                    'sort_order' => 2,
                ],
                [
                    'question' => 'What should learners do after completing a lesson?',
                    'options' => [
                        ['text' => 'Practice and review', 'is_correct' => true],
                        ['text' => 'Delete notes', 'is_correct' => false],
                        ['text' => 'Skip assessments', 'is_correct' => false],
                        ['text' => 'Ignore examples', 'is_correct' => false],
                    ],
                    'points' => 1,
                ]
            );

            $essay = Assessment::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => $course->title.' — Short Essay',
                ],
                [
                    'chapter_id' => $course->chapters()->skip(1)->first()?->id ?? $course->chapters()->first()?->id,
                    'type' => 'essay',
                    'description' => 'Write a short response demonstrating understanding.',
                    'pass_score' => 60,
                    'time_limit_minutes' => 45,
                    'status' => $course->status === 'published' ? 'published' : 'draft',
                ]
            );

            AssessmentQuestion::query()->updateOrCreate(
                [
                    'assessment_id' => $essay->id,
                    'sort_order' => 1,
                ],
                [
                    'question' => 'Explain one key concept from this course and how you would apply it in a real project.',
                    'model_answer' => 'A strong answer should define the concept clearly and include a practical application example.',
                    'points' => 10,
                ]
            );

            if ($course->status === 'published' && $students->isNotEmpty()) {
                foreach ($students as $studentIndex => $enrolStudent) {
                    $progress = [35, 60, 85, 100][$studentIndex % 4];
                    $status = $progress >= 100 ? 'completed' : 'active';

                    Enrolment::query()->updateOrCreate(
                        [
                            'course_id' => $course->id,
                            'student_id' => $enrolStudent->id,
                        ],
                        [
                            'status' => $status,
                            'amount_paid' => $course->price,
                            'progress' => $progress,
                            'enrolled_at' => now()->subDays(20 - ($studentIndex * 3)),
                            'completed_at' => $status === 'completed' ? now()->subDays(2) : null,
                        ]
                    );

                    AssessmentSubmission::query()->updateOrCreate(
                        [
                            'assessment_id' => $essay->id,
                            'student_id' => $enrolStudent->id,
                        ],
                        [
                            'status' => $studentIndex === 0 ? 'pending' : ($studentIndex === 1 ? 'ai_graded' : 'graded'),
                            'score' => $studentIndex === 0 ? null : (70 + ($studentIndex * 5)),
                            'feedback' => $studentIndex === 0 ? null : 'Good structure. Add more concrete examples.',
                            'submitted_at' => now()->subDays(5 - $studentIndex),
                            'graded_at' => $studentIndex === 0 ? null : now()->subDays(3 - $studentIndex),
                        ]
                    );
                }

                $course->update([
                    'enrolments_count' => Enrolment::query()->where('course_id', $course->id)->count(),
                ]);
            }
        }
    }
}
