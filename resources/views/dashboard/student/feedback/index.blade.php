@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'AI Feedback — Student — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">AI Feedback</h1>
    <p class="text-gray-500 mt-1">Review scores and feedback from your submitted assessments.</p>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label' => 'Total Submissions', 'value' => number_format($counts['all'])],
        ['label' => 'AI Graded', 'value' => number_format($counts['ai_graded'])],
        ['label' => 'Instructor Graded', 'value' => number_format($counts['graded'])],
        ['label' => 'Average Score', 'value' => ($avgScore ?: 0).'%'],
    ] as $stat)
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ $stat['value'] }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['label' => 'All', 'value' => null, 'count' => $counts['all']],
                ['label' => 'AI Graded', 'value' => 'ai_graded', 'count' => $counts['ai_graded']],
                ['label' => 'Graded', 'value' => 'graded', 'count' => $counts['graded']],
                ['label' => 'Pending', 'value' => 'pending', 'count' => $counts['pending']],
            ] as $tab)
            <a href="{{ route('dashboard.student.feedback.index', array_filter(['status' => $tab['value'], 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($status ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
            @endforeach
        </div>
        <form method="get" action="{{ route('dashboard.student.feedback.index') }}" class="relative w-full lg:max-w-xs">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search feedback..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full dashboard-table">
            <thead>
                <tr>
                    <th>Assessment</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $submission)
                <tr>
                    <td class="font-semibold text-gray-900">{{ $submission->assessment?->title }}</td>
                    <td class="text-gray-500">{{ $submission->assessment?->course?->title }}</td>
                    <td>
                        <span class="dashboard-badge {{ $submission->status === 'pending' ? 'dashboard-badge-amber' : ($submission->status === 'ai_graded' ? 'dashboard-badge-green' : 'dashboard-badge-blue') }}">
                            {{ str_replace('_', ' ', ucfirst($submission->status)) }}
                        </span>
                    </td>
                    <td class="font-semibold">{{ $submission->score !== null ? $submission->score.'%' : '—' }}</td>
                    <td class="text-gray-500">{{ $submission->submitted_at?->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('dashboard.student.feedback.show', $submission) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-500 py-8">No feedback yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.dashboard.pagination', ['paginator' => $submissions])
</div>

@endsection
