@extends('layouts.admin')

@section('page-title', 'Gestion de autores')

@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/administracion/autor.css') }}" rel="stylesheet" />
@endsection
@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/administracion/autor.js') }}?v={{ filemtime(public_path('js/administracion/autor.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Autores</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Directorio de autores</h2>
                <p class="admin-panel__copy">Registra autores, organiza procedencia y mantiene limpio el catálogo relacional de libros.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevo" class="admin-btn admin-btn--primary">Agregar autor</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-autor" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
                <thead>
                    <tr>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Pais</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalAutor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formAutor" class="modal-content shadow-sm author-modal">
            <input type="hidden" id="id" name="id">
            <div class="modal-header author-modal__header">
                <div>
                    <span class="author-modal__eyebrow">
                        <i class="bi bi-person-lines-fill"></i>
                        Autoridad bibliografica
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de autor</h5>
                    <p class="author-modal__copy mb-0">Crea una ficha limpia del autor para mantener consistencia en el catálogo y evitar registros duplicados.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body author-modal__body">
                <div class="author-modal__intro">
                    <div class="author-modal__intro-icon">
                        <i class="bi bi-feather"></i>
                    </div>
                    <div>
                        <strong>Identidad del autor</strong>
                        <p class="mb-0">Registra nombre, apellidos y procedencia para mejorar la relacion entre autores, libros y catalogacion descriptiva.</p>
                    </div>
                </div>

                <div class="admin-modal-section author-modal__section">
                    <h6 class="author-modal__section-title">Datos del autor</h6>
                    <p class="author-modal__section-copy">Completa los nombres tal como deben visualizarse en el sistema y asigna el pais cuando sea relevante.</p>
                    <div class="row g-3">
                            <div class="col-md-6 form-group form-required">
                                <label class="form-label">Nombre</label>
                                <input type="text" id="nombre" name="nombre" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Apellidos</label>
                                <input type="text" id="apellidos" name="apellidos" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Pais</label>
                                <select id="pais" name="pais" class="form-select select2">
                                    <option value="0">Seleccione</option>
                                    @foreach($paises as $pais)
                                        <option value="{{$pais->id}}">{{$pais->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer author-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
