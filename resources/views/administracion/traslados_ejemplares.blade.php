@extends('layouts.admin')

@section('page-title', 'Traslados de ejemplares')

@section('css')
    <link href="{{ asset('css/administracion/traslados_ejemplares.css') }}?v={{ filemtime(public_path('css/administracion/traslados_ejemplares.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script>
        window.transferDashboardConfig = {
            bibliotecaFijaId: @json($bibliotecaFijaId),
            puedeFiltrarBiblioteca: @json($puedeFiltrarBiblioteca),
            accesoGlobal: @json($accesoGlobalBibliotecas),
            bibliotecasUsuarioIds: @json($bibliotecasUsuarioIds),
        };
    </script>
    <script src="{{ asset('/js/administracion/traslados_ejemplares.js') }}?v={{ filemtime(public_path('js/administracion/traslados_ejemplares.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section transfer-dashboard">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Traslados de ejemplares</span>
    </div>

    {{-- Hero --}}
    <section class="admin-panel transfer-dashboard__hero">
        <div class="transfer-dashboard__hero-body">
            <span class="transfer-dashboard__eyebrow">
                <i class="bi bi-arrow-left-right"></i>
                Circulacion interna
            </span>
            <h2 class="admin-panel__title mt-2">Bandeja de traslados</h2>
            <p class="admin-panel__copy">
                Gestiona los ejemplares en movimiento entre bibliotecas.
                La pestana <strong>Por aceptar</strong> muestra lo que llega a tu biblioteca; <strong>Enviados</strong> muestra lo que tu biblioteca envio y aun puede cancelarse.
            </p>
        </div>
        <div class="transfer-dashboard__hero-aside">
            <div class="transfer-dashboard__summary">
                <div class="transfer-dashboard__summary-item transfer-dashboard__summary-item--pending">
                    <i class="bi bi-inbox"></i>
                    <div>
                        <span class="transfer-dashboard__summary-label">Por aceptar</span>
                        <strong class="transfer-dashboard__summary-count" id="hero-count-pendientes">—</strong>
                    </div>
                </div>
                <div class="transfer-dashboard__summary-item transfer-dashboard__summary-item--sent">
                    <i class="bi bi-send"></i>
                    <div>
                        <span class="transfer-dashboard__summary-label">Enviados</span>
                        <strong class="transfer-dashboard__summary-count" id="hero-count-enviados">—</strong>
                    </div>
                </div>
            </div>
            <div class="transfer-dashboard__hero-actions">
                <a href="{{ url('/administracion/libros') }}" class="admin-btn admin-btn--ghost">
                    <i class="bi bi-arrow-left-circle"></i>
                    Volver a libros
                </a>
                <a href="{{ url('/administracion/traslados_ejemplares') }}" class="admin-btn admin-btn--primary">
                    <i class="bi bi-arrow-clockwise"></i>
                    Actualizar
                </a>
            </div>
        </div>
    </section>

    {{-- Panel con tabs --}}
    <section class="admin-panel transfer-dashboard__panel">

        {{-- Nav tabs --}}
        <div class="transfer-dashboard__tabs-wrap">
            <ul class="nav transfer-dashboard__tabs" id="transferTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="transfer-dashboard__tab active" id="tab-pendientes-btn"
                        data-bs-toggle="tab" data-bs-target="#tab-pendientes"
                        type="button" role="tab" aria-selected="true">
                        <i class="bi bi-inbox"></i>
                        <span>Por aceptar</span>
                        <span class="transfer-dashboard__tab-badge transfer-dashboard__tab-badge--pending" id="badge-pendientes" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="transfer-dashboard__tab" id="tab-enviados-btn"
                        data-bs-toggle="tab" data-bs-target="#tab-enviados"
                        type="button" role="tab" aria-selected="false">
                        <i class="bi bi-send"></i>
                        <span>Enviados</span>
                        <span class="transfer-dashboard__tab-badge transfer-dashboard__tab-badge--sent" id="badge-enviados" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content transfer-dashboard__tab-content">

            {{-- Tab 1: Por aceptar --}}
            <div class="tab-pane fade show active" id="tab-pendientes" role="tabpanel">
                <div class="transfer-dashboard__tab-header">
                    <div>
                        <h3 class="admin-card__title mb-0">Ejemplares que llegan a tu biblioteca</h3>
                        <p class="admin-panel__copy">Estos traslados estan esperando que los aceptes o rechaces. Solo ves los que tienen tu biblioteca como destino.</p>
                    </div>
                </div>

                <div id="pendingBulkBar" class="transfer-dashboard__bulkbar transfer-dashboard__bulkbar--pending" style="display:none;">
                    <div class="transfer-dashboard__bulkbar-summary">
                        <i class="bi bi-check2-square transfer-dashboard__bulk-icon transfer-dashboard__bulk-icon--pending"></i>
                        <span><strong id="pendingBulkCount">0</strong> seleccionados</span>
                    </div>
                    <div class="transfer-dashboard__bulkbar-actions">
                        <button type="button" class="admin-btn admin-btn--success" onclick="procesarTrasladosSeleccionados('pendientes', 'aceptar')">
                            <i class="bi bi-check-circle"></i>
                            Aceptar seleccionados
                        </button>
                        <button type="button" class="admin-btn admin-btn--danger" onclick="procesarTrasladosSeleccionados('pendientes', 'rechazar')">
                            <i class="bi bi-x-circle"></i>
                            Rechazar seleccionados
                        </button>
                    </div>
                </div>

                <div class="admin-table-shell table-responsive transfer-dashboard__table-shell">
                    <table id="tabla-traslados-pendientes" class="table table-hover table-bordered align-middle w-100">
                        <thead>
                            <tr>
                                <th width="40" title="Seleccionar todos"><input type="checkbox" id="checkAllPendientes" title="Seleccionar todos"></th>
                                <th><i class="bi bi-book text-muted me-1"></i>Libro</th>
                                <th><i class="bi bi-bookmark text-muted me-1"></i>Ejemplar</th>
                                <th><i class="bi bi-box-arrow-right text-muted me-1"></i>Origen</th>
                                <th><i class="bi bi-person text-muted me-1"></i>Solicitado por</th>
                                <th width="160" class="text-center"><i class="bi bi-sliders text-muted me-1"></i>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            {{-- Tab 2: Enviados --}}
            <div class="tab-pane fade" id="tab-enviados" role="tabpanel">
                <div class="transfer-dashboard__tab-header">
                    <div>
                        <h3 class="admin-card__title mb-0">Ejemplares enviados desde tu biblioteca</h3>
                        <p class="admin-panel__copy">Estos traslados fueron iniciados por tu biblioteca y siguen pendientes. Puedes cancelarlos mientras el destino no los haya aceptado.</p>
                    </div>
                </div>

                <div id="sentBulkBar" class="transfer-dashboard__bulkbar transfer-dashboard__bulkbar--sent" style="display:none;">
                    <div class="transfer-dashboard__bulkbar-summary">
                        <i class="bi bi-check2-square transfer-dashboard__bulk-icon transfer-dashboard__bulk-icon--sent"></i>
                        <span><strong id="sentBulkCount">0</strong> seleccionados</span>
                    </div>
                    <div class="transfer-dashboard__bulkbar-actions">
                        <button type="button" class="admin-btn admin-btn--danger" onclick="procesarTrasladosSeleccionados('enviados', 'cancelar')">
                            <i class="bi bi-x-circle"></i>
                            Cancelar seleccionados
                        </button>
                    </div>
                </div>

                <div class="admin-table-shell table-responsive transfer-dashboard__table-shell">
                    <table id="tabla-traslados-enviados" class="table table-hover table-bordered align-middle w-100">
                        <thead>
                            <tr>
                                <th width="40" title="Seleccionar todos"><input type="checkbox" id="checkAllEnviados" title="Seleccionar todos"></th>
                                <th><i class="bi bi-book text-muted me-1"></i>Libro</th>
                                <th><i class="bi bi-bookmark text-muted me-1"></i>Ejemplar</th>
                                <th><i class="bi bi-box-arrow-in-right text-muted me-1"></i>Destino</th>
                                <th><i class="bi bi-person text-muted me-1"></i>Solicitado por</th>
                                <th width="120" class="text-center"><i class="bi bi-sliders text-muted me-1"></i>Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
