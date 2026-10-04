<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrolment;
use App\Support\Basket;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $courses = Basket::courses(Basket::CART);

        return view('landing.cart', [
            'courses' => $courses,
            'total' => $courses->sum('price'),
        ]);
    }

    public function add(Course $course): RedirectResponse
    {
        $this->guardPurchasable($course);

        if ($error = $this->alreadyOwned($course)) {
            return back()->withErrors(['cart' => $error]);
        }

        Basket::add(Basket::CART, $course->id);

        return redirect()->route('cart')->with('success', "\"{$course->title}\" was added to your cart.");
    }

    /** "Enrol Now": add to cart and go straight to checkout. */
    public function buyNow(Course $course): RedirectResponse
    {
        $this->guardPurchasable($course);

        if ($error = $this->alreadyOwned($course)) {
            return back()->withErrors(['cart' => $error]);
        }

        Basket::add(Basket::CART, $course->id);

        return redirect()->route('checkout');
    }

    public function remove(Course $course): RedirectResponse
    {
        Basket::remove(Basket::CART, $course->id);

        return back()->with('success', 'Course removed from your cart.');
    }

    private function guardPurchasable(Course $course): void
    {
        abort_unless($course->status === 'published', 404);
    }

    private function alreadyOwned(Course $course): ?string
    {
        if (! auth()->check()) {
            return null;
        }

        if ($course->instructor_id === auth()->id()) {
            return 'You cannot enrol in your own course.';
        }

        $owned = Enrolment::query()
            ->where('course_id', $course->id)
            ->where('student_id', auth()->id())
            ->exists();

        return $owned ? 'You are already enrolled in this course.' : null;
    }
}
