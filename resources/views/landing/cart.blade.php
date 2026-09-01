@extends('layouts.landing')

@section('title', 'Shopping Cart — SkillForge')

@section('content')

@php
$cartCourses = [
    [
        'title' => 'UI/UX Design Fundamentals',
        'category' => 'Design & Creative',
        'instructor' => 'Sarah Chen',
        'image' => 'photo-1561070791-2526d30994b5',
        'price' => 49,
        'lessons' => 24,
        'hours' => 18,
    ],
    [
        'title' => 'Full Stack Web Development',
        'category' => 'Tech & Programming',
        'instructor' => 'John Doe',
        'image' => 'photo-1498050108023-c5249f4df085',
        'price' => 79,
        'lessons' => 36,
        'hours' => 42,
    ],
];
$subtotal = array_sum(array_column($cartCourses, 'price'));
$discount = 10;
$total = $subtotal - $discount;
@endphp

<section class="checkout-page pt-20 lg:pt-24 pb-16 lg:pb-20 bg-white">
    <div class="page-container max-w-6xl">
        {{-- Step indicator --}}
        <div class="checkout-steps reveal">
            <div class="checkout-steps-track">
                <div class="checkout-steps-progress" style="width: 50%"></div>
            </div>
            <div class="checkout-steps-list">
                <div class="checkout-steps-item is-current">
                    <span class="checkout-steps-num">1</span>
                    <span>Cart</span>
                </div>
                <div class="checkout-steps-item">
                    <span class="checkout-steps-num">2</span>
                    <span>Payment</span>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-5 gap-8 lg:gap-12 mt-10 lg:mt-12 items-start">
            {{-- Cart items --}}
            <div class="lg:col-span-3 reveal">
                <div class="mb-8">
                    <h1 class="font-display text-2xl sm:text-[1.75rem] font-bold text-gray-900 tracking-tight">Shopping cart</h1>
                    <p class="mt-1.5 text-sm text-gray-500">{{ count($cartCourses) }} courses selected</p>
                </div>

                <div class="checkout-panel divide-y divide-gray-100">
                    @foreach($cartCourses as $course)
                    <article class="cart-course-row">
                        <img src="https://images.unsplash.com/{{ $course['image'] }}?w=160&h=100&fit=crop"
                            alt="{{ $course['title'] }}"
                            class="cart-course-thumb">
                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-brand-dark">{{ $course['category'] }}</p>
                            <h3 class="mt-0.5 text-sm sm:text-base font-medium text-gray-900 leading-snug">{{ $course['title'] }}</h3>
                            <p class="mt-1 text-xs text-gray-400">{{ $course['instructor'] }} · {{ $course['lessons'] }} lessons · {{ $course['hours'] }}h</p>
                        </div>
                        <div class="flex flex-col items-end justify-between shrink-0 gap-3">
                            <button type="button" class="cart-remove-btn" aria-label="Remove {{ $course['title'] }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <span class="text-sm font-semibold text-gray-900 tabular-nums">${{ $course['price'] }}</span>
                        </div>
                    </article>
                    @endforeach
                </div>

                <a href="{{ route('courses') }}" class="mt-5 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-dark transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Continue browsing
                </a>
            </div>

            {{-- Order summary --}}
            <aside class="lg:col-span-2 reveal lg:sticky lg:top-24">
                <div class="checkout-order">
                    <h2 class="checkout-order-title">Order summary</h2>

                    <div class="checkout-order-lines pt-2">
                        @foreach($cartCourses as $course)
                        <div class="checkout-order-line">
                            <span class="truncate pr-3">{{ $course['title'] }}</span>
                            <span class="tabular-nums shrink-0">${{ $course['price'] }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="checkout-order-promo">
                        <label for="promo-code" class="checkout-label mb-2">Promo code</label>
                        <div class="flex gap-2">
                            <input id="promo-code" type="text" value="SKILLFORGE10" class="checkout-input flex-1 text-sm" readonly>
                            <span class="checkout-promo-badge">Applied</span>
                        </div>
                        <p class="mt-2 text-xs checkout-discount">− ${{ $discount }} discount applied</p>
                    </div>

                    <div class="checkout-order-lines">
                        <div class="checkout-order-line">
                            <span>Subtotal</span>
                            <span class="tabular-nums">${{ $subtotal }}</span>
                        </div>
                        <div class="checkout-order-line">
                            <span>Discount</span>
                            <span class="tabular-nums checkout-discount">− ${{ $discount }}</span>
                        </div>
                        <div class="checkout-order-line">
                            <span>Platform fee</span>
                            <span class="checkout-discount font-medium">Free</span>
                        </div>
                    </div>

                    <div class="checkout-order-total">
                        <span>Total</span>
                        <span class="tabular-nums">${{ $total }}</span>
                    </div>

                    <a href="{{ route('checkout') }}" class="checkout-cta mt-6">
                        Proceed to payment
                    </a>

                    <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-gray-400">
                        <svg class="w-3.5 h-3.5 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Secure checkout
                    </p>
                </div>
            </aside>
        </div>
    </div>
</section>

@endsection
