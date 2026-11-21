@if ($paginator->hasPages())
    <nav class="super-admin-pagination" aria-label="{{ ($arabic ?? false) ? 'التنقّل بين الصفحات' : 'Pagination' }}">
        @if ($paginator->firstItem())
            <span>{{ ($arabic ?? false) ? 'عرض' : 'Showing' }} {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} {{ ($arabic ?? false) ? 'من' : 'of' }} {{ $paginator->total() }}</span>
        @endif
        <div class="super-admin-pagination-links">
            @if ($paginator->previousPageUrl())
                <a class="admin-button admin-button-outline" rel="prev" href="{{ $paginator->previousPageUrl() }}">{{ ($arabic ?? false) ? 'السابق' : 'Previous' }}</a>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="super-admin-page-number">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="super-admin-page-number" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="super-admin-page-number" href="{{ $url }}" aria-label="{{ ($arabic ?? false) ? 'صفحة' : 'Page' }} {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->nextPageUrl())
                <a class="admin-button admin-button-outline" rel="next" href="{{ $paginator->nextPageUrl() }}">{{ ($arabic ?? false) ? 'التالي' : 'Next' }}</a>
            @endif
        </div>
    </nav>
@endif
