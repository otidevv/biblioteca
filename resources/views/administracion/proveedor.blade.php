@extends('layouts.admin')

@section('page-title', 'Gestion de proveedores')

@section('css')
    <link href="{{ asset('css/administracion/proveedor.css') }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/proveedor.js') }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Proveedores</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Proveedores y abastecimiento</h2>
                <p class="admin-panel__copy">Mantiene actualizada la base de proveedores para compras, adquisiciones y control documental.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevo" class="admin-btn admin-btn--primary">Agregar proveedor</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-proveedor" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
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
<div class="modal fade" id="modalProveedor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formProveedor" class="modal-content shadow-sm supplier-modal">
            <input type="hidden" id="id" name="id">
            <div class="modal-header supplier-modal__header">
                <div>
                    <span class="supplier-modal__eyebrow">
                        <i class="bi bi-truck-front-fill"></i>
                        Abastecimiento institucional
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de proveedor</h5>
                    <p class="supplier-modal__copy mb-0">Mantiene actualizada la ficha comercial y de contacto para compras, adquisiciones y seguimiento documental.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body supplier-modal__body">
                <div class="supplier-modal__intro">
                    <div class="supplier-modal__intro-icon">
                        <i class="bi bi-building-check"></i>
                    </div>
                    <div>
                        <strong>Ficha comercial</strong>
                        <p class="mb-0">Registra información clara del proveedor para identificarlo rápido, validar sus datos y facilitar futuras compras institucionales.</p>
                    </div>
                </div>

                <div class="admin-modal-section supplier-modal__section">
                    <h6 class="supplier-modal__section-title">Identificacion del proveedor</h6>
                    <p class="supplier-modal__section-copy">Completa el documento y la razon social tal como deben quedar registrados dentro del sistema.</p>
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
                            <input type="text" id="nro_documento" name="nro_documento" class="form-control validar_numero" placeholder="Numero de identificacion">
                        </div>
                        <div class="col-md-8 form-group form-required">
                            <label class="form-label">Razon social</label>
                            <input type="text" id="razon_social" name="razon_social" class="form-control" placeholder="Nombre o razon social del proveedor">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Responsable</label>
                            <input type="text" id="responsable" name="responsable" class="form-control" placeholder="Persona de contacto">
                        </div>
                    </div>
                </div>

                <div class="admin-modal-section supplier-modal__section supplier-modal__section--contact">
                    <h6 class="supplier-modal__section-title">Contacto y ubicación</h6>
                    <p class="supplier-modal__section-copy">Estos datos ayudan a coordinar compras, confirmar entregas y mantener un historial más ordenado del proveedor.</p>
                    <div class="row g-3">
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Telefono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control validar_numero" placeholder="Teléfono principal">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Web</label>
                            <input type="text" id="web" name="web" class="form-control" placeholder="Sitio web o red de referencia">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Direccion</label>
                            <input type="text" id="direccion" name="direccion" class="form-control" placeholder="Dirección comercial o fiscal">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer supplier-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
