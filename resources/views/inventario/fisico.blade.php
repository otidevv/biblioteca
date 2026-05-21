@extends('layouts.admin')

@section('page-title', 'Inventario fisico')

@section('css')
    <link href="{{ asset('css/inventario/fisico.css') }}?v={{ filemtime(public_path('css/inventario/fisico.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script>
        window.inventoryPhysicalConfig = {
            fixedLibraryId: @json($bibliotecaFijaId),
            canFilterLibrary: @json($puedeFiltrarBiblioteca),
            requestUrl: @json(url('inventario/fisico/reportes/solicitar')),
            historyUrl: @json(route('reportes.descargas')),
        };
    </script>
    <script src="{{ asset('/js/inventario/fisico.js') }}?v={{ filemtime(public_path('js/inventario/fisico.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Inventario</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Inventario fisico</span>
    </div>

    <section class="admin-panel physical-inventory">
        <div class="admin-panel__header physical-inventory__header">
            <div class="physical-inventory__heading">
                <span class="physical-inventory__eyebrow">
                    <i class="bi bi-boxes"></i>
                    Control de existencias
                </span>
                <h2 class="admin-panel__title">Inventario fisico de libros</h2>
                <p class="admin-panel__copy">Consulta el fondo fisico por biblioteca, revisa disponibilidad real y entra rapido al detalle de ejemplares por libro.</p>
            </div>

            <div class="physical-inventory__tools">
                @if ($puedeFiltrarBiblioteca)
                    <div class="physical-inventory__filter">
                        <label for="filtro_biblioteca" class="form-label physical-inventory__filter-label">
                            <i class="bi bi-building"></i>
                            Filtrar por biblioteca
                        </label>
                        <select id="filtro_biblioteca" class="form-select">
                            <option value="">Todas las bibliotecas</option>
                            @foreach ($bibliotecas as $biblioteca)
                                <option value="{{ $biblioteca->id }}">{{ $biblioteca->nombre }}</option>
                            @endforeach
                            <option value="sin_biblioteca">Sin biblioteca</option>
                        </select>
                    </div>
                @elseif ($bibliotecaFijaId)
                    <div class="physical-inventory__assigned">
                        <span>
                            <i class="bi bi-building-check"></i>
                            Biblioteca asignada
                        </span>
                        <strong>{{ optional($bibliotecas->firstWhere('id', $bibliotecaFijaId))->nombre ?? 'Biblioteca seleccionada' }}</strong>
                    </div>
                @endif

                <div class="physical-inventory__exports-group">
                    <span class="physical-inventory__exports-label">Exportar</span>
                    <div class="admin-actions physical-inventory__exports">
                        <a href="{{ route('reportes.descargas') }}" class="admin-btn admin-btn--ghost" title="Ver centro de reportes">
                            <i class="bi bi-folder2-open"></i>
                            <span class="physical-inventory__btn-text">Centro de reportes</span>
                        </a>
                        <button type="button" id="btnSolicitarExcelFisico" class="admin-btn admin-btn--ghost physical-inventory__btn-excel" title="Solicitar reporte Excel">
                            <i class="bi bi-file-earmark-excel"></i>
                            <span class="physical-inventory__btn-text">Excel</span>
                        </button>
                        <button type="button" id="btnSolicitarPdfFisico" class="admin-btn admin-btn--primary physical-inventory__btn-pdf" title="Solicitar reporte PDF">
                            <i class="bi bi-file-earmark-pdf"></i>
                            <span class="physical-inventory__btn-text">PDF</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="physical-inventory__hint">
            <i class="bi bi-info-circle-fill physical-inventory__hint-icon"></i>
            <span>Los reportes grandes se generan en segundo plano. Revisa el estado de tus solicitudes en el <a href="{{ route('reportes.descargas') }}" class="physical-inventory__hint-link">Centro de reportes</a>.</span>
        </div>

        <div class="admin-table-shell table-responsive physical-inventory__table-shell">
            <table id="tabla-inventario-fisico" class="table table-hover table-bordered align-middle w-100">
                <thead>
                    <tr>
                        <th><i class="bi bi-image text-muted me-1"></i>Imagen</th>
                        <th><i class="bi bi-upc-scan text-muted me-1"></i>Codigo</th>
                        <th><i class="bi bi-book text-muted me-1"></i>Titulo</th>
                        <th><i class="bi bi-building text-muted me-1"></i>Biblioteca</th>
                        <th><i class="bi bi-layers text-muted me-1"></i>Estado fisico</th>
                        <th class="text-center"><i class="bi bi-sliders text-muted me-1"></i>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection
