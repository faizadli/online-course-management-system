<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Enrolment;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsFixtures;
use Tests\TestCase;

class CatalogAndCheckoutTest extends TestCase
{
    use BuildsFixtures, RefreshDatabase;

    private function card(array $override = []): array
    {
        return array_merge([
            'method' => 'card',
            'card_number' => '4242 4242 4242 4242',
            'card_name' => 'Test Student',
            'card_expiry' => '12 / 39',
            'card_cvc' => '123',
            'terms' => '1',
        ], $override);
    }

    public function test_catalogue_lists_only_published_courses(): void
    {
        $published = $this->makeCourse(null, ['title' => 'Published Course']);
        $this->makeCourse(null, ['title' => 'Hidden Draft', 'status' => 'draft']);

        $this->get('/courses')
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee('Hidden Draft');
    }

    public function test_draft_course_detail_page_is_not_public(): void
    {
        $draft = $this->makeCourse(null, ['status' => 'draft']);

        $this->get('/courses/'.$draft->slug)->assertNotFound();
    }

    public function test_published_course_detail_page_shows_curriculum(): void
    {
        $course = $this->makeCourseWithContent();

        $this->get('/courses/'.$course->slug)
            ->assertOk()
            ->assertSee($course->title)
            ->assertSee('Chapter One')
            ->assertSee('Intro video');
    }

    public function test_guest_can_add_to_cart_but_checkout_requires_login(): void
    {
        $course = $this->makeCourse();

        $this->post('/cart/'.$course->slug)->assertRedirect(route('cart'));
        $this->get('/cart')->assertOk()->assertSee($course->title);
        $this->get('/checkout')->assertRedirect(route('login'));
    }

    public function test_student_pays_by_card_and_gets_enrolled(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourseWithContent();

        $this->actingAs($student)->post('/cart/'.$course->slug.'/buy')->assertRedirect(route('checkout'));
        $response = $this->actingAs($student)->post('/checkout', $this->card());

        $enrolment = Enrolment::query()->where('student_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($enrolment, 'Enrolment record must be created after payment.');
        $this->assertSame('active', $enrolment->status);

        $payment = Payment::query()->where('enrolment_id', $enrolment->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('paid', $payment->status);
        $this->assertSame('4242', $payment->card_last4);
        $this->assertEquals(50, $payment->amount);

        $response->assertRedirect(route('checkout.receipt', $payment->reference));
        $this->actingAs($student)->get(route('checkout.receipt', $payment->reference))->assertOk()->assertSee($payment->reference);

        $this->assertSame(1, $course->fresh()->enrolments_count);
        $this->assertSame([], session('cart', []), 'Cart must be emptied after payment.');
    }

    public function test_declined_card_creates_no_enrolment(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourse();

        $this->actingAs($student)->post('/cart/'.$course->slug);
        $this->actingAs($student)
            ->post('/checkout', $this->card(['card_number' => '4000 0000 0000 0002']))
            ->assertSessionHasErrors('payment');

        $this->assertSame(0, Enrolment::query()->count());
        $this->assertSame(0, Payment::query()->count());
    }

    public function test_invalid_card_number_and_expired_card_are_rejected(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourse();
        $this->actingAs($student)->post('/cart/'.$course->slug);

        $this->actingAs($student)->post('/checkout', $this->card(['card_number' => '1234 5678 9012 3456']))
            ->assertSessionHasErrors('card_number');

        $this->actingAs($student)->post('/checkout', $this->card(['card_expiry' => '01 / 20']))
            ->assertSessionHasErrors('card_expiry');

        $this->actingAs($student)->post('/checkout', $this->card(['terms' => null]))
            ->assertSessionHasErrors('terms');

        $this->assertSame(0, Enrolment::query()->count());
    }

    public function test_paypal_payment_is_approved(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourse();
        $this->actingAs($student)->post('/cart/'.$course->slug);

        $this->actingAs($student)->post('/checkout', ['method' => 'paypal', 'terms' => '1']);

        $payment = Payment::query()->first();
        $this->assertNotNull($payment);
        $this->assertSame('paypal', $payment->method);
    }

    public function test_free_course_can_be_enrolled_without_card(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourse(null, ['price' => 0]);

        $this->actingAs($student)->post('/cart/'.$course->slug.'/buy');
        $this->actingAs($student)->post('/checkout', ['terms' => '1']);

        $this->assertSame(1, Enrolment::query()->count());
        $this->assertSame('free', Payment::query()->first()->method);
    }

    public function test_multiple_courses_can_be_purchased_in_one_checkout(): void
    {
        $student = $this->makeUser();
        $a = $this->makeCourse();
        $b = $this->makeCourse();

        $this->actingAs($student)->post('/cart/'.$a->slug);
        $this->actingAs($student)->post('/cart/'.$b->slug);
        $this->actingAs($student)->post('/checkout', $this->card());

        $this->assertSame(2, Enrolment::query()->where('student_id', $student->id)->count());
        $this->assertSame(2, Payment::query()->count());
    }

    public function test_already_enrolled_course_cannot_be_added_again(): void
    {
        $student = $this->makeUser();
        $course = $this->makeCourse();
        $this->enrol($student, $course);

        $this->actingAs($student)->post('/cart/'.$course->slug)->assertSessionHasErrors('cart');
    }

    public function test_instructor_cannot_use_checkout(): void
    {
        $instructor = $this->makeUser(UserRole::Instructor);
        $course = $this->makeCourse();
        $this->actingAs($instructor)->post('/cart/'.$course->slug);

        $this->actingAs($instructor)->get('/checkout')->assertForbidden();
    }

    public function test_wishlist_toggle_and_move_to_cart(): void
    {
        $course = $this->makeCourse();

        $this->post('/wishlist/'.$course->slug)->assertSessionHas('success');
        $this->get('/wishlist')->assertSee($course->title);

        $this->post('/wishlist/'.$course->slug.'/cart')->assertRedirect(route('cart'));
        $this->get('/wishlist')->assertSee('Your wishlist is empty');
        $this->get('/cart')->assertSee($course->title);
    }
}
