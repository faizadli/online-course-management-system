@extends('layouts.dashboard')

@section('title', 'Dashboard — SkillForge')

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 animate-fade-in-up">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Dashboard</h1>
        <p class="text-gray-500 mt-1">Welcome back! Here's your learning overview.</p>
    </div>
    <div class="flex flex-wrap gap-2 sm:gap-3">
        <button class="flex-1 sm:flex-none px-4 sm:px-5 py-2.5 bg-gray-900 text-white text-sm font-semibold rounded-full hover:bg-gray-800 transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2 min-w-[120px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Project
        </button>
        <button class="flex-1 sm:flex-none px-4 sm:px-5 py-2.5 border border-gray-200 text-sm font-semibold rounded-full hover:bg-gray-50 transition-all duration-300 text-center min-w-[120px]">
            Import Data
        </button>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @php
    $stats = [
        ['label' => 'Total Course', 'value' => '35', 'trend' => '8.5%', 'up' => true, 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['label' => 'Course Complete', 'value' => '27', 'trend' => '4.9%', 'up' => false, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Course In Progress', 'value' => '18', 'trend' => '9.7%', 'up' => true, 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['label' => 'Upcoming Live Courses', 'value' => '10', 'trend' => '1.7%', 'up' => true, 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
    ];
    $statDelays = ['reveal-delay-0', 'reveal-delay-80', 'reveal-delay-160', 'reveal-delay-240'];
    @endphp
    @foreach($stats as $index => $stat)
    <div class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5 reveal {{ $statDelays[$index] ?? 'reveal-delay-0' }}">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 bg-brand-light rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $stat['icon'] }}"/></svg>
            </div>
            <span class="text-xs font-medium {{ $stat['up'] ? 'text-green-500' : 'text-red-500' }}">
                {{ $stat['trend'] }} {{ $stat['up'] ? 'Up' : 'Down' }}
            </span>
        </div>
        <p class="mt-4 text-3xl font-bold">{{ $stat['value'] }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
        <a href="#" class="inline-flex items-center gap-1 mt-3 text-xs font-semibold text-brand-dark hover:gap-2 transition-all duration-300">
            View Details
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
    @endforeach
</div>

{{-- Main Grid --}}
<div class="grid lg:grid-cols-3 gap-6 mb-8">
    {{-- Course Statistics Chart --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm reveal overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-lg font-bold">Course Statistics</h2>
            <div class="flex bg-gray-100 rounded-full p-1 self-start sm:self-auto">
                @foreach(['Daily', 'Weekly', 'Monthly'] as $i => $tab)
                <button class="px-3 sm:px-4 py-1.5 text-xs font-semibold rounded-full transition-all duration-300 {{ $i === 1 ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">{{ $tab }}</button>
                @endforeach
            </div>
        </div>
        {{-- Bar Chart --}}
        <div class="overflow-x-auto -mx-2 px-2 pb-2">
        <div class="flex items-end justify-between gap-1 sm:gap-2 h-48 min-w-[480px] sm:min-w-0 px-2">
            @php
            $bars = [40, 65, 45, 80, 55, 70, 90, 60, 75, 50, 85, 68];
            $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            @endphp
            @foreach($bars as $i => $height)
            <div class="flex-1 flex flex-col items-center gap-2 group min-w-[28px]">
                <div class="w-full relative flex items-end justify-center h-40">
                    <div class="w-full max-w-[28px] h-0 rounded-t-lg bg-gradient-to-t from-brand to-brand/40 transition-all duration-700 group-hover:from-brand-dark group-hover:to-brand/60"
                        data-bar-height="{{ $height }}"></div>
                </div>
                <span class="text-[10px] text-gray-400 font-medium">{{ $months[$i] }}</span>
            </div>
            @endforeach
        </div>
        </div>
    </div>

    {{-- Recent Courses --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm reveal">
        <h2 class="text-lg font-bold mb-4">Recent Courses</h2>
        @php
        $recentCourses = [
            ['title' => 'Develop API Endpoints', 'due' => 'Due in 2 days', 'status' => 'Complete', 'statusColor' => 'bg-brand-light text-brand-dark'],
            ['title' => 'UI Design System', 'due' => 'Due in 5 days', 'status' => 'In Progress', 'statusColor' => 'bg-green-100 text-green-700'],
            ['title' => 'Database Optimization', 'due' => 'Due in 1 week', 'status' => 'Pending', 'statusColor' => 'bg-gray-100 text-gray-600'],
            ['title' => 'React Native Basics', 'due' => 'Due in 3 days', 'status' => 'In Progress', 'statusColor' => 'bg-green-100 text-green-700'],
            ['title' => 'Cloud Architecture', 'due' => 'Due tomorrow', 'status' => 'Complete', 'statusColor' => 'bg-brand-light text-brand-dark'],
        ];
        @endphp
        <div class="space-y-4">
            @foreach($recentCourses as $course)
            <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-all duration-300 group cursor-pointer">
                <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-brand-light transition-colors duration-300">
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-brand-dark transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate">{{ $course['title'] }}</p>
                    <p class="text-xs text-gray-400">{{ $course['due'] }}</p>
                </div>
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $course['statusColor'] }}">{{ $course['status'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Bottom Grid --}}
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Assignments Table --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm reveal">
        <h2 class="text-lg font-bold mb-6">Your Assignments</h2>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <th class="pb-4 pr-4">Course Name</th>
                        <th class="pb-4 pr-4">Progress</th>
                        <th class="pb-4 pr-4">Status</th>
                        <th class="pb-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                    $assignments = [
                        ['name' => 'Figma UI Design', 'icon' => 'F', 'iconBg' => 'bg-purple-100 text-purple-600', 'progress' => 85, 'status' => 'In Progress', 'statusColor' => 'bg-brand-light text-brand-dark'],
                        ['name' => 'Behance Portfolio', 'icon' => 'B', 'iconBg' => 'bg-blue-100 text-blue-600', 'progress' => 100, 'status' => 'Completed', 'statusColor' => 'bg-green-100 text-green-700'],
                        ['name' => 'React Fundamentals', 'icon' => 'R', 'iconBg' => 'bg-cyan-100 text-cyan-600', 'progress' => 45, 'status' => 'In Progress', 'statusColor' => 'bg-brand-light text-brand-dark'],
                        ['name' => 'Python for Data', 'icon' => 'P', 'iconBg' => 'bg-yellow-100 text-yellow-600', 'progress' => 60, 'status' => 'In Progress', 'statusColor' => 'bg-brand-light text-brand-dark'],
                    ];
                    @endphp
                    @foreach($assignments as $assignment)
                    <tr class="group hover:bg-gray-50/50 transition-colors duration-300">
                        <td class="py-4 pr-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 {{ $assignment['iconBg'] }} rounded-lg flex items-center justify-center text-xs font-bold">{{ $assignment['icon'] }}</div>
                                <span class="text-sm font-semibold">{{ $assignment['name'] }}</span>
                            </div>
                        </td>
                        <td class="py-4 pr-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden max-w-[120px]">
                                    <div class="h-full w-0 bg-brand rounded-full transition-all duration-1000 ease-out" data-progress="{{ $assignment['progress'] }}"></div>
                                </div>
                                <span class="text-xs text-gray-400 font-medium">{{ $assignment['progress'] }}%</span>
                            </div>
                        </td>
                        <td class="py-4 pr-4">
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $assignment['statusColor'] }}">{{ $assignment['status'] }}</span>
                        </td>
                        <td class="py-4">
                            <button class="w-8 h-8 bg-gray-900 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-brand hover:scale-110">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden space-y-3">
            @foreach($assignments as $assignment)
            <div class="p-4 rounded-xl border border-gray-100 hover:border-brand/30 hover:shadow-sm transition-all duration-300">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 {{ $assignment['iconBg'] }} rounded-lg flex items-center justify-center text-xs font-bold shrink-0">{{ $assignment['icon'] }}</div>
                        <span class="text-sm font-semibold truncate">{{ $assignment['name'] }}</span>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full shrink-0 {{ $assignment['statusColor'] }}">{{ $assignment['status'] }}</span>
                </div>
                <div class="mt-3 flex items-center gap-3">
                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full w-0 bg-brand rounded-full transition-all duration-1000 ease-out" data-progress="{{ $assignment['progress'] }}"></div>
                    </div>
                    <span class="text-xs text-gray-400 font-medium shrink-0">{{ $assignment['progress'] }}%</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Right Widgets --}}
    <div class="space-y-6">
        {{-- Assignment Breakdown --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm reveal">
            <h2 class="text-lg font-bold mb-4">Assignment Breakdown</h2>
            <div class="space-y-3 mb-6">
                @foreach([['label' => 'Pending', 'count' => 11, 'color' => 'bg-red-400'], ['label' => 'Submitted', 'count' => 34, 'color' => 'bg-amber-400'], ['label' => 'Graded', 'count' => 145, 'color' => 'bg-green-400']] as $item)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $item['color'] }}"></span>
                        <span class="text-sm text-gray-600">{{ $item['label'] }}</span>
                    </div>
                    <span class="text-sm font-bold">{{ $item['count'] }}</span>
                </div>
                @endforeach
            </div>
            {{-- Gauge --}}
            <div class="flex flex-col items-center">
                <div class="relative w-36 h-20 overflow-hidden">
                    <div class="absolute inset-0 rounded-t-full border-[12px] border-gray-100"></div>
                    <div class="absolute inset-0 rounded-t-full border-[12px] border-transparent border-t-brand border-r-brand-dark" style="transform: rotate(45deg)"></div>
                </div>
                <p class="text-2xl font-bold mt-2">85%</p>
                <p class="text-xs text-gray-400">Assignment Complete</p>
            </div>
        </div>

        {{-- Community --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm reveal">
            <h2 class="text-lg font-bold mb-4">Community</h2>
            <div class="space-y-3">
                <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-all duration-300 group">
                    <div class="w-10 h-10 bg-[#4A154B] rounded-xl flex items-center justify-center">
                        <span class="text-white text-xs font-bold">S</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold group-hover:text-brand-dark transition-colors duration-300">Slack Community</p>
                        <p class="text-xs text-gray-400">2,450 members</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-brand-dark group-hover:translate-x-0.5 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-all duration-300 group">
                    <div class="w-10 h-10 bg-[#5865F2] rounded-xl flex items-center justify-center">
                        <span class="text-white text-xs font-bold">D</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold group-hover:text-brand-dark transition-colors duration-300">Discord Server</p>
                        <p class="text-xs text-gray-400">1,820 members</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-brand-dark group-hover:translate-x-0.5 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>

        {{-- Chats Widget --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm reveal">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">Chats</h2>
                <a href="#" class="text-xs font-semibold text-brand-dark hover:underline">All Messages</a>
            </div>
            <div class="flex -space-x-2">
                @foreach(['photo-1494790108377-be9c29b29330', 'photo-1507003211169-0a1dd7228f2d', 'photo-1438761681033-6461ffad8d80', 'photo-1472099645785-5658abf4ff4e', 'photo-1500648767791-00dcc994a43e'] as $avatar)
                <img src="https://images.unsplash.com/{{ $avatar }}?w=64&h=64&fit=crop&crop=face" alt="" class="w-9 h-9 rounded-full border-2 border-white object-cover hover:scale-110 hover:z-10 transition-transform duration-300 cursor-pointer">
                @endforeach
                <div class="w-9 h-9 rounded-full border-2 border-white bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500 hover:bg-brand hover:text-gray-800 transition-colors duration-300 cursor-pointer">+5</div>
            </div>
        </div>
    </div>
</div>

@endsection
