<span id="libros-total-meta" data-total="{{ $libros->total() }}" hidden></span>
<div class="row g-4" id="contenedorLibros">
    @forelse($libros as $libro)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            @php
                $libroShowParams = ['id' => $libro->id];
                if ($libro->biblioteca_actual ?? null) {
                    $libroShowParams['biblioteca'] = $libro->biblioteca_actual->id;
                }
            @endphp
            <a href="{{ route('libro.show', $libroShowParams) }}"
               class="catalog-grid-card"
               aria-label="Ver detalle del libro {{ $libro->titulo }}">

                <div class="catalog-grid-cover-wrap">
                    <img src="{{ $libro->imagen_url }}"
                         alt="{{ $libro->titulo }}"
                         class="catalog-grid-cover"
                         onerror="this.onerror=null;this.src='{{ asset('img/libro-placeholder.png') }}';"
                         loading="lazy"
                         decoding="async">
                    <span class="catalog-grid-tag">
                        <i class="bi bi-bookmark-star-fill"></i>
                        Catálogo
                    </span>
                </div>

                <div class="catalog-grid-body">
                    <h6 class="catalog-grid-title" title="{{ $libro->titulo }}">
                        {{ \Illuminate\Support\Str::limit($libro->titulo, 52) }}
                    </h6>

                    <div class="catalog-grid-authors">
                        <i class="bi bi-person-fill catalog-grid-authors__icon"></i>
                        <span>
                            @forelse($libro->autores as $autor)
                                {{ $autor->nombres }} {{ $autor->apellidos }}@if(!$loop->last), @endif
                            @empty
                                Autor no disponible
                            @endforelse
                        </span>
                    </div>

                    <div class="catalog-grid-codes">
                        @if($libro->codigo_ant)
                            <div class="catalog-grid-code">
                                <i class="bi bi-archive-fill"></i>
                                {{ $libro->codigo_ant }}
                            </div>
                        @endif
                        @php $codigoMostrar = $libro->codigo ?: ($libro->isbn ?: null); @endphp
                        @if($codigoMostrar)
                            <div class="catalog-grid-code catalog-grid-code--secondary">
                                <i class="bi bi-upc"></i>
                                {{ $codigoMostrar }}
                            </div>
                        @endif
                    </div>

                    @if($libro->biblioteca_actual ?? null)
                        <div class="catalog-grid-lib" title="{{ $libro->biblioteca_actual->nombre }}">
                            <i class="bi bi-building"></i>
                            {{ \Illuminate\Support\Str::limit($libro->biblioteca_actual->nombre, 32) }}
                        </div>
                    @endif

                    <div class="catalog-grid-rating">
                        <x-rating-stars :rating="$libro->rating_promedio" :count="$libro->comentarios_count" />
                    </div>

                    @php $disponible = ($libro->disponibles_biblioteca ?? 1) > 0; @endphp
                    <div class="catalog-grid-footer">
                        <span class="catalog-grid-avail{{ $disponible ? '' : ' catalog-grid-avail--off' }}">
                            <i class="bi bi-check-circle-fill"></i>
                            {{ $disponible ? 'Disponible' : 'No disponible' }}
                        </span>
                        <span class="catalog-grid-button">
                            Ver detalle <i class="bi bi-arrow-right-short"></i>
                        </span>
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

{{ $libros->links('vendor.pagination.bootstrap-5') }}
