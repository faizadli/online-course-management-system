{{-- Full courses catalog with search, filters & pagination — expects $courses (Course models) and $categories --}}
@php
$priceMax = max(1, (int) ceil((float) $courses->max('price')));
$categoryNames = $categories->pluck('name');
@endphp

<section id="courses-catalog" class="pb-20 lg:pb-28 bg-white" data-per-page="6" data-price-min="0" data-price-max="{{ $priceMax }}">
    <div class="page-container">
        {{-- Search & filters --}}
        <div class="catalog-filters relative z-40 mb-8 sm:mb-10">
            <div class="flex flex-col lg:flex-row gap-3 lg:gap-4 lg:items-center">
                {{-- Search --}}
                <div class="relative flex-1 min-w-0">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        type="search"
                        id="course-search-input"
                        placeholder="Search by course title or instructor..."
                        class="catalog-filter-control w-full pl-11 pr-4 placeholder:text-gray-400"
                    >
                </div>

                {{-- Searchable category combobox --}}
                <div class="relative w-full lg:w-56 shrink-0" id="course-category-combobox">
                    <input type="hidden" id="course-category-filter" value="">
                    <button
                        type="button"
                        id="course-category-trigger"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        class="catalog-filter-control w-full flex items-center justify-between gap-2 px-4 text-gray-700 text-left"
                    >
                        <span id="course-category-label" class="truncate">All Categories</span>
                        <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" id="course-category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div
                        id="course-category-dropdown"
                        class="hidden absolute z-50 top-full left-0 right-0 mt-2 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden"
                    >
                        <div class="p-2 border-b border-gray-100">
                            <div class="relative">
                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input
                                    type="search"
                                    id="course-category-search"
                                    placeholder="Search categories..."
                                    autocomplete="off"
                                    class="category-dropdown-search w-full py-2 pl-10 pr-3 rounded-lg border border-gray-200 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand"
                                >
                            </div>
                        </div>
                        <ul id="course-category-options" role="listbox" class="max-h-52 overflow-y-auto py-1">
                            <li role="option" data-value="" class="category-option px-3 py-2.5 text-sm text-gray-700 cursor-pointer hover:bg-brand-light hover:text-gray-900 transition-colors">All Categories</li>
                            @foreach($categoryNames as $category)
                            <li role="option" data-value="{{ $category }}" class="category-option px-3 py-2.5 text-sm text-gray-700 cursor-pointer hover:bg-brand-light hover:text-gray-900 transition-colors">{{ $category }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Price range: inputs + slider --}}
                <div class="catalog-filter-control flex items-center gap-2 w-full lg:w-auto lg:min-w-[17rem] shrink-0 px-3">
                    <div class="flex items-center gap-1 shrink-0">
                        <span class="text-sm text-gray-400">$</span>
                        <input
                            type="number"
                            id="price-min-num"
                            min="0"
                            value="0"
                            class="price-num-input w-12 sm:w-14"
                            aria-label="Minimum price"
                        >
                    </div>
                    <div class="price-range-slider relative flex-1 h-6 min-w-[4.5rem]" id="course-price-range">
                        <div class="price-range-track absolute top-1/2 -translate-y-1/2 left-0 right-0 h-1 rounded-full bg-gray-200"></div>
                        <div id="price-range-fill" class="price-range-fill absolute top-1/2 -translate-y-1/2 h-1 rounded-full bg-brand"></div>
                        <input type="range" id="price-min" min="0" max="{{ $priceMax }}" value="0" class="price-range-input" aria-label="Minimum price slider">
                        <input type="range" id="price-max" min="0" max="{{ $priceMax }}" value="{{ $priceMax }}" class="price-range-input" aria-label="Maximum price slider">
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <span class="text-sm text-gray-400">$</span>
                        <input
                            type="number"
                            id="price-max-num"
                            min="0"
                            value="{{ $priceMax }}"
                            class="price-num-input w-14 sm:w-16"
                            aria-label="Maximum price"
                        >
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
                <p id="courses-result-count" class="text-gray-500"></p>
                <button type="button" id="course-reset-filters" class="text-gray-500 hover:text-brand-dark font-medium transition-colors duration-300 hidden">
                    Clear filters
                </button>
            </div>
        </div>

        {{-- Course grid --}}
        <div id="courses-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $index => $course)
                @include('partials.course-card', [
                    'course' => $course,
                    'delay' => ['reveal-delay-0', 'reveal-delay-100', 'reveal-delay-200'][$index % 3],
                    'extraAttributes' => 'data-title="'.e(strtolower($course->title)).'" data-instructor="'.e(strtolower($course->instructor?->name ?? '')).'" data-category="'.e($course->category?->name ?? '').'" data-price="'.(float) $course->price.'"',
                ])
            @endforeach
        </div>

        {{-- Empty state --}}
        <div id="courses-empty" class="hidden text-center py-16 sm:py-20">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-brand-light flex items-center justify-center">
                <svg class="w-8 h-8 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">No courses found</h3>
            <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">Try adjusting your search or filters to find what you're looking for.</p>
        </div>

        {{-- Pagination --}}
        <nav id="courses-pagination" class="mt-12 flex flex-wrap items-center justify-center gap-2" aria-label="Courses pagination"></nav>
    </div>
</section>
