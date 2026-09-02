@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'Student Dashboard — SkillForge')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 animate-fade-in-up">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Student Dashboard</h1>
        <p class="text-gray-500 mt-1">Track your enrolled courses, assessments, and AI feedback.</p>
    </div>
    <a href="{{ route('courses') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Browse Courses
    </a>
</div>

@php
$stats = [
    ['label' => 'Enrolled Courses', 'value' => '4', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ['label' => 'Completed', 'value' => '1', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['label' => 'In Progress', 'value' => '2', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
    ['label' => 'Pending Assessments', 'value' => '3', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
];
@endphp

<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach($stats as $index => $stat)
    <div class="dashboard-stat-card reveal {{ ['reveal-delay-0','reveal-delay-80','reveal-delay-160','reveal-delay-240'][$index] }}">
        <div class="w-10 h-10 bg-brand-light rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $stat['icon'] }}"/></svg>
        </div>
        <p class="mt-4 text-3xl font-bold">{{ $stat['value'] }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm reveal">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold">My Enrolled Courses</h2>
            <a href="#" class="text-sm font-semibold text-brand-dark hover:text-brand">View all</a>
        </div>
        @php
        $enrolledCourses = [
            ['title' => 'UI/UX Design Fundamentals', 'instructor' => 'Sarah Chen', 'progress' => 72, 'image' => 'photo-1561070791-2526d30994b5', 'slug' => 'ui-ux-design-fundamentals'],
            ['title' => 'Full Stack Web Development', 'instructor' => 'John Doe', 'progress' => 38, 'image' => 'photo-1498050108023-c5249f4df085', 'slug' => 'full-stack-web-development'],
            ['title' => 'Python Data Science Bootcamp', 'instructor' => 'Mike Johnson', 'progress' => 15, 'image' => 'photo-1555949963-aa79d882981c', 'slug' => 'python-data-science-bootcamp'],
        ];
        @endphp
        <div class="space-y-4">
            @foreach($enrolledCourses as $course)
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-brand/30 hover:shadow-sm transition-all duration-300">
                <img src="https://images.unsplash.com/{{ $course['image'] }}?w=120&h=80&fit=crop" alt="" class="w-full sm:w-24 h-20 rounded-lg object-cover shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $course['title'] }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $course['instructor'] }}</p>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-brand rounded-full transition-all duration-1000" style="width: {{ $course['progress'] }}%"></div>
                        </div>
                        <span class="text-xs text-gray-400 font-medium shrink-0">{{ $course['progress'] }}%</span>
                    </div>
                </div>
                <a href="{{ route('courses.show', $course['slug']) }}" class="shrink-0 px-4 py-2 text-sm font-semibold border border-gray-200 rounded-full hover:border-brand-dark hover:text-brand-dark transition-colors text-center">Continue</a>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm reveal">
        <h2 class="text-lg font-bold mb-4">Upcoming Assessments</h2>
        @php
        $assessments = [
            ['title' => 'UI/UX — Design Thinking MCQ', 'type' => 'MCQ', 'due' => 'Due in 2 days', 'typeColor' => 'bg-blue-100 text-blue-700'],
            ['title' => 'Full Stack — API Essay', 'type' => 'Essay', 'due' => 'Due in 5 days', 'typeColor' => 'bg-purple-100 text-purple-700'],
            ['title' => 'Python — Data Analysis MCQ', 'type' => 'MCQ', 'due' => 'Due in 1 week', 'typeColor' => 'bg-blue-100 text-blue-700'],
        ];
        @endphp
        <div class="space-y-3">
            @foreach($assessments as $item)
            <div class="p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-semibold text-gray-900 leading-snug">{{ $item['title'] }}</p>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 {{ $item['typeColor'] }}">{{ $item['type'] }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $item['due'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm reveal">
        <h2 class="text-lg font-bold mb-6">Recent AI Feedback</h2>
        <div class="space-y-4">
            @php
            $feedback = [
                ['course' => 'UI/UX Design Fundamentals', 'question' => 'Explain the design thinking process', 'score' => '85/100', 'date' => '2 days ago'],
                ['course' => 'Full Stack Web Development', 'question' => 'Describe REST API best practices', 'score' => '78/100', 'date' => '5 days ago'],
            ];
            @endphp
            @foreach($feedback as $item)
            <div class="p-4 rounded-xl bg-brand-light/40 border border-brand/10">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-gray-900">{{ $item['course'] }}</p>
                    <span class="text-sm font-bold text-brand-dark">{{ $item['score'] }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-2">{{ $item['question'] }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $item['date'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm reveal">
        <h2 class="text-lg font-bold mb-6">Assessment Status</h2>
        <div class="space-y-3 mb-6">
            @foreach([['label' => 'Pending', 'count' => 3, 'color' => 'bg-amber-400'], ['label' => 'Submitted', 'count' => 8, 'color' => 'bg-blue-400'], ['label' => 'AI Graded', 'count' => 5, 'color' => 'bg-green-400']] as $item)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $item['color'] }}"></span>
                    <span class="text-sm text-gray-600">{{ $item['label'] }}</span>
                </div>
                <span class="text-sm font-bold">{{ $item['count'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="p-4 rounded-xl bg-gray-50 text-center">
            <p class="text-3xl font-bold text-brand-dark">16</p>
            <p class="text-sm text-gray-500 mt-1">Total assessments completed</p>
        </div>
    </div>
</div>

@endsection
