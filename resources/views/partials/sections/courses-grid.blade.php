{{-- Courses grid section (home page) — expects $courses (collection of Course models) --}}
@php
$courseDelays = ['reveal-delay-0', 'reveal-delay-100', 'reveal-delay-200'];
$showHeader = $showHeader ?? true;
$sectionClass = $sectionClass ?? 'py-20 lg:py-28 bg-surface';
@endphp

<section id="courses" class="{{ $sectionClass }}">
    <div class="page-container">
        @if($showHeader)
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8 sm:mb-12 reveal">
            <h2 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold">Trending Courses This Week</h2>
            <a href="{{ route('courses') }}" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-brand transition-colors duration-300 shrink-0">
                View all courses
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        @endif

        @if($courses->isEmpty())
        <p class="text-center text-gray-500 py-10">No published courses yet. Please check back soon.</p>
        @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $index => $course)
                @include('partials.course-card', ['course' => $course, 'delay' => $courseDelays[$index % 3]])
            @endforeach
        </div>
        @endif
    </div>
</section>
