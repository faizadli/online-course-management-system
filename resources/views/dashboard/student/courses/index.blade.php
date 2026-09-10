@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'My Courses — Student — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">My Courses</h1>
        <p class="text-gray-500 mt-1">Continue learning from your enrolled courses.</p>
    </div>
    <a href="{{ route('dashboard.student.courses.browse') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
        Browse Courses
    </a>
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['label' => 'All', 'value' => null, 'count' => $counts['all']],
                ['label' => 'In Progress', 'value' => 'active', 'count' => $counts['active']],
                ['label' => 'Completed', 'value' => 'completed', 'count' => $counts['completed']],
            ] as $tab)
            <a href="{{ route('dashboard.student.courses.index', array_filter(['status' => $tab['value'], 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($status ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
            @endforeach
        </div>
        <form method="get" action="{{ route('dashboard.student.courses.index') }}" class="relative w-full lg:max-w-xs">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search my courses..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full dashboard-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Instructor</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Enrolled</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrolments as $enrolment)
                <tr>
                    <td>
                        <p class="font-semibold text-gray-900">{{ $enrolment->course?->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $enrolment->course?->category?->name }}</p>
                    </td>
                    <td class="text-gray-500">{{ $enrolment->course?->instructor?->name ?? '—' }}</td>
                    <td>
                        <span class="dashboard-badge {{ $enrolment->status === 'completed' ? 'dashboard-badge-green' : 'dashboard-badge-blue' }}">
                            {{ $enrolment->status === 'active' ? 'In Progress' : ucfirst($enrolment->status) }}
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
                    <td class="text-gray-500">{{ $enrolment->enrolled_at?->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('dashboard.student.courses.show', $enrolment->course) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">Continue</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-500 py-8">No enrolled courses found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.dashboard.pagination', ['paginator' => $enrolments])
</div>

@endsection
