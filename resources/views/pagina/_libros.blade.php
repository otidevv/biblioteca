<style>
.catalog-grid-card {
    display: block;
    height: 100%;
    overflow: hidden;
    border-radius: 1.4rem;
    background: rgba(255, 255, 255, 0.88);
    color: inherit;
    text-decoration: none;
    box-shadow: 0 16px 36px rgba(24, 77, 59, 0.08);
    transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.catalog-grid-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 24px 48px rgba(24, 77, 59, 0.15);
}

.catalog-grid-card:focus-visible {
    outline: 3px solid rgba(43, 122, 93, 0.4);
    outline-offset: 4px;
}

.catalog-grid-cover-wrap {
    padding: 1rem 1rem 0;
}

.catalog-grid-cover {
    width: 100%;
    height: 300px;
    object-fit: contain;
    border-radius: 1rem;
    background: linear-gradient(180deg, #fbfaf5, #eef2ec);
    padding: 0.85rem;
}

.catalog-grid-body {
    display: flex;
    flex-direction: column;
    height: calc(100% - 316px);
    padding: 1rem 1rem 1.1rem;
}

.catalog-grid-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    align-self: flex-start;
    padding: 0.32rem 0.6rem;
    margin-bottom: 0.7rem;
    border-radius: 999px;
    background: rgba(216, 177, 92, 0.16);
    color: #946418;
    font-size: 0.77rem;
    font-weight: 700;
}

.catalog-grid-title {
    margin-bottom: 0.45rem;
    color: #173d2f;
    font-size: 1rem;
    font-weight: 800;
}

.catalog-grid-authors {
    min-height: 2.8rem;
    color: #6c7d76;
    font-size: 0.9rem;
}

.catalog-grid-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-top: auto;
    padding-top: 0.9rem;
}

.catalog-grid-meta small {
    color: #688078;
}

.catalog-grid-button {
    padding: 0.55rem 0.85rem;
    border-radius: 0.9rem;
    color: #fff;
    background: linear-gradient(135deg, #2b7a5d, #11392c);
    font-size: 0.88rem;
    font-weight: 700;
}
</style>

<div class="row g-4" id="contenedorLibros">
    @forelse($libros as $libro)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <a href="{{ route('libro.show', $libro->id) }}"
               class="catalog-grid-card"
               aria-label="Ver detalle del libro {{ $libro->titulo }}">
                <div class="catalog-grid-cover-wrap">
                    <img src="{{ $libro->imagen ?: asset('img/banner1.png') }}"
                         alt="{{ $libro->titulo }}"
                         class="catalog-grid-cover"
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
