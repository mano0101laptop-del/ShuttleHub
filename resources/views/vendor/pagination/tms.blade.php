@if ($paginator->hasPages())
<nav class="pg-nav" role="navigation" aria-label="Pagination Navigation">
  <div class="pg-info">
    Showing <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
    of <strong>{{ $paginator->total() }}</strong>
  </div>

  <div class="pg-links">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
      <span class="pg-btn pg-disabled" aria-disabled="true"><i class="fas fa-chevron-left"></i></span>
    @else
      <a class="pg-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="fas fa-chevron-left"></i></a>
    @endif

    {{-- Page numbers --}}
    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="pg-btn pg-dots">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="pg-btn pg-active">{{ $page }}</span>
          @else
            <a class="pg-btn" href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
      <a class="pg-btn" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="fas fa-chevron-right"></i></a>
    @else
      <span class="pg-btn pg-disabled" aria-disabled="true"><i class="fas fa-chevron-right"></i></span>
    @endif
  </div>
</nav>
@endif
