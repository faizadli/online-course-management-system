@extends('layouts.landing')

@section('title', $instructor['name'] . ' — SkillForge')

@section('content')

<section class="instructor-show pt-20 lg:pt-24 pb-16 lg:pb-20 bg-white">
    <div class="page-container">
        {{-- Profile header --}}
        <div class="checkout-panel !p-6 sm:!p-8 lg:!p-10 reveal">
            <div class="flex flex-col sm:flex-row items-start gap-6">
                <img src="https://images.unsplash.com/{{ $instructor['avatar'] }}?w=200&h=200&fit=crop"
                    alt="{{ $instructor['name'] }}"
                    class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover ring-4 ring-brand-light shrink-0">
                <div class="flex-1 min-w-0">
                    <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">{{ $instructor['name'] }}</h1>
                    <p class="mt-1 text-brand-dark font-medium">{{ $instructor['role'] }}</p>
                    <p class="mt-4 text-gray-600 leading-relaxed max-w-2xl">{{ $instructor['bio'] }}</p>

                    <div class="mt-6 flex flex-wrap gap-6 text-sm">
                        <div>
                            <p class="font-display text-xl font-bold text-gray-900">{{ $instructor['stats']['students'] }}</p>
                            <p class="text-gray-500">Students</p>
                        </div>
                        <div>
                            <p class="font-display text-xl font-bold text-gray-900">{{ count($instructor['courses']) }}</p>
                            <p class="text-gray-500">Courses</p>
                        </div>
                        <div>
                            <p class="font-display text-xl font-bold text-gray-900">{{ $instructor['stats']['rating'] }}</p>
                            <p class="text-gray-500">Avg. rating</p>
                        </div>
                        <div>
                            <p class="font-display text-xl font-bold text-gray-900">{{ $instructor['stats']['reviews'] }}</p>
                            <p class="text-gray-500">Reviews</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- About --}}
        <div class="mt-8 checkout-panel !p-6 sm:!p-8 reveal">
            <h2 class="font-display text-xl font-bold text-gray-900 mb-4">About</h2>
            <div class="space-y-4 text-sm sm:text-base text-gray-600 leading-relaxed">
                @foreach($instructor['about'] as $paragraph)
                <p>{{ $paragraph }}</p>
                @endforeach
            </div>

            @if(!empty($instructor['expertise']))
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Areas of expertise</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($instructor['expertise'] as $skill)
                    <span class="instructor-tag">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Courses --}}
        <div id="instructor-courses" class="mt-10 reveal" data-per-page="3">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-display text-xl sm:text-2xl font-bold text-gray-900">Courses by {{ $instructor['name'] }}</h2>
                    <p id="instructor-courses-count" class="mt-1 text-sm text-gray-500">{{ count($instructor['courses']) }} courses available</p>
                </div>
                <div class="relative w-full sm:max-w-xs">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input id="instructor-course-search" type="search" placeholder="Search courses..." class="checkout-input !rounded-full !pl-10 text-sm" autocomplete="off">
                </div>
            </div>

            <div id="instructor-courses-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($instructor['courses'] as $course)
                <article class="instructor-course-card group bg-white rounded-2xl overflow-hidden border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                    data-title="{{ strtolower($course['title']) }}"
                    data-category="{{ strtolower($course['category']) }}">
                    <div class="relative overflow-hidden">
                        <a href="{{ route('courses.show', $course['slug']) }}">
                            <img src="https://images.unsplash.com/{{ $course['image'] }}?w=600&h=340&fit=crop"
                                alt="{{ $course['title'] }}"
                                class="w-full h-44 object-cover transition-transform duration-500 group-hover:scale-105">
                        </a>
                        @if($course['badge'])
                        <span class="absolute top-3 right-3 px-2.5 py-1 text-xs font-bold text-white rounded-full {{ $course['badgeColor'] }}">{{ $course['badge'] }}</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ $course['category'] }}</p>
                        <h3 class="mt-1 text-base font-bold text-gray-900 line-clamp-2 group-hover:text-brand-dark transition-colors">
                            <a href="{{ route('courses.show', $course['slug']) }}">{{ $course['title'] }}</a>
                        </h3>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="flex items-center gap-1 text-sm font-semibold text-amber-500">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $course['rating'] }}
                            </span>
                            <span class="text-lg font-bold text-gray-900">${{ $course['price'] }}</span>
                        </div>
                        <a href="{{ route('courses.show', $course['slug']) }}" class="btn-course-view mt-4 inline-block w-full text-center px-4 py-2 text-sm font-semibold border border-gray-200 rounded-full transition-all duration-300">
                            View Course
                        </a>
                    </div>
                </article>
                @endforeach
            </div>

            <div id="instructor-courses-empty" class="hidden text-center py-12">
                <p class="text-gray-500 text-sm">No courses match your search.</p>
            </div>

            <nav id="instructor-courses-pagination" class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Instructor courses pagination"></nav>
        </div>
    </div>
</section>

@endsection
