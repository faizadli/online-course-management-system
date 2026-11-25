@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'My Courses — Instructor — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">My Courses</h1>
        <p class="text-gray-500 mt-1">Create, publish, and manage your course catalogue.</p>
    </div>
    <button type="button" data-modal-open="create-course-modal" class="inline-flex items-center justify-center px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
        + Create Course
    </button>
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['label' => 'All', 'value' => null, 'count' => $counts['all']],
                ['label' => 'Published', 'value' => 'published', 'count' => $counts['published']],
                ['label' => 'Draft', 'value' => 'draft', 'count' => $counts['draft']],
                ['label' => 'Archived', 'value' => 'archived', 'count' => $counts['archived']],
            ] as $tab)
            <a href="{{ route('dashboard.instructor.courses.index', array_filter(['status' => $tab['value'], 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($status ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
            @endforeach
        </div>
        <form method="get" action="{{ route('dashboard.instructor.courses.index') }}" class="relative w-full lg:max-w-xs">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search courses..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full dashboard-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Enrolments</th>
                    <th>Chapters</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                <tr>
                    <td>
                        <p class="font-semibold text-gray-900">{{ $course->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $course->level }} · {{ $course->language }}</p>
                    </td>
                    <td class="text-gray-500">{{ $course->category?->name ?? '—' }}</td>
                    <td>
                        <span class="dashboard-badge {{ $course->status === 'published' ? 'dashboard-badge-green' : ($course->status === 'draft' ? 'dashboard-badge-gray' : 'dashboard-badge-amber') }}">
                            {{ ucfirst($course->status) }}
                        </span>
                    </td>
                    <td class="font-semibold">${{ number_format($course->price, 0) }}</td>
                    <td class="text-gray-500">{{ number_format($course->enrolments_count) }}</td>
                    <td class="text-gray-500">{{ $course->chapters_count }}</td>
                    <td>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('dashboard.instructor.builder.show', $course) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">Builder</a>
                            <form action="{{ route('dashboard.instructor.courses.toggle', $course) }}" method="post" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-sm font-semibold text-gray-600 hover:text-brand-dark">
                                    {{ $course->status === 'published' ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                            @if($course->enrolments_count === 0)
                            <form action="{{ route('dashboard.instructor.courses.destroy', $course) }}" method="post" class="inline" onsubmit="return confirm('Delete this course?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-red-500 hover:text-red-600">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-gray-500 py-8">No courses found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.dashboard.pagination', ['paginator' => $courses])
</div>

<x-dashboard.modal id="create-course-modal" title="Create Course" size="lg">
    <form action="{{ route('dashboard.instructor.courses.store') }}" method="post" enctype="multipart/form-data" class="space-y-4" data-validate="instructor-course-create" novalidate>
        @csrf
        <div class="form-field">
            <label class="dashboard-label" for="title">Course Title</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" class="dashboard-input @error('title') is-invalid @enderror" placeholder="e.g. UI/UX Design Fundamentals">
            @include('partials.field-error', ['name' => 'title'])
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="form-field">
                <label class="dashboard-label" for="category_id">Category</label>
                <select id="category_id" name="category_id" class="dashboard-input @error('category_id') is-invalid @enderror">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @include('partials.field-error', ['name' => 'category_id'])
            </div>
            <div class="form-field">
                <label class="dashboard-label" for="price">Price (USD)</label>
                <input type="number" id="price" name="price" value="{{ old('price', 0) }}" min="0" step="1" class="dashboard-input @error('price') is-invalid @enderror">
                @include('partials.field-error', ['name' => 'price'])
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="form-field">
                <label class="dashboard-label" for="level">Level</label>
                <select id="level" name="level" class="dashboard-input">
                    @foreach(['Beginner', 'Intermediate', 'Advanced'] as $level)
                    <option value="{{ $level }}" @selected(old('level', 'Beginner') === $level)>{{ $level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-field">
                <label class="dashboard-label" for="language">Language</label>
                <input type="text" id="language" name="language" value="{{ old('language', 'English') }}" class="dashboard-input">
            </div>
        </div>
        <div class="form-field">
            <label class="dashboard-label" for="short_description">Short Description</label>
            <textarea id="short_description" name="short_description" rows="2" class="dashboard-input @error('short_description') is-invalid @enderror" placeholder="One or two sentences about the course...">{{ old('short_description') }}</textarea>
            @include('partials.field-error', ['name' => 'short_description'])
        </div>
        <div class="form-field">
            <label class="dashboard-label" for="description">Full Description</label>
            <textarea id="description" name="description" rows="3" class="dashboard-input @error('description') is-invalid @enderror" placeholder="What will students learn?">{{ old('description') }}</textarea>
            @include('partials.field-error', ['name' => 'description'])
        </div>
        <div class="form-field">
            <label class="dashboard-label" for="thumbnail">Thumbnail <span class="text-gray-400 font-normal">(JPG, PNG or WebP · max 3 MB)</span></label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/png,image/jpeg,image/webp" class="dashboard-input @error('thumbnail') is-invalid @enderror">
            @include('partials.field-error', ['name' => 'thumbnail'])
        </div>
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" data-modal-close class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800">Cancel</button>
            <button type="submit" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">
                Create & Open Builder
            </button>
        </div>
    </form>
</x-dashboard.modal>

@if(request('create') || ($errors->any() && !old('_method')))
<script>document.addEventListener('DOMContentLoaded', () => window.openDashboardModal?.('create-course-modal'));</script>
@endif

@endsection
