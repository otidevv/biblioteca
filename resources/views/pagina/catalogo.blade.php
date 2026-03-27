@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Catalogo')
@section('meta_description', 'Consulta el catalogo de libros de la Biblioteca UNAMAD, filtra por titulo, autor, idioma y materia.')

@section('js')
<script src="{{ asset('/js/pagina/catalogo.js') }}"></script>
<script>
    let libro = @json($biblioteca ?? null);
</script>
@endsection

@section('content')
<style>
.catalog-hero {
    padding: 1.8rem;
    border-radius: 1.7rem;
    background:
        linear-gradient(135deg, rgba(17, 56, 42, 0.94), rgba(46, 114, 86, 0.88)),
        url('{{ asset('img/banner.png') }}') center/cover;
    color: #fff;
    box-shadow: 0 18px 45px rgba(17, 56, 42, 0.18);
}

.catalog-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.42rem 0.8rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    color: #f5dc9a;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.catalog-hero h1 {
    margin: 0.95rem 0 0.6rem;
    font-size: clamp(1.9rem, 4vw, 2.8rem);
    font-weight: 800;
}

.catalog-hero p {
    max-width: 760px;
    margin: 0;
    color: rgba(255, 255, 255, 0.84);
}

.catalog-filter-card {
    margin-top: 1.4rem;
    padding: 1.15rem;
    border-radius: 1.5rem;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 14px 34px rgba(24, 77, 59, 0.08);
}

.catalog-filter-card label {
    display: block;
    margin-bottom: 0.45rem;
    color: #245340;
    font-size: 0.84rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.catalog-filter-card .form-control,
.catalog-filter-card .select2-container--default .select2-selection--single {
    min-height: 48px;
    border-radius: 0.95rem;
    border: 1px solid rgba(28, 82, 63, 0.12);
    box-shadow: none;
}

.catalog-filter-card .form-control {
    padding: 0.85rem 0.95rem;
}

.catalog-filter-card .select2-container--default .select2-selection--single {
    display: flex;
    align-items: center;
    padding: 0.4rem 0.7rem;
}

.catalog-filter-card .select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 1.6rem;
}

.catalog-filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 46px;
}

.catalog-filter-card .btn {
    min-height: 48px;
    border: 0;
    border-radius: 0.95rem;
    font-weight: 700;
}

.catalog-filter-card .btn-search {
    color: #14392c;
    background: linear-gradient(135deg, #f2cf82, #dcb052);
}

.catalog-filter-card .btn-clear {
    color: #fff;
    background: linear-gradient(135deg, #2d7b5e, #133a2c);
}

.catalog-results-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 1.6rem;
    margin-bottom: 1rem;
}

.catalog-results-bar h2 {
    margin: 0;
    color: #163d2f;
    font-size: 1.35rem;
    font-weight: 800;
}

.catalog-results-bar p {
    margin: 0.28rem 0 0;
    color: #6a7a74;
}

.catalog-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 0.8rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: #235842;
    font-weight: 700;
    white-space: nowrap;
}

.catalog-empty {
    padding: 2rem;
    border-radius: 1.4rem;
    text-align: center;
    background: rgba(255, 255, 255, 0.82);
    color: #6b7a74;
    box-shadow: 0 12px 30px rgba(24, 77, 59, 0.08);
}

.pagination svg {
    width: 18px !important;
    height: 18px !important;
}

.pagination li {
    display: inline-block;
}

.pagination {
    align-items: center;
    gap: 0.2rem;
}

@media (max-width: 767.98px) {
    .catalog-hero,
    .catalog-filter-card {
        padding: 1.2rem;
    }

    .catalog-results-bar {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<section class="catalog-hero">
    <span class="catalog-hero-badge">
        <i class="bi bi-collection-fill"></i>
        Catalogo institucional
    </span>

    <h1>
        Catalogo de libros
        @isset($biblioteca)
            de {{ $biblioteca->nombre }}
        @endisset
    </h1>

    <p>
        Encuentra materiales bibliograficos por titulo, autor, idioma o materia y navega por el acervo disponible
        de la Biblioteca UNAMAD.
    </p>
</section>

<section class="catalog-filter-card" aria-label="Filtros de catalogo">
    <form method="GET" action="{{ route('catalogo') }}">
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-3">
                <label for="titulo">Titulo</label>
                <input type="text"
                       id="titulo"
                       name="titulo"
                       class="form-control"
                       placeholder="Ej. Botanica amazonica"
                       value="{{ request('titulo') }}">
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <label for="autor_id">Autor</label>
                <select name="autor_id" id="autor_id" class="form-control select2" aria-label="Filtrar por autor"></select>
            </div>

            <div class="col-12 col-md-6 col-lg-2">
                <label for="idioma_id">Idioma</label>
                <select name="idioma_id" id="idioma_id" class="form-control select2" aria-label="Filtrar por idioma"></select>
            </div>

            <div class="col-12 col-md-6 col-lg-2">
                <label for="materia_id">Materia</label>
                <select name="materia_id" id="materia_id" class="form-control select2" aria-label="Filtrar por materia"></select>
            </div>

            <div class="col-12 col-lg-2">
                <label class="d-none d-lg-block">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button class="btn btn-search" type="submit">
                        <i class="bi bi-search me-1"></i>
                        Buscar
                    </button>
                    <a href="{{ route('catalogo') }}" class="btn btn-clear">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Limpiar
                    </a>
                </div>
            </div>
        </div>
    </form>
</section>

<section class="catalog-results-bar">
    <div>
        <h2>Resultados del catalogo</h2>
        <p>Explora libros disponibles y accede al detalle de cada registro.</p>
    </div>

    <span class="catalog-chip">
        <i class="bi bi-journal-text"></i>
        {{ $libros->total() }} resultados
    </span>
</section>

<section id="contenedor-libros">
    @include('pagina._libros')
</section>
@endsection
