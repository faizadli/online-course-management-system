@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'Profile — Student — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">Profile</h1>
    <p class="text-gray-500 mt-1">Manage your personal information.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=160&h=160&fit=crop&crop=face" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover mx-auto ring-4 ring-brand/20">
        <h2 class="text-lg font-bold mt-4">{{ $user->name }}</h2>
        <p class="text-sm text-gray-500">{{ $user->email }}</p>
        <span class="inline-flex mt-3 dashboard-badge dashboard-badge-blue">{{ $user->role->label() }}</span>
        <p class="text-xs text-gray-400 mt-4">Member since {{ $user->created_at->format('M d, Y') }}</p>
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-6">Personal Information</h2>
        <form action="{{ route('dashboard.student.profile.update') }}" method="post" class="space-y-5" data-validate="admin-profile" novalidate>
            @csrf
            @method('PUT')
            <div class="form-field">
                <label class="dashboard-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="dashboard-input @error('name') is-invalid @enderror">
                @include('partials.field-error', ['name' => 'name'])
            </div>
            <div class="form-field">
                <label class="dashboard-label" for="email">Email Address</label>
                <input type="text" id="email" name="email" value="{{ old('email', $user->email) }}" class="dashboard-input @error('email') is-invalid @enderror">
                @include('partials.field-error', ['name' => 'email'])
            </div>
            <div class="form-field">
                <label class="dashboard-label" for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4" class="dashboard-input @error('bio') is-invalid @enderror" placeholder="Tell us about your learning goals...">{{ old('bio', $user->bio) }}</textarea>
                @include('partials.field-error', ['name' => 'bio'])
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">Save Profile</button>
            </div>
        </form>
    </div>
</div>

@endsection
