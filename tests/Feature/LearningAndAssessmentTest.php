<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AssessmentSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class LearningAndAssessmentTest extends TestCase
{
    use BuildsFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.key' => 'test-key', 'services.gemini.model' => 'gemini-test', 'services.gemini.fallback_model' => 'gemini-fallback']);
    }

    // ----- Lessons & progress ----------------------------------------------

    public function test_enrolled_student_can_open_each_lesson_type(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $this->enrol($student, $course);
        [$video, $text] = $course->chapters()->first()->contents()->get()->all();

        $this->actingAs($student)->get("/dashboard/student/courses/{$course->id}")->assertOk()->assertSee('Intro video');
        $this->actingAs($student)->get("/dashboard/student/courses/{$course->id}/lessons/{$video->id}")
            ->assertOk()->assertSee('youtube.com/embed/aqz-KE-bpKQ', false);
        $this->actingAs($student)->get("/dashboard/student/courses/{$course->id}/lessons/{$text->id}")
            ->assertOk()->assertSee('<strong>world</strong>', false);
    }

    public function test_non_enrolled_student_cannot_view_course_or_lessons(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $lesson = $course->chapters()->first()->contents()->first();

        $this->actingAs($student)->get("/dashboard/student/courses/{$course->id}")->assertForbidden();
        $this->actingAs($student)->get("/dashboard/student/courses/{$course->id}/lessons/{$lesson->id}")->assertForbidden();
    }

    public function test_completing_lessons_updates_progress_until_course_is_completed(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $enrolment = $this->enrol($student, $course);
        [$video, $text] = $course->chapters()->first()->contents()->get()->all();

        $this->actingAs($student)->post("/dashboard/student/courses/{$course->id}/lessons/{$video->id}/complete");
        $this->assertSame(50, $enrolment->fresh()->progress);
        $this->assertSame('active', $enrolment->fresh()->status);

        $this->actingAs($student)->post("/dashboard/student/courses/{$course->id}/lessons/{$text->id}/complete");
        $this->assertSame(100, $enrolment->fresh()->progress);
        $this->assertSame('completed', $enrolment->fresh()->status);
        $this->assertNotNull($enrolment->fresh()->completed_at);

        // Undo lowers the progress again
        $this->actingAs($student)->post("/dashboard/student/courses/{$course->id}/lessons/{$text->id}/complete", ['undo' => 1]);
        $this->assertSame(50, $enrolment->fresh()->progress);
    }

    // ----- MCQ ---------------------------------------------------------------

    public function test_mcq_is_scored_automatically(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $this->enrol($student, $course);
        $mcq = $this->makeMcq($course);
        [$q1, $q2] = $mcq->questions->all();

        $this->actingAs($student)->post("/dashboard/student/assessments/{$mcq->id}/submit", [
            'answers' => [$q1->id => '0', $q2->id => '1'],
        ]);

        $submission = AssessmentSubmission::query()->where('student_id', $student->id)->first();
        $this->assertSame('graded', $submission->status);
        $this->assertEquals(50.0, (float) $submission->score);
        $this->assertCount(2, $submission->ai_details['mcq']);
        $this->assertTrue($submission->ai_details['mcq'][0]['is_correct']);
        $this->assertFalse($submission->ai_details['mcq'][1]['is_correct']);
    }

    public function test_mcq_requires_all_questions_and_blocks_resubmission(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $this->enrol($student, $course);
        $mcq = $this->makeMcq($course);
        [$q1, $q2] = $mcq->questions->all();

        $this->actingAs($student)->post("/dashboard/student/assessments/{$mcq->id}/submit", ['answers' => [$q1->id => '0']])
            ->assertSessionHasErrors('answers');
        $this->assertSame(0, AssessmentSubmission::query()->count());

        $answers = ['answers' => [$q1->id => '0', $q2->id => '0']];
        $this->actingAs($student)->post("/dashboard/student/assessments/{$mcq->id}/submit", $answers);
        $this->actingAs($student)->post("/dashboard/student/assessments/{$mcq->id}/submit", $answers)
            ->assertSessionHasErrors('assessment');
        $this->assertSame(1, AssessmentSubmission::query()->count());
    }

    public function test_assessment_progress_counts_towards_course_completion(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $enrolment = $this->enrol($student, $course);
        $mcq = $this->makeMcq($course);
        [$q1, $q2] = $mcq->questions->all();

        $this->actingAs($student)->post("/dashboard/student/assessments/{$mcq->id}/submit", ['answers' => [$q1->id => '0', $q2->id => '0']]);

        // 1 of 3 items (2 lessons + 1 assessment) done = 33%
        $this->assertSame(33, $enrolment->fresh()->progress);
    }

    public function test_student_cannot_take_unpublished_or_unenrolled_assessments(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $draft = $this->makeMcq($course, ['status' => 'draft']);
        $published = $this->makeMcq($course, ['title' => 'Other']);
        $this->enrol($student, $course);

        $this->actingAs($student)->get("/dashboard/student/assessments/{$draft->id}")->assertNotFound();

        $outsider = $this->makeUser();
        $this->actingAs($outsider)->get("/dashboard/student/assessments/{$published->id}")->assertForbidden();
    }

    public function test_expired_time_limit_rejects_submission(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $this->enrol($student, $course);
        $mcq = $this->makeMcq($course, ['time_limit_minutes' => 5]);
        [$q1, $q2] = $mcq->questions->all();

        $this->actingAs($student)->get("/dashboard/student/assessments/{$mcq->id}")->assertOk()->assertSee('Time remaining');

        $this->withSession(["assessment_started.{$mcq->id}" => now()->subMinutes(30)->timestamp])
            ->actingAs($student)
            ->post("/dashboard/student/assessments/{$mcq->id}/submit", ['answers' => [$q1->id => '0', $q2->id => '0']])
            ->assertSessionHasErrors('assessment');

        $this->assertSame(0, AssessmentSubmission::query()->count());
    }

    // ----- Essay + Gemini ----------------------------------------------------

    public function test_essay_is_scored_by_gemini_with_rubric_breakdown(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse([100, 80, 60], 'Well structured answer.'))]);

        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $this->enrol($student, $course);
        $essay = $this->makeEssay($course);
        $question = $essay->questions->first();

        $this->actingAs($student)->post("/dashboard/student/assessments/{$essay->id}/submit", [
            'answers' => [$question->id => 'A stack is LIFO like plates, a queue is FIFO like a line.'],
        ]);

        $submission = AssessmentSubmission::query()->where('student_id', $student->id)->first();
        $this->assertSame('ai_graded', $submission->status);
        $this->assertSame('gemini-test', $submission->ai_model);

        // 50% * 100 + 30% * 80 + 20% * 60 = 50 + 24 + 12 = 86 of 100
        $this->assertEquals(86.0, (float) $submission->score);

        $criteria = $submission->ai_details['questions'][0]['criteria'];
        $this->assertCount(3, $criteria);
        $this->assertEquals(5.0, $criteria[0]['score']);
        $this->assertEquals(5.0, $criteria[0]['max']);
        $this->assertStringContainsString('Well structured answer.', $submission->feedback);

        // The request must carry the rubric + answer and authenticate with the API key header.
        Http::assertSent(function ($request) use ($question) {
            $prompt = $request['contents'][0]['parts'][0]['text'];

            return str_contains($request->url(), 'models/gemini-test:generateContent')
                && $request->header('x-goog-api-key')[0] === 'test-key'
                && str_contains($prompt, 'Correctness of Concept')
                && str_contains($prompt, 'A stack is LIFO like plates')
                && str_contains($prompt, $question->model_answer);
        });

        $this->actingAs($student)->get("/dashboard/student/feedback/{$submission->id}")
            ->assertOk()->assertSee('Rubric breakdown')->assertSee('Scored by Google Gemini')->assertSee('Correctness of Concept');
    }

    public function test_gemini_failure_falls_back_to_manual_grading(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Quota exceeded']], 429)]);

        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $this->enrol($student, $course);
        $essay = $this->makeEssay($course);
        $question = $essay->questions->first();

        $response = $this->actingAs($student)->post("/dashboard/student/assessments/{$essay->id}/submit", [
            'answers' => [$question->id => 'My answer'],
        ]);

        $submission = AssessmentSubmission::query()->where('student_id', $student->id)->first();
        $this->assertNotNull($submission, 'Submission must still be stored when AI is unavailable.');
        $this->assertSame('pending', $submission->status);
        $this->assertNull($submission->score);
        $this->assertStringContainsString('Quota exceeded', $submission->ai_error);
        $response->assertRedirect(route('dashboard.student.feedback.show', $submission));
    }

    public function test_primary_model_failure_uses_fallback_model(): void
    {
        Http::fake([
            '*models/gemini-test:*' => Http::response(['error' => ['message' => 'Model overloaded']], 503),
            '*models/gemini-fallback:*' => Http::response($this->geminiResponse([100, 100, 100])),
        ]);

        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $this->enrol($student, $course);
        $essay = $this->makeEssay($course);
        $question = $essay->questions->first();

        $this->actingAs($student)->post("/dashboard/student/assessments/{$essay->id}/submit", ['answers' => [$question->id => 'Great answer']]);

        $submission = AssessmentSubmission::query()->first();
        $this->assertSame('ai_graded', $submission->status);
        $this->assertSame('gemini-fallback', $submission->ai_model);
        $this->assertEquals(100.0, (float) $submission->score);
    }

    public function test_instructor_can_rescore_pending_submission_and_override_grade(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent($instructor);
        $this->enrol($student, $course);
        $essay = $this->makeEssay($course);
        $question = $essay->questions->first();

        $submission = AssessmentSubmission::query()->create([
            'assessment_id' => $essay->id, 'student_id' => $student->id, 'status' => 'pending',
            'answers' => [(string) $question->id => 'Stack is LIFO, queue is FIFO.'],
            'ai_error' => 'Earlier failure', 'submitted_at' => now(),
        ]);

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse([100, 100, 100]))]);

        $this->actingAs($instructor)->post("/dashboard/instructor/submissions/{$submission->id}/rescore")->assertSessionHas('success');
        $this->assertSame('ai_graded', $submission->fresh()->status);
        $this->assertNull($submission->fresh()->ai_error);

        $this->actingAs($instructor)->put("/dashboard/instructor/submissions/{$submission->id}/grade", ['score' => 72.5, 'feedback' => 'Adjusted manually.']);
        $this->assertSame('graded', $submission->fresh()->status);
        $this->assertEquals(72.5, (float) $submission->fresh()->score);

        // Another instructor cannot touch it
        $stranger = $this->makeUser(UserRole::Instructor);
        $this->actingAs($stranger)->post("/dashboard/instructor/submissions/{$submission->id}/rescore")->assertForbidden();
    }

    public function test_empty_essay_answers_are_rejected(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();
        $this->enrol($student, $course);
        $essay = $this->makeEssay($course);
        $question = $essay->questions->first();

        Http::fake();
        $this->actingAs($student)->post("/dashboard/student/assessments/{$essay->id}/submit", ['answers' => [$question->id => '']])
            ->assertSessionHasErrors('answers');
        Http::assertNothingSent();
    }

    // ----- Instructor assessment authoring -----------------------------------

    public function test_instructor_builds_assessments_with_rubric_validation(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourseWithContent($instructor);
        $chapter = $course->chapters()->first();

        $this->actingAs($instructor)
            ->post("/dashboard/instructor/builder/{$course->id}/chapters/{$chapter->id}/assessments", [
                'title' => 'Essay Test', 'type' => 'essay', 'pass_score' => 60,
            ]);

        $assessment = $course->assessments()->where('title', 'Essay Test')->first();
        $this->assertNotNull($assessment);
        $this->assertSame($chapter->id, $assessment->chapter_id);

        $url = "/dashboard/instructor/assessments/{$assessment->id}/questions";

        // Weights not totalling 100 are rejected
        $this->actingAs($instructor)->post($url, [
            'question' => 'Explain X', 'points' => 10, 'model_answer' => 'X is Y',
            'rubric' => [['criteria' => 'Concept', 'weight' => 60], ['criteria' => 'Clarity', 'weight' => 30]],
        ])->assertSessionHasErrors('rubric');

        $this->actingAs($instructor)->post($url, [
            'question' => 'Explain X', 'points' => 10, 'model_answer' => 'X is Y',
            'rubric' => [['criteria' => 'Concept', 'description' => 'Correct', 'weight' => 60], ['criteria' => 'Clarity', 'weight' => 40]],
        ])->assertSessionHasNoErrors();

        $question = $assessment->questions()->first();
        $this->assertCount(2, $question->rubric);

        // Publishing needs at least one question
        $empty = $this->makeMcq($course, ['title' => 'Empty', 'status' => 'draft']);
        $empty->questions()->delete();
        $this->actingAs($instructor)->patch("/dashboard/instructor/assessments/{$empty->id}/toggle")->assertSessionHasErrors('assessment');
    }

    public function test_mcq_question_requires_two_options_and_correct_answer(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourseWithContent($instructor);
        $mcq = $this->makeMcq($course, ['title' => 'Fresh']);

        $this->actingAs($instructor)->post("/dashboard/instructor/assessments/{$mcq->id}/questions", [
            'question' => 'Pick one', 'options' => [['text' => 'Only one']], 'correct_option' => 0,
        ])->assertSessionHasErrors('options');

        $this->actingAs($instructor)->post("/dashboard/instructor/assessments/{$mcq->id}/questions", [
            'question' => 'Pick one', 'options' => [['text' => 'A'], ['text' => 'B']], 'correct_option' => 1, 'explanation' => 'Because B.',
        ])->assertSessionHasNoErrors();

        $added = $mcq->questions()->where('question', 'Pick one')->first();
        $this->assertTrue($added->options[1]['is_correct']);
        $this->assertSame('Because B.', $added->explanation);
    }
}
