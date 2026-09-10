@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'Course Builder — '.$course->title.' — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <a href="{{ route('dashboard.instructor.courses.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-dark">← Back to My Courses</a>
        <h1 class="text-2xl sm:text-3xl font-bold mt-2">Course Builder</h1>
        <p class="text-gray-500 mt-1">{{ $course->title }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <form action="{{ route('dashboard.instructor.courses.toggle', $course) }}" method="post">
            @csrf
            @method('PATCH')
            <button type="submit" class="px-5 py-2.5 border border-gray-200 text-sm font-semibold rounded-full hover:bg-gray-50 transition-all duration-300">
                {{ $course->status === 'published' ? 'Unpublish' : 'Publish Course' }}
            </button>
        </form>
        <span class="inline-flex items-center px-4 py-2.5 text-sm font-semibold rounded-full dashboard-badge {{ $course->status === 'published' ? 'dashboard-badge-green' : 'dashboard-badge-gray' }}">
            {{ ucfirst($course->status) }}
        </span>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <h2 class="text-lg font-bold mb-4">Step 1 · Course Info</h2>
            <form action="{{ route('dashboard.instructor.builder.info', $course) }}" method="post" class="space-y-4" data-validate="instructor-course-update" novalidate>
                @csrf
                @method('PUT')
                <div class="form-field">
                    <label class="dashboard-label" for="title">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $course->title) }}" class="dashboard-input @error('title') is-invalid @enderror">
                    @include('partials.field-error', ['name' => 'title'])
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
                        <label class="dashboard-label" for="price">Price</label>
                        <input type="number" id="price" name="price" value="{{ old('price', $course->price) }}" min="0" step="1" class="dashboard-input">
                    </div>
                    <div class="form-field">
                        <label class="dashboard-label" for="status">Status</label>
                        <select id="status" name="status" class="dashboard-input">
                            @foreach(['draft', 'published', 'archived'] as $statusOption)
                            <option value="{{ $statusOption }}" @selected(old('status', $course->status) === $statusOption)>{{ ucfirst($statusOption) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="form-field">
                        <label class="dashboard-label" for="level">Level</label>
                        <select id="level" name="level" class="dashboard-input">
                            @foreach(['Beginner', 'Intermediate', 'Advanced'] as $level)
                            <option value="{{ $level }}" @selected(old('level', $course->level) === $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="dashboard-label" for="language">Language</label>
                        <input type="text" id="language" name="language" value="{{ old('language', $course->language) }}" class="dashboard-input">
                    </div>
                </div>
                <div class="form-field">
                    <label class="dashboard-label" for="short_description">Short Description</label>
                    <textarea id="short_description" name="short_description" rows="2" class="dashboard-input">{{ old('short_description', $course->short_description) }}</textarea>
                </div>
                <div class="form-field">
                    <label class="dashboard-label" for="description">Full Description</label>
                    <textarea id="description" name="description" rows="4" class="dashboard-input">{{ old('description', $course->description) }}</textarea>
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
                    @include('partials.field-error', ['name' => 'title'])
                </div>
                <button type="submit" class="w-full py-2.5 border border-gray-200 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors">+ Add Chapter</button>
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
            <div class="mb-6 last:mb-0 p-4 rounded-xl border border-gray-100">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Chapter {{ $chapter->sort_order }}</p>
                        <h3 class="text-base font-bold text-gray-900">{{ $chapter->title }}</h3>
                    </div>
                    <form action="{{ route('dashboard.instructor.builder.chapters.destroy', [$course, $chapter]) }}" method="post" onsubmit="return confirm('Delete this chapter and its content?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-red-500 hover:text-red-600">Delete</button>
                    </form>
                </div>

                <div class="space-y-2 mb-4">
                    @forelse($chapter->contents as $content)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-gray-50">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $content->title }}</p>
                            <p class="text-xs text-gray-500">{{ strtoupper($content->type) }} · {{ $content->duration_minutes }} min</p>
                        </div>
                        <form action="{{ route('dashboard.instructor.builder.contents.destroy', [$course, $chapter, $content]) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-500">Remove</button>
                        </form>
                    </div>
                    @empty
                    <p class="text-sm text-gray-500">No content yet.</p>
                    @endforelse
                </div>

                <form action="{{ route('dashboard.instructor.builder.contents.store', [$course, $chapter]) }}" method="post" class="grid sm:grid-cols-2 gap-3 p-3 rounded-lg border border-dashed border-gray-200" data-validate="instructor-content" novalidate>
                    @csrf
                    <div class="form-field sm:col-span-2">
                        <label class="dashboard-label">Content Title</label>
                        <input type="text" name="title" class="dashboard-input" placeholder="Lesson title">
                    </div>
                    <div class="form-field">
                        <label class="dashboard-label">Type</label>
                        <select name="type" class="dashboard-input">
                            <option value="video">Video</option>
                            <option value="text">Blog / Text</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="dashboard-label">Duration (min)</label>
                        <input type="number" name="duration_minutes" value="10" min="0" class="dashboard-input">
                    </div>
                    <div class="form-field sm:col-span-2">
                        <label class="dashboard-label">URL (video / PDF)</label>
                        <input type="text" name="url" class="dashboard-input" placeholder="https://...">
                    </div>
                    <div class="form-field sm:col-span-2">
                        <label class="dashboard-label">Text Body</label>
                        <textarea name="body" rows="2" class="dashboard-input" placeholder="For text lessons..."></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">Add Content</button>
                    </div>
                </form>
            </div>
            @empty
            <p class="text-gray-500 text-sm py-8 text-center">Add your first chapter to start building the course curriculum.</p>
            @endforelse
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">Linked Assessments</h2>
                <a href="{{ route('dashboard.instructor.assessments.index', ['create' => 1]) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">+ New Assessment</a>
            </div>
            <div class="space-y-2">
                @forelse($course->assessments as $assessment)
                <a href="{{ route('dashboard.instructor.assessments.show', $assessment) }}" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:border-brand/40 transition-colors">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $assessment->title }}</p>
                        <p class="text-xs text-gray-500">{{ strtoupper($assessment->type) }} · {{ ucfirst($assessment->status) }}</p>
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
