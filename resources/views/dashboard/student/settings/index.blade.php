@extends('layouts.dashboard', ['role' => 'student'])

@section('title', 'Settings — Student — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">Settings</h1>
    <p class="text-gray-500 mt-1">Manage your account security and preferences.</p>
</div>

<div class="space-y-6 max-w-3xl">
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-1">Change Password</h2>
        <p class="text-sm text-gray-500 mb-6">Use a strong password that you do not use elsewhere.</p>
        <form action="{{ route('dashboard.student.settings.password') }}" method="post" class="space-y-5" data-validate="admin-password" novalidate>
            @csrf
            @method('PUT')
            <div class="form-field">
                <label class="dashboard-label" for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="dashboard-input @error('current_password') is-invalid @enderror" autocomplete="current-password">
                @include('partials.field-error', ['name' => 'current_password'])
            </div>
            <div class="form-field">
                <label class="dashboard-label" for="password">New Password</label>
                <input type="password" id="password" name="password" class="dashboard-input @error('password') is-invalid @enderror" autocomplete="new-password">
                @include('partials.field-error', ['name' => 'password'])
            </div>
            <div class="form-field">
                <label class="dashboard-label" for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="dashboard-input @error('password_confirmation') is-invalid @enderror" autocomplete="new-password">
                @include('partials.field-error', ['name' => 'password_confirmation'])
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">Update Password</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 class="text-lg font-bold mb-1">Account</h2>
        <p class="text-sm text-gray-500 mb-6">Your account status and role on the platform.</p>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Account Status</p>
                    <p class="text-xs text-gray-500 mt-0.5">Whether your account can access the dashboard</p>
                </div>
                <span class="dashboard-badge {{ $user->is_active ? 'dashboard-badge-green' : 'dashboard-badge-gray' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Role</p>
                    <p class="text-xs text-gray-500 mt-0.5">Your access level on OCMS</p>
                </div>
                <span class="dashboard-badge dashboard-badge-blue">{{ $user->role->label() }}</span>
            </div>
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Email</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</p>
                </div>
                <a href="{{ route('dashboard.student.profile.index') }}" class="text-sm font-semibold text-brand-dark hover:text-brand">Edit profile</a>
            </div>
        </div>
    </div>
</div>

@endsection
