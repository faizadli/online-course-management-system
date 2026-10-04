@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'Edit Content — '.$course->title.' — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <a href="{{ route('dashboard.instructor.builder.show', $course) }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to Course Builder</a>
    <h1 class="text-2xl sm:text-3xl font-bold mt-2">Edit {{ $content->type_label }} Lesson</h1>
    <p class="text-gray-500 mt-1">{{ $course->title }} · {{ $chapter->title }}</p>
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm max-w-3xl">
    @include('partials.builder-content-form', [
        'action' => route('dashboard.instructor.builder.contents.update', [$course, $chapter, $content]),
        'type' => $content->type,
        'uid' => 'edit-'.$content->id,
        'content' => $content,
        'submitLabel' => 'Save Changes',
    ])
</div>

@endsection
