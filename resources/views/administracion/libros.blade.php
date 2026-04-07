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

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Catalogo bibliografico</h2>
                <p class="admin-panel__copy">Consulta el inventario bibliografico y entra rapido a registro, edicion y revision de ejemplares.</p>
            </div>

            <div class="admin-actions">
                <a href="{{ url('administracion/libros_nuevo') }}" class="admin-btn admin-btn--primary">
                    Agregar libro
                </a>
            </div>
        </div>

        <div class="admin-table-shell table-responsive books-table-shell">
            <table id="tabla-libros" class="table table-hover table-bordered align-middle datatable w-100">
                <thead>
                    <tr>
                        <th>Codigo Dewey</th>
                        <th>Codigo</th>
                        <th>ISBN</th>
                        <th>Tipo</th>
                        <th>Titulo</th>
                        <th>Autor</th>
                        <th>Ejem.</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection
