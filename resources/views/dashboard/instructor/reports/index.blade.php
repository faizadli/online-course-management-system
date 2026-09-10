@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'Reports — Instructor — SkillForge')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">Reports</h1>
    <p class="text-gray-500 mt-1">Monitor enrolments, revenue, and assessment performance for your courses.</p>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label' => 'Total Enrolments', 'value' => number_format($stats['total_enrolments']), 'sub' => $stats['active_students'].' active students'],
        ['label' => 'Completed', 'value' => number_format($stats['completed_enrolments']), 'sub' => 'Across all courses'],
        ['label' => 'Revenue', 'value' => '$'.number_format($stats['revenue'], 0), 'sub' => 'From enrolments'],
        ['label' => 'Avg. Assessment Score', 'value' => ($stats['avg_score'] ?: 0).'%', 'sub' => $stats['assessments'].' assessments'],
    ] as $stat)
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ $stat['value'] }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
        <p class="text-xs text-gray-400 mt-2">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-6">Course Performance</h2>
        <div class="overflow-x-auto">
            <table class="w-full dashboard-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Enrolments</th>
                        <th>Chapters</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coursePerformance as $course)
                    <tr>
                        <td class="font-semibold text-gray-900">{{ $course->title }}</td>
                        <td>
                            <span class="dashboard-badge {{ $course->status === 'published' ? 'dashboard-badge-green' : 'dashboard-badge-gray' }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </td>
                        <td class="text-gray-500">{{ $course->enrolments_count }}</td>
                        <td class="text-gray-500">{{ $course->chapters_count }}</td>
                        <td class="font-semibold">${{ number_format($course->price, 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-8">No courses yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-6">Assessment Performance</h2>
        <div class="space-y-3">
            @forelse($assessmentStats as $assessment)
            <div class="p-3 rounded-xl border border-gray-100">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $assessment->title }}</p>
                        <p class="text-xs text-gray-500">{{ $assessment->course?->title }} · {{ $assessment->submissions_count }} submissions</p>
                    </div>
                    <span class="text-sm font-bold text-brand-dark shrink-0">
                        {{ $assessment->submissions_avg_score ? round($assessment->submissions_avg_score, 1).'%' : '—' }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500 py-4 text-center">No assessment data yet.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection
