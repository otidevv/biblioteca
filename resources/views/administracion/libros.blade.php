@extends('layouts.admin')

@section('page-title', 'Gestion de libros')

@section('css')
    <link href="{{ asset('css/administracion/libros.css') }}?v={{ filemtime(public_path('css/administracion/libros.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/libros.js') }}?v={{ filemtime(public_path('js/administracion/libros.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Libros</span>
    </div>

    <section class="admin-panel books-panel">
        <div class="admin-panel__header books-panel__header">
            <div class="books-panel__heading">
                <span class="books-panel__eyebrow">
                    <i class="bi bi-books"></i>
                    Catalogo bibliografico
                </span>
                <h2 class="admin-panel__title mt-2">Gestion de libros</h2>
                <p class="admin-panel__copy">Consulta el inventario bibliografico y entra rapido a registro, edicion y revision de ejemplares.</p>
            </div>

            <div class="books-panel__actions">
                <a href="{{ url('administracion/traslados_ejemplares') }}" class="admin-btn admin-btn--ghost" title="Gestionar traslados entre bibliotecas">
                    <i class="bi bi-arrow-left-right"></i>
                    <span class="books-panel__btn-text">Traslados</span>
                </a>
                <a href="{{ url('administracion/libros_nuevo') }}" class="admin-btn admin-btn--primary">
                    <i class="bi bi-plus-circle"></i>
                    Agregar libro
                </a>
            </div>
        </div>

        <div class="books-filters">
            <div class="books-filter-group">
                <label class="books-filter-label" for="filtro-biblioteca">
                    <i class="bi bi-building"></i> Biblioteca
                </label>
                <select id="filtro-biblioteca" class="books-filter-select form-select form-select-sm">
                    <option value="">Todas las bibliotecas</option>
                </select>
            </div>
            <div class="books-filter-group">
                <label class="books-filter-label" for="filtro-tipo">
                    <i class="bi bi-tag"></i> Tipo de registro
                </label>
                <select id="filtro-tipo" class="books-filter-select form-select form-select-sm">
                    <option value="">Todos los tipos</option>
                </select>
            </div>
            <div class="books-filter-group">
                <label class="books-filter-label" for="filtro-estado">
                    <i class="bi bi-circle-half"></i> Estado
                </label>
                <select id="filtro-estado" class="books-filter-select form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="books-filter-group books-filter-group--solo-ejemplares">
                <label class="books-filter-label" for="filtro-con-ejemplares">
                    <i class="bi bi-collection"></i> Ejemplares
                </label>
                <select id="filtro-con-ejemplares" class="books-filter-select form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="1">Con ejemplares</option>
                    <option value="0">Sin ejemplares</option>
                </select>
            </div>
            <button id="btn-limpiar-filtros" class="books-filter-clear admin-btn admin-btn--ghost" style="display:none" title="Limpiar filtros">
                <i class="bi bi-x-circle"></i>
                <span>Limpiar</span>
            </button>
        </div>

        <div class="admin-table-shell table-responsive books-table-shell">
            <table id="tabla-libros" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
                <thead>
                    <tr>
                        <th><i class="bi bi-grid-3x2-gap text-muted me-1"></i>Clasificacion</th>
                        <th style="display:none"></th>
                        <th><i class="bi bi-barcode text-muted me-1"></i>ISBN</th>
                        <th><i class="bi bi-tag text-muted me-1"></i>Tipo</th>
                        <th><i class="bi bi-book text-muted me-1"></i>Titulo</th>
                        <th><i class="bi bi-person-lines-fill text-muted me-1"></i>Autor</th>
                        <th><i class="bi bi-collection text-muted me-1"></i>Ejemplares</th>
                        <th><i class="bi bi-circle-half text-muted me-1"></i>Estado</th>
                        <th class="text-center"><i class="bi bi-sliders text-muted me-1"></i>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection
