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
    <div class="sm:w-64">
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">Course progress</span>
            <span class="font-bold text-brand-dark" id="course-progress-value">{{ $enrolment->progress }}%</span>
        </div>
        <div class="mt-2 h-2.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-brand-dark rounded-full transition-all duration-700" data-progress="{{ $enrolment->progress }}" style="width: 0"></div>
        </div>
        @if($nextLesson)
        <a href="{{ route('dashboard.student.courses.lesson', [$course, $nextLesson]) }}" id="continue-learning" class="mt-3 block text-center px-4 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">
            {{ $completedIds->isEmpty() ? 'Start learning' : 'Continue learning' }}
        </a>
        @elseif($enrolment->progress >= 100)
        <p class="mt-3 text-center text-sm font-semibold text-brand-dark">🎉 Course completed</p>
        @endif
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        @if($course->description || $course->short_description)
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-2">About this course</h2>
            <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $course->description ?: $course->short_description }}</p>
        </div>
        @endif

        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Course Content</h2>
            @forelse($course->chapters as $chapter)
            <div class="mb-4 last:mb-0 p-4 rounded-xl border border-gray-100">
                <h3 class="font-semibold text-gray-900 mb-3">Chapter {{ $loop->iteration }}: {{ $chapter->title }}</h3>
                <div class="space-y-2">
                    @foreach($chapter->contents as $content)
                    @php $done = $completedIds->contains($content->id); @endphp
                    <a href="{{ route('dashboard.student.courses.lesson', [$course, $content]) }}" class="lesson-row flex items-center gap-3 p-3 rounded-lg bg-gray-50 hover:bg-brand-light transition-colors">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 {{ $done ? 'bg-brand-dark text-white' : 'bg-white border border-gray-200 text-gray-400' }}">
                            @if($done)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @elseif($content->type === 'video')
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/></svg>
                            @elseif($content->type === 'pdf')
                            <span class="text-[9px] font-bold">PDF</span>
                            @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $content->title }}</p>
                            <p class="text-xs text-gray-500">{{ $content->type_label }} · {{ $content->duration_minutes }} min</p>
                        </div>
                        <span class="text-xs font-semibold text-brand-dark shrink-0">{{ $done ? 'Review' : 'Open' }}</span>
                    </a>
                    @endforeach

                    @foreach($chapter->assessments as $assessment)
                    @php $submission = $submissions->get($assessment->id); @endphp
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-brand/30 bg-brand-light/40">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 {{ $submission ? 'bg-brand-dark text-white' : 'bg-white border border-brand text-brand-dark' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $assessment->title }}</p>
                            <p class="text-xs text-gray-500">{{ $assessment->type === 'mcq' ? 'MCQ assessment · instant score' : 'Essay assessment · AI scored' }}</p>
                        </div>
                        @if($submission)
                        <a href="{{ route('dashboard.student.feedback.show', $submission) }}" class="text-xs font-semibold text-brand-dark">View result</a>
                        @else
                        <a href="{{ route('dashboard.student.assessments.show', $assessment) }}" class="text-xs font-semibold text-brand-dark">Take assessment</a>
                        @endif
                    </div>
                    @endforeach

                    @if($chapter->contents->isEmpty() && $chapter->assessments->isEmpty())
                    <p class="text-sm text-gray-500">No lessons in this chapter yet.</p>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500">Course content is being prepared.</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Assessments</h2>
            <div class="space-y-3">
                @forelse($course->assessments as $assessment)
                @php $submission = $submissions->get($assessment->id); @endphp
                <div class="p-3 rounded-xl border border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">{{ $assessment->title }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ strtoupper($assessment->type) }}
                        @if($submission && $submission->score !== null) · Score {{ rtrim(rtrim(number_format((float) $submission->score, 1), '0'), '.') }}% @endif
                        @if($submission && $submission->isPending()) · Awaiting instructor review @endif
                    </p>
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
