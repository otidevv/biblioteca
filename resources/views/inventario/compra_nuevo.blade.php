@extends('layouts.admin')

@section('page-title', 'Nueva compra')

@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/inventario/compra_nuevo.css') }}?v={{ filemtime(public_path('css/inventario/compra_nuevo.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/inventario/compra_nueva.js') }}?v={{ filemtime(public_path('js/inventario/compra_nueva.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section purchase-create">
    <div class="admin-breadcrumb">
        <span>Inventario</span>
        <span>/</span>
        <a href="{{ url('inventario/compras') }}" class="admin-breadcrumb__current">Compras</a>
        <span>/</span>
        <span class="admin-breadcrumb__current">Nueva compra</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Registrar compra</h2>
                <p class="admin-panel__copy">Registra una adquisicion, agrega libros al detalle y genera sus ejemplares desde una sola pantalla.</p>
            </div>
            <div class="admin-actions">
                <a href="{{ url('inventario/compras') }}" class="admin-btn admin-btn--ghost">
                    <i class="bi bi-arrow-left"></i>
                    Volver a compras
                </a>
            </div>
        </div>

        <form id="formCompra" class="purchase-create__form">
            @csrf

            <div class="admin-modal-section purchase-create__section">
                <h6 class="purchase-create__section-title">Datos generales</h6>
                <div class="row g-3">
                    <div class="col-md-3 form-group form-required">
                        <label class="form-label">Numero SIAF</label>
                        <input type="text" id="numero_siaf" name="numero_siaf" class="form-control" placeholder="Ej. 2026-00452">
                    </div>
                    <div class="col-md-3 form-group form-required">
                        <label class="form-label">Fecha de compra</label>
                        <input type="date" id="fecha_compra" name="fecha_compra" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6 form-group form-required">
                        <label class="form-label">Proveedor</label>
                        <select id="proveedor_id" name="proveedor_id" class="form-select select2">
                            <option value="">Seleccione un proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}">
                                    {{ $proveedor->razon_social . ($proveedor->responsable ? ' - ' . $proveedor->responsable : '') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="form-label">Monto total</label>
                        <input type="number" step="0.01" id="monto_total" name="monto_total" class="form-control" readonly value="0.00">
                    </div>
                    <div class="col-md-8 form-group">
                        <label class="form-label">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="2" class="form-control" placeholder="Notas adicionales de la compra"></textarea>
                    </div>
                </div>
            </div>

            <div class="admin-modal-section purchase-create__section">
                <div class="purchase-create__detail-header">
                    <div>
                        <h6 class="purchase-create__section-title mb-1">Detalle de libros</h6>
                        <p class="purchase-create__detail-copy mb-0">Agrega cada libro con cantidad y precio unitario. El sistema calculara el total y generara los ejemplares.</p>
                    </div>
                    <button type="button" id="btnNuevoLibro" class="admin-btn admin-btn--primary">
                        <i class="bi bi-plus-circle"></i>
                        Agregar libro
                    </button>
                </div>

                <div class="admin-table-shell table-responsive purchase-create__table-shell">
                    <table class="table table-hover table-bordered align-middle mb-0" id="tablaDetalles">
                        <thead>
                            <tr>
                                <th>Titulo</th>
                                <th>Autor</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th class="text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="purchase-create__empty-row">
                                <td colspan="6" class="text-center text-muted py-4">Todavia no has agregado libros a esta compra.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="purchase-create__footer">
                <a href="{{ url('inventario/compras') }}" class="admin-btn admin-btn--ghost">Cancelar</a>
                <button type="submit" id="btnGuardarCompra" class="admin-btn admin-btn--primary">
                    <i class="bi bi-save2"></i>
                    Guardar compra
                </button>
            </div>
        </form>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalLibro" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form id="formLibro" class="modal-content shadow-sm purchase-book-modal">
            <div class="modal-header purchase-book-modal__header">
                <div>
                    <span class="purchase-book-modal__eyebrow">
                        <i class="bi bi-journal-plus"></i>
                        Detalle de compra
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Agregar libro a la compra</h5>
                    <p class="purchase-book-modal__copy mb-0">Selecciona un libro existente, revisa sus datos y define cantidad y precio unitario.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body purchase-book-modal__body">
                <div class="purchase-book-modal__intro">
                    <div class="purchase-book-modal__intro-icon">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <div>
                        <strong>Vista rapida del registro</strong>
                        <p class="mb-0">El detalle usa libros ya registrados. Si el libro aun no existe, primero debes registrarlo desde el catalogo.</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="admin-modal-section purchase-book-modal__section">
                            <h6 class="purchase-book-modal__section-title">Detalle de compra</h6>
                            <div class="row g-3">
                                <div class="col-12 form-group form-required">
                                    <label class="form-label">Libro</label>
                                    <select id="libros" class="form-select"></select>
                                </div>
                                <div class="col-md-6 form-group form-required">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" id="modal_cantidad" value="1" min="1" class="form-control">
                                </div>
                                <div class="col-md-6 form-group form-required">
                                    <label class="form-label">Precio unitario</label>
                                    <input type="number" step="0.01" id="modal_precio" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="admin-modal-section purchase-book-modal__section purchase-book-modal__preview">
                            <h6 class="purchase-book-modal__section-title">Informacion del libro</h6>
                            <div class="purchase-book-modal__preview-grid">
                                <div class="purchase-book-modal__cover">
                                    <img id="preview_imagen" class="purchase-book-modal__image" alt="Portada del libro">
                                    <div id="preview_empty" class="purchase-book-modal__image-empty">
                                        <i class="bi bi-image"></i>
                                        <span>Sin portada disponible</span>
                                    </div>
                                </div>
                                <div class="purchase-book-modal__meta">
                                    <div class="purchase-book-modal__field">
                                        <label>Titulo</label>
                                        <div id="lbl_titulo" class="form-control bg-light"></div>
                                    </div>
                                    <div class="purchase-book-modal__field">
                                        <label>Autor</label>
                                        <div id="lbl_autor" class="form-control bg-light"></div>
                                    </div>
                                    <div class="purchase-book-modal__field">
                                        <label>Editorial</label>
                                        <div id="lbl_editorial" class="form-control bg-light"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer purchase-book-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success px-4" type="submit">Agregar al detalle</button>
            </div>
        </form>
    </div>
</div>
@endsection
