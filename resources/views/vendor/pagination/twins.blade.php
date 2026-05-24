@if ($paginator->hasPages())
    <div class="twins-pagination-container">
        <ul class="twins-pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="twins-page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="twins-page-link" aria-hidden="true"><iconify-icon icon="solar:alt-arrow-left-line-duotone"></iconify-icon></span>
                </li>
            @else
                <li class="twins-page-item">
                    <a class="twins-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><iconify-icon icon="solar:alt-arrow-left-line-duotone"></iconify-icon></a>
                </li>
            @endif

            {{-- Pagination Elements (Sliding window of max 3 pages) --}}
            @php
                $start = max(1, $paginator->currentPage() - 1);
                $end = min($paginator->lastPage(), $paginator->currentPage() + 1);

                // Adjust start/end to always show 3 pages if possible
                if ($end - $start < 2) {
                    if ($start == 1) {
                        $end = min($paginator->lastPage(), 3);
                    } elseif ($end == $paginator->lastPage()) {
                        $start = max(1, $paginator->lastPage() - 2);
                    }
                }
            @endphp

            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $paginator->currentPage())
                    <li class="twins-page-item active" aria-current="page"><span class="twins-page-link">{{ $page }}</span></li>
                @else
                    <li class="twins-page-item"><a class="twins-page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                @endif
            @endfor

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="twins-page-item">
                    <a class="twins-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"><iconify-icon icon="solar:alt-arrow-right-line-duotone"></iconify-icon></a>
                </li>
            @else
                <li class="twins-page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="twins-page-link" aria-hidden="true"><iconify-icon icon="solar:alt-arrow-right-line-duotone"></iconify-icon></span>
                </li>
            @endif
        </ul>
    </div>
@endif
