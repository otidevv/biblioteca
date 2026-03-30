@extends('layouts.admin')

@section('page-title', 'Gestion de compras')

@section('css')
    <link href="{{ asset('css/inventario/compras.css') }}?v={{ filemtime(public_path('css/inventario/compras.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/inventario/compras.js') }}?v={{ filemtime(public_path('js/inventario/compras.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Inventario</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Compras</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Compras registradas</h2>
                <p class="admin-panel__copy">Consulta adquisiciones recientes, revisa montos y entra rapido al detalle de cada compra.</p>
            </div>

            <div class="admin-actions">
                <a href="{{ url('inventario/compra_nuevo') }}" id="btnNuevo" class="admin-btn admin-btn--primary">
                    <i class="bi bi-plus-circle"></i>
                    Agregar compra
                </a>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-compras" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
                <thead>
                    <tr>
                        <th>Nro SIAF</th>
                        <th>Proveedor</th>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalVerCompra">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header purchase-view-modal__header">
                <div>
                    <span class="purchase-view-modal__eyebrow">
                        <i class="bi bi-receipt-cutoff"></i>
                        Resumen de compra
                    </span>
                    <h5 class="modal-title">Detalle de compra</h5>
                    <p class="purchase-view-modal__copy mb-0">Revisa proveedor, monto total y los ejemplares generados para cada libro de la adquisicion.</p>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="admin-modal-section purchase-view-modal__summary">
                    <div class="purchase-view-modal__metrics">
                        <article class="purchase-view-modal__metric">
                            <span class="purchase-view-modal__metric-label">SIAF</span>
                            <strong id="ver_siaf">-</strong>
                        </article>
                        <article class="purchase-view-modal__metric">
                            <span class="purchase-view-modal__metric-label">Fecha</span>
                            <strong id="ver_fecha">-</strong>
                        </article>
                        <article class="purchase-view-modal__metric purchase-view-modal__metric--wide">
                            <span class="purchase-view-modal__metric-label">Proveedor</span>
                            <strong id="ver_proveedor">-</strong>
                            <small id="ver_proveedor_responsable">Sin responsable registrado</small>
                        </article>
                        <article class="purchase-view-modal__metric">
                            <span class="purchase-view-modal__metric-label">Total</span>
                            <strong id="ver_total">S/ 0.00</strong>
                        </article>
                    </div>

                    <div class="purchase-view-modal__observations" id="bloque_observaciones_compra">
                        <span class="purchase-view-modal__metric-label">Observaciones</span>
                        <p class="mb-0" id="ver_observaciones">Sin observaciones registradas.</p>
                    </div>
                </div>

                <div class="admin-table-shell table-responsive purchase-view-modal__table-shell">
                    <table class="table table-sm table-bordered purchase-view-modal__table">
                        <thead>
                            <tr>
                                <th>Libro</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th>Ejemplares</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDetalleCompra"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="admin-btn admin-btn--ghost" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection
