@extends('layouts.dashboard', ['role' => 'admin'])

@section('title', 'Users — Admin — SkillForge')

@section('content')

@include('partials.dashboard.alerts')

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold">User Management</h1>
    <p class="text-gray-500 mt-1">Search, view, and manage student and instructor accounts.</p>
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['label' => 'All', 'value' => null, 'count' => $counts['all']],
                ['label' => 'Students', 'value' => 'student', 'count' => $counts['student']],
                ['label' => 'Instructors', 'value' => 'instructor', 'count' => $counts['instructor']],
                ['label' => 'Admins', 'value' => 'admin', 'count' => $counts['admin']],
            ] as $tab)
            <a href="{{ route('dashboard.admin.users.index', array_filter(['role' => $tab['value'], 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($role ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
            @endforeach
        </div>
        <form method="get" action="{{ route('dashboard.admin.users.index') }}" class="relative w-full lg:max-w-xs">
            @if($role)<input type="hidden" name="role" value="{{ $role }}">@endif
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search users..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full dashboard-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="font-semibold text-gray-900">{{ $user->name }}</td>
                    <td>
                        <span class="dashboard-badge {{ $user->role->value === 'student' ? 'dashboard-badge-blue' : ($user->role->value === 'instructor' ? 'dashboard-badge-purple' : 'dashboard-badge-amber') }}">
                            {{ $user->role->label() }}
                        </span>
                    </td>
                    <td class="text-gray-500">{{ $user->email }}</td>
                    <td>
                        <span class="dashboard-badge {{ $user->is_active ? 'dashboard-badge-green' : 'dashboard-badge-gray' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('dashboard.admin.users.toggle', $user) }}" method="post" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-sm font-semibold text-brand-dark hover:text-brand">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-500 py-8">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.dashboard.pagination', ['paginator' => $users])
</div>

@endsection
