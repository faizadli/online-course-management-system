<?php

namespace Tests\Unit;

use App\Models\AssessmentQuestion;
use App\Services\GeminiException;
use App\Services\GeminiScoringService;
use App\Services\PaymentGateway;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    public function test_luhn_validation(): void
    {
        $this->assertTrue(PaymentGateway::passesLuhn('4242 4242 4242 4242'));
        $this->assertTrue(PaymentGateway::passesLuhn('5555555555554444'));
        $this->assertFalse(PaymentGateway::passesLuhn('4242 4242 4242 4241'));
        $this->assertFalse(PaymentGateway::passesLuhn('1234'));
    }

    public function test_gateway_declines_the_decline_test_card_and_approves_others(): void
    {
        $gateway = new PaymentGateway();

        $this->assertTrue($gateway->charge('card', 10, ['number' => '4242424242424242'])['success']);
        $this->assertFalse($gateway->charge('card', 10, ['number' => '4000000000000002'])['success']);
        $this->assertTrue($gateway->charge('paypal', 10)['success']);
        $this->assertSame('Visa', PaymentGateway::brand('4242424242424242'));
        $this->assertSame('Mastercard', PaymentGateway::brand('5555555555554444'));
    }

    public function test_html_sanitizer_removes_dangerous_markup(): void
    {
        $dirty = '<p onclick="x()">Hi</p><script>alert(1)</script><img src="javascript:alert(1)" onerror="x()"><a href="https://ok.com">ok</a><iframe src="https://evil"></iframe>';
        $clean = HtmlSanitizer::clean($dirty);

        $this->assertStringContainsString('<p>Hi</p>', $clean);
        $this->assertStringContainsString('https://ok.com', $clean);
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('iframe', $clean);
    }

    public function test_default_rubric_is_used_when_instructor_defines_none(): void
    {
        $question = new AssessmentQuestion(['question' => 'Q', 'model_answer' => 'A', 'points' => 10]);
        $rubric = $question->effectiveRubric();

        $this->assertSame(100, array_sum(array_column($rubric, 'weight')));
        $this->assertCount(3, $rubric);
    }

    public function test_scoring_service_scales_percentages_to_question_points(): void
    {
        config(['services.gemini.key' => 'k', 'services.gemini.model' => 'm', 'services.gemini.fallback_model' => null]);
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode([
                'criteria' => [
                    ['name' => 'Concept', 'percent' => 100, 'comment' => 'Good'],
                    ['name' => 'Clarity', 'percent' => 50, 'comment' => 'OK'],
                ],
                'feedback' => 'Feedback text',
            ])]]]]],
        ])]);

        $question = new AssessmentQuestion([
            'question' => 'Q', 'points' => 20,
            'rubric' => [['criteria' => 'Concept', 'description' => '', 'weight' => 75], ['criteria' => 'Clarity', 'description' => '', 'weight' => 25]],
        ]);

        $result = (new GeminiScoringService())->scoreAnswer($question, 'answer');

        // 20 * 0.75 * 1.0 + 20 * 0.25 * 0.5 = 15 + 2.5
        $this->assertEquals(17.5, $result['score']);
        $this->assertSame('Feedback text', $result['feedback']);
    }

    public function test_scoring_service_clamps_out_of_range_percentages(): void
    {
        config(['services.gemini.key' => 'k', 'services.gemini.model' => 'm', 'services.gemini.fallback_model' => null]);
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode([
                'criteria' => [['name' => 'Concept', 'percent' => 250, 'comment' => 'x']],
                'feedback' => 'f',
            ])]]]]],
        ])]);

        $question = new AssessmentQuestion(['question' => 'Q', 'points' => 10, 'rubric' => [['criteria' => 'Concept', 'description' => '', 'weight' => 100]]]);
        $result = (new GeminiScoringService())->scoreAnswer($question, 'answer');

        $this->assertEquals(10.0, $result['score'], 'A manipulated model response can never exceed the maximum score.');
    }

    public function test_scoring_service_requires_an_api_key(): void
    {
        config(['services.gemini.key' => null]);

        $this->expectException(GeminiException::class);
        (new GeminiScoringService())->scoreAnswer(new AssessmentQuestion(['question' => 'Q', 'points' => 5]), 'answer');
    }

    public function test_scoring_service_rejects_malformed_responses(): void
    {
        config(['services.gemini.key' => 'k', 'services.gemini.model' => 'm', 'services.gemini.fallback_model' => null]);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'not json']]]]]])]);

        $this->expectException(GeminiException::class);
        (new GeminiScoringService())->scoreAnswer(new AssessmentQuestion(['question' => 'Q', 'points' => 5]), 'answer');
    }
}
