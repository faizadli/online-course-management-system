@if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
@php
    $queryParams = request()->except(['per_page', 'page']);
    $lastPage = max($paginator->lastPage(), 1);
@endphp
<div class="dashboard-pagination">
    <div class="dashboard-pagination-meta">
        <form method="get" action="{{ request()->url() }}" class="dashboard-per-page-form">
            @foreach ($queryParams as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                    @endforeach
                @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label for="per-page-{{ $paginator->getPageName() }}" class="dashboard-per-page-label">Show</label>
            <select id="per-page-{{ $paginator->getPageName() }}" name="per_page" class="dashboard-per-page-select" onchange="this.form.submit()">
                @foreach (\App\Support\AdminPagination::PER_PAGE_OPTIONS as $option)
                <option value="{{ $option }}" @selected($paginator->perPage() === $option)>{{ $option }}</option>
                @endforeach
            </select>
            <span class="dashboard-per-page-label">entries</span>
        </form>

        <p class="dashboard-pagination-info">
            Showing
            <span class="font-medium text-gray-700">{{ $paginator->firstItem() ?? 0 }}</span>
            to
            <span class="font-medium text-gray-700">{{ $paginator->lastItem() ?? 0 }}</span>
            of
            <span class="font-medium text-gray-700">{{ $paginator->total() }}</span>
            results
        </p>
    </div>

    <nav role="navigation" aria-label="Pagination" class="dashboard-pagination-nav">
        @if ($paginator->onFirstPage())
        <span class="dashboard-pagination-btn dashboard-pagination-btn-disabled" aria-disabled="true">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Previous
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" class="dashboard-pagination-btn" rel="prev">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Previous
        </a>
        @endif

        <div class="dashboard-pagination-pages">
            @for ($page = 1; $page <= $lastPage; $page++)
                @if ($page == $paginator->currentPage())
                <span class="dashboard-pagination-page dashboard-pagination-page-active" aria-current="page">{{ $page }}</span>
                @else
                <a href="{{ $paginator->url($page) }}" class="dashboard-pagination-page">{{ $page }}</a>
                @endif
            @endfor
        </div>

        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="dashboard-pagination-btn" rel="next">
            Next
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        @else
        <span class="dashboard-pagination-btn dashboard-pagination-btn-disabled" aria-disabled="true">
            Next
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </span>
        @endif
    </nav>
</div>
@endif
