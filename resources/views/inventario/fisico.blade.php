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
            <div>
                <span class="physical-inventory__eyebrow">Control de existencias</span>
                <h2 class="admin-panel__title">Inventario fisico de libros</h2>
                <p class="admin-panel__copy">Consulta el fondo fisico por biblioteca, revisa disponibilidad real y entra rapido al detalle de ejemplares por libro.</p>
            </div>

            <div class="physical-inventory__tools">
                @if ($puedeFiltrarBiblioteca)
                    <div class="physical-inventory__filter">
                        <label for="filtro_biblioteca" class="form-label">Filtrar por biblioteca</label>
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
                        <span>Biblioteca asignada</span>
                        <strong>{{ optional($bibliotecas->firstWhere('id', $bibliotecaFijaId))->nombre ?? 'Biblioteca seleccionada' }}</strong>
                    </div>
                @endif

                <div class="admin-actions physical-inventory__exports">
                    <a href="{{ route('reportes.descargas') }}" class="admin-btn admin-btn--ghost">
                        <i class="bi bi-folder2-open"></i>
                        Ver centro de reportes
                    </a>
                    <button type="button" id="btnSolicitarExcelFisico" class="admin-btn admin-btn--ghost">
                        <i class="bi bi-file-earmark-excel"></i>
                        Solicitar Excel
                    </button>
                    <button type="button" id="btnSolicitarPdfFisico" class="admin-btn admin-btn--primary">
                        <i class="bi bi-file-earmark-pdf"></i>
                        Solicitar PDF
                    </button>
                </div>
            </div>
        </div>

        <div class="physical-inventory__hint">
            <i class="bi bi-info-circle"></i>
            <span>Los reportes grandes se generan en segundo plano. Todas las solicitudes y descargas se revisan desde el Centro de reportes.</span>
        </div>

        <div class="admin-table-shell table-responsive physical-inventory__table-shell">
            <table id="tabla-inventario-fisico" class="table table-hover table-bordered align-middle w-100">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Codigo</th>
                        <th>Titulo</th>
                        <th>Biblioteca</th>
                        <th>Estado fisico</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection
