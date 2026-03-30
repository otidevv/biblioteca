@extends('layouts.admin')

@section('page-title', 'Centro de reportes')

@section('css')
    <link href="{{ asset('css/reportes/index.css') }}?v={{ time() }}" rel="stylesheet" />
@endsection

@section('content')
@php
    $etiquetasModulo = [
        'inventario_fisico' => 'Inventario fisico',
        'lectores_historial_prestamos' => 'Historial de prestamos',
    ];
@endphp
<div class="admin-section report-center">
    <div class="admin-breadcrumb">
        <span>Reportes</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Centro de descargas</span>
    </div>

    <section class="admin-panel report-center__hero">
        <div>
            <span class="report-center__eyebrow">Archivos generados</span>
            <h2 class="admin-panel__title">Centro de reportes</h2>
            <p class="admin-panel__copy">Aqui se concentran todas tus solicitudes de exportacion. Puedes revisar el estado, el modulo de origen y descargar los archivos cuando esten listos.</p>
        </div>
        <div class="report-center__actions">
            <a href="{{ route('reportes.grafico') }}" class="admin-btn admin-btn--ghost">
                <i class="bi bi-bar-chart-line"></i>
                Ver reportes estadisticos
            </a>
        </div>
    </section>

    @if (session('status'))
        <section class="report-center__flash">
            <i class="bi bi-check2-circle"></i>
            <span>{{ session('status') }}</span>
        </section>
    @endif

    <section class="report-center__stats">
        <article class="report-center__stat">
            <span>Total</span>
            <strong>{{ $resumen['total'] }}</strong>
        </article>
        <article class="report-center__stat">
            <span>Pendientes</span>
            <strong>{{ $resumen['pendientes'] }}</strong>
        </article>
        <article class="report-center__stat">
            <span>Completados</span>
            <strong>{{ $resumen['completados'] }}</strong>
        </article>
        <article class="report-center__stat">
            <span>Fallidos</span>
            <strong>{{ $resumen['fallidos'] }}</strong>
        </article>
    </section>

    <section class="admin-panel report-center__filters">
        <form method="GET" class="report-center__filters-form">
            <div class="report-center__filter-field">
                <label for="modulo">Modulo</label>
                <select id="modulo" name="modulo">
                    <option value="">Todos</option>
                    @foreach ($modulosDisponibles as $modulo)
                        <option value="{{ $modulo }}" @selected(request('modulo') === $modulo)>
                            {{ $etiquetasModulo[$modulo] ?? Str::headline(str_replace('_', ' ', $modulo)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="report-center__filter-field">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendiente</option>
                    <option value="procesando" @selected(request('estado') === 'procesando')>Procesando</option>
                    <option value="completado" @selected(request('estado') === 'completado')>Completado</option>
                    <option value="fallido" @selected(request('estado') === 'fallido')>Fallido</option>
                </select>
            </div>
            <div class="report-center__actions">
                <button type="submit" class="admin-btn admin-btn--primary">
                    <i class="bi bi-search"></i>
                    Filtrar
                </button>
                <a href="{{ route('reportes.descargas') }}" class="admin-btn admin-btn--ghost">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    <section class="admin-panel report-center__table-panel">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle report-center__table">
                <thead>
                    <tr>
                        <th>Modulo</th>
                        <th>Formato</th>
                        <th>Filtros</th>
                        <th>Estado</th>
                        <th>Registros</th>
                        <th>Solicitado</th>
                        <th>Procesado</th>
                        <th>Archivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportes as $reporte)
                        <tr>
                            <td>{{ $etiquetasModulo[$reporte->modulo] ?? Str::headline(str_replace('_', ' ', $reporte->modulo)) }}</td>
                            <td>{{ strtoupper($reporte->formato) }}</td>
                            <td>
                                {{ collect($reporte->filtros ?? [])->filter(fn ($valor) => $valor !== null && $valor !== '')->isNotEmpty() ? collect($reporte->filtros)->filter(fn ($valor) => $valor !== null && $valor !== '')->map(fn ($valor, $clave) => $clave . ': ' . $valor)->implode(' | ') : 'Sin filtros adicionales' }}
                            </td>
                            <td>
                                <span class="report-center__state report-center__state--{{ $reporte->estado }}">{{ ucfirst($reporte->estado) }}</span>
                                @if ($reporte->error)
                                    <div class="small text-muted mt-1">{{ $reporte->error }}</div>
                                @endif
                            </td>
                            <td>{{ $reporte->total_registros ?? '-' }}</td>
                            <td>{{ optional($reporte->solicitado_en)->format('d/m/Y H:i') ?: '-' }}</td>
                            <td>{{ optional($reporte->procesado_en)->format('d/m/Y H:i') ?: '-' }}</td>
                            <td>
                                @if ($reporte->estado === 'completado')
                                    <a href="{{ route('reportes.descargar', $reporte) }}" class="report-center__download">
                                        <i class="bi bi-download"></i>
                                        Descargar
                                    </a>
                                @elseif ($reporte->estado === 'fallido')
                                    <form method="POST" action="{{ route('reportes.reintentar', $reporte) }}" class="report-center__retry-form">
                                        @csrf
                                        <button type="submit" class="report-center__retry">
                                            <i class="bi bi-arrow-repeat"></i>
                                            Reintentar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">No disponible</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Todavia no tienes reportes solicitados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-center__pagination">
            {{ $reportes->links() }}
        </div>
    </section>
</div>
@endsection
