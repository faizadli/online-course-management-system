@extends('layouts.landing')

@section('title', 'SkillForge — Where Ambition Meets Expertise')

@section('nav_variant', 'hero')

@section('content')

{{-- Hero Section --}}
<section class="hero-section relative overflow-hidden bg-white">
    <div class="relative z-10 mx-auto grid min-h-[100dvh] w-full max-w-[1600px] items-center gap-12 px-5 pb-20 pt-28 sm:px-8 md:px-12 lg:grid-cols-[0.95fr_1.05fr] lg:gap-8 lg:px-14 lg:pb-12 lg:pt-28 xl:px-16">
        <div class="hero-copy relative z-10 max-w-2xl">
            <div class="hero-line hero-line-1 inline-flex items-center gap-2 rounded-full border border-gray-900/10 bg-white/75 px-3 py-1.5 text-xs font-medium text-gray-800 shadow-sm backdrop-blur-sm">
                <svg class="h-4 w-4 text-brand-dark" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 2.2 6.6H21l-5.5 4 2.1 6.6-5.6-4.1-5.6 4.1 2.1-6.6L3 8.6h6.8L12 2Z"/></svg>
                <span>4.9 on Trustpilot</span>
            </div>

            <h1 class="mt-6 max-w-2xl font-display text-[2.65rem] font-extrabold leading-[0.95] tracking-[-0.06em] text-gray-900 sm:text-5xl lg:text-[3.6rem] xl:text-[4.15rem]">
                <span class="hero-line hero-line-2 block">Learn With Purpose,</span>
                <span class="hero-line hero-line-3 block">Build Real Skills,</span>
                <span class="hero-line hero-line-4 block">Shape Your <span class="hero-editorial-word">Future.</span></span>
            </h1>

            <p class="hero-line hero-line-4 mt-7 max-w-md text-sm leading-relaxed text-gray-600 sm:text-base">
                Learn anywhere. Build skills that move you forward.
            </p>

            <a href="#courses" class="hero-line hero-line-4 mt-7 inline-flex items-center gap-2 rounded-full bg-brand-dark px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-dark/20 transition-all duration-300 hover:-translate-y-0.5 hover:bg-brand">
                Explore Courses
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </a>

            <div class="hero-line hero-line-4 mt-10 flex items-center gap-7 border-t border-gray-900/10 pt-5 sm:gap-10">
                <div>
                    <p class="font-display text-2xl font-extrabold tracking-tight text-gray-900">100+</p>
                    <p class="mt-1 text-[0.65rem] uppercase tracking-wider text-gray-500">Expert courses</p>
                </div>
                <div>
                    <p class="font-display text-2xl font-extrabold tracking-tight text-gray-900">12k+</p>
                    <p class="mt-1 text-[0.65rem] uppercase tracking-wider text-gray-500">Active learners</p>
                </div>
                <div>
                    <p class="font-display text-2xl font-extrabold tracking-tight text-gray-900">4.9</p>
                    <p class="mt-1 text-[0.65rem] uppercase tracking-wider text-gray-500">Average rating</p>
                </div>
            </div>
        </div>

        <div class="hero-media-stage relative mx-auto h-[28rem] w-full max-w-[38rem] sm:h-[34rem] lg:h-[min(78vh,42rem)]">
            <div class="hero-media-frame absolute inset-x-12 bottom-4 top-4 overflow-hidden rounded-[2rem] shadow-[0_24px_70px_rgba(30,70,90,0.18)] sm:inset-x-16">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1600&h=1200&fit=crop&q=85&auto=format"
                    alt="Three people collaborating around a laptop"
                    class="hero-bg-image h-full w-full rounded-[2rem] object-cover object-center">
            </div>

            <div class="hero-float hero-float-delay-1 absolute right-0 top-10 flex items-center gap-2 rounded-full bg-white px-3 py-2 text-[0.7rem] font-semibold text-gray-800 shadow-lg">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-dark text-white">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 12 4 4L19 6"/></svg>
                </span>
                Learn together
            </div>

            <div class="hero-float hero-float-delay-2 absolute right-2 top-24 flex items-center gap-2 rounded-full bg-white px-3 py-2 text-[0.7rem] font-semibold text-gray-800 shadow-lg">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-dark text-white">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m5 12 4 4L19 6"/></svg>
                </span>
                Learn at your pace
            </div>

            <div class="hero-float hero-float-delay-3 absolute left-2 top-1/2 rounded-2xl bg-white px-4 py-3 text-gray-900 shadow-xl">
                <p class="text-[0.65rem] font-semibold uppercase tracking-wider text-gray-500">Add your skills</p>
                <div class="mt-2 flex gap-1.5">
                    <span class="rounded-full border border-brand-dark/40 px-2 py-1 text-[0.6rem] font-medium text-gray-700">Design</span>
                    <span class="rounded-full border border-brand-dark/40 px-2 py-1 text-[0.6rem] font-medium text-gray-700">Code</span>
                    <span class="rounded-full border border-brand-dark/40 px-2 py-1 text-[0.6rem] font-medium text-gray-700">Grow</span>
                </div>
            </div>

            <div class="hero-float hero-float-left absolute bottom-12 left-0 rounded-2xl bg-brand-dark px-4 py-3 text-white shadow-xl">
                <p class="font-display text-2xl font-extrabold leading-none">50+</p>
                <p class="mt-1 text-[0.65rem] font-semibold uppercase tracking-wider text-white/80">Learning paths</p>
            </div>

            <div class="hero-float hero-float-right hero-float-delay-1 absolute bottom-1/3 right-[-0.25rem] rounded-2xl bg-brand-dark px-4 py-3 text-white shadow-xl">
                <p class="font-display text-2xl font-extrabold leading-none">24/7</p>
                <p class="mt-1 text-[0.65rem] font-semibold uppercase tracking-wider text-white/80">Learn anywhere</p>
            </div>
        </div>
    </div>
</section>

@include('partials.sections.categories', ['bgClass' => 'bg-brand-light'])
@include('partials.sections.courses-grid')
@include('partials.sections.testimonials')

@endsection
