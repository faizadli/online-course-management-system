@extends('layouts.landing')

@section('title', 'Wishlist — OCMS')

@section('content')

<section class="checkout-page pt-20 lg:pt-24 pb-16 lg:pb-20 bg-white">
    <div class="page-container max-w-6xl">
        <div class="mb-8 reveal">
            <h1 class="font-display text-2xl sm:text-[1.75rem] font-bold text-gray-900 tracking-tight">My Wishlist</h1>
            <p class="mt-1.5 text-sm text-gray-500">{{ $courses->count() }} saved {{ \Illuminate\Support\Str::plural('course', $courses->count()) }}</p>
        </div>

        @include('partials.dashboard.alerts')

        @if($courses->isEmpty())
        <div class="text-center py-16">
            <h2 class="font-display text-xl font-bold text-gray-900">Your wishlist is empty</h2>
            <p class="mt-2 text-sm text-gray-500">Save courses you love and enrol when you're ready.</p>
            <a href="{{ route('courses') }}" class="checkout-cta mt-6 !w-auto inline-flex px-8">Browse courses</a>
        </div>
        @else
        <div class="grid lg:grid-cols-5 gap-8 lg:gap-12 items-start">
            <div class="lg:col-span-3 space-y-4 reveal">
                @foreach($courses as $course)
                <article class="checkout-panel wishlist-item">
                    <div class="wishlist-item-inner">
                        <div class="relative shrink-0">
                            <a href="{{ route('courses.show', $course->slug) }}">
                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="wishlist-thumb">
                            </a>
                            @if($course->badge)
                            <span class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold text-white rounded-full {{ $course->badge_color }}">{{ $course->badge }}</span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-brand-dark">{{ $course->category?->name ?? 'General' }}</p>
                            <h3 class="mt-0.5 text-base font-semibold text-gray-900 leading-snug">
                                <a href="{{ route('courses.show', $course->slug) }}" class="hover:text-brand-dark transition-colors">{{ $course->title }}</a>
                            </h3>
                            <p class="mt-1 text-xs text-gray-400">{{ $course->instructor?->name }} · {{ $course->contents_count }} lessons</p>
                        </div>

                        <div class="wishlist-item-actions">
                            <form action="{{ route('wishlist.toggle', $course->slug) }}" method="post">
                                @csrf
                                <button type="submit" class="wishlist-remove-btn" aria-label="Remove from wishlist">
                                    <svg class="w-5 h-5 text-brand-dark fill-brand-dark/20" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </button>
                            </form>
                            <p class="font-display text-lg font-bold text-gray-900 tabular-nums">{{ $course->price_label }}</p>
                            <form action="{{ route('wishlist.cart', $course->slug) }}" method="post">
                                @csrf
                                <button type="submit" class="wishlist-cart-btn">Add to cart</button>
                            </form>
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
                    <p class="checkout-order-meta">{{ $courses->count() }} courses saved</p>

                    <div class="checkout-order-lines pt-4">
                        @foreach($courses as $course)
                        <div class="checkout-order-line">
                            <span class="truncate pr-3">{{ $course->title }}</span>
                            <span class="tabular-nums shrink-0">{{ $course->price_label }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="checkout-order-total">
                        <span>Total value</span>
                        <span class="tabular-nums">${{ number_format($totalValue, 2) }}</span>
                    </div>

                    <form action="{{ route('wishlist.cart.all') }}" method="post">
                        @csrf
                        <button type="submit" class="checkout-cta mt-6 w-full">Add all to cart</button>
                    </form>

                    <p class="mt-4 text-center text-xs text-gray-400">
                        Save courses you love and enrol when you're ready.
                    </p>
                </div>
            </aside>
        </div>
        @endif
    </div>
</section>

@endsection
