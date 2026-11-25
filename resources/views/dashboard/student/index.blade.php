@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'Student Dashboard — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Student Dashboard</h1>
        <p class="text-gray-500 mt-1">Track your enrolled courses, assessments, and AI feedback.</p>
    </div>
    <a href="{{ route('dashboard.student.courses.browse') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
        Browse Courses
    </a>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label' => 'Enrolled Courses', 'value' => $stats['enrolled']],
        ['label' => 'Completed', 'value' => $stats['completed']],
        ['label' => 'In Progress', 'value' => $stats['in_progress']],
        ['label' => 'Pending Assessments', 'value' => $stats['pending_assessments']],
    ] as $stat)
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ number_format($stat['value']) }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold">My Enrolled Courses</h2>
            <a href="{{ route('dashboard.student.courses.index') }}" class="text-sm font-semibold text-brand-dark hover:text-brand">View all</a>
        </div>
        <div class="space-y-4">
            @forelse($recentEnrolments as $enrolment)
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl border border-gray-100">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $enrolment->course?->title }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $enrolment->course?->instructor?->name ?? 'Instructor' }}</p>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-brand rounded-full" style="width: {{ $enrolment->progress }}%"></div>
                        </div>
                        <span class="text-xs text-gray-400 font-medium shrink-0">{{ $enrolment->progress }}%</span>
                    </div>
                </div>
                <a href="{{ route('dashboard.student.courses.show', $enrolment->course) }}" class="shrink-0 px-4 py-2 text-sm font-semibold border border-gray-200 rounded-full hover:border-brand-dark hover:text-brand-dark transition-colors text-center">Continue</a>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-8">No enrolled courses yet. Browse and enrol to start learning.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">Upcoming Assessments</h2>
            <a href="{{ route('dashboard.student.assessments.index') }}" class="text-sm font-semibold text-brand-dark hover:text-brand">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($upcomingAssessments as $assessment)
            <a href="{{ route('dashboard.student.assessments.show', $assessment) }}" class="block p-3 rounded-xl border border-gray-100 hover:border-brand/40 transition-colors">
                <p class="text-sm font-semibold text-gray-900">{{ $assessment->title }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $assessment->course?->title }} · {{ strtoupper($assessment->type) }}</p>
            </a>
            @empty
            <p class="text-sm text-gray-500">No pending assessments.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold">Recent AI Feedback</h2>
        <a href="{{ route('dashboard.student.feedback.index') }}" class="text-sm font-semibold text-brand-dark hover:text-brand">View all</a>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
        @forelse($recentFeedback as $item)
        <a href="{{ route('dashboard.student.feedback.show', $item) }}" class="p-4 rounded-xl border border-gray-100 hover:border-brand/40 transition-colors">
            <div class="flex items-center justify-between gap-3 mb-2">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $item->assessment?->title }}</p>
                <span class="text-sm font-bold text-brand-dark">{{ $item->score }}%</span>
            </div>
            <p class="text-xs text-gray-500 line-clamp-2">{{ $item->feedback }}</p>
        </a>
        @empty
        <p class="text-sm text-gray-500 sm:col-span-2 text-center py-6">No feedback yet. Complete an assessment to see results here.</p>
        @endforelse
    </div>
</div>

@endsection
