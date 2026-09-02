@props(['id', 'title', 'size' => 'md'])

@php
    $sizeClass = match ($size) {
        'sm' => 'max-w-md',
        'lg' => 'max-w-2xl',
        default => 'max-w-lg',
    };
@endphp

<div id="{{ $id }}" data-modal class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" aria-hidden="true">
    <div data-modal-close class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative w-full {{ $sizeClass }} bg-white rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">{{ $title }}</h2>
            <button type="button" data-modal-close class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5">
            {{ $slot }}
        </div>
    </div>
</div>
