{{-- Categories section --}}
@php
$bgClass = $bgClass ?? 'bg-white';
$categories = [
    ['title' => 'Design & Creativity', 'desc' => 'UI/UX, branding, and visual storytelling', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['title' => 'Tech & Programming', 'desc' => 'Web, mobile, data, and software engineering', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
    ['title' => 'Business & Marketing', 'desc' => 'Strategy, growth, finance, and leadership', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
    ['title' => 'Photography & Visual Arts', 'desc' => 'Capture, edit, and express through lens', 'icon' => 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z'],
    ['title' => 'Personal Development', 'desc' => 'Mindset, productivity, and communication', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
];
$categoryDelays = ['reveal-delay-0', 'reveal-delay-80', 'reveal-delay-160', 'reveal-delay-240', 'reveal-delay-320'];
@endphp

<section id="categories" class="py-20 lg:py-28 {{ $bgClass }}">
    <div class="page-container">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 xl:gap-x-16 lg:items-start">
            <div class="lg:col-span-4 reveal lg:sticky lg:top-28">
                <p class="text-sm font-semibold uppercase tracking-widest text-brand-dark mb-4">Browse by Category</p>
                <h2 class="font-display text-3xl sm:text-4xl font-bold leading-tight text-gray-900">
                    Explore Paths That Match Your Goals
                </h2>
                <p class="mt-5 text-base sm:text-lg text-gray-500 leading-relaxed max-w-md">
                    Whether you're starting fresh or leveling up, find the right track across design, tech, business, and more.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4 text-sm text-gray-400">
                    <span class="inline-flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-brand"></span>5 learning paths</span>
                    <span class="inline-flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-brand"></span>120+ courses</span>
                </div>
                <a href="{{ route('courses') }}" class="mt-10 inline-flex items-center gap-2 px-8 py-3.5 bg-brand text-gray-800 font-semibold rounded-full hover:bg-brand-dark transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-brand/25">
                    All Categories
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>

            <div class="mt-12 lg:mt-0 lg:col-span-8 grid sm:grid-cols-2 gap-4 sm:gap-5">
                @foreach($categories as $index => $category)
                <a href="{{ route('courses') }}" class="group reveal {{ $categoryDelays[$index] ?? 'reveal-delay-0' }} flex items-start gap-4 p-5 sm:p-6 rounded-2xl border border-gray-100 {{ $bgClass === 'bg-white' ? 'bg-surface/50 hover:bg-white' : 'bg-white' }} hover:border-brand/30 hover:shadow-lg hover:shadow-brand/5 transition-all duration-300 hover:-translate-y-0.5">
                    <span class="shrink-0 flex items-center justify-center w-12 h-12 rounded-xl bg-brand-light text-brand-dark group-hover:bg-brand group-hover:text-gray-800 transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $category['icon'] }}"/></svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-lg sm:text-xl font-semibold text-gray-900 group-hover:text-brand-dark transition-colors duration-300">{{ $category['title'] }}</span>
                        <span class="mt-1 block text-sm text-gray-500 leading-snug">{{ $category['desc'] }}</span>
                    </span>
                    <svg class="w-5 h-5 shrink-0 mt-1 text-gray-300 group-hover:text-brand group-hover:translate-x-0.5 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
