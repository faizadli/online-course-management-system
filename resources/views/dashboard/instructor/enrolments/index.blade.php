@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'Enrolments — Instructor — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">Enrolments</h1>
    <p class="text-gray-500 mt-1">Track students enrolled in your courses and their progress.</p>
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['label' => 'All', 'value' => null, 'count' => $counts['all']],
                ['label' => 'Active', 'value' => 'active', 'count' => $counts['active']],
                ['label' => 'Completed', 'value' => 'completed', 'count' => $counts['completed']],
                ['label' => 'Cancelled', 'value' => 'cancelled', 'count' => $counts['cancelled']],
            ] as $tab)
            <a href="{{ route('dashboard.instructor.enrolments.index', array_filter(['status' => $tab['value'], 'course_id' => $courseId, 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($status ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
            @endforeach
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
            <form method="get" action="{{ route('dashboard.instructor.enrolments.index') }}" class="flex gap-2">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
                <select name="course_id" class="dashboard-input !w-auto min-w-[12rem]" onchange="this.form.submit()">
                    <option value="">All courses</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" @selected((string) $courseId === (string) $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </form>
            <form method="get" action="{{ route('dashboard.instructor.enrolments.index') }}" class="relative w-full sm:max-w-xs">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                @if($courseId)<input type="hidden" name="course_id" value="{{ $courseId }}">@endif
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="search" value="{{ $search }}" placeholder="Search students..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full dashboard-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Amount Paid</th>
                    <th>Enrolled</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrolments as $enrolment)
                <tr>
                    <td>
                        <p class="font-semibold text-gray-900">{{ $enrolment->student?->name }}</p>
                        <p class="text-xs text-gray-500">{{ $enrolment->student?->email }}</p>
                    </td>
                    <td class="text-gray-500">{{ $enrolment->course?->title }}</td>
                    <td>
                        <span class="dashboard-badge {{ $enrolment->status === 'active' ? 'dashboard-badge-blue' : ($enrolment->status === 'completed' ? 'dashboard-badge-green' : 'dashboard-badge-gray') }}">
                            {{ ucfirst($enrolment->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="min-w-[8rem]">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span>{{ $enrolment->progress }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-brand-dark rounded-full" style="width: {{ $enrolment->progress }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="font-semibold">${{ number_format($enrolment->amount_paid, 0) }}</td>
                    <td class="text-gray-500">{{ $enrolment->enrolled_at?->format('M d, Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-500 py-8">No enrolments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.dashboard.pagination', ['paginator' => $enrolments])
</div>

@endsection
