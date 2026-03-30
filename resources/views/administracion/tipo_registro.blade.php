@extends('layouts.admin')

@section('page-title', 'Tipos de registro')

@section('css')
    <link href="{{ asset('css/administracion/tipo_registro.css') }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/tipo_registro.js') }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Tipos de registro</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Clasificacion de registros</h2>
                <p class="admin-panel__copy">Define los tipos bibliograficos del sistema con codigo, abreviatura y descripcion operativa.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevo" class="admin-btn admin-btn--primary">Agregar tipo de registro</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-tipo-registro" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Abreviatura</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalTipoRegistro" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formTipoRegistro" class="modal-content shadow-sm recordtype-modal">
            <input type="hidden" id="id" name="id">
            <div class="modal-header recordtype-modal__header">
                <div>
                    <span class="recordtype-modal__eyebrow">
                        <i class="bi bi-tags-fill"></i>
                        Clasificacion bibliografica
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de tipo de registro</h5>
                    <p class="recordtype-modal__copy mb-0">Define como se etiquetaran los distintos tipos de material bibliografico dentro del sistema.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body recordtype-modal__body">
                <div class="recordtype-modal__intro">
                    <div class="recordtype-modal__intro-icon">
                        <i class="bi bi-bookmarks-fill"></i>
                    </div>
                    <div>
                        <strong>Ficha de clasificacion</strong>
                        <p class="mb-0">Usa codigos claros, abreviaturas consistentes y una descripcion breve para facilitar la catalogacion del material.</p>
                    </div>
                </div>

                <div class="admin-modal-section recordtype-modal__section">
                    <h6 class="recordtype-modal__section-title">Datos del tipo de registro</h6>
                    <p class="recordtype-modal__section-copy">Estos campos se usan en formularios de libros y deben mantener un lenguaje uniforme para todo el equipo.</p>
                    <div class="row g-3">
                            <div class="col-md-6 form-group form-required">
                                <label class="form-label">Codigo</label>
                                <input type="text" id="codigo" name="codigo" class="form-control mayuscula" placeholder="Ingrese el codigo">
                            </div>
                            <div class="col-md-6 form-group form-required">
                                <label class="form-label">Abreviatura</label>
                                <input type="text" id="abreviatura" name="abreviatura" class="form-control mayuscula" placeholder="Ingrese la abreviatura">
                            </div>
                            <div class="col-md-6 form-group form-required">
                                <label class="form-label">Nombre</label>
                                <input type="text" id="nombre" name="nombre" class="form-control mayuscula" placeholder="Ingrese el nombre">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Descripcion</label>
                                <textarea id="descripcion" name="descripcion" class="form-control mayuscula" placeholder="Ingrese la descripcion"></textarea>
                            </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer recordtype-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
