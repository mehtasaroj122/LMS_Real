@if ($paginator && $paginator->total() > 0)
    @php
        $currentPage = max(1, (int) $paginator->currentPage());
        $lastPage = max(1, (int) $paginator->lastPage());
        $pages = [];

        if ($lastPage <= 7) {
            $pages = range(1, $lastPage);
        } else {
            $startPage = max(2, $currentPage - 1);
            $endPage = min($lastPage - 1, $currentPage + 1);

            if ($currentPage <= 3) {
                $endPage = 4;
            }

            if ($currentPage >= $lastPage - 2) {
                $startPage = $lastPage - 3;
            }

            $pages[] = 1;

            if ($startPage > 2) {
                $pages[] = null;
            }

            for ($page = $startPage; $page <= $endPage; $page++) {
                $pages[] = $page;
            }

            if ($endPage < $lastPage - 1) {
                $pages[] = null;
            }

            $pages[] = $lastPage;
        }
    @endphp

    <div class="admin-table-pagination">
        <div class="admin-table-pagination-meta">
            <div class="admin-table-pagination-summary">
                Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} results
            </div>
            <div class="admin-table-pagination-page">
                Page {{ $currentPage }} of {{ $lastPage }}
            </div>
        </div>

        <nav class="admin-table-pagination-nav" aria-label="Pagination navigation">
            @if ($paginator->onFirstPage())
                <span class="admin-table-pagination-link is-disabled" aria-disabled="true">&larr; Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="admin-table-pagination-link" rel="prev">&larr; Previous</a>
            @endif

            @foreach ($pages as $page)
                @if ($page === null)
                    <span class="admin-table-pagination-ellipsis" aria-hidden="true">&hellip;</span>
                @elseif ($page === $currentPage)
                    <span class="admin-table-pagination-link is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="admin-table-pagination-link" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="admin-table-pagination-link" rel="next">Next &rarr;</a>
            @else
                <span class="admin-table-pagination-link is-disabled" aria-disabled="true">Next &rarr;</span>
            @endif
        </nav>
    </div>
@endif
