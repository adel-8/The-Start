@if ($paginator->hasPages())
    <ul class="pagination">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="disabled"><span class="page-link">‹</span></li>
        @else
            <li>
                <a href="{{ $paginator->previousPageUrl() }}" class="page-link">‹</a>
            </li>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="disabled"><span class="page-link">{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li class="{{ $page == $paginator->currentPage() ? 'active' : '' }}">
                        <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                    </li>
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li>
                <a href="{{ $paginator->nextPageUrl() }}" class="page-link">›</a>
            </li>
        @else
            <li class="disabled"><span class="page-link">›</span></li>
        @endif

    </ul>
@endif