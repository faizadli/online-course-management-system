@extends('layouts.landing')

@section('title', $instructor->name . ' — SkillForge')

@section('content')

<section class="instructor-show pt-20 lg:pt-24 pb-16 lg:pb-20 bg-white">
    <div class="page-container">
        {{-- Profile header --}}
        <div class="checkout-panel !p-6 sm:!p-8 lg:!p-10 reveal">
            <div class="flex flex-col sm:flex-row items-start gap-6">
                <span class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-brand-light text-brand-dark text-4xl font-bold flex items-center justify-center ring-4 ring-brand-light shrink-0">{{ strtoupper(substr($instructor->name, 0, 1)) }}</span>
                <div class="flex-1 min-w-0">
                    <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">{{ $instructor->name }}</h1>
                    @if($instructor->expertise)
                    <p class="mt-1 text-brand-dark font-medium">{{ $instructor->expertise }}</p>
                    @endif
                    @if($instructor->bio)
                    <p class="mt-4 text-gray-600 leading-relaxed max-w-2xl">{{ $instructor->bio }}</p>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-6 text-sm">
                        <div>
                            <p class="font-display text-xl font-bold text-gray-900">{{ number_format($studentCount) }}</p>
                            <p class="text-gray-500">Students</p>
                        </div>
                        <div>
                            <p class="font-display text-xl font-bold text-gray-900">{{ $courses->count() }}</p>
                            <p class="text-gray-500">Courses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Courses --}}
        <div id="instructor-courses" class="mt-10 reveal" data-per-page="3">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display text-xl sm:text-2xl font-bold text-gray-900">Courses by {{ $instructor->name }}</h2>
                    <p id="instructor-courses-count" class="mt-1 text-sm text-gray-500">{{ $courses->count() }} courses available</p>
                </div>
                <div class="relative w-full sm:max-w-xs">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input id="instructor-course-search" type="search" placeholder="Search courses..." class="checkout-input !rounded-full !pl-10 text-sm" autocomplete="off">
                </div>
            </div>

            <div id="instructor-courses-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($courses as $course)
                <article class="instructor-course-card group bg-white rounded-2xl overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                    data-title="{{ strtolower($course->title) }}"
                    data-category="{{ strtolower($course->category?->name ?? '') }}">
                    <div class="relative overflow-hidden">
                        <a href="{{ route('courses.show', $course->slug) }}">
                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-44 object-cover transition-transform duration-500 group-hover:scale-105">
                        </a>
                        @if($course->badge)
                        <span class="absolute top-3 right-3 px-2.5 py-1 text-xs font-bold text-white rounded-full {{ $course->badge_color }}">{{ $course->badge }}</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ $course->category?->name ?? 'General' }}</p>
                        <h3 class="mt-1 text-base font-bold text-gray-900 line-clamp-2 group-hover:text-brand-dark transition-colors">
                            <a href="{{ route('courses.show', $course->slug) }}">{{ $course->title }}</a>
                        </h3>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm text-gray-400">{{ $course->contents_count }} lessons</span>
                            <span class="text-lg font-bold text-gray-900">{{ $course->price_label }}</span>
                        </div>
                        <a href="{{ route('courses.show', $course->slug) }}" class="btn-course-view mt-4 inline-block w-full text-center px-4 py-2 text-sm font-semibold border border-gray-200 rounded-full transition-all duration-300">
                            View Course
                        </a>
                    </div>
                </article>
                @endforeach
            </div>

            <div id="instructor-courses-empty" class="{{ $courses->isEmpty() ? '' : 'hidden' }} text-center py-12">
                <p class="text-gray-500 text-sm">{{ $courses->isEmpty() ? 'This instructor has no published courses yet.' : 'No courses match your search.' }}</p>
            </div>

            <nav id="instructor-courses-pagination" class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Instructor courses pagination"></nav>
        </div>
    </div>
</section>

@endsection
