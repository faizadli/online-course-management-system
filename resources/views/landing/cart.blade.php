@extends('layouts.landing')

@section('title', 'Shopping Cart — SkillForge')

@section('content')

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

        <div class="mt-8">@include('partials.dashboard.alerts')</div>

        @if($courses->isEmpty())
        <div class="text-center py-16">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-light flex items-center justify-center">
                <svg class="w-8 h-8 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            </div>
            <h1 class="font-display text-2xl font-bold text-gray-900">Your cart is empty</h1>
            <p class="mt-2 text-sm text-gray-500">Browse our catalogue and add the courses you want to learn.</p>
            <a href="{{ route('courses') }}" class="checkout-cta mt-6 !w-auto inline-flex px-8">Browse courses</a>
        </div>
        @else
        <div class="grid lg:grid-cols-5 gap-8 lg:gap-12 mt-6 items-start">
            {{-- Cart items --}}
            <div class="lg:col-span-3 reveal">
                <div class="mb-8">
                    <h1 class="font-display text-2xl sm:text-[1.75rem] font-bold text-gray-900 tracking-tight">Shopping cart</h1>
                    <p class="mt-1.5 text-sm text-gray-500">{{ $courses->count() }} {{ \Illuminate\Support\Str::plural('course', $courses->count()) }} selected</p>
                </div>

                <div class="checkout-panel cart-course-list">
                    @foreach($courses as $course)
                    <article class="cart-course-row">
                        <a href="{{ route('courses.show', $course->slug) }}" class="cart-course-link shrink-0">
                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="cart-course-thumb">
                        </a>
                        <a href="{{ route('courses.show', $course->slug) }}" class="cart-course-link min-w-0 flex-1 group">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-brand-dark">{{ $course->category?->name ?? 'General' }}</p>
                            <h3 class="mt-0.5 text-sm sm:text-base font-medium text-gray-900 leading-snug group-hover:text-brand-dark transition-colors">{{ $course->title }}</h3>
                            <p class="mt-1 text-xs text-gray-400">{{ $course->instructor?->name }} · {{ $course->contents_count }} lessons</p>
                        </a>
                        <div class="flex flex-col items-end justify-between shrink-0 gap-3">
                            <form action="{{ route('cart.remove', $course->slug) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cart-remove-btn" aria-label="Remove {{ $course->title }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                            <span class="text-sm font-semibold text-gray-900 tabular-nums">{{ $course->price_label }}</span>
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
                        @foreach($courses as $course)
                        <div class="checkout-order-line">
                            <a href="{{ route('courses.show', $course->slug) }}" class="truncate pr-3 hover:text-brand-dark transition-colors">{{ $course->title }}</a>
                            <span class="tabular-nums shrink-0">{{ $course->price_label }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="checkout-order-lines">
                        <div class="checkout-order-line">
                            <span>Platform fee</span>
                            <span class="checkout-discount font-medium">Free</span>
                        </div>
                    </div>

                    <div class="checkout-order-total">
                        <span>Total</span>
                        <span class="tabular-nums">${{ number_format($total, 2) }}</span>
                    </div>

                    <a href="{{ route('checkout') }}" id="proceed-to-payment" class="checkout-cta mt-6">
                        Proceed to payment
                    </a>

                    <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-gray-400">
                        <svg class="w-3.5 h-3.5 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Secure checkout
                    </p>
                </div>
            </aside>
        </div>
        @endif
    </div>
</section>

@endsection
