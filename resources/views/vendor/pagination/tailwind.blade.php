@if ($paginator->hasPages())
    <nav class="pager" role="navigation" aria-label="Pagination">
        <ul class="pager-list">
            @if ($paginator->onFirstPage())
                <li class="pager-item is-disabled"><span>Prev</span></li>
            @else
                <li class="pager-item"><a href="{{ $paginator->previousPageUrl() }}" rel="prev">Prev</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="pager-item is-disabled"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="pager-item is-active"><span>{{ $page }}</span></li>
                        @else
                            <li class="pager-item"><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="pager-item"><a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a></li>
            @else
                <li class="pager-item is-disabled"><span>Next</span></li>
            @endif
        </ul>
    </nav>
@endif
