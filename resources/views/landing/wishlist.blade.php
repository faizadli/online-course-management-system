@extends('layouts.landing')

@section('title', 'Wishlist — SkillForge')

@section('content')

@php
$wishlistCourses = [
    [
        'title' => 'UI/UX Design Fundamentals',
        'category' => 'Design & Creative',
        'instructor' => 'Sarah Chen',
        'image' => 'photo-1561070791-2526d30994b5',
        'price' => 49,
        'rating' => '4.9',
        'lessons' => 24,
        'hours' => 18,
        'badge' => 'Best Seller',
        'badgeColor' => 'bg-red-500',
    ],
    [
        'title' => 'Digital Marketing Mastery',
        'category' => 'Business & Marketing',
        'instructor' => 'Emily Rose',
        'image' => 'photo-1460925895917-afdab827c52f',
        'price' => 59,
        'rating' => '4.7',
        'lessons' => 28,
        'hours' => 22,
        'badge' => 'Hot',
        'badgeColor' => 'bg-orange-500',
    ],
    [
        'title' => 'Python Data Science Bootcamp',
        'category' => 'Tech & Programming',
        'instructor' => 'Mike Johnson',
        'image' => 'photo-1555949963-aa79d882981c',
        'price' => 89,
        'rating' => '4.8',
        'lessons' => 42,
        'hours' => 36,
        'badge' => 'Best Seller',
        'badgeColor' => 'bg-red-500',
    ],
];
$totalValue = array_sum(array_column($wishlistCourses, 'price'));
@endphp

<section class="checkout-page pt-20 lg:pt-24 pb-16 lg:pb-20 bg-white">
    <div class="page-container max-w-6xl">
        <div class="mb-8 reveal">
            <h1 class="font-display text-2xl sm:text-[1.75rem] font-bold text-gray-900 tracking-tight">My Wishlist</h1>
            <p class="mt-1.5 text-sm text-gray-500">{{ count($wishlistCourses) }} saved courses</p>
        </div>

        <div class="grid lg:grid-cols-5 gap-8 lg:gap-12 items-start">
            <div class="lg:col-span-3 space-y-4 reveal">
                @foreach($wishlistCourses as $course)
                <article class="checkout-panel wishlist-item">
                    <div class="wishlist-item-inner">
                        <div class="relative shrink-0">
                            <img src="https://images.unsplash.com/{{ $course['image'] }}?w=240&h=150&fit=crop"
                                alt="{{ $course['title'] }}"
                                class="wishlist-thumb">
                            @if($course['badge'])
                            <span class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold text-white rounded-full {{ $course['badgeColor'] }}">{{ $course['badge'] }}</span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-brand-dark">{{ $course['category'] }}</p>
                            <h3 class="mt-0.5 text-base font-semibold text-gray-900 leading-snug">{{ $course['title'] }}</h3>
                            <p class="mt-1 text-xs text-gray-400">{{ $course['instructor'] }} · {{ $course['lessons'] }} lessons · {{ $course['hours'] }}h</p>
                            <div class="mt-2 flex items-center gap-1 text-xs font-semibold text-amber-500">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $course['rating'] }}
                            </div>
                        </div>

                        <div class="wishlist-item-actions">
                            <button type="button" class="wishlist-remove-btn" aria-label="Remove from wishlist">
                                <svg class="w-5 h-5 text-brand-dark fill-brand-dark/20" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>
                            <p class="font-display text-lg font-bold text-gray-900 tabular-nums">${{ $course['price'] }}</p>
                            <a href="{{ route('cart') }}" class="wishlist-cart-btn">Add to cart</a>
                        </div>
                    </div>
                </article>
                @endforeach

                <a href="{{ route('courses') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-dark transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Browse more courses
                </a>
            </div>

            <aside class="lg:col-span-2 reveal lg:sticky lg:top-24">
                <div class="checkout-order">
                    <h2 class="checkout-order-title">Wishlist summary</h2>
                    <p class="checkout-order-meta">{{ count($wishlistCourses) }} courses saved</p>

                    <div class="checkout-order-lines pt-4">
                        @foreach($wishlistCourses as $course)
                        <div class="checkout-order-line">
                            <span class="truncate pr-3">{{ $course['title'] }}</span>
                            <span class="tabular-nums shrink-0">${{ $course['price'] }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="checkout-order-total">
                        <span>Total value</span>
                        <span class="tabular-nums">${{ $totalValue }}</span>
                    </div>

                    <a href="{{ route('cart') }}" class="checkout-cta mt-6">
                        Add all to cart
                    </a>

                    <p class="mt-4 text-center text-xs text-gray-400">
                        Save courses you love and enroll when you're ready.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</section>

@endsection
