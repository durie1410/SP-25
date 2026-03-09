@if ($paginator->hasPages())
    <nav aria-label="Page navigation" class="mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted">
                <small>
                    Hiển thị 
                    <span class="fw-bold">{{ $paginator->firstItem() }}</span>
                    đến 
                    <span class="fw-bold">{{ $paginator->lastItem() }}</span>
                    trong tổng số 
                    <span class="fw-bold">{{ $paginator->total() }}</span>
                    kết quả
                </small>
            </div>
            <ul class="pagination pagination-sm mb-0" style="list-style: none; margin-left: 0; padding-left: 0;">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" style="list-style: none;">
                        <span class="page-link" aria-hidden="true">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item" style="list-style: none;">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page" style="list-style: none;">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item" style="list-style: none;">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item" style="list-style: none;">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" style="list-style: none;">
                        <span class="page-link" aria-hidden="true">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
