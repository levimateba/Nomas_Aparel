@if ($paginator->hasPages())
    <nav class="pager" role="navigation" aria-label="Pagination">
        <ul class="pager-list">
            @if ($paginator->onFirstPage())
                <li class="pager-item is-disabled"><span>Prev</span></li>
            @else
                <li class="pager-item"><a href="{{ $paginator->previousPageUrl() }}" rel="prev">Prev</a></li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="pager-item"><a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a></li>
            @else
                <li class="pager-item is-disabled"><span>Next</span></li>
            @endif
        </ul>
    </nav>
@endif
