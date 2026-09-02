@extends('layouts.dashboard', ['role' => 'instructor'])

@section('title', 'Instructor Dashboard — SkillForge')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 animate-fade-in-up">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Instructor Dashboard</h1>
        <p class="text-gray-500 mt-1">Manage your courses, content, assessments, and student performance.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <button type="button" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
            + Create Course
        </button>
        <button type="button" class="px-5 py-2.5 border border-gray-200 text-sm font-semibold rounded-full hover:bg-gray-50 transition-all duration-300">
            Course Builder
        </button>
    </div>
</div>

@php
$stats = [
    ['label' => 'Total Courses', 'value' => '6', 'sub' => '4 published'],
    ['label' => 'Total Students', 'value' => '842', 'sub' => '+12 this week'],
    ['label' => 'Draft Courses', 'value' => '2', 'sub' => 'Ready to publish'],
    ['label' => 'Pending Reviews', 'value' => '18', 'sub' => 'Essay submissions'],
];
@endphp

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach($stats as $index => $stat)
    <div class="dashboard-stat-card reveal {{ ['reveal-delay-0','reveal-delay-80','reveal-delay-160','reveal-delay-240'][$index] }}">
        <p class="text-3xl font-bold">{{ $stat['value'] }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
        <p class="text-xs text-brand-dark font-medium mt-2">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm reveal">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold">My Courses</h2>
            <div class="flex gap-2">
                @foreach(['All', 'Published', 'Draft'] as $i => $tab)
                <button type="button" class="px-3 py-1 text-xs font-semibold rounded-full {{ $i === 0 ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">{{ $tab }}</button>
                @endforeach
            </div>
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
                    @php
                    $courses = [
                        ['title' => 'UI/UX Design Fundamentals', 'status' => 'Published', 'statusColor' => 'dashboard-badge-green', 'students' => 324, 'chapters' => 4],
                        ['title' => 'Design Systems Masterclass', 'status' => 'Published', 'statusColor' => 'dashboard-badge-green', 'students' => 186, 'chapters' => 5],
                        ['title' => 'Mobile UI Design', 'status' => 'Draft', 'statusColor' => 'dashboard-badge-gray', 'students' => 0, 'chapters' => 2],
                        ['title' => 'UX Research Essentials', 'status' => 'Draft', 'statusColor' => 'dashboard-badge-gray', 'students' => 0, 'chapters' => 1],
                    ];
                    @endphp
                    @foreach($courses as $course)
                    <tr>
                        <td class="font-semibold text-gray-900">{{ $course['title'] }}</td>
                        <td><span class="dashboard-badge {{ $course['statusColor'] }}">{{ $course['status'] }}</span></td>
                        <td>{{ $course['students'] }}</td>
                        <td>{{ $course['chapters'] }}</td>
                        <td><a href="#" class="text-sm font-semibold text-brand-dark hover:text-brand">Manage</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm reveal">
        <h2 class="text-lg font-bold mb-4">Course Performance</h2>
        <div class="space-y-4">
            @foreach([
                ['course' => 'UI/UX Design Fundamentals', 'enrolled' => 324, 'avg' => '4.9'],
                ['course' => 'Design Systems Masterclass', 'enrolled' => 186, 'avg' => '4.8'],
                ['course' => 'Brand Identity Design', 'enrolled' => 142, 'avg' => '4.7'],
            ] as $item)
            <div class="p-3 rounded-xl border border-gray-100">
                <p class="text-sm font-semibold text-gray-900">{{ $item['course'] }}</p>
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $item['enrolled'] }} enrolled</span>
                    <span class="flex items-center gap-1 text-amber-500 font-semibold">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        {{ $item['avg'] }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm reveal">
        <h2 class="text-lg font-bold mb-6">Recent Student Submissions</h2>
        <div class="space-y-3">
            @php
            $submissions = [
                ['student' => 'Ahmad Rizki', 'course' => 'UI/UX Design', 'type' => 'Essay', 'status' => 'AI Graded', 'statusColor' => 'dashboard-badge-green'],
                ['student' => 'Lisa Park', 'course' => 'UI/UX Design', 'type' => 'MCQ', 'status' => 'Auto-scored', 'statusColor' => 'dashboard-badge-blue'],
                ['student' => 'David Chen', 'course' => 'Design Systems', 'type' => 'Essay', 'status' => 'Pending', 'statusColor' => 'dashboard-badge-amber'],
            ];
            @endphp
            @foreach($submissions as $sub)
            <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-gray-100">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">{{ $sub['student'] }}</p>
                    <p class="text-xs text-gray-500">{{ $sub['course'] }} · {{ $sub['type'] }}</p>
                </div>
                <span class="dashboard-badge {{ $sub['statusColor'] }} shrink-0">{{ $sub['status'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm reveal">
        <h2 class="text-lg font-bold mb-6">Enrolment Overview</h2>
        <div class="flex items-end justify-between gap-2 h-40 px-2">
            @php $bars = [45, 62, 38, 80, 55, 72]; $months = ['Jan','Feb','Mar','Apr','May','Jun']; @endphp
            @foreach($bars as $i => $height)
            <div class="flex-1 flex flex-col items-center gap-2 group">
                <div class="w-full relative flex items-end justify-center h-32">
                    <div class="w-full max-w-[28px] rounded-t-lg bg-gradient-to-t from-brand to-brand/40 transition-all duration-700 group-hover:from-brand-dark" style="height: {{ $height }}%"></div>
                </div>
                <span class="text-[10px] text-gray-400 font-medium">{{ $months[$i] }}</span>
            </div>
            @endforeach
        </div>
        <p class="text-sm text-gray-500 text-center mt-4">Monthly new enrolments across all courses</p>
    </div>
</div>

@endsection
