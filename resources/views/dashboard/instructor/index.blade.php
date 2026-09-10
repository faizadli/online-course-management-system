@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'Instructor Dashboard — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Instructor Dashboard</h1>
        <p class="text-gray-500 mt-1">Manage your courses, content, assessments, and student performance.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('dashboard.instructor.courses.index', ['create' => 1]) }}" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
            + Create Course
        </a>
        <a href="{{ route('dashboard.instructor.courses.index') }}" class="px-5 py-2.5 border border-gray-200 text-sm font-semibold rounded-full hover:bg-gray-50 transition-all duration-300">
            My Courses
        </a>
    </div>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label' => 'Total Courses', 'value' => number_format($stats['total_courses']), 'sub' => $stats['published_courses'].' published'],
        ['label' => 'Total Students', 'value' => number_format($stats['total_students']), 'sub' => $stats['total_enrolments'].' enrolments'],
        ['label' => 'Draft Courses', 'value' => number_format($stats['draft_courses']), 'sub' => 'Ready to publish'],
        ['label' => 'Pending Reviews', 'value' => number_format($stats['pending_reviews']), 'sub' => 'Essay submissions'],
    ] as $stat)
    <div class="dashboard-stat-card">
        <p class="text-3xl font-bold">{{ $stat['value'] }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
        <p class="text-xs text-brand-dark font-medium mt-2">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold">My Courses</h2>
            <a href="{{ route('dashboard.instructor.courses.index') }}" class="text-sm font-semibold text-brand-dark hover:text-brand">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full dashboard-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Students</th>
                        <th>Chapters</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td class="font-semibold text-gray-900">{{ $course->title }}</td>
                        <td>
                            <span class="dashboard-badge {{ $course->status === 'published' ? 'dashboard-badge-green' : ($course->status === 'draft' ? 'dashboard-badge-gray' : 'dashboard-badge-amber') }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </td>
                        <td>{{ number_format($course->enrolments_count) }}</td>
                        <td>{{ $course->chapters_count }}</td>
                        <td>
                            <a href="{{ route('dashboard.instructor.builder.show', $course) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">Manage</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-8">No courses yet. Create your first course.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-4">Course Performance</h2>
        <div class="space-y-4">
            @forelse($topCourses as $item)
            <div class="p-3 rounded-xl border border-gray-100">
                <p class="text-sm font-semibold text-gray-900">{{ $item->title }}</p>
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ number_format($item->enrolments_count) }} enrolled</span>
                    <span class="font-semibold text-brand-dark">${{ number_format($item->price, 0) }}</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500">No course performance data yet.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold">Recent Student Submissions</h2>
            <a href="{{ route('dashboard.instructor.assessments.index') }}" class="text-sm font-semibold text-brand-dark hover:text-brand">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($recentSubmissions as $sub)
            <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-gray-100">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">{{ $sub->student?->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $sub->assessment?->course?->title }} · {{ strtoupper($sub->assessment?->type) }}</p>
                </div>
                <span class="dashboard-badge {{ $sub->status === 'pending' ? 'dashboard-badge-amber' : ($sub->status === 'ai_graded' ? 'dashboard-badge-green' : 'dashboard-badge-blue') }} shrink-0">
                    {{ str_replace('_', ' ', ucfirst($sub->status)) }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-500 py-4 text-center">No submissions yet.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-6">Enrolment Overview</h2>
        @php $maxCount = max(1, $monthlyEnrolments->max('count')); @endphp
        <div class="flex items-end justify-between gap-2 h-40 px-2">
            @foreach($monthlyEnrolments as $bar)
            <div class="flex-1 flex flex-col items-center gap-2 group">
                <div class="w-full relative flex items-end justify-center h-32">
                    <div class="w-full max-w-[28px] rounded-t-lg bg-gradient-to-t from-brand to-brand/40 transition-all duration-700 group-hover:from-brand-dark" style="height: {{ ($bar['count'] / $maxCount) * 100 }}%"></div>
                </div>
                <span class="text-[10px] text-gray-400 font-medium">{{ $bar['label'] }}</span>
            </div>
            @endforeach
        </div>
        <p class="text-sm text-gray-500 text-center mt-4">Monthly new enrolments across all courses</p>
    </div>
</div>

@endsection
