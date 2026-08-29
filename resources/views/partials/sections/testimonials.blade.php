{{-- Testimonials section --}}
@php
$testimonials = [
    ['name' => 'Jessica Miller', 'role' => 'Freelance Graphic Designer', 'text' => 'SkillForge completely transformed my career. The <span class="text-brand font-semibold">UI/UX course</span> gave me the skills I needed to land my first freelance clients within weeks.', 'avatar' => 'photo-1494790108377-be9c29b29330'],
    ['name' => 'Ryan Thompson', 'role' => 'Software Engineer', 'text' => 'The quality of instruction is <span class="text-amber-500 font-semibold">outstanding</span>. I went from zero coding knowledge to building full-stack applications in just 6 months.', 'avatar' => 'photo-1507003211169-0a1dd7228f2d'],
    ['name' => 'Maria Santos', 'role' => 'Marketing Manager', 'text' => 'Best investment I\'ve made in my professional development. The <span class="text-purple-500 font-semibold">business courses</span> helped me launch campaigns that actually convert.', 'avatar' => 'photo-1438761681033-6461ffad8d80'],
];
$testimonialDelays = ['reveal-delay-0', 'reveal-delay-150', 'reveal-delay-300'];
@endphp

<section class="py-20 lg:py-28 bg-white">
    <div class="page-container">
        <h2 class="font-display text-3xl sm:text-4xl font-bold text-center mb-16 reveal">Stories From Our Community</h2>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach($testimonials as $index => $testimonial)
            <div class="reveal {{ $testimonialDelays[$index] ?? 'reveal-delay-0' }} p-8 rounded-2xl hover:bg-surface transition-all duration-300">
                <svg class="w-10 h-10 text-brand/30 mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z"/></svg>
                <p class="text-gray-600 leading-relaxed">{!! $testimonial['text'] !!}</p>
                <div class="mt-6 flex items-center gap-3">
                    <img src="https://images.unsplash.com/{{ $testimonial['avatar'] }}?w=80&h=80&fit=crop&crop=face" alt="{{ $testimonial['name'] }}" class="w-11 h-11 rounded-full object-cover">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $testimonial['name'] }}</p>
                        <p class="text-sm text-gray-500">{{ $testimonial['role'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
