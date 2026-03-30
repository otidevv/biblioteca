@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Catalogo')
@section('meta_description', 'Consulta el catalogo de libros de la Biblioteca UNAMAD, filtra por titulo, autor, idioma y materia.')

@section('css')
<link href="{{ asset('css/pagina/catalogo.css') }}" rel="stylesheet">
<link href="{{ asset('css/pagina/libros-grid.css') }}" rel="stylesheet">
@endsection

@section('js')
<script src="{{ asset('js/pagina/catalogo.js') }}"></script>
@endsection



@section('content')


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
    <form method="GET" action="{{ route('catalogo') }}" id="catalogoFiltrosForm">
        <div class="catalog-filter-header">
            <div>
                <span class="catalog-filter-kicker">Busqueda avanzada</span>
                <h2 class="catalog-filter-title">Busqueda avanzada</h2>
                <p class="catalog-filter-subtitle">
                    Combina titulo, autor, idioma y materia para afinar el resultado del catalogo segun lo que necesitas encontrar.
                </p>
            </div>
            <div class="catalog-filter-actions">
                <button class="btn btn-search" type="submit">
                    <i class="bi bi-search me-1"></i>
                    Aplicar filtros
                </button>
                <a href="{{ route('catalogo') }}" class="btn btn-clear">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                    Limpiar
                </a>
            </div>
        </div>

        <div class="catalog-filter-grid">
            <div class="catalog-filter-primary">
                <label for="titulo">Busqueda principal</label>
                <div class="catalog-filter-input-shell">
                    <i class="bi bi-search catalog-filter-input-icon"></i>
                    <input type="text"
                           id="titulo"
                           name="titulo"
                           class="form-control"
                           placeholder="Buscar por titulo del libro"
                           value="{{ request('titulo') }}">
                </div>
            </div>

            <div class="catalog-filter-secondary">
                <div class="catalog-filter-box">
                    <label for="autor_id">Autor</label>
                    <select name="autor_id" id="autor_id" class="form-control select2" aria-label="Filtrar por autor"></select>
                </div>

                <div class="catalog-filter-box">
                    <label for="idioma_id">Idioma</label>
                    <select name="idioma_id" id="idioma_id" class="form-control select2" aria-label="Filtrar por idioma"></select>
                </div>

                <div class="catalog-filter-box">
                    <label for="materia_id">Materia</label>
                    <select name="materia_id" id="materia_id" class="form-control select2" aria-label="Filtrar por materia"></select>
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


