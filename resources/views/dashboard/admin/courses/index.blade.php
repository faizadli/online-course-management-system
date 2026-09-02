@extends('layouts.dashboard', ['role' => 'admin'])

@section('title', 'Courses — Admin — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">Course Management</h1>
    <p class="text-gray-500 mt-1">View all courses on the platform and their publish status.</p>
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
            <a href="{{ route('dashboard.admin.courses.index', array_filter(['status' => $tab['value'], 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($status ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
            @endforeach
        </div>
        <form method="get" action="{{ route('dashboard.admin.courses.index') }}" class="relative w-full lg:max-w-xs">
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
                    <th>Instructor</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Enrolments</th>
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
                    <td class="text-gray-500">{{ $course->instructor?->name ?? '—' }}</td>
                    <td>
                        <span class="dashboard-badge {{ $course->status === 'published' ? 'dashboard-badge-green' : ($course->status === 'draft' ? 'dashboard-badge-gray' : 'dashboard-badge-amber') }}">
                            {{ ucfirst($course->status) }}
                        </span>
                    </td>
                    <td class="font-semibold">${{ number_format($course->price, 0) }}</td>
                    <td class="text-gray-500">{{ number_format($course->enrolments_count) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-500 py-8">No courses found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.dashboard.pagination', ['paginator' => $courses])
</div>

@endsection
