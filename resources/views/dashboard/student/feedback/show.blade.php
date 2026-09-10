@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'Feedback Detail — Student — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <a href="{{ route('dashboard.student.feedback.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to AI Feedback</a>
    <h1 class="text-2xl sm:text-3xl font-bold mt-2">{{ $submission->assessment?->title }}</h1>
    <p class="text-gray-500 mt-1">{{ $submission->assessment?->course?->title }}</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-4">Feedback</h2>
        @if($submission->feedback)
        <div class="p-4 rounded-xl bg-brand-light/40 border border-brand/20 text-sm text-gray-700 leading-relaxed">
            {{ $submission->feedback }}
        </div>
        @else
        <p class="text-sm text-gray-500">Feedback is not available yet.</p>
        @endif

        @if(is_array($submission->answers) && count($submission->answers))
        <h2 class="text-lg font-bold mt-8 mb-4">Your Answers</h2>
        <div class="space-y-4">
            @foreach($submission->assessment->questions as $index => $question)
            @php
                $answer = $submission->answers[(string) $question->id] ?? $submission->answers[$question->id] ?? null;
                $display = $answer;
                if ($submission->assessment->type === 'mcq' && is_array($question->options) && isset($question->options[(int) $answer])) {
                    $display = $question->options[(int) $answer]['text'] ?? $answer;
                }
            @endphp
            <div class="p-4 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-400 font-semibold mb-1">Q{{ $index + 1 }}</p>
                <p class="text-sm font-semibold text-gray-900 mb-2">{{ $question->question }}</p>
                <p class="text-sm text-gray-600">{{ $display ?: '—' }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm h-fit">
        <h2 class="text-lg font-bold mb-4">Result Summary</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Score</span>
                <span class="text-2xl font-bold text-brand-dark">{{ $submission->score !== null ? $submission->score.'%' : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Status</span>
                <span class="dashboard-badge {{ $submission->status === 'pending' ? 'dashboard-badge-amber' : ($submission->status === 'ai_graded' ? 'dashboard-badge-green' : 'dashboard-badge-blue') }}">
                    {{ str_replace('_', ' ', ucfirst($submission->status)) }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Submitted</span>
                <span class="text-sm font-medium">{{ $submission->submitted_at?->format('M d, Y H:i') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Graded</span>
                <span class="text-sm font-medium">{{ $submission->graded_at?->format('M d, Y H:i') ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Pass score</span>
                <span class="text-sm font-medium">{{ $submission->assessment?->pass_score }}%</span>
            </div>
        </div>
    </div>
</div>

@endsection
