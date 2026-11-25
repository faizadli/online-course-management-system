<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\ChapterContent;
use App\Models\ContentCompletion;
use App\Models\Course;
use App\Models\Enrolment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo learning activity: enrolments with payments, lesson completions,
 * MCQ results and essay submissions (AI-scored and pending).
 */
class LearningActivitySeeder extends Seeder
{
    private int $referenceCounter = 0;

    public function run(): void
    {
        // [student email, course title, lesson completion %, mcq correct answers (null = not taken), essay state (null|ai|pending), days ago]
        $activity = [
            ['student@example.com', 'UI/UX Design Fundamentals', 60, 3, 'ai', 21],
            ['student@example.com', 'Full Stack Web Development', 35, 2, null, 14],
            ['student@example.com', 'Photography for Beginners', 100, 2, 'ai', 30],
            ['student@example.com', 'Study Skills Starter Kit', 20, null, null, 5],
            ['lisa@example.com', 'UI/UX Design Fundamentals', 100, 3, 'ai', 40],
            ['lisa@example.com', 'Digital Marketing Mastery', 50, 2, 'ai', 18],
            ['david@example.com', 'Full Stack Web Development', 80, 3, 'pending', 25],
            ['david@example.com', 'Python Data Science Bootcamp', 30, 1, null, 12],
            ['maya@example.com', 'UI/UX Design Fundamentals', 40, 2, 'pending', 9],
            ['maya@example.com', 'Digital Marketing Mastery', 75, 3, 'ai', 15],
            ['budi@example.com', 'Photography for Beginners', 55, 1, null, 7],
            ['budi@example.com', 'Financial Literacy 101', 100, 2, 'ai', 28],
            ['budi@example.com', 'Graphic Design with Figma', 25, null, null, 3],
        ];

        foreach ($activity as [$email, $title, $completion, $mcqCorrect, $essayState, $daysAgo]) {
            $student = User::query()->where('email', $email)->first();
            $course = Course::query()->where('title', $title)->first();

            if (! $student || ! $course) {
                continue;
            }

            $this->seedEnrolment($student, $course, $completion, $mcqCorrect, $essayState, $daysAgo);
        }

        Course::query()->get()->each(function (Course $course) {
            $course->update(['enrolments_count' => $course->enrolments()->count()]);
        });
    }

    private function seedEnrolment(User $student, Course $course, int $completion, ?int $mcqCorrect, ?string $essayState, int $daysAgo): void
    {
        $enrolledAt = now()->subDays($daysAgo);

        $enrolment = Enrolment::query()->updateOrCreate(
            ['course_id' => $course->id, 'student_id' => $student->id],
            ['status' => 'active', 'amount_paid' => $course->price, 'progress' => 0, 'enrolled_at' => $enrolledAt, 'completed_at' => null]
        );

        $free = (float) $course->price <= 0;

        Payment::query()->updateOrCreate(
            ['enrolment_id' => $enrolment->id],
            [
                'reference' => 'PAY-DEMO'.str_pad((string) (++$this->referenceCounter), 4, '0', STR_PAD_LEFT).strtoupper(Str::random(3)),
                'amount' => $course->price,
                'method' => $free ? 'free' : ($this->referenceCounter % 4 === 0 ? 'paypal' : 'card'),
                'status' => 'paid',
                'card_brand' => $free || $this->referenceCounter % 4 === 0 ? ($free ? null : 'PayPal') : ($this->referenceCounter % 2 ? 'Visa' : 'Mastercard'),
                'card_last4' => $free || $this->referenceCounter % 4 === 0 ? null : ($this->referenceCounter % 2 ? '4242' : '4444'),
                'paid_at' => $enrolledAt,
            ]
        );

        // Lesson completions
        $lessons = ChapterContent::query()
            ->whereIn('chapter_id', $course->chapters()->pluck('id'))
            ->orderBy('chapter_id')->orderBy('sort_order')
            ->get();

        $take = (int) ceil($lessons->count() * $completion / 100);
        foreach ($lessons->take($take) as $lesson) {
            ContentCompletion::query()->updateOrCreate(
                ['enrolment_id' => $enrolment->id, 'chapter_content_id' => $lesson->id],
                ['completed_at' => $enrolledAt->copy()->addDays(1)]
            );
        }

        // MCQ
        if ($mcqCorrect !== null) {
            $mcq = Assessment::query()->where('course_id', $course->id)->where('type', 'mcq')->with('questions')->first();
            if ($mcq) {
                $this->seedMcqSubmission($mcq, $student, $mcqCorrect, $enrolledAt->copy()->addDays(2));
            }
        }

        // Essay
        if ($essayState) {
            $essay = Assessment::query()->where('course_id', $course->id)->where('type', 'essay')->with('questions')->first();
            if ($essay) {
                $this->seedEssaySubmission($essay, $student, $essayState, $enrolledAt->copy()->addDays(3));
            }
        }

        $enrolment->refresh()->recalculateProgress();
    }

    private function seedMcqSubmission(Assessment $assessment, User $student, int $correctCount, $when): void
    {
        $answers = [];
        $breakdown = [];
        $earned = 0;
        $total = max(1, $assessment->questions->sum('points'));

        foreach ($assessment->questions->values() as $i => $question) {
            $options = $question->options ?? [];
            $correctIndex = collect($options)->search(fn ($o) => ! empty($o['is_correct']));
            $isCorrect = $i < $correctCount;
            $selected = $isCorrect ? $correctIndex : (($correctIndex + 1) % max(1, count($options)));

            $answers[(string) $question->id] = (string) $selected;
            $earned += $isCorrect ? $question->points : 0;

            $breakdown[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'selected_text' => $options[$selected]['text'] ?? null,
                'correct_text' => $options[$correctIndex]['text'] ?? null,
                'is_correct' => $isCorrect,
                'points' => $isCorrect ? (int) $question->points : 0,
                'max' => (int) $question->points,
                'explanation' => $question->explanation,
            ];
        }

        $score = round(($earned / $total) * 100, 1);
        $correct = collect($breakdown)->where('is_correct', true)->count();

        AssessmentSubmission::query()->updateOrCreate(
            ['assessment_id' => $assessment->id, 'student_id' => $student->id],
            [
                'status' => 'graded',
                'answers' => $answers,
                'score' => $score,
                'feedback' => $score >= $assessment->pass_score
                    ? "Great job! You answered {$correct} of ".count($breakdown)." questions correctly ({$score}%) and passed this MCQ assessment."
                    : "You answered {$correct} of ".count($breakdown)." questions correctly ({$score}%). Review the course materials and try to improve next time.",
                'ai_details' => ['mcq' => $breakdown],
                'submitted_at' => $when,
                'graded_at' => $when,
            ]
        );
    }

    private function seedEssaySubmission(Assessment $assessment, User $student, string $state, $when): void
    {
        $question = $assessment->questions->first();
        if (! $question) {
            return;
        }

        $answerText = 'In my own words: '.Str::limit((string) $question->model_answer, 400, '').' I would also relate this to a practical example from everyday work to make the idea concrete.';
        $answers = [(string) $question->id => $answerText];

        if ($state === 'pending') {
            AssessmentSubmission::query()->updateOrCreate(
                ['assessment_id' => $assessment->id, 'student_id' => $student->id],
                [
                    'status' => 'pending',
                    'answers' => $answers,
                    'score' => null,
                    'feedback' => null,
                    'ai_details' => null,
                    'ai_model' => null,
                    'ai_error' => 'AI scoring was temporarily unavailable when this answer was submitted (demo data).',
                    'submitted_at' => $when,
                    'graded_at' => null,
                ]
            );

            return;
        }

        // Pre-computed result in the same structure produced by GeminiScoringService.
        $rubric = $question->effectiveRubric();
        $totalWeight = max(1, array_sum(array_column($rubric, 'weight')));
        $percents = [90, 80, 85];
        $criteria = [];
        $earned = 0.0;

        foreach ($rubric as $i => $row) {
            $percent = $percents[$i % 3] - (($student->id + $i) % 3) * 5;
            $max = round($question->points * $row['weight'] / $totalWeight, 2);
            $got = round($max * $percent / 100, 2);
            $earned += $got;
            $criteria[] = [
                'name' => $row['criteria'],
                'weight' => $row['weight'],
                'percent' => $percent,
                'score' => $got,
                'max' => $max,
                'comment' => ['Key ideas are explained accurately.', 'A relevant example is given; add more detail.', 'Clear and well organised.'][$i % 3],
            ];
        }

        $score = round(($earned / $question->points) * 100, 1);
        $feedbackText = 'Good answer overall: the main concept is correct and clearly explained. Strengthen it by adding a more specific, real-world example and a short concluding sentence.';

        AssessmentSubmission::query()->updateOrCreate(
            ['assessment_id' => $assessment->id, 'student_id' => $student->id],
            [
                'status' => 'ai_graded',
                'answers' => $answers,
                'score' => $score,
                'feedback' => "Well done! You scored {$score}% and passed this assessment (pass mark {$assessment->pass_score}%). Q1: {$feedbackText}",
                'ai_details' => ['questions' => [[
                    'question_id' => $question->id,
                    'question' => $question->question,
                    'answer' => $answerText,
                    'score' => round($earned, 2),
                    'max' => (int) $question->points,
                    'feedback' => $feedbackText,
                    'criteria' => $criteria,
                ]]],
                'ai_model' => 'gemini-2.5-flash',
                'ai_error' => null,
                'submitted_at' => $when,
                'graded_at' => $when,
            ]
        );
    }
}
