@extends('layouts.admin')

@section('page-title', 'Gestion de editoriales')

@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/administracion/editorial.css') }}" rel="stylesheet" />
@endsection
@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/administracion/editorial.js') }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Editoriales</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Editoriales registradas</h2>
                <p class="admin-panel__copy">Gestiona editoriales, datos de contacto y procedencia para mejorar la calidad bibliografica del catalogo.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevo" class="admin-btn admin-btn--primary">Agregar editorial</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-editorial" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
                <thead>
                    <tr>
                        <th>RUC/DNI</th>
                        <th>Nombre</th>
                        <th>Telefono</th>
                        <th>Correo</th>
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
<div class="modal fade" id="modalEditorial" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formEditorial" class="modal-content shadow-sm editorial-modal">
            <input type="hidden" id="id" name="id">
            <div class="modal-header editorial-modal__header">
                <div>
                    <span class="editorial-modal__eyebrow">
                        <i class="bi bi-journal-richtext"></i>
                        Fuente bibliografica
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de editorial</h5>
                    <p class="editorial-modal__copy mb-0">Conserva una ficha clara de la editorial para mejorar la catalogacion, trazabilidad y procedencia de los libros.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body editorial-modal__body">
                <div class="editorial-modal__intro">
                    <div class="editorial-modal__intro-icon">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <div>
                        <strong>Datos de la editorial</strong>
                        <p class="mb-0">Registra identificacion, contacto y pais de origen para mantener una base editorial ordenada y util en catalogacion.</p>
                    </div>
                </div>

                <div class="admin-modal-section editorial-modal__section">
                    <h6 class="editorial-modal__section-title">Identificacion institucional</h6>
                    <p class="editorial-modal__section-copy">Usa el nombre oficial y el documento correcto para evitar duplicados o variantes de una misma editorial.</p>
                    <div class="row g-3">
                            <div class="col-md-6 form-group form-required">
                                <label class="form-label">Tipo de documento</label>
                                <select id="tipo_documento" name="tipo_documento" class="form-select validar_select">
                                    <option value="0">Seleccione</option>
                                    <option value="DNI">DNI</option>
                                    <option value="RUC">RUC</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group form-required">
                                <label class="form-label">Nro de documento</label>
                                <input type="text" id="nro_documento" name="nro_documento" class="form-control validar_numero">
                            </div>
                            <div class="col-md-6 form-group form-required">
                                <label class="form-label">Nombre</label>
                                <input type="text" id="nombre" name="nombre" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Responsable</label>
                                <input type="text" id="responsable" name="responsable" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Telefono</label>
                                <input type="text" id="telefono" name="telefono" class="form-control validar_numero">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Correo</label>
                                <input type="email" id="correo" name="correo" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">Web</label>
                                <input type="text" id="web" name="web" class="form-control">
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
                            <div class="col-md-12 form-group">
                                <label class="form-label">Direccion</label>
                                <input type="text" id="direccion" name="direccion" class="form-control">
                            </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer editorial-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
