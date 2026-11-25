@extends('layouts.dashboard', ['role' => 'student'])

@section('title', $content->title.' — '.$course->title.' — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
    <div>
        <a href="{{ route('dashboard.student.courses.show', $course) }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to {{ $course->title }}</a>
        <p class="mt-3 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $chapter->title }} · Lesson {{ $position }} of {{ $totalLessons }}</p>
        <h1 class="text-2xl sm:text-3xl font-bold mt-1">{{ $content->title }}</h1>
        <p class="text-gray-500 mt-1 text-sm">{{ $content->type_label }} · {{ $content->duration_minutes }} min</p>
    </div>
    <div class="sm:w-56">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span>Course progress</span>
            <span class="font-bold text-brand-dark">{{ $enrolment->progress }}%</span>
        </div>
        <div class="mt-1.5 h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-brand-dark rounded-full transition-all duration-700" data-progress="{{ $enrolment->progress }}" style="width: 0"></div>
        </div>
    </div>
</div>

<div class="grid xl:grid-cols-[1fr_20rem] gap-6 items-start">
    <div class="bg-white rounded-2xl p-3 sm:p-6 shadow-sm min-w-0">
        @include('partials.lesson-body', ['content' => $content, 'safeBody' => $safeBody])

        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex gap-2">
                @if($previous)
                <a href="{{ route('dashboard.student.courses.lesson', [$course, $previous]) }}" class="px-4 py-2.5 border border-gray-200 text-sm font-semibold rounded-lg hover:bg-gray-50">← Previous</a>
                @endif
                @if($next)
                <a href="{{ route('dashboard.student.courses.lesson', [$course, $next]) }}" class="px-4 py-2.5 border border-gray-200 text-sm font-semibold rounded-lg hover:bg-gray-50">Next →</a>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                @if($isCompleted)
                <form action="{{ route('dashboard.student.courses.lesson.complete', [$course, $content]) }}" method="post">
                    @csrf
                    <input type="hidden" name="undo" value="1">
                    <button type="submit" class="px-4 py-2.5 border border-gray-200 text-sm font-semibold rounded-lg text-gray-500 hover:bg-gray-50">Mark as not completed</button>
                </form>
                <span class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold rounded-lg bg-brand-light text-brand-dark">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg> Completed
                </span>
                @else
                <form action="{{ route('dashboard.student.courses.lesson.complete', [$course, $content]) }}" method="post">
                    @csrf
                    <input type="hidden" name="go_next" value="1">
                    <button type="submit" id="complete-lesson" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">
                        {{ $next ? 'Mark complete & continue' : 'Mark complete' }}
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Curriculum sidebar --}}
    <aside class="bg-white rounded-2xl p-4 shadow-sm xl:sticky xl:top-24 max-h-[75vh] overflow-y-auto custom-scrollbar">
        <h2 class="text-sm font-bold mb-3">Course content</h2>
        @php $doneIds = $enrolment->completions()->pluck('chapter_content_id'); @endphp
        @foreach($course->chapters as $c)
        <p class="mt-3 first:mt-0 text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ $c->title }}</p>
        <ul class="mt-1.5 space-y-1">
            @foreach($c->contents as $item)
            <li>
                <a href="{{ route('dashboard.student.courses.lesson', [$course, $item]) }}"
                    class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-sm {{ $item->id === $content->id ? 'bg-brand-light text-gray-900 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <span class="w-4 h-4 rounded-full border {{ $doneIds->contains($item->id) ? 'bg-brand-dark border-brand-dark' : 'border-gray-300' }} shrink-0 flex items-center justify-center">
                        @if($doneIds->contains($item->id))<svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>@endif
                    </span>
                    <span class="truncate">{{ $item->title }}</span>
                </a>
            </li>
            @endforeach
        </ul>
        @endforeach
    </aside>
</div>

@endsection
