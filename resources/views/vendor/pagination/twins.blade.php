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

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="twins-page-item disabled mobile-hide" aria-disabled="true"><span class="twins-page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @php
                            $isNear = abs($page - $paginator->currentPage()) <= 1;
                        @endphp
                        @if ($page == $paginator->currentPage())
                            <li class="twins-page-item active" aria-current="page"><span class="twins-page-link">{{ $page }}</span></li>
                        @else
                            <li class="twins-page-item {{ $isNear ? '' : 'mobile-hide' }}"><a class="twins-page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

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
