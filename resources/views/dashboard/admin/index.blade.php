@extends('layouts.dashboard', ['role' => 'admin'])

@section('title', 'Admin Dashboard — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Admin Dashboard</h1>
        <p class="text-gray-500 mt-1">Overview of platform activity and key metrics.</p>
    </div>
    <a href="{{ route('dashboard.admin.categories.index', ['create' => 1]) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
        + Add Category
    </a>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ number_format($stats['total_users']) }}</p>
        <p class="text-sm text-gray-500 mt-1">Total Users</p>
        <p class="text-xs text-gray-400 mt-2">{{ $stats['students'] }} students · {{ $stats['instructors'] }} instructors</p>
    </div>
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ number_format($stats['active_courses']) }}</p>
        <p class="text-sm text-gray-500 mt-1">Published Courses</p>
        <p class="text-xs text-gray-400 mt-2">{{ $stats['draft_courses'] }} drafts</p>
    </div>
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ number_format($stats['categories']) }}</p>
        <p class="text-sm text-gray-500 mt-1">Categories</p>
        <p class="text-xs text-gray-400 mt-2">All active</p>
    </div>
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ number_format($stats['total_enrolments']) }}</p>
        <p class="text-sm text-gray-500 mt-1">Total Enrolments</p>
        <p class="text-xs text-gray-400 mt-2">Across all courses</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold">Recent Users</h2>
            <a href="{{ route('dashboard.admin.users.index') }}" class="text-sm font-semibold text-brand-dark hover:text-brand">View all</a>
        </div>
        <div class="space-y-3">
            @foreach($recentUsers as $user)
            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                </div>
                <span class="dashboard-badge {{ $user->role->value === 'student' ? 'dashboard-badge-blue' : ($user->role->value === 'instructor' ? 'dashboard-badge-purple' : 'dashboard-badge-amber') }}">
                    {{ $user->role->label() }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold">Recent Courses</h2>
            <a href="{{ route('dashboard.admin.courses.index') }}" class="text-sm font-semibold text-brand-dark hover:text-brand">View all</a>
        </div>
        <div class="space-y-3">
            @foreach($recentCourses as $course)
            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $course->title }}</p>
                    <p class="text-xs text-gray-500">{{ $course->instructor?->name ?? 'Unassigned' }} · {{ $course->category?->name ?? 'No category' }}</p>
                </div>
                <span class="dashboard-badge {{ $course->status === 'published' ? 'dashboard-badge-green' : 'dashboard-badge-gray' }} shrink-0 ml-2">
                    {{ ucfirst($course->status) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
