@extends('layouts.dashboard', ['role' => 'student'])

@section('title', $course->title.' — My Courses — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
    <div>
        <a href="{{ route('dashboard.student.courses.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to My Courses</a>
        <h1 class="text-2xl sm:text-3xl font-bold mt-2">{{ $course->title }}</h1>
        <p class="text-gray-500 mt-1">{{ $course->instructor?->name }} · {{ $course->category?->name }}</p>
    </div>
    <div class="text-right">
        <p class="text-sm text-gray-500">Your progress</p>
        <p class="text-2xl font-bold text-brand-dark">{{ $enrolment->progress }}%</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-2">About this course</h2>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $course->description ?: ($course->short_description ?: 'No description provided.') }}</p>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Course Content</h2>
            @forelse($course->chapters as $chapter)
            <div class="mb-4 last:mb-0 p-4 rounded-xl border border-gray-100">
                <h3 class="font-semibold text-gray-900 mb-3">Chapter {{ $chapter->sort_order }}: {{ $chapter->title }}</h3>
                <div class="space-y-2">
                    @forelse($chapter->contents as $content)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-gray-50">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $content->title }}</p>
                            <p class="text-xs text-gray-500">{{ strtoupper($content->type) }} · {{ $content->duration_minutes }} min</p>
                        </div>
                        @if($content->url)
                        <a href="{{ $content->url }}" target="_blank" class="text-xs font-semibold text-brand-dark hover:text-brand">Open</a>
                        @endif
                    </div>
                    @if($content->type === 'text' && $content->body)
                    <p class="text-xs text-gray-500 px-3">{{ $content->body }}</p>
                    @endif
                    @empty
                    <p class="text-sm text-gray-500">No lessons in this chapter yet.</p>
                    @endforelse
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500">Course content is being prepared.</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Update Progress</h2>
            <form action="{{ route('dashboard.student.courses.progress', $course) }}" method="post" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="form-field">
                    <label class="dashboard-label" for="progress">Progress (%)</label>
                    <input type="number" id="progress" name="progress" min="0" max="100" value="{{ $enrolment->progress }}" class="dashboard-input">
                </div>
                <button type="submit" class="w-full py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">Save Progress</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Assessments</h2>
            <div class="space-y-3">
                @forelse($course->assessments as $assessment)
                @php $submission = $submissions->get($assessment->id); @endphp
                <div class="p-3 rounded-xl border border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">{{ $assessment->title }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ strtoupper($assessment->type) }}</p>
                    @if($submission)
                    <a href="{{ route('dashboard.student.feedback.show', $submission) }}" class="inline-block mt-2 text-sm font-semibold text-brand-dark hover:text-brand">View result →</a>
                    @else
                    <a href="{{ route('dashboard.student.assessments.show', $assessment) }}" class="inline-block mt-2 text-sm font-semibold text-brand-dark hover:text-brand">Take assessment →</a>
                    @endif
                </div>
                @empty
                <p class="text-sm text-gray-500">No assessments available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
