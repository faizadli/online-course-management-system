@extends('layouts.landing')

@section('title', $content->title . ' (Free preview) — SkillForge')

@section('content')

<section class="pt-24 lg:pt-28 pb-16 bg-white">
    <div class="page-container max-w-4xl">
        <a href="{{ route('courses.show', $course->slug) }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to {{ $course->title }}</a>
        <p class="mt-4 inline-block text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-brand-light text-brand-dark">Free preview</p>
        <h1 class="mt-2 font-display text-2xl sm:text-3xl font-bold text-gray-900">{{ $content->title }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $content->type_label }} · {{ $content->duration_minutes }} min · {{ $course->instructor?->name }}</p>

        <div class="mt-6 checkout-panel !p-4 sm:!p-6">
            @include('partials.lesson-body', ['content' => $content, 'safeBody' => $safeBody])
        </div>

        <div class="mt-8 p-5 rounded-2xl bg-brand-light flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <p class="text-sm text-gray-700">Enjoying this lesson? Enrol to unlock the full course, quizzes and AI-scored assessments.</p>
            <a href="{{ route('courses.show', $course->slug) }}" class="checkout-cta !w-auto px-6 shrink-0">View course · {{ $course->price_label }}</a>
        </div>
    </div>
</section>

@endsection
