@if ($paginator->hasPages())
    <nav class="super-admin-pagination" aria-label="Pagination">
        @if ($paginator->firstItem())
            <span>Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        @endif
        <div class="super-admin-pagination-links">
            @if ($paginator->previousPageUrl())
                <a class="admin-button admin-button-outline" rel="prev" href="{{ $paginator->previousPageUrl() }}">Previous</a>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="super-admin-page-number">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="super-admin-page-number" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="super-admin-page-number" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->nextPageUrl())
                <a class="admin-button admin-button-outline" rel="next" href="{{ $paginator->nextPageUrl() }}">Next</a>
            @endif
        </div>
    </nav>
@endif
