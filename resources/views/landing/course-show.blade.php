@extends('layouts.landing')

@section('title', $course['title'] . ' — SkillForge')

@section('content')

<section class="course-show pt-20 lg:pt-24 pb-16 lg:pb-20 bg-white">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="reveal mb-6 text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('courses') }}" class="hover:text-brand-dark transition-colors">Courses</a></li>
                <li aria-hidden="true" class="text-gray-300">›</li>
                <li><span class="font-medium text-gray-900">{{ $course['title'] }}</span></li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-[1fr_22rem] xl:grid-cols-[1fr_26rem] gap-8 lg:gap-12 items-start">
            {{-- Main content --}}
            <div class="reveal">
                <div class="relative overflow-hidden rounded-2xl mb-8">
                    <img src="https://images.unsplash.com/{{ $course['image'] }}?w=1200&h=600&fit=crop"
                        alt="{{ $course['title'] }}"
                        class="w-full h-56 sm:h-72 lg:h-80 object-cover">
                    @if($course['badge'])
                    <span class="absolute top-4 left-4 px-3 py-1 text-xs font-bold text-white rounded-full {{ $course['badgeColor'] }}">{{ $course['badge'] }}</span>
                    @endif
                </div>

                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark">{{ $course['category'] }}</p>
                <h1 class="mt-2 font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">{{ $course['title'] }}</h1>
                <p class="mt-4 text-gray-600 leading-relaxed">{{ $course['description'] }}</p>

                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <img src="https://images.unsplash.com/{{ $course['avatar'] }}?w=80&h=80&fit=crop"
                            alt="{{ $course['instructor'] }}"
                            class="w-8 h-8 rounded-full object-cover">
                        <span>{{ $course['instructor'] }}</span>
                    </div>
                    <span class="flex items-center gap-1 font-semibold text-amber-500">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        {{ $course['rating'] }}
                    </span>
                    <span>{{ $course['students'] }} students</span>
                    <span>{{ $course['lessons'] }} lessons</span>
                    <span>{{ $course['hours'] }} hours</span>
                </div>

                {{-- What you'll learn --}}
                <div class="mt-10 checkout-panel !p-6 sm:!p-8">
                    <h2 class="font-display text-xl font-bold text-gray-900 mb-5">What you'll learn</h2>
                    <ul class="grid sm:grid-cols-2 gap-3">
                        @foreach($course['learn'] as $item)
                        <li class="flex items-start gap-2.5 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-brand-dark shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Curriculum --}}
                <div class="mt-8 checkout-panel !p-0 overflow-hidden">
                    <h2 class="font-display text-xl font-bold text-gray-900 px-6 sm:px-8 pt-6 sm:pt-8 pb-4">Course content</h2>
                    <div class="divide-y divide-gray-100">
                        @foreach($course['curriculum'] as $index => $section)
                        <div class="px-6 sm:px-8 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <h3 class="text-sm font-semibold text-gray-900">Section {{ $index + 1 }}: {{ $section['title'] }}</h3>
                                <span class="text-xs text-gray-400 shrink-0">{{ $section['lessons'] }} lessons · {{ $section['duration'] }}</span>
                            </div>
                            <ul class="mt-3 space-y-2">
                                @foreach($section['items'] as $lesson)
                                <li class="flex items-center gap-2 text-sm text-gray-500">
                                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $lesson }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Instructor --}}
                <div class="mt-8 checkout-panel !p-6 sm:!p-8">
                    <h2 class="font-display text-xl font-bold text-gray-900 mb-4">Instructor</h2>
                    <div class="flex items-start gap-4">
                        <img src="https://images.unsplash.com/{{ $course['avatar'] }}?w=120&h=120&fit=crop"
                            alt="{{ $course['instructor'] }}"
                            class="w-16 h-16 rounded-full object-cover ring-2 ring-brand-light">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $course['instructor'] }}</h3>
                            <p class="text-sm text-brand-dark">{{ $course['instructorRole'] }}</p>
                            <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $course['instructorBio'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="reveal lg:sticky lg:top-24">
                <div class="checkout-order">
                    <p class="font-display text-3xl font-bold text-gray-900">${{ $course['price'] }}</p>
                    <p class="mt-1 text-sm text-gray-400 line-through">${{ $course['originalPrice'] }}</p>

                    <div class="mt-5 space-y-3">
                        <a href="{{ route('cart') }}" class="checkout-cta">
                            Add to cart
                        </a>
                        <a href="{{ route('wishlist') }}" class="course-btn-secondary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Add to wishlist
                        </a>
                    </div>

                    <ul class="mt-6 space-y-2.5 text-sm text-gray-600">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Lifetime access
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Certificate of completion
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Mobile & desktop access
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            30-day money-back guarantee
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

@endsection
