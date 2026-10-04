@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'Feedback Detail — Student — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

@php
$assessment = $submission->assessment;
$details = $submission->ai_details ?? [];
$questionsDetail = collect($details['questions'] ?? [])->keyBy('question_id');
$mcqDetail = collect($details['mcq'] ?? [])->keyBy('question_id');
$scoreLabel = $submission->score !== null ? rtrim(rtrim(number_format((float) $submission->score, 1), '0'), '.').'%' : '—';
$passed = $submission->score !== null && (float) $submission->score >= $assessment->pass_score;
@endphp

<div class="mb-8">
    <a href="{{ route('dashboard.student.feedback.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to AI Feedback</a>
    <h1 class="text-2xl sm:text-3xl font-bold mt-2">{{ $assessment?->title }}</h1>
    <p class="text-gray-500 mt-1">{{ $assessment?->course?->title }}</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">{{ $submission->ai_model ? 'AI Feedback' : 'Feedback' }}</h2>
                @if($submission->ai_model)
                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-brand-light text-brand-dark">Scored by Google Gemini</span>
                @endif
            </div>

            @if($submission->isPending())
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800 leading-relaxed" id="pending-notice">
                Your submission is waiting for your instructor to review it.
                @if($submission->ai_error)
                <span class="block mt-1 text-xs text-amber-700">AI scoring was temporarily unavailable. You will be notified here once a score is available.</span>
                @endif
            </div>
            @elseif($submission->feedback)
            <div class="p-4 rounded-xl bg-brand-light/40 border border-brand/20 text-sm text-gray-700 leading-relaxed" id="feedback-text">
                {{ $submission->feedback }}
            </div>
            @else
            <p class="text-sm text-gray-500">Feedback is not available yet.</p>
            @endif
        </div>

        @if($assessment && $assessment->questions->isNotEmpty())
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Question Breakdown</h2>
            <div class="space-y-5">
                @foreach($assessment->questions as $index => $question)
                @php
                    $answer = $submission->answers[(string) $question->id] ?? $submission->answers[$question->id] ?? null;
                    $ai = $questionsDetail->get($question->id);
                    $mcq = $mcqDetail->get($question->id);
                @endphp
                <div class="p-4 rounded-xl border border-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs text-gray-400 font-semibold mb-1">Q{{ $index + 1 }}</p>
                            <p class="text-sm font-semibold text-gray-900 whitespace-pre-line">{{ $question->question }}</p>
                        </div>
                        @if($ai)
                        <span class="shrink-0 text-sm font-bold text-brand-dark">{{ rtrim(rtrim(number_format($ai['score'], 2), '0'), '.') }} / {{ $ai['max'] }}</span>
                        @elseif($mcq)
                        <span class="shrink-0 text-xs font-bold px-2.5 py-1 rounded-full {{ $mcq['is_correct'] ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">{{ $mcq['is_correct'] ? 'Correct' : 'Incorrect' }}</span>
                        @endif
                    </div>

                    @if($assessment->type === 'mcq')
                        @php
                            $selectedText = $mcq['selected_text'] ?? ($question->options[(int) $answer]['text'] ?? '—');
                        @endphp
                        <p class="mt-3 text-sm text-gray-600"><span class="text-gray-400">Your answer:</span> {{ $selectedText }}</p>
                        @if($mcq && ! $mcq['is_correct'] && $mcq['correct_text'])
                        <p class="mt-1 text-sm text-green-700"><span class="text-gray-400">Correct answer:</span> {{ $mcq['correct_text'] }}</p>
                        @endif
                        @if($question->explanation)
                        <p class="mt-2 text-xs text-gray-500">💡 {{ $question->explanation }}</p>
                        @endif
                    @else
                        <div class="mt-3 p-3 rounded-lg bg-gray-50 text-sm text-gray-700 whitespace-pre-line">{{ $answer ?: '—' }}</div>

                        @if($ai)
                        <div class="mt-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Rubric breakdown</p>
                            <div class="space-y-3">
                                @foreach($ai['criteria'] as $criterion)
                                <div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium text-gray-800">{{ $criterion['name'] }} <span class="text-xs text-gray-400">({{ $criterion['weight'] }}%)</span></span>
                                        <span class="font-semibold text-gray-700">{{ rtrim(rtrim(number_format($criterion['score'], 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($criterion['max'], 2), '0'), '.') }}</span>
                                    </div>
                                    <div class="mt-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-dark rounded-full" style="width: {{ $criterion['percent'] }}%"></div>
                                    </div>
                                    @if($criterion['comment'])
                                    <p class="mt-1 text-xs text-gray-500">{{ $criterion['comment'] }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @if($ai['feedback'])
                            <p class="mt-4 text-sm text-gray-600 leading-relaxed"><span class="font-semibold text-gray-800">Feedback:</span> {{ $ai['feedback'] }}</p>
                            @endif
                        </div>
                        @endif
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm h-fit">
        <h2 class="text-lg font-bold mb-4">Result Summary</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Score</span>
                <span class="text-2xl font-bold text-brand-dark" id="result-score">{{ $scoreLabel }}</span>
            </div>
            @if($submission->score !== null)
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Result</span>
                <span class="dashboard-badge {{ $passed ? 'dashboard-badge-green' : 'dashboard-badge-amber' }}">{{ $passed ? 'Passed' : 'Not passed' }}</span>
            </div>
            @endif
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
                <span class="text-sm font-medium">{{ $assessment?->pass_score }}%</span>
            </div>
        </div>
        <a href="{{ route('dashboard.student.courses.show', $assessment->course_id) }}" class="mt-6 block text-center px-4 py-2.5 border border-gray-200 text-sm font-semibold rounded-lg hover:bg-gray-50">Back to course</a>
    </div>
</div>

@endsection
