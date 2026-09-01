@extends('layouts.landing')

@section('title', 'About — SkillForge')

@section('content')

@include('partials.page-header', [
    'eyebrow' => 'About SkillForge',
    'title' => 'Learn With Purpose, Grow With Confidence',
    'description' => 'SkillForge is an online learning platform built for professionals, creators, and lifelong learners who want practical skills that translate into real-world results.',
    'bgClass' => 'bg-white',
])

{{-- Mission --}}
<section class="py-16 lg:py-20 bg-surface">
    <div class="page-container">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="reveal">
                <h2 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">Our Mission</h2>
                <p class="mt-4 text-gray-600 leading-relaxed">
                    We believe everyone deserves access to high-quality education — taught by industry practitioners, designed for real outcomes, and flexible enough to fit any schedule.
                </p>
                <p class="mt-4 text-gray-600 leading-relaxed">
                    From design and development to business and personal growth, SkillForge connects learners with curated paths that match their ambitions.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-4 reveal reveal-delay-150">
                <div class="p-6 rounded-2xl bg-white text-center">
                    <p class="text-3xl sm:text-4xl font-bold text-gray-900">120+</p>
                    <p class="mt-1 text-sm text-gray-500">Courses</p>
                </div>
                <div class="p-6 rounded-2xl bg-brand-light text-center">
                    <p class="text-3xl sm:text-4xl font-bold text-gray-900">50K+</p>
                    <p class="mt-1 text-sm text-gray-500">Learners</p>
                </div>
                <div class="p-6 rounded-2xl bg-brand-light text-center">
                    <p class="text-3xl sm:text-4xl font-bold text-gray-900">200+</p>
                    <p class="mt-1 text-sm text-gray-500">Instructors</p>
                </div>
                <div class="p-6 rounded-2xl bg-white text-center">
                    <p class="text-3xl sm:text-4xl font-bold text-gray-900">4.8</p>
                    <p class="mt-1 text-sm text-gray-500">Avg. Rating</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Categories --}}
@include('partials.sections.categories', ['bgClass' => 'bg-white'])

@endsection
