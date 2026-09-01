{{-- Full courses catalog with search, filters & pagination --}}
@php
$courses = [
    ['title' => 'UI/UX Design Fundamentals', 'category' => 'Design & Creative', 'instructor' => 'Sarah Chen', 'price' => '$49', 'priceValue' => 49, 'rating' => '4.9', 'badge' => 'Best Seller', 'badgeColor' => 'bg-red-500', 'image' => 'photo-1561070791-2526d30994b5'],
    ['title' => 'Full Stack Web Development', 'category' => 'Tech & Programming', 'instructor' => 'John Doe', 'price' => '$79', 'priceValue' => 79, 'rating' => '4.8', 'badge' => 'New', 'badgeColor' => 'bg-brand', 'image' => 'photo-1498050108023-c5249f4df085'],
    ['title' => 'Digital Marketing Mastery', 'category' => 'Business & Marketing', 'instructor' => 'Emily Rose', 'price' => '$59', 'priceValue' => 59, 'rating' => '4.7', 'badge' => 'Hot', 'badgeColor' => 'bg-orange-500', 'image' => 'photo-1460925895917-afdab827c52f'],
    ['title' => 'Photography for Beginners', 'category' => 'Photography', 'instructor' => 'Alex Kim', 'price' => '$39', 'priceValue' => 39, 'rating' => '4.9', 'badge' => null, 'badgeColor' => '', 'image' => 'photo-1452587925148-ce544e77e70d'],
    ['title' => 'Python Data Science Bootcamp', 'category' => 'Tech & Programming', 'instructor' => 'Mike Johnson', 'price' => '$89', 'priceValue' => 89, 'rating' => '4.8', 'badge' => 'Best Seller', 'badgeColor' => 'bg-red-500', 'image' => 'photo-1555949963-aa79d882981c'],
    ['title' => 'Public Speaking Confidence', 'category' => 'Personal Development', 'instructor' => 'Lisa Park', 'price' => '$29', 'priceValue' => 29, 'rating' => '4.6', 'badge' => 'New', 'badgeColor' => 'bg-brand', 'image' => 'photo-1475721027785-f74eccf877e2'],
    ['title' => 'Graphic Design with Figma', 'category' => 'Design & Creative', 'instructor' => 'Tom Wilson', 'price' => '$54', 'priceValue' => 54, 'rating' => '4.9', 'badge' => null, 'badgeColor' => '', 'image' => 'photo-1626785774573-4b799315345d'],
    ['title' => 'Financial Literacy 101', 'category' => 'Business & Marketing', 'instructor' => 'Anna Lee', 'price' => '$34', 'priceValue' => 34, 'rating' => '4.7', 'badge' => 'Hot', 'badgeColor' => 'bg-orange-500', 'image' => 'photo-1554224155-6726b3ff858f'],
    ['title' => 'Mobile App Development', 'category' => 'Tech & Programming', 'instructor' => 'David Park', 'price' => '$99', 'priceValue' => 99, 'rating' => '4.8', 'badge' => 'Best Seller', 'badgeColor' => 'bg-red-500', 'image' => 'photo-1512941937669-90a1b58e7e9c'],
    ['title' => 'Brand Identity Design', 'category' => 'Design & Creative', 'instructor' => 'Nina Ortiz', 'price' => '$44', 'priceValue' => 44, 'rating' => '4.8', 'badge' => 'New', 'badgeColor' => 'bg-brand', 'image' => 'photo-1561070791-2526d30994b5'],
    ['title' => 'SEO & Content Strategy', 'category' => 'Business & Marketing', 'instructor' => 'James Wu', 'price' => '$64', 'priceValue' => 64, 'rating' => '4.7', 'badge' => null, 'badgeColor' => '', 'image' => 'photo-1460925895917-afdab827c52f'],
    ['title' => 'Mindfulness & Productivity', 'category' => 'Personal Development', 'instructor' => 'Clara Evans', 'price' => '$35', 'priceValue' => 35, 'rating' => '4.9', 'badge' => 'Hot', 'badgeColor' => 'bg-orange-500', 'image' => 'photo-1503676260728-1c00da094a0b'],
];

$categories = collect($courses)->pluck('category')->unique()->sort()->values();
$priceMin = collect($courses)->min('priceValue');
$priceMax = collect($courses)->max('priceValue');
@endphp

<section id="courses-catalog" class="pb-20 lg:pb-28 bg-white" data-per-page="6" data-price-min="{{ $priceMin }}" data-price-max="{{ $priceMax }}">
    <div class="page-container">
        {{-- Search & filters --}}
        <div class="reveal mb-8 sm:mb-10">
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
                        class="catalog-filter-control w-full flex items-center justify-between gap-2 text-gray-700 text-left"
                    >
                        <span id="course-category-label" class="truncate">All Categories</span>
                        <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" id="course-category-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div
                        id="course-category-dropdown"
                        class="hidden absolute z-30 top-full left-0 right-0 mt-2 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden"
                    >
                        <div class="p-2 border-b border-gray-100">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input
                                    type="search"
                                    id="course-category-search"
                                    placeholder="Search categories..."
                                    autocomplete="off"
                                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-200 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand"
                                >
                            </div>
                        </div>
                        <ul id="course-category-options" role="listbox" class="max-h-52 overflow-y-auto py-1">
                            <li role="option" data-value="" class="category-option px-3 py-2.5 text-sm text-gray-700 cursor-pointer hover:bg-brand-light hover:text-gray-900 transition-colors">All Categories</li>
                            @foreach($categories as $category)
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
                            min="{{ $priceMin }}"
                            max="{{ $priceMax }}"
                            value="{{ $priceMin }}"
                            class="price-num-input w-11 sm:w-12"
                            aria-label="Minimum price"
                        >
                    </div>
                    <div class="price-range-slider relative flex-1 h-6 min-w-[4.5rem]" id="course-price-range">
                        <div class="price-range-track absolute top-1/2 -translate-y-1/2 left-0 right-0 h-1 rounded-full bg-gray-200"></div>
                        <div id="price-range-fill" class="price-range-fill absolute top-1/2 -translate-y-1/2 h-1 rounded-full bg-brand"></div>
                        <input type="range" id="price-min" min="{{ $priceMin }}" max="{{ $priceMax }}" value="{{ $priceMin }}" class="price-range-input" aria-label="Minimum price slider">
                        <input type="range" id="price-max" min="{{ $priceMin }}" max="{{ $priceMax }}" value="{{ $priceMax }}" class="price-range-input" aria-label="Maximum price slider">
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <span class="text-sm text-gray-400">$</span>
                        <input
                            type="number"
                            id="price-max-num"
                            min="{{ $priceMin }}"
                            max="{{ $priceMax }}"
                            value="{{ $priceMax }}"
                            class="price-num-input w-11 sm:w-12"
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
            <article
                class="course-card group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 reveal {{ ['reveal-delay-0', 'reveal-delay-100', 'reveal-delay-200'][$index % 3] }}"
                data-title="{{ strtolower($course['title']) }}"
                data-instructor="{{ strtolower($course['instructor']) }}"
                data-category="{{ $course['category'] }}"
                data-price="{{ $course['priceValue'] }}"
            >
                <div class="relative overflow-hidden">
                    <img src="https://images.unsplash.com/{{ $course['image'] }}?w=600&h=340&fit=crop"
                        alt="{{ $course['title'] }}"
                        class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-110">
                    @if($course['badge'])
                    <span class="absolute top-3 right-3 px-2.5 py-1 text-xs font-bold text-white rounded-full {{ $course['badgeColor'] }}">{{ $course['badge'] }}</span>
                    @endif
                </div>
                <div class="p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ $course['category'] }}</p>
                    <h3 class="mt-1 text-lg font-bold text-gray-900 line-clamp-2 group-hover:text-brand transition-colors duration-300">{{ $course['title'] }}</h3>
                    <div class="mt-3 flex items-center gap-2">
                        <div class="w-7 h-7 bg-gray-200 rounded-full"></div>
                        <span class="text-sm text-gray-500">{{ $course['instructor'] }}</span>
                        <span class="ml-auto flex items-center gap-1 text-sm text-amber-500">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            {{ $course['rating'] }}
                        </span>
                    </div>
                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <span class="text-xl font-bold text-gray-900">{{ $course['price'] }}</span>
                        <button type="button" class="w-full sm:w-auto px-4 py-2 text-sm font-semibold border border-gray-200 rounded-full hover:bg-brand hover:text-gray-800 hover:border-brand transition-all duration-300 text-center">
                            View Course
                        </button>
                    </div>
                </div>
            </article>
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
