@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'Course Builder — '.$course->title.' — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

@php
$statusClass = ['published' => 'dashboard-badge-green', 'draft' => 'dashboard-badge-gray', 'archived' => 'dashboard-badge-amber'][$course->status] ?? 'dashboard-badge-gray';
$canPublish = ! in_array(false, $checklist, true);
$openForm = old('_form');
@endphp

<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
    <div>
        <a href="{{ route('dashboard.instructor.courses.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to My Courses</a>
        <h1 class="text-2xl sm:text-3xl font-bold mt-2">Course Builder</h1>
        <p class="text-gray-500 mt-1">{{ $course->title }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center px-4 py-2.5 text-sm font-semibold rounded-full dashboard-badge {{ $statusClass }}" id="course-status-badge">{{ ucfirst($course->status) }}</span>
        <a href="{{ route('dashboard.instructor.builder.preview', $course) }}" target="_blank" class="px-5 py-2.5 border border-gray-200 text-sm font-semibold rounded-full hover:bg-gray-50 transition-all duration-300">Preview</a>
        <form action="{{ route('dashboard.instructor.courses.toggle', $course) }}" method="post">
            @csrf
            @method('PATCH')
            <button type="submit" id="publish-button" class="px-5 py-2.5 text-sm font-semibold rounded-full transition-all duration-300 {{ $course->status === 'published' ? 'border border-gray-200 hover:bg-gray-50' : 'bg-brand-dark text-white hover:bg-brand' }}">
                {{ $course->status === 'published' ? 'Unpublish' : 'Publish Course' }}
            </button>
        </form>
        <form action="{{ route('dashboard.instructor.courses.archive', $course) }}" method="post" onsubmit="return confirm('{{ $course->status === 'archived' ? 'Restore this course to draft?' : 'Archive this course? It will be removed from the catalogue.' }}')">
            @csrf
            @method('PATCH')
            <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-gray-500 rounded-full hover:bg-gray-50">{{ $course->status === 'archived' ? 'Restore' : 'Archive' }}</button>
        </form>
    </div>
</div>

{{-- Course-level performance report (FR10) --}}
<div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3 sm:gap-4 mb-8" id="course-report">
    @foreach([
        ['Students enrolled', number_format($report['enrolments'])],
        ['Revenue', '$'.number_format($report['revenue'], 2)],
        ['Avg. progress', $report['avg_progress'].'%'],
        ['Completed', number_format($report['completed'])],
        ['Submissions', number_format($report['submissions'])],
        ['Avg. score', $report['avg_score'] !== null ? round($report['avg_score'], 1).'%' : '—'],
        ['Awaiting review', number_format($report['pending'])],
    ] as [$label, $value])
    <div class="dashboard-stat-card !p-4">
        <p class="text-xl sm:text-2xl font-bold">{{ $value }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $label }}</p>
    </div>
    @endforeach
</div>

@if($course->status !== 'published')
<div class="mb-8 bg-white rounded-2xl p-4 sm:p-6 shadow-sm" id="publish-checklist">
    <div class="flex items-center justify-between gap-3 mb-3">
        <h2 class="text-base font-bold">Publish requirements</h2>
        <span class="text-xs font-semibold {{ $canPublish ? 'text-brand-dark' : 'text-amber-600' }}">{{ $canPublish ? 'Ready to publish' : 'Complete the items below to publish' }}</span>
    </div>
    <ul class="grid sm:grid-cols-2 lg:grid-cols-5 gap-2">
        @foreach($checklist as $label => $met)
        <li class="flex items-center gap-2 text-sm {{ $met ? 'text-gray-700' : 'text-gray-400' }}">
            <span class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 {{ $met ? 'bg-brand-dark text-white' : 'border border-gray-300' }}">
                @if($met)<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>@endif
            </span>
            {{ $label }}
        </li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Step 1 · Course Info</h2>
            <form action="{{ route('dashboard.instructor.builder.info', $course) }}" method="post" enctype="multipart/form-data" class="space-y-4" novalidate id="course-info-form">
                @csrf
                @method('PUT')
                <div class="form-field">
                    <label class="dashboard-label" for="title">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $course->title) }}" class="dashboard-input @error('title') is-invalid @enderror">
                    @include('partials.field-error', ['name' => 'title'])
                </div>
                <div class="form-field">
                    <label class="dashboard-label" for="thumbnail">Thumbnail</label>
                    <img src="{{ $course->thumbnail_url }}" alt="Thumbnail" class="w-full h-32 object-cover rounded-xl mb-2 {{ $course->thumbnail ? '' : 'opacity-40' }}" id="thumbnail-preview">
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/png,image/jpeg,image/webp" class="dashboard-input @error('thumbnail') is-invalid @enderror">
                    @include('partials.field-error', ['name' => 'thumbnail'])
                </div>
                <div class="form-field">
                    <label class="dashboard-label" for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="dashboard-input">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $course->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="form-field">
                        <label class="dashboard-label" for="price">Price (USD)</label>
                        <input type="number" id="price" name="price" value="{{ old('price', $course->price) }}" min="0" step="1" class="dashboard-input">
                    </div>
                    <div class="form-field">
                        <label class="dashboard-label" for="level">Level</label>
                        <select id="level" name="level" class="dashboard-input">
                            @foreach(['Beginner', 'Intermediate', 'Advanced'] as $level)
                            <option value="{{ $level }}" @selected(old('level', $course->level) === $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-field">
                    <label class="dashboard-label" for="language">Language</label>
                    <input type="text" id="language" name="language" value="{{ old('language', $course->language) }}" class="dashboard-input">
                </div>
                <div class="form-field">
                    <label class="dashboard-label" for="short_description">Short Description</label>
                    <textarea id="short_description" name="short_description" rows="2" class="dashboard-input">{{ old('short_description', $course->short_description) }}</textarea>
                </div>
                <div class="form-field">
                    <label class="dashboard-label" for="description">Full Description</label>
                    <textarea id="description" name="description" rows="5" class="dashboard-input">{{ old('description', $course->description) }}</textarea>
                </div>
                <button type="submit" class="w-full py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">Save Info</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Add Chapter</h2>
            <form action="{{ route('dashboard.instructor.builder.chapters.store', $course) }}" method="post" class="space-y-4" data-validate="instructor-chapter" novalidate>
                @csrf
                <div class="form-field">
                    <label class="dashboard-label" for="chapter-title">Chapter Title</label>
                    <input type="text" id="chapter-title" name="title" class="dashboard-input @error('title') is-invalid @enderror" placeholder="e.g. Introduction">
                </div>
                <button type="submit" id="add-chapter-btn" class="w-full py-2.5 border border-gray-200 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors">+ Add Chapter</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold">Step 2 · Chapters & Content</h2>
                <span class="text-sm text-gray-500">{{ $course->chapters->count() }} chapters</span>
            </div>

            @forelse($course->chapters as $chapter)
            @php
                $panelOpen = $openForm && str_starts_with($openForm, 'add-'.$chapter->id.'-');
            @endphp
            <div class="mb-6 last:mb-0 p-4 rounded-xl border border-gray-100" data-chapter="{{ $chapter->id }}">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Chapter {{ $loop->iteration }}</p>
                        <form action="{{ route('dashboard.instructor.builder.chapters.update', [$course, $chapter]) }}" method="post" class="flex items-center gap-2 mt-0.5">
                            @csrf
                            @method('PUT')
                            <input type="text" name="title" value="{{ $chapter->title }}" class="flex-1 min-w-0 bg-transparent text-base font-bold text-gray-900 border-0 border-b border-transparent hover:border-gray-200 focus:border-brand-dark focus:ring-0 px-0 py-0.5" aria-label="Chapter title">
                            <button type="submit" class="text-xs font-semibold text-brand-dark hover:text-brand shrink-0">Rename</button>
                        </form>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <form action="{{ route('dashboard.instructor.builder.chapters.move', [$course, $chapter]) }}" method="post">
                            @csrf <input type="hidden" name="direction" value="up">
                            <button type="submit" class="move-btn" title="Move up" @disabled($loop->first)>↑</button>
                        </form>
                        <form action="{{ route('dashboard.instructor.builder.chapters.move', [$course, $chapter]) }}" method="post">
                            @csrf <input type="hidden" name="direction" value="down">
                            <button type="submit" class="move-btn" title="Move down" @disabled($loop->last)>↓</button>
                        </form>
                        <form action="{{ route('dashboard.instructor.builder.chapters.destroy', [$course, $chapter]) }}" method="post" onsubmit="return confirm('Delete this chapter and its content?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-1 text-sm font-semibold text-red-500 hover:text-red-600">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="space-y-2 mb-4">
                    @foreach($chapter->contents as $content)
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                        <span class="w-8 h-8 rounded-lg bg-white border border-gray-100 flex items-center justify-center text-[10px] font-bold text-brand-dark shrink-0">{{ strtoupper($content->type === 'text' ? 'TXT' : $content->type) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $content->title }}</p>
                            <p class="text-xs text-gray-500">{{ $content->type_label }} · {{ $content->duration_minutes }} min @if($content->is_free_preview) · <span class="text-brand-dark font-semibold">Free preview</span> @endif</p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <form action="{{ route('dashboard.instructor.builder.contents.move', [$course, $chapter, $content]) }}" method="post">
                                @csrf <input type="hidden" name="direction" value="up">
                                <button type="submit" class="move-btn" title="Move up" @disabled($loop->first)>↑</button>
                            </form>
                            <form action="{{ route('dashboard.instructor.builder.contents.move', [$course, $chapter, $content]) }}" method="post">
                                @csrf <input type="hidden" name="direction" value="down">
                                <button type="submit" class="move-btn" title="Move down" @disabled($loop->last)>↓</button>
                            </form>
                            <a href="{{ route('dashboard.instructor.builder.contents.edit', [$course, $chapter, $content]) }}" class="ml-1 text-xs font-semibold text-brand-dark hover:text-brand">Edit</a>
                            <form action="{{ route('dashboard.instructor.builder.contents.destroy', [$course, $chapter, $content]) }}" method="post" onsubmit="return confirm('Remove this content item?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-1 text-xs font-semibold text-red-500">Remove</button>
                            </form>
                        </div>
                    </div>
                    @endforeach

                    @foreach($chapter->assessments as $assessment)
                    <a href="{{ route('dashboard.instructor.assessments.show', $assessment) }}" class="flex items-center gap-3 p-3 rounded-lg border border-brand/30 bg-brand-light/40 hover:border-brand transition-colors">
                        <span class="w-8 h-8 rounded-lg bg-white border border-brand/30 flex items-center justify-center text-[10px] font-bold text-brand-dark shrink-0">{{ $assessment->type === 'mcq' ? 'MCQ' : 'ESSAY' }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $assessment->title }}</p>
                            <p class="text-xs text-gray-500">Assessment · {{ ucfirst($assessment->status) }}</p>
                        </div>
                        <span class="text-xs font-semibold text-brand-dark">Manage →</span>
                    </a>
                    @endforeach

                    @if($chapter->contents->isEmpty() && $chapter->assessments->isEmpty())
                    <p class="text-sm text-gray-500">No content yet.</p>
                    @endif
                </div>

                {{-- + Add Content --}}
                <div data-add-content class="{{ $panelOpen ? 'is-open' : '' }}">
                    <button type="button" data-add-toggle class="add-content-btn w-full">+ Add Content</button>

                    <div class="add-content-panel hidden mt-3 p-4 rounded-xl border border-dashed border-gray-200">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Choose content type</p>
                        <div class="flex flex-wrap gap-2 mb-4" role="tablist">
                            @foreach(['video' => 'Video', 'text' => 'Blog / Text', 'pdf' => 'PDF', 'assessment' => 'Assessment'] as $key => $label)
                            <button type="button" role="tab" data-tab="{{ $key }}" class="content-type-tab {{ ($panelOpen ? str_ends_with($openForm, '-'.$key) : $key === 'video') ? 'is-active' : '' }}">{{ $label }}</button>
                            @endforeach
                        </div>

                        @foreach(['video', 'text', 'pdf'] as $type)
                        <div data-pane="{{ $type }}" class="{{ ($panelOpen ? str_ends_with($openForm, '-'.$type) : $type === 'video') ? '' : 'hidden' }}">
                            @include('partials.builder-content-form', [
                                'action' => route('dashboard.instructor.builder.contents.store', [$course, $chapter]),
                                'type' => $type,
                                'uid' => 'add-'.$chapter->id.'-'.$type,
                                'content' => null,
                            ])
                        </div>
                        @endforeach

                        <div data-pane="assessment" class="{{ $panelOpen && str_ends_with($openForm, '-assessment') ? '' : 'hidden' }}">
                            <form action="{{ route('dashboard.instructor.builder.assessments.store', [$course, $chapter]) }}" method="post" class="grid sm:grid-cols-2 gap-3" novalidate>
                                @csrf
                                <div class="form-field sm:col-span-2">
                                    <label class="dashboard-label">Assessment title</label>
                                    <input type="text" name="title" class="dashboard-input" placeholder="e.g. Chapter 1 Quiz">
                                </div>
                                <div class="form-field">
                                    <label class="dashboard-label">Type</label>
                                    <select name="type" class="dashboard-input">
                                        <option value="mcq">Multiple choice (auto-scored)</option>
                                        <option value="essay">Essay / short answer (AI-scored)</option>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label class="dashboard-label">Pass score (%)</label>
                                    <input type="number" name="pass_score" value="70" min="0" max="100" class="dashboard-input">
                                </div>
                                <div class="form-field">
                                    <label class="dashboard-label">Time limit (min, optional)</label>
                                    <input type="number" name="time_limit_minutes" min="1" class="dashboard-input">
                                </div>
                                <div class="sm:col-span-2">
                                    <button type="submit" class="px-4 py-2 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">Create & Add Questions</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-sm py-8 text-center">Add your first chapter to start building the course curriculum.</p>
            @endforelse
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">All Assessments</h2>
                <a href="{{ route('dashboard.instructor.assessments.index', ['create' => 1]) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">+ New Assessment</a>
            </div>
            <div class="space-y-2">
                @forelse($course->assessments as $assessment)
                <a href="{{ route('dashboard.instructor.assessments.show', $assessment) }}" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:border-brand/40 transition-colors">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $assessment->title }}</p>
                        <p class="text-xs text-gray-500">{{ strtoupper($assessment->type) }} · {{ ucfirst($assessment->status) }}@if($assessment->chapter) · {{ $assessment->chapter->title }}@endif</p>
                    </div>
                    <span class="text-sm text-brand-dark font-semibold">Manage →</span>
                </a>
                @empty
                <p class="text-sm text-gray-500">No assessments linked yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
