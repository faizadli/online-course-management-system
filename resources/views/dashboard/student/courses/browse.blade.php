@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'Browse Courses — Student — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">Browse Courses</h1>
    <p class="text-gray-500 mt-1">Explore published courses and enrol to start learning.</p>
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm mb-6">
    <form method="get" action="{{ route('dashboard.student.courses.browse') }}" class="flex flex-col lg:flex-row gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search courses..." class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
        </div>
        <select name="category_id" class="dashboard-input !w-full lg:!w-56" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">Search</button>
    </form>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @forelse($courses as $course)
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-transparent hover:border-brand/30 transition-all">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $course->category?->name ?? 'Course' }}</p>
                <h2 class="text-base font-bold text-gray-900 mt-1">{{ $course->title }}</h2>
            </div>
            <span class="text-sm font-bold text-brand-dark">{{ $course->price_label }}</span>
        </div>
        <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ $course->short_description ?: 'No description available.' }}</p>
        <div class="flex items-center justify-between text-xs text-gray-400 mb-4">
            <span>{{ $course->instructor?->name ?? 'Instructor' }}</span>
            <span>{{ $course->chapters_count }} chapters · {{ $course->level }}</span>
        </div>
        @if($enrolledIds->contains($course->id))
        <a href="{{ route('dashboard.student.courses.show', $course) }}" class="block w-full text-center py-2.5 border border-gray-200 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors">
            Continue Learning
        </a>
        @else
        <form action="{{ route('cart.buy', $course->slug) }}" method="post">
            @csrf
            <button type="submit" class="w-full py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">
                Enrol Now · {{ $course->price_label }}
            </button>
        </form>
        @endif
    </div>
    @empty
    <div class="sm:col-span-2 xl:col-span-3 bg-white rounded-2xl p-10 shadow-sm text-center text-gray-500">
        No published courses found.
    </div>
    @endforelse
</div>

@include('partials.dashboard.pagination', ['paginator' => $courses])

@endsection
