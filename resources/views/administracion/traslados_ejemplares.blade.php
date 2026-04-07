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

    <section class="admin-panel transfer-dashboard__hero">
        <div>
            <span class="transfer-dashboard__eyebrow">Circulacion interna</span>
            <h2 class="admin-panel__title">Bandeja de traslados entre bibliotecas</h2>
            <p class="admin-panel__copy">Esta vista muestra solo los ejemplares pendientes por aceptar y los ejemplares enviados que todavia pueden cancelarse. Puedes resolverlos uno por uno o por lote.</p>
        </div>
        <div class="transfer-dashboard__actions">
            <a href="{{ url('/administracion/libros') }}" class="admin-btn admin-btn--secondary">Volver a libros</a>
            <a href="{{ url('/administracion/traslados_ejemplares') }}" class="admin-btn admin-btn--primary">Actualizar bandeja</a>
        </div>
    </section>

    <section class="admin-panel transfer-dashboard__panel">
        <div class="admin-panel__header">
            <div>
                <h3 class="admin-card__title mb-0">Ejemplares por aceptar</h3>
                <p class="admin-panel__copy">Solo se muestran los traslados pendientes que estan esperando respuesta de tu biblioteca.</p>
            </div>
        </div>

        <div id="pendingBulkBar" class="transfer-dashboard__bulkbar" style="display:none;">
            <div><strong id="pendingBulkCount">0</strong> movimientos seleccionados</div>
            <div class="transfer-dashboard__bulkbar-actions">
                <button type="button" class="btn btn-success btn-sm" onclick="procesarTrasladosSeleccionados('pendientes', 'aceptar')">Aceptar seleccionados</button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="procesarTrasladosSeleccionados('pendientes', 'rechazar')">Rechazar seleccionados</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive transfer-dashboard__table-shell">
            <table id="tabla-traslados-pendientes" class="table table-hover table-bordered align-middle w-100">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="checkAllPendientes"></th>
                        <th>Libro</th>
                        <th>Ejemplar</th>
                        <th>Origen</th>
                        <th>Solicitado por</th>
                        <th width="160">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <section class="admin-panel transfer-dashboard__panel mt-4">
        <div class="admin-panel__header">
            <div>
                <h3 class="admin-card__title mb-0">Ejemplares movidos para cancelar</h3>
                <p class="admin-panel__copy">Aqui solo aparecen los traslados pendientes enviados desde tu biblioteca. Mientras sigan pendientes, puedes cancelarlos si es necesario.</p>
            </div>
        </div>

        <div id="sentBulkBar" class="transfer-dashboard__bulkbar" style="display:none;">
            <div><strong id="sentBulkCount">0</strong> movimientos seleccionados</div>
            <div class="transfer-dashboard__bulkbar-actions">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="procesarTrasladosSeleccionados('enviados', 'cancelar')">Cancelar seleccionados</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive transfer-dashboard__table-shell">
            <table id="tabla-traslados-enviados" class="table table-hover table-bordered align-middle w-100">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="checkAllEnviados"></th>
                        <th>Libro</th>
                        <th>Ejemplar</th>
                        <th>Destino</th>
                        <th>Solicitado por</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection
