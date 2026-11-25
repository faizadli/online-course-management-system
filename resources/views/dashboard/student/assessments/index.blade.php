@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'Assessments — Student — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">Assessments</h1>
    <p class="text-gray-500 mt-1">Take MCQ and essay assessments from your enrolled courses.</p>
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['label' => 'All', 'param' => 'filter', 'value' => null, 'count' => $counts['all']],
                ['label' => 'Available', 'param' => 'filter', 'value' => 'available', 'count' => $counts['available']],
                ['label' => 'Completed', 'param' => 'filter', 'value' => 'completed', 'count' => $counts['completed']],
            ] as $tab)
            <a href="{{ route('dashboard.student.assessments.index', array_filter(['filter' => $tab['value'], 'type' => $type, 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($filter ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
            @endforeach
            @foreach([
                ['label' => 'MCQ', 'value' => 'mcq'],
                ['label' => 'Essay', 'value' => 'essay'],
            ] as $tab)
            <a href="{{ route('dashboard.student.assessments.index', array_filter(['type' => $tab['value'], 'filter' => $filter, 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($type ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }}
            </a>
            @endforeach
        </div>
        <form method="get" action="{{ route('dashboard.student.assessments.index') }}" class="relative w-full lg:max-w-xs">
            @if($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
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
                    <th>Questions</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($assessments as $assessment)
                @php $submission = $submissionMap->get($assessment->id); @endphp
                <tr>
                    <td class="font-semibold text-gray-900">{{ $assessment->title }}</td>
                    <td class="text-gray-500">{{ $assessment->course?->title }}</td>
                    <td>
                        <span class="dashboard-badge {{ $assessment->type === 'mcq' ? 'dashboard-badge-blue' : 'dashboard-badge-purple' }}">
                            {{ strtoupper($assessment->type) }}
                        </span>
                    </td>
                    <td class="text-gray-500">{{ $assessment->questions_count }}</td>
                    <td>
                        @if($submission)
                        <span class="dashboard-badge {{ $submission->status === 'pending' ? 'dashboard-badge-amber' : 'dashboard-badge-green' }}">
                            {{ str_replace('_', ' ', ucfirst($submission->status)) }}
                        </span>
                        @else
                        <span class="dashboard-badge dashboard-badge-gray">Not started</span>
                        @endif
                    </td>
                    <td>
                        @if($submission)
                        <a href="{{ route('dashboard.student.feedback.show', $submission) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">View result</a>
                        @else
                        <a href="{{ route('dashboard.student.assessments.show', $assessment) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">Start</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-500 py-8">No assessments available. Enrol in a course first.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.dashboard.pagination', ['paginator' => $assessments])
</div>

@endsection
