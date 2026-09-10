@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'Course Builder — Instructor — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Course Builder</h1>
        <p class="text-gray-500 mt-1">Select a course to edit information, chapters, and learning materials.</p>
    </div>
    <a href="{{ route('dashboard.instructor.courses.index', ['create' => 1]) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
        + Create Course
    </a>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($courses as $course)
    <a href="{{ route('dashboard.instructor.builder.show', $course) }}" class="block bg-white rounded-2xl p-5 shadow-sm border border-transparent hover:border-brand/40 hover:shadow-md transition-all duration-300">
        <div class="flex items-start justify-between gap-3 mb-3">
            <h2 class="text-base font-bold text-gray-900">{{ $course->title }}</h2>
            <span class="dashboard-badge {{ $course->status === 'published' ? 'dashboard-badge-green' : 'dashboard-badge-gray' }} shrink-0">
                {{ ucfirst($course->status) }}
            </span>
        </div>
        <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ $course->short_description ?: 'No description yet.' }}</p>
        <div class="flex items-center justify-between text-xs text-gray-400">
            <span>{{ $course->chapters_count }} chapters · {{ $course->assessments_count }} assessments</span>
            <span class="font-semibold text-brand-dark">Open builder →</span>
        </div>
    </a>
    @empty
    <div class="sm:col-span-2 xl:col-span-3 bg-white rounded-2xl p-10 shadow-sm text-center">
        <p class="text-gray-500 mb-4">You don't have any courses yet.</p>
        <a href="{{ route('dashboard.instructor.courses.index', ['create' => 1]) }}" class="inline-flex px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-colors">
            Create your first course
        </a>
    </div>
    @endforelse
</div>

@endsection
