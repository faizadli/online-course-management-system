{{-- Courses grid section --}}
@php
$limit = $limit ?? null;
$courses = [
    ['title' => 'UI/UX Design Fundamentals', 'category' => 'Design & Creative', 'instructor' => 'Sarah Chen', 'price' => '$49', 'rating' => '4.9', 'badge' => 'Best Seller', 'badgeColor' => 'bg-red-500', 'image' => 'photo-1561070791-2526d30994b5'],
    ['title' => 'Full Stack Web Development', 'category' => 'Tech & Programming', 'instructor' => 'John Doe', 'price' => '$79', 'rating' => '4.8', 'badge' => 'New', 'badgeColor' => 'bg-brand', 'image' => 'photo-1498050108023-c5249f4df085'],
    ['title' => 'Digital Marketing Mastery', 'category' => 'Business & Marketing', 'instructor' => 'Emily Rose', 'price' => '$59', 'rating' => '4.7', 'badge' => 'Hot', 'badgeColor' => 'bg-orange-500', 'image' => 'photo-1460925895917-afdab827c52f'],
    ['title' => 'Photography for Beginners', 'category' => 'Photography', 'instructor' => 'Alex Kim', 'price' => '$39', 'rating' => '4.9', 'badge' => null, 'badgeColor' => '', 'image' => 'photo-1452587925148-ce544e77e70d'],
    ['title' => 'Python Data Science Bootcamp', 'category' => 'Tech & Programming', 'instructor' => 'Mike Johnson', 'price' => '$89', 'rating' => '4.8', 'badge' => 'Best Seller', 'badgeColor' => 'bg-red-500', 'image' => 'photo-1555949963-aa79d882981c'],
    ['title' => 'Public Speaking Confidence', 'category' => 'Personal Development', 'instructor' => 'Lisa Park', 'price' => '$29', 'rating' => '4.6', 'badge' => 'New', 'badgeColor' => 'bg-brand', 'image' => 'photo-1475721027785-f74eccf877e2'],
    ['title' => 'Graphic Design with Figma', 'category' => 'Design & Creative', 'instructor' => 'Tom Wilson', 'price' => '$54', 'rating' => '4.9', 'badge' => null, 'badgeColor' => '', 'image' => 'photo-1626785774573-4b799315345d'],
    ['title' => 'Financial Literacy 101', 'category' => 'Business & Marketing', 'instructor' => 'Anna Lee', 'price' => '$34', 'rating' => '4.7', 'badge' => 'Hot', 'badgeColor' => 'bg-orange-500', 'image' => 'photo-1554224155-6726b3ff858f'],
    ['title' => 'Mobile App Development', 'category' => 'Tech & Programming', 'instructor' => 'David Park', 'price' => '$99', 'rating' => '4.8', 'badge' => 'Best Seller', 'badgeColor' => 'bg-red-500', 'image' => 'photo-1512941937669-90a1b58e7e9c'],
];
if ($limit) {
    $courses = array_slice($courses, 0, $limit);
}
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

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $index => $course)
            <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 reveal {{ $courseDelays[$index % 3] }}">
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
                        <button class="w-full sm:w-auto px-4 py-2 text-sm font-semibold border border-gray-200 rounded-full hover:bg-brand hover:text-gray-800 hover:border-brand transition-all duration-300 text-center">
                            View Course
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
