

<div class="row g-4" id="contenedorLibros">
    @forelse($libros as $libro)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <a href="{{ route('libro.show', $libro->id) }}"
               class="catalog-grid-card"
               aria-label="Ver detalle del libro {{ $libro->titulo }}">
                <div class="catalog-grid-cover-wrap">
                    <img src="{{ $libro->imagen_url }}"
                         alt="{{ $libro->titulo }}"
                         class="catalog-grid-cover"
                         onerror="this.onerror=null;this.src='{{ asset('img/libro-placeholder.png') }}';"
                         loading="lazy"
                         decoding="async">
                </div>

                <div class="catalog-grid-body">
                    <span class="catalog-grid-tag">
                        <i class="bi bi-book-half"></i>
                        Catalogo
                    </span>

                    <h6 class="catalog-grid-title" title="{{ $libro->titulo }}">
                        {{ \Illuminate\Support\Str::limit($libro->titulo, 46) }}
                    </h6>

                    <div class="catalog-grid-authors">
                        @forelse($libro->autores as $autor)
                            {{ $autor->nombres }} {{ $autor->apellidos }}@if(!$loop->last), @endif
                        @empty
                            Autor no disponible
                        @endforelse
                    </div>

                    <div class="catalog-grid-rating">
                        <x-rating-stars :rating="$libro->rating_promedio" :count="$libro->comentarios_count" />
                    </div>

                    <div class="catalog-grid-meta">
                        <small>
                            <i class="bi bi-journal-text me-1"></i>
                            Disponible en catalogo
                        </small>
                        <span class="catalog-grid-button">Ver detalle</span>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="catalog-empty">
                <i class="bi bi-search fs-2 d-block mb-2"></i>
                <p class="mb-0">No hay libros disponibles con los filtros seleccionados.</p>
            </div>
        </div>
    @endforelse
</div>

@if($libros->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $libros->links('vendor.pagination.bootstrap-5') }}
</div>
@endif

