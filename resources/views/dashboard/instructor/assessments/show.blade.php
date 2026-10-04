@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', $assessment->title.' — Assessments — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <a href="{{ route('dashboard.instructor.assessments.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to Assessments</a>
        <h1 class="text-2xl sm:text-3xl font-bold mt-2">{{ $assessment->title }}</h1>
        <p class="text-gray-500 mt-1">{{ $assessment->course?->title }} · {{ $assessment->type === 'mcq' ? 'MCQ' : 'Essay / Short answer (AI scored)' }}@if($assessment->chapter) · {{ $assessment->chapter->title }}@endif</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('dashboard.instructor.builder.show', $assessment->course_id) }}" class="px-5 py-2.5 border border-gray-200 text-sm font-semibold rounded-full hover:bg-gray-50">Course Builder</a>
        <form action="{{ route('dashboard.instructor.assessments.toggle', $assessment) }}" method="post">
            @csrf
            @method('PATCH')
            <button type="submit" id="toggle-assessment" class="px-5 py-2.5 text-sm font-semibold rounded-full {{ $assessment->status === 'published' ? 'border border-gray-200 hover:bg-gray-50' : 'bg-brand-dark text-white hover:bg-brand' }}">
                {{ $assessment->status === 'published' ? 'Unpublish' : 'Publish' }}
            </button>
        </form>
        <form action="{{ route('dashboard.instructor.assessments.destroy', $assessment) }}" method="post" onsubmit="return confirm('Delete this assessment?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-red-500 rounded-full hover:bg-red-50">Delete</button>
        </form>
    </div>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ $assessment->questions->count() }}</p>
        <p class="text-sm text-gray-500 mt-1">Questions</p>
    </div>
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ $assessment->submissions->count() }}</p>
        <p class="text-sm text-gray-500 mt-1">Submissions</p>
    </div>
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ $assessment->pass_score }}%</p>
        <p class="text-sm text-gray-500 mt-1">Pass Score</p>
    </div>
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ $assessment->submissions->whereNotNull('score')->count() ? round($assessment->submissions->whereNotNull('score')->avg('score'), 1).'%' : '—' }}</p>
        <p class="text-sm text-gray-500 mt-1">Average Score</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Settings</h2>
            <form action="{{ route('dashboard.instructor.assessments.update', $assessment) }}" method="post" class="grid sm:grid-cols-3 gap-3" novalidate>
                @csrf
                @method('PUT')
                <div class="form-field sm:col-span-3">
                    <label class="dashboard-label">Title</label>
                    <input type="text" name="title" value="{{ old('title', $assessment->title) }}" class="dashboard-input">
                </div>
                <div class="form-field sm:col-span-3">
                    <label class="dashboard-label">Instructions</label>
                    <textarea name="description" rows="2" class="dashboard-input">{{ old('description', $assessment->description) }}</textarea>
                </div>
                <div class="form-field">
                    <label class="dashboard-label">Pass score (%)</label>
                    <input type="number" name="pass_score" value="{{ old('pass_score', $assessment->pass_score) }}" min="0" max="100" class="dashboard-input">
                </div>
                <div class="form-field">
                    <label class="dashboard-label">Time limit (min)</label>
                    <input type="number" name="time_limit_minutes" value="{{ old('time_limit_minutes', $assessment->time_limit_minutes) }}" min="1" class="dashboard-input" placeholder="None">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2.5 border border-gray-200 text-sm font-semibold rounded-lg hover:bg-gray-50">Save</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Questions</h2>
            <div class="space-y-3 mb-6">
                @forelse($assessment->questions as $question)
                <div class="p-4 rounded-xl border border-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 font-semibold mb-1">Q{{ $loop->iteration }} · {{ $question->points }} pts</p>
                            <p class="text-sm font-semibold text-gray-900 whitespace-pre-line">{{ $question->question }}</p>
                            @if($assessment->type === 'mcq' && is_array($question->options))
                            <ul class="mt-2 space-y-1">
                                @foreach($question->options as $option)
                                <li class="text-xs {{ !empty($option['is_correct']) ? 'text-green-700 font-semibold' : 'text-gray-500' }}">
                                    {{ $option['text'] }}{{ !empty($option['is_correct']) ? ' ✓' : '' }}
                                </li>
                                @endforeach
                            </ul>
                            @if($question->explanation)<p class="mt-2 text-xs text-gray-500">💡 {{ $question->explanation }}</p>@endif
                            @else
                            @if($question->model_answer)
                            <p class="mt-2 text-xs text-gray-500"><span class="font-semibold">Model answer:</span> {{ $question->model_answer }}</p>
                            @endif
                            <div class="mt-3 overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="text-left text-gray-400"><th class="py-1 pr-3 font-semibold">Criteria</th><th class="py-1 pr-3 font-semibold">Weight</th><th class="py-1 font-semibold">Max pts</th></tr>
                                    </thead>
                                    <tbody class="text-gray-600">
                                        @php $rubricRows = $question->effectiveRubric(); $wSum = max(1, array_sum(array_column($rubricRows, 'weight'))); @endphp
                                        @foreach($rubricRows as $row)
                                        <tr class="border-t border-gray-50">
                                            <td class="py-1 pr-3"><span class="font-medium text-gray-800">{{ $row['criteria'] }}</span> @if($row['description'])<span class="text-gray-400">— {{ $row['description'] }}</span>@endif</td>
                                            <td class="py-1 pr-3">{{ $row['weight'] }}%</td>
                                            <td class="py-1">{{ round($question->points * $row['weight'] / $wSum, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if(empty($question->rubric))<p class="mt-1 text-[11px] text-gray-400">Default rubric applied (no custom criteria defined).</p>@endif
                            </div>
                            @endif
                        </div>
                        <form action="{{ route('dashboard.instructor.assessments.questions.destroy', [$assessment, $question]) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-500">Delete</button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500">No questions yet.</p>
                @endforelse
            </div>

            <h3 class="text-sm font-bold mb-3">Add Question</h3>
            <form action="{{ route('dashboard.instructor.assessments.questions.store', $assessment) }}" method="post" class="space-y-4" id="question-form" novalidate>
                @csrf
                <div class="form-field">
                    <label class="dashboard-label">Question</label>
                    <textarea name="question" rows="3" class="dashboard-input @error('question') is-invalid @enderror" placeholder="Enter the question...">{{ old('question') }}</textarea>
                    @include('partials.field-error', ['name' => 'question'])
                </div>
                <div class="form-field">
                    <label class="dashboard-label">{{ $assessment->type === 'essay' ? 'Maximum score (points)' : 'Points' }}</label>
                    <input type="number" name="points" value="{{ old('points', $assessment->type === 'essay' ? 10 : 1) }}" min="1" class="dashboard-input">
                </div>

                @if($assessment->type === 'mcq')
                <div class="space-y-3">
                    <p class="text-sm font-medium text-gray-700">Options <span class="text-gray-400 font-normal">(select the correct one)</span></p>
                    @for($i = 0; $i < 4; $i++)
                    <div class="flex items-center gap-3">
                        <input type="radio" name="correct_option" value="{{ $i }}" class="checkout-checkbox" {{ (int) old('correct_option', 0) === $i ? 'checked' : '' }}>
                        <input type="text" name="options[{{ $i }}][text]" value="{{ old("options.$i.text") }}" class="dashboard-input" placeholder="Option {{ $i + 1 }}">
                    </div>
                    @endfor
                    @include('partials.field-error', ['name' => 'options'])
                    @include('partials.field-error', ['name' => 'correct_option'])
                </div>
                <div class="form-field">
                    <label class="dashboard-label">Explanation <span class="text-gray-400 font-normal">(optional, shown after submission)</span></label>
                    <input type="text" name="explanation" value="{{ old('explanation') }}" class="dashboard-input" placeholder="Why is this the correct answer?">
                </div>
                @else
                <div class="form-field">
                    <label class="dashboard-label">Model Answer</label>
                    <textarea name="model_answer" rows="3" class="dashboard-input @error('model_answer') is-invalid @enderror" placeholder="Ideal answer used by the AI as a reference...">{{ old('model_answer') }}</textarea>
                    @include('partials.field-error', ['name' => 'model_answer'])
                </div>

                <div class="form-field" id="rubric-builder">
                    <div class="flex items-center justify-between">
                        <label class="dashboard-label !mb-0">Grading rubric <span class="text-gray-400 font-normal">(weights must total 100%)</span></label>
                        <span class="text-xs font-semibold" id="rubric-total">Total: 0%</span>
                    </div>
                    <div class="mt-2 space-y-2" id="rubric-rows">
                        @php
                            $oldRubric = old('rubric', [
                                ['criteria' => 'Correctness of Concept', 'description' => 'Accurately explains the key concept', 'weight' => 40],
                                ['criteria' => 'Use of Examples', 'description' => 'Provides valid, clear examples', 'weight' => 30],
                                ['criteria' => 'Clarity of Explanation', 'description' => 'Well organised and free of major errors', 'weight' => 30],
                            ]);
                        @endphp
                        @foreach($oldRubric as $i => $row)
                        <div class="grid grid-cols-[1fr_5rem_2rem] sm:grid-cols-[10rem_1fr_5rem_2rem] gap-2 items-center rubric-row">
                            <input type="text" name="rubric[{{ $i }}][criteria]" value="{{ $row['criteria'] ?? '' }}" class="dashboard-input" placeholder="Criteria">
                            <input type="text" name="rubric[{{ $i }}][description]" value="{{ $row['description'] ?? '' }}" class="dashboard-input hidden sm:block" placeholder="Description">
                            <input type="number" name="rubric[{{ $i }}][weight]" value="{{ $row['weight'] ?? '' }}" min="0" max="100" class="dashboard-input rubric-weight" placeholder="%">
                            <button type="button" class="rubric-remove text-gray-400 hover:text-red-500" aria-label="Remove criterion">✕</button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" id="rubric-add" class="mt-2 text-sm font-semibold text-brand-dark hover:text-brand">+ Add criterion</button>
                    @include('partials.field-error', ['name' => 'rubric'])
                </div>
                @endif

                <button type="submit" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">Add Question</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm h-fit">
        <h2 class="text-lg font-bold mb-4">Submissions</h2>
        <div class="space-y-4">
            @forelse($assessment->submissions as $submission)
            @php $aiQuestions = collect($submission->ai_details['questions'] ?? []); @endphp
            <div class="p-4 rounded-xl border border-gray-100" data-submission="{{ $submission->id }}">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $submission->student?->name }}</p>
                        <p class="text-xs text-gray-500">{{ $submission->submitted_at?->format('M d, Y H:i') }}</p>
                    </div>
                    <span class="dashboard-badge {{ $submission->status === 'pending' ? 'dashboard-badge-amber' : ($submission->status === 'ai_graded' ? 'dashboard-badge-green' : 'dashboard-badge-blue') }}">
                        {{ str_replace('_', ' ', ucfirst($submission->status)) }}
                    </span>
                </div>
                @if($submission->score !== null)
                <p class="text-sm text-gray-600 mb-2">Score: <span class="font-bold">{{ rtrim(rtrim(number_format((float) $submission->score, 1), '0'), '.') }}%</span>
                    @if($submission->ai_model)<span class="text-xs text-gray-400">· AI ({{ $submission->ai_model }})</span>@endif
                </p>
                @endif
                @if($submission->ai_error && $submission->isPending())
                <p class="text-xs text-amber-700 bg-amber-50 rounded-lg p-2 mb-2">AI scoring failed: {{ \Illuminate\Support\Str::limit($submission->ai_error, 140) }}</p>
                @endif
                @if($submission->feedback)
                <p class="text-xs text-gray-500 mb-3">{{ \Illuminate\Support\Str::limit($submission->feedback, 260) }}</p>
                @endif

                @if($assessment->type === 'essay')
                <details class="mb-3 text-xs">
                    <summary class="cursor-pointer font-semibold text-gray-600">View student answers @if($aiQuestions->isNotEmpty()) &amp; AI breakdown @endif</summary>
                    <div class="mt-2 space-y-3">
                        @foreach($assessment->questions as $question)
                        @php
                            $answer = $submission->answers[(string) $question->id] ?? $submission->answers[$question->id] ?? '—';
                            $ai = $aiQuestions->firstWhere('question_id', $question->id);
                        @endphp
                        <div class="p-3 rounded-lg bg-gray-50">
                            <p class="font-semibold text-gray-700">{{ $question->question }}</p>
                            <p class="mt-1 text-gray-600 whitespace-pre-line">{{ $answer }}</p>
                            @if($ai)
                            <ul class="mt-2 space-y-0.5 text-gray-500">
                                @foreach($ai['criteria'] as $criterion)
                                <li>• {{ $criterion['name'] }}: <strong>{{ rtrim(rtrim(number_format($criterion['score'], 2), '0'), '.') }}/{{ rtrim(rtrim(number_format($criterion['max'], 2), '0'), '.') }}</strong> — {{ $criterion['comment'] }}</li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </details>

                <form action="{{ route('dashboard.instructor.submissions.rescore', $submission) }}" method="post" class="mb-3">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-brand-dark hover:text-brand">↻ {{ $submission->isPending() ? 'Score with AI now' : 'Re-run AI scoring' }}</button>
                </form>
                @endif

                @if($submission->status === 'pending' || $assessment->type === 'essay')
                <form action="{{ route('dashboard.instructor.submissions.grade', $submission) }}" method="post" class="space-y-3" data-validate="instructor-grade" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="form-field">
                        <label class="dashboard-label">{{ $submission->isPending() ? 'Score' : 'Override score (%)' }}</label>
                        <input type="number" name="score" value="{{ old('score', $submission->score) }}" min="0" max="100" step="0.1" class="dashboard-input">
                    </div>
                    <div class="form-field">
                        <label class="dashboard-label">Feedback</label>
                        <textarea name="feedback" rows="2" class="dashboard-input">{{ old('feedback', $submission->feedback) }}</textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">
                        Save Grade
                    </button>
                </form>
                @endif
            </div>
            @empty
            <p class="text-sm text-gray-500">No submissions yet.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection
