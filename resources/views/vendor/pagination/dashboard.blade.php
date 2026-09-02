@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination" class="flex items-center justify-center gap-1">
    @if ($paginator->onFirstPage())
    <span class="px-3 py-1.5 text-sm text-gray-300 rounded-lg cursor-not-allowed">&laquo;</span>
    @else
    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">&laquo;</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
        <span class="px-2 py-1.5 text-sm text-gray-400">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <span class="px-3 py-1.5 text-sm font-semibold text-white bg-brand-dark rounded-lg">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">&raquo;</a>
    @else
    <span class="px-3 py-1.5 text-sm text-gray-300 rounded-lg cursor-not-allowed">&raquo;</span>
    @endif
</nav>
@endif
