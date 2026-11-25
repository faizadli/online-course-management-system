@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'Assessments — Instructor — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Assessments</h1>
        <p class="text-gray-500 mt-1">Create MCQ and essay assessments, then review student submissions.</p>
    </div>
    <button type="button" data-modal-open="create-assessment-modal" class="inline-flex items-center justify-center px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
        + New Assessment
    </button>
</div>

@if($pendingSubmissions->isNotEmpty())
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5 mb-6">
    <h2 class="text-sm font-bold text-amber-800 mb-3">Pending Reviews ({{ $counts['pending'] }})</h2>
    <div class="space-y-2">
        @foreach($pendingSubmissions as $submission)
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-amber-900">
                <span class="font-semibold">{{ $submission->student?->name }}</span>
                submitted {{ $submission->assessment?->title }}
            </p>
            <a href="{{ route('dashboard.instructor.assessments.show', $submission->assessment) }}" class="text-sm font-semibold text-brand-dark hover:text-brand shrink-0">Grade</a>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['label' => 'All', 'value' => null, 'count' => $counts['all']],
                ['label' => 'MCQ', 'value' => 'mcq', 'count' => $counts['mcq']],
                ['label' => 'Essay', 'value' => 'essay', 'count' => $counts['essay']],
            ] as $tab)
            <a href="{{ route('dashboard.instructor.assessments.index', array_filter(['type' => $tab['value'], 'status' => $status, 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($type ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
            @endforeach
        </div>
        <form method="get" action="{{ route('dashboard.instructor.assessments.index') }}" class="relative w-full lg:max-w-xs">
            @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search assessments..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full dashboard-table">
            <thead>
                <tr>
                    <th>Assessment</th>
                    <th>Course</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Questions</th>
                    <th>Submissions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($assessments as $assessment)
                <tr>
                    <td class="font-semibold text-gray-900">{{ $assessment->title }}</td>
                    <td class="text-gray-500">{{ $assessment->course?->title }}</td>
                    <td>
                        <span class="dashboard-badge {{ $assessment->type === 'mcq' ? 'dashboard-badge-blue' : 'dashboard-badge-purple' }}">
                            {{ strtoupper($assessment->type) }}
                        </span>
                    </td>
                    <td>
                        <span class="dashboard-badge {{ $assessment->status === 'published' ? 'dashboard-badge-green' : 'dashboard-badge-gray' }}">
                            {{ ucfirst($assessment->status) }}
                        </span>
                    </td>
                    <td class="text-gray-500">{{ $assessment->questions_count }}</td>
                    <td class="text-gray-500">{{ $assessment->submissions_count }}</td>
                    <td>
                        <a href="{{ route('dashboard.instructor.assessments.show', $assessment) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">Manage</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-gray-500 py-8">No assessments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.dashboard.pagination', ['paginator' => $assessments])
</div>

<x-dashboard.modal id="create-assessment-modal" title="Create Assessment" size="lg">
    <form action="{{ route('dashboard.instructor.assessments.store') }}" method="post" class="space-y-4" data-validate="instructor-assessment-create" novalidate>
        @csrf
        <div class="form-field">
            <label class="dashboard-label" for="assessment-title">Title</label>
            <input type="text" id="assessment-title" name="title" value="{{ old('title') }}" class="dashboard-input @error('title') is-invalid @enderror" placeholder="e.g. Chapter 1 Quiz">
            @include('partials.field-error', ['name' => 'title'])
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="form-field">
                <label class="dashboard-label" for="course_id">Course</label>
                <select id="course_id" name="course_id" class="dashboard-input @error('course_id') is-invalid @enderror">
                    <option value="">Select course</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
                @include('partials.field-error', ['name' => 'course_id'])
            </div>
            <div class="form-field">
                <label class="dashboard-label" for="type">Type</label>
                <select id="type" name="type" class="dashboard-input">
                    <option value="mcq" @selected(old('type') === 'mcq')>MCQ</option>
                    <option value="essay" @selected(old('type') === 'essay')>Essay / Short Answer</option>
                </select>
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="form-field">
                <label class="dashboard-label" for="pass_score">Pass Score (%)</label>
                <input type="number" id="pass_score" name="pass_score" value="{{ old('pass_score', 70) }}" min="0" max="100" class="dashboard-input">
            </div>
            <div class="form-field">
                <label class="dashboard-label" for="time_limit_minutes">Time Limit (min)</label>
                <input type="number" id="time_limit_minutes" name="time_limit_minutes" value="{{ old('time_limit_minutes', 30) }}" min="1" class="dashboard-input">
            </div>
        </div>
        <div class="form-field">
            <label class="dashboard-label" for="description">Description</label>
            <textarea id="description" name="description" rows="3" class="dashboard-input">{{ old('description') }}</textarea>
        </div>
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" data-modal-close class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800">Cancel</button>
            <button type="submit" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">Create Assessment</button>
        </div>
    </form>
</x-dashboard.modal>

@if(request('create') || ($errors->any() && !old('_method')))
<script>document.addEventListener('DOMContentLoaded', () => window.openDashboardModal?.('create-assessment-modal'));</script>
@endif

@endsection
