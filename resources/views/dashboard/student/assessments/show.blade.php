@extends('layouts.dashboard', ['role' => 'student'])

@section('title', $assessment->title.' — Assessment — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <a href="{{ route('dashboard.student.assessments.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to Assessments</a>
    <h1 class="text-2xl sm:text-3xl font-bold mt-2">{{ $assessment->title }}</h1>
    <p class="text-gray-500 mt-1">{{ $assessment->course?->title }} · {{ strtoupper($assessment->type) }} · Pass score {{ $assessment->pass_score }}%</p>
</div>

@if($submission)
<div class="bg-white rounded-2xl p-6 shadow-sm max-w-3xl">
    <h2 class="text-lg font-bold mb-2">Already submitted</h2>
    <p class="text-sm text-gray-500 mb-4">You submitted this assessment on {{ $submission->submitted_at?->format('M d, Y H:i') }}.</p>
    <div class="flex items-center gap-4 mb-4">
        <span class="dashboard-badge {{ $submission->status === 'pending' ? 'dashboard-badge-amber' : 'dashboard-badge-green' }}">
            {{ str_replace('_', ' ', ucfirst($submission->status)) }}
        </span>
        @if($submission->score !== null)
        <span class="text-2xl font-bold text-brand-dark">{{ $submission->score }}%</span>
        @endif
    </div>
    @if($submission->feedback)
    <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $submission->feedback }}</p>
    @endif
    <a href="{{ route('dashboard.student.feedback.show', $submission) }}" class="inline-flex px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">
        View full feedback
    </a>
</div>
@else
<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm max-w-3xl">
    @if($assessment->description)
    <p class="text-sm text-gray-600 mb-6">{{ $assessment->description }}</p>
    @endif

    <form action="{{ route('dashboard.student.assessments.submit', $assessment) }}" method="post" class="space-y-6" data-validate="student-assessment" novalidate>
        @csrf

        @foreach($assessment->questions as $index => $question)
        <div class="form-field p-4 rounded-xl border border-gray-100">
            <p class="text-xs font-semibold text-gray-400 mb-2">Question {{ $index + 1 }} · {{ $question->points }} pts</p>
            <p class="text-sm font-semibold text-gray-900 mb-4">{{ $question->question }}</p>

            @if($assessment->type === 'mcq')
                <div class="space-y-2">
                    @foreach(($question->options ?? []) as $optionIndex => $option)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $optionIndex }}" class="checkout-checkbox">
                        <span class="text-sm text-gray-700">{{ $option['text'] }}</span>
                    </label>
                    @endforeach
                </div>
            @else
                <textarea name="answers[{{ $question->id }}]" rows="5" class="dashboard-input" placeholder="Write your answer here..."></textarea>
            @endif
        </div>
        @endforeach

        @include('partials.field-error', ['name' => 'answers'])

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">
                Submit Assessment
            </button>
        </div>
    </form>
</div>
@endif

@endsection
