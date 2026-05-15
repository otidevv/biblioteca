@if ($paginator->hasPages())
<nav class="catalog-pagination" aria-label="Navegación de páginas">

    <div class="catalog-pagination__info">
        <i class="bi bi-journals"></i>
        Mostrando
        <strong>{{ number_format($paginator->firstItem()) }}</strong>
        –
        <strong>{{ number_format($paginator->lastItem()) }}</strong>
        de
        <strong>{{ number_format($paginator->total()) }}</strong>
        libros
    </div>

    <div class="catalog-pagination__nav">
        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="catalog-page-btn catalog-page-btn--arrow disabled" aria-disabled="true">
                <i class="bi bi-chevron-left"></i>
            </span>
        @else
            <a class="catalog-page-btn catalog-page-btn--arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Página anterior">
                <i class="bi bi-chevron-left"></i>
            </a>
        @endif

        {{-- Números --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="catalog-page-btn catalog-page-btn--dots">···</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="catalog-page-btn catalog-page-btn--active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="catalog-page-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <a class="catalog-page-btn catalog-page-btn--arrow" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Página siguiente">
                <i class="bi bi-chevron-right"></i>
            </a>
        @else
            <span class="catalog-page-btn catalog-page-btn--arrow disabled" aria-disabled="true">
                <i class="bi bi-chevron-right"></i>
            </span>
        @endif
    </div>

</nav>
@endif
