@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Catálogo')
@section('meta_description', 'Consulta el catálogo de libros de la Biblioteca UNAMAD, filtra por título, autor, idioma y materia.')

@section('css')
<link href="{{ asset('css/pagina/catalogo.css') }}?v={{ filemtime(public_path('css/pagina/catalogo.css')) }}" rel="stylesheet">
<link href="{{ asset('css/pagina/libros-grid.css') }}?v={{ filemtime(public_path('css/pagina/libros-grid.css')) }}" rel="stylesheet">
@endsection

@section('js')
<script src="{{ asset('js/pagina/catalogo.js') }}?v={{ filemtime(public_path('js/pagina/catalogo.js')) }}"></script>
@endsection



@section('content')


<section class="catalog-hero">
    <span class="catalog-hero-badge">
        <i class="bi bi-books"></i>
        Catalogo institucional
    </span>

    <h1>
        Catalogo de libros
        @isset($biblioteca)
            de {{ $biblioteca->nombre }}
        @endisset
    </h1>

    <p>
        Encuentra materiales bibliográficos por título, autor, idioma o materia y navega por el acervo disponible
        de la Biblioteca UNAMAD.
    </p>
</section>

<section class="catalog-filter-card" aria-label="Filtros de catálogo">
    <form method="GET" action="{{ route('catalogo') }}" id="catalogoFiltrosForm">
        <div class="catalog-filter-header">
            <div>
                <span class="catalog-filter-kicker">
                    <i class="bi bi-funnel-fill"></i>
                    Filtrar catálogo
                </span>
                <h2 class="catalog-filter-title">Encuentra el libro que necesitas</h2>
                <p class="catalog-filter-subtitle">
                    Busca por título y afina el resultado con autor, idioma o materia.
                </p>
            </div>
        </div>

        <div class="catalog-tab-nav">
            <button type="button" class="catalog-tab-btn active" data-tab="busqueda">
                <i class="bi bi-search"></i> Búsqueda
            </button>
            <button type="button" class="catalog-tab-btn" data-tab="autores">
                <i class="bi bi-sort-alpha-down"></i> Explorar autores
            </button>
        </div>

        {{-- Tab 1: Búsqueda general --}}
        <div class="catalog-tab-panel active" id="tab-busqueda">
            <div class="catalog-filter-grid">
                <div class="catalog-filter-primary">
                    <label for="titulo">Título o palabra clave</label>
                    <div class="catalog-filter-input-shell">
                        <i class="bi bi-search catalog-filter-input-icon" id="catalog-search-icon"></i>
                        <input type="text"
                               id="titulo"
                               name="titulo"
                               class="form-control"
                               placeholder="Escribe el título del libro o una palabra relacionada"
                               value="{{ request('titulo') }}">
                    </div>
                </div>

                <div class="catalog-filter-refine">
                    <div class="catalog-filter-section-label">
                        <span>Refinar resultados</span>
                    </div>

                    <div class="catalog-filter-secondary">
                        <div class="catalog-filter-box">
                            <label for="autor_id">Autor</label>
                            <select name="autor_id" id="autor_id" class="form-control select2" aria-label="Filtrar por autor">
                                @if($autorSeleccionado ?? null)
                                    <option value="{{ $autorSeleccionado->id }}" selected>
                                        {{ trim($autorSeleccionado->nombres . ' ' . $autorSeleccionado->apellidos) }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <div class="catalog-filter-box">
                            <label for="idioma_id">Idioma</label>
                            <select name="idioma_id" id="idioma_id" class="form-control select2" aria-label="Filtrar por idioma">
                                @if($idiomaSeleccionado ?? null)
                                    <option value="{{ $idiomaSeleccionado->id }}" selected>{{ $idiomaSeleccionado->nombre }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="catalog-filter-box">
                            <label for="materia_id">Materia</label>
                            <select name="materia_id" id="materia_id" class="form-control select2" aria-label="Filtrar por materia">
                                @if($materiaSeleccionada ?? null)
                                    <option value="{{ $materiaSeleccionada->id }}" selected>{{ $materiaSeleccionada->nombre }}</option>
                                @endif
                            </select>
                        </div>

                        <div class="catalog-filter-box">
                            <label for="codigo_ant">Código interno</label>
                            <input type="text"
                                   id="codigo_ant"
                                   name="codigo_ant"
                                   class="form-control"
                                   placeholder="Ej: 001, A-23..."
                                   value="{{ request('codigo_ant') }}">
                        </div>

                        <div class="catalog-filter-actions">
                            <button class="btn btn-search" type="submit">
                                <i class="bi bi-search me-1"></i>
                                Aplicar
                            </button>
                            <a href="{{ route('catalogo') }}" class="btn btn-clear">
                                <i class="bi bi-arrow-clockwise"></i>
                                Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 2: Explorar autores A-Z --}}
        <div class="catalog-tab-panel" id="tab-autores">
            <div class="catalog-autor-explorer">
                <p class="autor-explorer-hint">
                    <i class="bi bi-info-circle"></i>
                    Selecciona una letra para explorar los autores disponibles. Al elegir uno se aplicará como filtro en el catálogo.
                </p>

                <div class="autor-alpha-picker">
                    <span class="autor-alpha-label">
                        <i class="bi bi-sort-alpha-down"></i> Selecciona una letra
                    </span>
                    <div class="autor-alpha-btns" id="autorAlphaBtns">
                        @foreach(range('A', 'Z') as $l)
                            <button type="button" class="autor-alpha-btn" data-letra="{{ $l }}">{{ $l }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="autor-alpha-results" id="autorAlphaResults"></div>

                <div class="autor-active-badge" id="autorActiveBadge">
                    <i class="bi bi-person-fill"></i>
                    Filtrando por: <strong id="autorActiveName"></strong>
                    <button type="button" id="autorActiveClear" title="Quitar filtro de autor">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<section class="catalog-results-bar">
    <div>
        <h2>Resultados del catálogo</h2>
        <p>Explora libros disponibles y accede al detalle de cada registro.</p>
    </div>

    <div class="catalog-results-bar__actions">
        <span class="catalog-chip" id="catalogResultsChip">
            <i class="bi bi-file-text-fill"></i>
            <span id="catalogResultsCount">{{ $libros->total() }}</span><span id="catalogResultsSuffix"> resultado{{ $libros->total() !== 1 ? 's' : '' }}</span>
        </span>

        <div class="catalog-perpage">
            <label for="perPage" class="catalog-perpage__label">Mostrar</label>
            <select id="perPage" class="catalog-perpage__select">
                @foreach([8, 16, 24, 32] as $n)
                    <option value="{{ $n }}" {{ (int) request('per_page', 8) === $n ? 'selected' : '' }}>
                        {{ $n }}
                    </option>
                @endforeach
            </select>
            <span class="catalog-perpage__suffix">por página</span>
        </div>
    </div>
</section>

<section id="contenedor-libros">
    @include('pagina._libros')
</section>
@endsection
