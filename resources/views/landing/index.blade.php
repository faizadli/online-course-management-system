@extends('layouts.landing')

@section('title', 'SkillForge — Where Ambition Meets Expertise')

@section('nav_variant', 'hero')

@section('content')

{{-- Hero Section with integrated Navbar --}}
<section class="relative min-h-[100dvh]">
    <div class="absolute inset-0 overflow-hidden">
        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1920&h=1080&fit=crop&q=80"
            alt="Professional learning online"
            class="hero-bg-image w-full h-full object-cover object-center lg:object-[75%_center] xl:object-right scale-105">
        <div class="absolute inset-0 bg-gradient-to-r from-black/50 via-black/25 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-black/15"></div>
        <div class="hero-left-blur" aria-hidden="true"></div>
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-white/10 to-transparent backdrop-blur-[2px]"></div>
    </div>

    <div class="relative z-10 flex min-h-[100dvh] items-center w-full px-5 sm:px-8 md:px-12 lg:px-14 xl:px-16">
        <div class="w-full max-w-[1600px] mx-auto lg:grid lg:grid-cols-2 lg:items-center lg:gap-x-12 xl:gap-x-20">
            <div class="relative text-left px-2 py-6 sm:px-4 sm:py-8 lg:px-6 lg:py-10">
                <h1 class="relative z-10 font-display max-w-2xl lg:max-w-none">
                    <span class="hero-line hero-line-1 hero-headline-row block text-3xl sm:text-4xl md:text-4xl lg:text-[2.35rem] xl:text-5xl 2xl:text-6xl font-extrabold leading-[1.15] tracking-tight text-white">
                        Learn With Purpose,
                    </span>
                    <span class="hero-line hero-line-2 hero-headline-row block mt-1 sm:mt-2 text-3xl sm:text-4xl md:text-4xl lg:text-[2.35rem] xl:text-5xl 2xl:text-6xl font-extrabold leading-[1.15] tracking-tight text-white">
                        Grow Your Potential,
                    </span>
                    <span class="hero-line hero-line-3 hero-headline-row block mt-1 sm:mt-2 text-3xl sm:text-4xl md:text-4xl lg:text-[2.35rem] xl:text-5xl 2xl:text-6xl font-extrabold leading-[1.15] tracking-tight text-white">
                        Create Real Impact,
                    </span>
                    <span class="hero-line hero-line-4 hero-headline-row block mt-1 sm:mt-2 text-3xl sm:text-4xl md:text-4xl lg:text-[2.35rem] xl:text-5xl 2xl:text-6xl font-extrabold leading-[1.15] tracking-tight text-white">
                        Shape Your <span class="hero-scratch-word relative inline-block text-brand">Future.<svg class="hero-scratch-svg absolute left-0 w-full pointer-events-none" viewBox="0 0 220 14" fill="none" preserveAspectRatio="none" aria-hidden="true"><path class="hero-scratch-path" d="M2 10 C40 2, 80 12, 120 4 S200 10, 218 6" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/></svg></span>
                    </span>
                </h1>
            </div>
            <div class="hidden lg:block" aria-hidden="true"></div>
        </div>
    </div>

    <div class="absolute bottom-6 sm:bottom-8 left-0 right-0 z-10 flex justify-center">
        <a href="#categories" class="flex flex-col items-center gap-2 text-white/60 hover:text-brand transition-colors duration-300 animate-bounce">
            <span class="text-xs font-medium tracking-widest uppercase hidden sm:block">Scroll</span>
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
        </a>
    </div>
</section>

@include('partials.sections.categories')
@include('partials.sections.courses-grid')
@include('partials.sections.testimonials')

@endsection
