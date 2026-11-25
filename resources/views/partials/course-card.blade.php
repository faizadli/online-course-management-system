{{-- Reusable public course card. Expects $course (Course model with instructor, category, contents_count) --}}
@php
$delay = $delay ?? '';
$extraAttributes = $extraAttributes ?? '';
$minutes = (int) ($course->contents_sum_duration_minutes ?? 0);
@endphp
<article class="course-card group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 reveal {{ $delay }}" {!! $extraAttributes !!}>
    <a href="{{ route('courses.show', $course->slug) }}" class="block relative overflow-hidden">
        <img src="{{ $course->thumbnail_url }}"
            alt="{{ $course->title }}"
            loading="lazy"
            class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-110">
        @if($course->badge)
        <span class="absolute top-3 right-3 px-2.5 py-1 text-xs font-bold text-white rounded-full {{ $course->badge_color }}">{{ $course->badge }}</span>
        @endif
    </a>
    <div class="p-5">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ $course->category?->name ?? 'General' }}</p>
        <h3 class="mt-1 text-lg font-bold text-gray-900 line-clamp-2 group-hover:text-brand-dark transition-colors duration-300">
            <a href="{{ route('courses.show', $course->slug) }}">{{ $course->title }}</a>
        </h3>
        <div class="mt-3 flex items-center gap-2">
            <span class="w-7 h-7 rounded-full bg-brand-light text-brand-dark text-xs font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr($course->instructor?->name ?? '?', 0, 1)) }}</span>
            <span class="text-sm text-gray-500 truncate">{{ $course->instructor?->name ?? 'OCMS' }}</span>
            <span class="ml-auto text-xs text-gray-400 shrink-0">{{ $course->contents_count ?? 0 }} lessons</span>
        </div>
        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <span class="text-xl font-bold text-gray-900">{{ $course->price_label }}</span>
            <a href="{{ route('courses.show', $course->slug) }}" class="btn-course-view w-full sm:w-auto px-4 py-2 text-sm font-semibold border border-gray-200 rounded-full transition-all duration-300 text-center">
                View Course
            </a>
        </div>
    </div>
</article>
