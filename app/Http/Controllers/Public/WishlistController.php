<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\Basket;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $courses = Basket::courses(Basket::WISHLIST);

        return view('landing.wishlist', [
            'courses' => $courses,
            'totalValue' => $courses->sum('price'),
        ]);
    }

    public function toggle(Course $course): RedirectResponse
    {
        abort_unless($course->status === 'published', 404);

        if (Basket::has(Basket::WISHLIST, $course->id)) {
            Basket::remove(Basket::WISHLIST, $course->id);

            return back()->with('success', 'Removed from your wishlist.');
        }

        Basket::add(Basket::WISHLIST, $course->id);

        return back()->with('success', 'Saved to your wishlist.');
    }

    public function moveAllToCart(): RedirectResponse
    {
        foreach (Basket::courses(Basket::WISHLIST) as $course) {
            Basket::add(Basket::CART, $course->id);
        }

        Basket::clear(Basket::WISHLIST);

        return redirect()->route('cart')->with('success', 'All wishlist courses were added to your cart.');
    }

    public function moveToCart(Course $course): RedirectResponse
    {
        abort_unless($course->status === 'published', 404);

        Basket::add(Basket::CART, $course->id);
        Basket::remove(Basket::WISHLIST, $course->id);

        return redirect()->route('cart')->with('success', "\"{$course->title}\" was moved to your cart.");
    }
}
