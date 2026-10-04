@extends('layouts.landing')

@section('title', $course->title . ' — SkillForge')

@section('content')

@php
$user = auth()->user();
$isStudent = ! $user || $user->role === \App\Enums\UserRole::Student;
$inWishlist = \App\Support\Basket::has(\App\Support\Basket::WISHLIST, $course->id);
$inCart = \App\Support\Basket::has(\App\Support\Basket::CART, $course->id);
$hours = round($minutes / 60, 1);
@endphp

<section class="course-show pt-20 lg:pt-24 pb-16 lg:pb-20 bg-white">
    <div class="page-container">
        @if($preview)
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800 flex flex-wrap items-center justify-between gap-3">
            <span><strong>Preview mode.</strong> This is how students will see your course. Status: <strong>{{ ucfirst($course->status) }}</strong>.</span>
            <a href="{{ route('dashboard.instructor.builder.show', $course) }}" class="font-semibold underline">← Back to Course Builder</a>
        </div>
        @endif

        @include('partials.dashboard.alerts')

        <div class="grid lg:grid-cols-[1fr_22rem] xl:grid-cols-[1fr_26rem] gap-8 lg:gap-12 items-start">
            {{-- Main content --}}
            <div class="reveal">
                <div class="relative overflow-hidden rounded-2xl mb-8">
                    <img src="{{ $course->thumbnail_url }}"
                        alt="{{ $course->title }}"
                        class="w-full h-56 sm:h-72 lg:h-80 object-cover">
                    @if($course->badge)
                    <span class="absolute top-4 left-4 px-3 py-1 text-xs font-bold text-white rounded-full {{ $course->badge_color }}">{{ $course->badge }}</span>
                    @endif
                </div>

                <p class="text-xs font-semibold uppercase tracking-wider text-brand-dark">{{ $course->category?->name ?? 'General' }}</p>
                <h1 class="mt-2 font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">{{ $course->title }}</h1>
                @if($course->short_description)
                <p class="mt-4 text-gray-600 leading-relaxed">{{ $course->short_description }}</p>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    @if($course->instructor)
                    <a href="{{ route('instructors.show', $course->instructor) }}" class="flex items-center gap-2 hover:text-brand-dark transition-colors group">
                        <span class="w-8 h-8 rounded-full bg-brand-light text-brand-dark text-sm font-bold flex items-center justify-center">{{ strtoupper(substr($course->instructor->name, 0, 1)) }}</span>
                        <span class="font-medium">{{ $course->instructor->name }}</span>
                    </a>
                    @endif
                    <span>{{ number_format($course->enrolments_count) }} students</span>
                    <span>{{ $lessonCount }} lessons</span>
                    @if($minutes > 0)<span>{{ $hours }} {{ $hours == 1 ? 'hour' : 'hours' }}</span>@endif
                    <span>{{ $course->level }}</span>
                    <span>{{ $course->language }}</span>
                </div>

                @if($course->description)
                <div class="mt-10 checkout-panel !p-6 sm:!p-8">
                    <h2 class="font-display text-xl font-bold text-gray-900 mb-4">About this course</h2>
                    <div class="text-sm sm:text-base text-gray-600 leading-relaxed whitespace-pre-line">{{ $course->description }}</div>
                </div>
                @endif

                {{-- Curriculum --}}
                <div class="mt-8 checkout-panel !p-0 overflow-hidden">
                    <h2 class="font-display text-xl font-bold text-gray-900 px-6 sm:px-8 pt-6 sm:pt-8 pb-4">Course content</h2>
                    <div class="divide-y divide-gray-100">
                        @forelse($course->chapters as $index => $chapter)
                        <div class="px-6 sm:px-8 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <h3 class="text-sm font-semibold text-gray-900">Chapter {{ $index + 1 }}: {{ $chapter->title }}</h3>
                                <span class="text-xs text-gray-400 shrink-0">{{ $chapter->contents->count() }} lessons · {{ $chapter->contents->sum('duration_minutes') }} min</span>
                            </div>
                            <ul class="mt-3 space-y-2">
                                @foreach($chapter->contents as $lesson)
                                <li class="flex items-center gap-2 text-sm text-gray-500">
                                    @if($lesson->type === 'video')
                                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif($lesson->type === 'pdf')
                                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    @else
                                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                                    @endif
                                    @if($lesson->is_free_preview)
                                    <a href="{{ route('courses.lesson-preview', [$course->slug, $lesson]) }}" class="flex-1 truncate text-brand-dark font-medium hover:underline">{{ $lesson->title }}</a>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-brand-light text-brand-dark">Free preview</span>
                                    @else
                                    <span class="flex-1 truncate">{{ $lesson->title }}</span>
                                    <span class="text-xs text-gray-300">{{ $lesson->type_label }}</span>
                                    @endif
                                </li>
                                @endforeach
                                @foreach($chapter->assessments as $assessment)
                                <li class="flex items-center gap-2 text-sm text-gray-500">
                                    <svg class="w-4 h-4 text-brand-dark shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="flex-1 truncate">{{ $assessment->title }}</span>
                                    <span class="text-xs text-brand-dark font-medium">{{ $assessment->type === 'mcq' ? 'MCQ Quiz' : 'Essay · AI scored' }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @empty
                        <p class="px-6 sm:px-8 py-6 text-sm text-gray-500">The curriculum for this course is being prepared.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Instructor --}}
                @if($course->instructor)
                <a href="{{ route('instructors.show', $course->instructor) }}" class="mt-8 checkout-panel !p-6 sm:!p-8 block group hover:border-brand-dark/30 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-display text-xl font-bold text-gray-900">Instructor</h2>
                        <span class="text-sm font-medium text-brand-dark opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                            View profile
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="w-16 h-16 rounded-full bg-brand-light text-brand-dark text-2xl font-bold flex items-center justify-center shrink-0 ring-2 ring-brand-light">{{ strtoupper(substr($course->instructor->name, 0, 1)) }}</span>
                        <div>
                            <h3 class="font-semibold text-gray-900 group-hover:text-brand-dark transition-colors">{{ $course->instructor->name }}</h3>
                            @if($course->instructor->expertise)
                            <p class="text-sm text-brand-dark">{{ $course->instructor->expertise }}</p>
                            @endif
                            @if($course->instructor->bio)
                            <p class="mt-2 text-sm text-gray-500 leading-relaxed line-clamp-3">{{ $course->instructor->bio }}</p>
                            @endif
                        </div>
                    </div>
                </a>
                @endif
            </div>

            {{-- Sidebar --}}
            <aside class="reveal lg:sticky lg:top-24">
                <div class="checkout-order">
                    <p class="font-display text-3xl font-bold text-gray-900">{{ $course->price_label }}</p>

                    <div class="mt-5 space-y-3">
                        @if($preview)
                            <p class="text-sm text-gray-500">Enrolment buttons are hidden in preview mode.</p>
                        @elseif($enrolment)
                            <a href="{{ route('dashboard.student.courses.show', $course) }}" class="checkout-cta">Go to course</a>
                            <p class="text-xs text-center text-gray-400">You are enrolled · {{ $enrolment->progress }}% complete</p>
                        @elseif(! $isStudent)
                            <p class="p-3 rounded-xl bg-gray-50 text-sm text-gray-500">Only student accounts can enrol in courses. Log in with a student account to continue.</p>
                        @else
                            <form action="{{ route('cart.buy', $course->slug) }}" method="post">
                                @csrf
                                <button type="submit" class="checkout-cta w-full" id="enrol-now-btn">{{ (float) $course->price <= 0 ? 'Enrol for free' : 'Enrol now' }}</button>
                            </form>
                            @if($inCart)
                            <a href="{{ route('cart') }}" class="course-btn-secondary">View in cart</a>
                            @else
                            <form action="{{ route('cart.add', $course->slug) }}" method="post">
                                @csrf
                                <button type="submit" class="course-btn-secondary w-full" id="add-to-cart-btn">Add to cart</button>
                            </form>
                            @endif
                            <form action="{{ route('wishlist.toggle', $course->slug) }}" method="post">
                                @csrf
                                <button type="submit" class="course-btn-secondary w-full">
                                    <svg class="w-5 h-5 {{ $inWishlist ? 'text-brand-dark fill-brand-dark/30' : '' }}" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    {{ $inWishlist ? 'Saved to wishlist' : 'Add to wishlist' }}
                                </button>
                            </form>
                        @endif
                    </div>

                    <ul class="mt-6 space-y-2.5 text-sm text-gray-600">
                        @foreach(['Lifetime access to all lessons', 'MCQ quizzes with instant scoring', 'Essay answers scored by AI with written feedback', 'Track your progress on any device'] as $perk)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-dark shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $perk }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

@endsection
