@extends('layouts.dashboard', ['role' => 'admin'])

@section('title', 'Categories — Admin — OCMS')

@section('content')

@include('partials.dashboard.alerts')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">Course Categories</h1>
        <p class="text-gray-500 mt-1">Manage categories used to organize courses on the platform.</p>
    </div>
    <button type="button" data-modal-open="create-category-modal" class="inline-flex items-center justify-center px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-full hover:bg-brand transition-all duration-300">
        + Add Category
    </button>
</div>

<div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach([
                ['label' => 'All', 'value' => null, 'count' => $counts['all']],
                ['label' => 'Active', 'value' => 'active', 'count' => $counts['active']],
                ['label' => 'Inactive', 'value' => 'inactive', 'count' => $counts['inactive']],
            ] as $tab)
            <a href="{{ route('dashboard.admin.categories.index', array_filter(['status' => $tab['value'], 'search' => $search])) }}"
                class="px-3 py-1.5 text-xs font-semibold rounded-full transition-colors {{ ($status ?? null) === $tab['value'] ? 'bg-brand-light text-brand-dark' : 'text-gray-500 hover:bg-gray-100' }}">
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
            @endforeach
        </div>
        <form method="get" action="{{ route('dashboard.admin.categories.index') }}" class="relative w-full lg:max-w-xs">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search categories..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full dashboard-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Courses</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="font-semibold text-gray-900">{{ $category->name }}</td>
                    <td class="text-gray-500">{{ $category->slug }}</td>
                    <td class="text-gray-500 max-w-xs truncate">{{ $category->description ?: '—' }}</td>
                    <td class="text-gray-500">{{ number_format($category->courses_count) }}</td>
                    <td>
                        <span class="dashboard-badge {{ $category->is_active ? 'dashboard-badge-green' : 'dashboard-badge-gray' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-gray-500">{{ $category->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="flex items-center gap-3">
                            <button type="button"
                                class="text-sm font-semibold text-brand-dark hover:text-brand"
                                data-edit-category
                                data-action="{{ route('dashboard.admin.categories.update', $category) }}"
                                data-name="{{ $category->name }}"
                                data-description="{{ $category->description }}"
                                data-active="{{ $category->is_active ? '1' : '0' }}">
                                Edit
                            </button>
                            @if($category->courses_count === 0)
                            <form action="{{ route('dashboard.admin.categories.destroy', $category) }}" method="post" class="inline" onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-red-500 hover:text-red-600">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-gray-500 py-8">No categories found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.dashboard.pagination', ['paginator' => $categories])
</div>

<x-dashboard.modal id="create-category-modal" title="Add Category">
    <form action="{{ route('dashboard.admin.categories.store') }}" method="post" class="space-y-4" data-validate="category-create" novalidate>
        @csrf
        <div class="form-field">
            <label class="dashboard-label" for="create-name">Name</label>
            <input type="text" id="create-name" name="name" value="{{ old('name') }}" class="dashboard-input @error('name') is-invalid @enderror" placeholder="e.g. Data Science">
            @include('partials.field-error', ['name' => 'name'])
        </div>
        <div class="form-field">
            <label class="dashboard-label" for="create-description">Description</label>
            <textarea id="create-description" name="description" rows="3" class="dashboard-input @error('description') is-invalid @enderror" placeholder="Brief description...">{{ old('description') }}</textarea>
            @include('partials.field-error', ['name' => 'description'])
        </div>
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" data-modal-close class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800">Cancel</button>
            <button type="submit" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">
                Create Category
            </button>
        </div>
    </form>
</x-dashboard.modal>

<x-dashboard.modal id="edit-category-modal" title="Edit Category">
    <form method="post" class="space-y-4" data-validate="category-edit" novalidate>
        @csrf
        @method('PUT')
        <div class="form-field">
            <label class="dashboard-label" for="edit-name">Name</label>
            <input type="text" id="edit-name" name="name" class="dashboard-input @error('name') is-invalid @enderror">
            @include('partials.field-error', ['name' => 'name'])
        </div>
        <div class="form-field">
            <label class="dashboard-label" for="edit-description">Description</label>
            <textarea id="edit-description" name="description" rows="3" class="dashboard-input @error('description') is-invalid @enderror"></textarea>
            @include('partials.field-error', ['name' => 'description'])
        </div>
        <div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="checkout-checkbox">
                Active
            </label>
        </div>
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" data-modal-close class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800">Cancel</button>
            <button type="submit" class="px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">
                Save Changes
            </button>
        </div>
    </form>
</x-dashboard.modal>

@if(request('create') || ($errors->any() && !old('_method')))
<script>document.addEventListener('DOMContentLoaded', () => window.openDashboardModal?.('create-category-modal'));</script>
@endif

@if($errors->any() && old('_method') === 'PUT')
<script>document.addEventListener('DOMContentLoaded', () => window.openDashboardModal?.('edit-category-modal'));</script>
@endif

@endsection
