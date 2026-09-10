@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', $assessment->title.' — Assessments — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <a href="{{ route('dashboard.instructor.assessments.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to Assessments</a>
        <h1 class="text-2xl sm:text-3xl font-bold mt-2">{{ $assessment->title }}</h1>
        <p class="text-gray-500 mt-1">{{ $assessment->course?->title }} · {{ strtoupper($assessment->type) }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <form action="{{ route('dashboard.instructor.assessments.toggle', $assessment) }}" method="post">
            @csrf
            @method('PATCH')
            <button type="submit" class="px-5 py-2.5 border border-gray-200 text-sm font-semibold rounded-full hover:bg-gray-50">
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

<div class="grid lg:grid-cols-3 gap-6 mb-8">
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
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Questions</h2>
            <div class="space-y-3 mb-6">
                @forelse($assessment->questions as $question)
                <div class="p-4 rounded-xl border border-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs text-gray-400 font-semibold mb-1">Q{{ $question->sort_order }} · {{ $question->points }} pts</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $question->question }}</p>
                            @if($assessment->type === 'mcq' && is_array($question->options))
                            <ul class="mt-2 space-y-1">
                                @foreach($question->options as $option)
                                <li class="text-xs {{ !empty($option['is_correct']) ? 'text-green-700 font-semibold' : 'text-gray-500' }}">
                                    {{ $option['text'] }}{{ !empty($option['is_correct']) ? ' ✓' : '' }}
                                </li>
                                @endforeach
                            </ul>
                            @elseif($question->model_answer)
                            <p class="mt-2 text-xs text-gray-500"><span class="font-semibold">Model answer:</span> {{ $question->model_answer }}</p>
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
            <form action="{{ route('dashboard.instructor.assessments.questions.store', $assessment) }}" method="post" class="space-y-4" data-validate="instructor-question" novalidate>
                @csrf
                <div class="form-field">
                    <label class="dashboard-label">Question</label>
                    <textarea name="question" rows="3" class="dashboard-input @error('question') is-invalid @enderror" placeholder="Enter the question..."></textarea>
                    @include('partials.field-error', ['name' => 'question'])
                </div>
                <div class="form-field">
                    <label class="dashboard-label">Points</label>
                    <input type="number" name="points" value="{{ $assessment->type === 'essay' ? 10 : 1 }}" min="1" class="dashboard-input">
                </div>

                @if($assessment->type === 'mcq')
                <div class="space-y-3">
                    <p class="text-sm font-medium text-gray-700">Options</p>
                    @for($i = 0; $i < 4; $i++)
                    <div class="flex items-center gap-3">
                        <input type="radio" name="correct_option" value="{{ $i }}" class="checkout-checkbox" {{ $i === 0 ? 'checked' : '' }}>
                        <input type="text" name="options[{{ $i }}][text]" class="dashboard-input" placeholder="Option {{ $i + 1 }}">
                    </div>
                    @endfor
                    @include('partials.field-error', ['name' => 'options'])
                    @include('partials.field-error', ['name' => 'correct_option'])
                </div>
                @else
                <div class="form-field">
                    <label class="dashboard-label">Model Answer</label>
                    <textarea name="model_answer" rows="3" class="dashboard-input @error('model_answer') is-invalid @enderror" placeholder="Ideal answer for AI / manual grading..."></textarea>
                    @include('partials.field-error', ['name' => 'model_answer'])
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
            <div class="p-4 rounded-xl border border-gray-100">
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
                <p class="text-sm text-gray-600 mb-2">Score: <span class="font-bold">{{ $submission->score }}</span></p>
                @endif
                @if($submission->feedback)
                <p class="text-xs text-gray-500 mb-3">{{ $submission->feedback }}</p>
                @endif

                @if($submission->status === 'pending' || $assessment->type === 'essay')
                <form action="{{ route('dashboard.instructor.submissions.grade', $submission) }}" method="post" class="space-y-3" data-validate="instructor-grade" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="form-field">
                        <label class="dashboard-label">Score</label>
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
