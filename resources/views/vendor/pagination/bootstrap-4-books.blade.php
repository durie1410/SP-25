@if ($paginator->hasPages())
    <ul class="pagination" style="list-style: none; display: flex; gap: 4px; justify-content: center;">
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}#books-stock-section">&lsaquo;</a>
            </li>
        @endif

        @foreach ($elements as $element)
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if (is_string($page))
                    @elseif ($page == $paginator->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}#books-stock-section">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}#books-stock-section">&rsaquo;</a>
            </li>
        @else
            <li class="page-item disabled"><span class="page-link">&rsaquo;</span></li>
        @endif
    </ul>
@endif
