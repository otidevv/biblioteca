@extends('layouts.admin')

@section('page-title', 'Historial de préstamos')

@section('css')
    <link href="{{ asset('css/prestamo/historial.css') }}?v={{ filemtime(public_path('css/prestamo/historial.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script>
        window.loanHistoryReportConfig = {
            requestUrl: @json(url('lectores/historial/reportes/solicitar')),
            historyUrl: @json(route('reportes.descargas')),
            filters: {
                q: @json(request('q')),
                estado: @json(request('estado')),
                estado_prestamo: @json(request('estado_prestamo')),
                fecha_desde: @json(request('fecha_desde')),
                fecha_hasta: @json(request('fecha_hasta')),
            }
        };
    </script>
    <script src="{{ asset('js/lectores/historial.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
@php
    $total = $historial->total();
    $activos = $historial->getCollection()->where('estado', 1)->count();
    $conTardanza = $historial->getCollection()->where('estado_prestamo', 2)->count();
    $conDeterioro = $historial->getCollection()->where('estado_prestamo', 3)->count();
@endphp

<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Lectores</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Historial de préstamos</span>
    </div>

    <div class="loan-history">
        <section class="loan-history__hero">
            <div>
                <span class="loan-history__eyebrow"><i class="bi bi-clock-history"></i> Trazabilidad de circulación</span>
                <h2>Historial de préstamos</h2>
                <p>Consulta el historial completo de préstamos, revisa cierres, devoluciones, tardanzas y deterioros para cada lector y biblioteca.</p>
            </div>
        </section>

        <section class="loan-history__stats">
            <article class="loan-history__stat">
                <span>Registros</span>
                <strong>{{ $total }}</strong>
            </article>
            <article class="loan-history__stat">
                <span>En curso</span>
                <strong>{{ $activos }}</strong>
            </article>
            <article class="loan-history__stat">
                <span>Incidencias</span>
                <strong>{{ $conTardanza + $conDeterioro }}</strong>
            </article>
        </section>

        <section class="loan-history__reports-panel">
            <div class="loan-history__reports-head">
                <div>
                    <span class="loan-history__filters-kicker">Reportes</span>
                    <h3>Solicita exportaciones del historial</h3>
                    <p>Los archivos se procesan en segundo plano y todas las descargas se concentran en un único Centro de reportes.</p>
                </div>
                <div class="loan-history__filter-actions">
                    <a href="{{ route('reportes.descargas') }}" class="loan-history__filter-btn loan-history__filter-btn--ghost">
                        <i class="bi bi-folder2-open"></i>
                        Ver centro de reportes
                    </a>
                    <button type="button" id="btnSolicitarExcelHistorial" class="loan-history__filter-btn loan-history__filter-btn--ghost">
                        <i class="bi bi-file-earmark-excel"></i>
                        Solicitar Excel
                    </button>
                    <button type="button" id="btnSolicitarPdfHistorial" class="loan-history__filter-btn loan-history__filter-btn--primary">
                        <i class="bi bi-file-earmark-pdf"></i>
                        Solicitar PDF
                    </button>
                </div>
            </div>
        </section>

        <section class="loan-history__filters">
            <form method="GET" class="loan-history__filters-form">
                <div class="loan-history__filters-head">
                    <div>
                        <span class="loan-history__filters-kicker">Búsqueda avanzada</span>
                        <h3>Filtra el historial con más precisión</h3>
                        <p>Encuentra préstamos por libro, lector, estado o rango de fechas sin perder la paginación actual.</p>
                    </div>
                    <div class="loan-history__filter-actions">
                        <button type="submit" class="loan-history__filter-btn loan-history__filter-btn--primary">
                            <i class="bi bi-search"></i>
                            Aplicar filtros
                        </button>
                        <a href="{{ url()->current() }}" class="loan-history__filter-btn loan-history__filter-btn--ghost">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Limpiar
                        </a>
                    </div>
                </div>

                <div class="loan-history__filter-search">
                    <label for="q">Búsqueda principal</label>
                    <div class="loan-history__search-box">
                        <i class="bi bi-search"></i>
                        <input
                            type="text"
                            id="q"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Buscar por libro, lector, biblioteca o ID del préstamo"
                        >
                    </div>
                </div>

                <div class="loan-history__filter-grid">
                    <div class="loan-history__filter-field">
                        <label for="estado">Estado general</label>
                        <select id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="1" @selected(request('estado') === '1')>Iniciado</option>
                            <option value="2" @selected(request('estado') === '2')>Finalizado</option>
                        </select>
                    </div>

                    <div class="loan-history__filter-field">
                        <label for="estado_prestamo">Estado del préstamo</label>
                        <select id="estado_prestamo" name="estado_prestamo">
                            <option value="">Todos</option>
                            <option value="0" @selected(request('estado_prestamo') === '0')>Prestado</option>
                            <option value="1" @selected(request('estado_prestamo') === '1')>Devuelto</option>
                            <option value="2" @selected(request('estado_prestamo') === '2')>Tardanza</option>
                            <option value="3" @selected(request('estado_prestamo') === '3')>Deterioro</option>
                        </select>
                    </div>

                    <div class="loan-history__filter-field">
                        <label for="fecha_desde">Fecha desde</label>
                        <input type="date" id="fecha_desde" name="fecha_desde" value="{{ request('fecha_desde') }}">
                    </div>

                    <div class="loan-history__filter-field">
                        <label for="fecha_hasta">Fecha hasta</label>
                        <input type="date" id="fecha_hasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    </div>
                </div>
            </form>
        </section>

        @if($historial->isEmpty())
            <section class="loan-history__empty">
                Aún no hay préstamos registrados para mostrar en este historial.
            </section>
        @else
            <section class="loan-history__list">
                @foreach($historial as $prestamo)
                    @php
                        $estadoPrestamo = (int) ($prestamo->estado_prestamo ?? 0);
                        $estadoGeneral = (int) ($prestamo->estado ?? 0);
                        $fechaInicio = $prestamo->fecha_prestamo;
                        $fechaFin = $prestamo->fecha_devolucion ?? now();
                        $codigoDeweyEjemplar = trim((string) ($prestamo->ejemplar->codigo_dewey ?? ''));
                        $codigoAntiguoEjemplar = trim((string) ($prestamo->ejemplar->codigo_ant ?? ''));
                        $codigoQntEjemplar = trim((string) ($prestamo->ejemplar->codigo_qnt ?? ''));
                        $tipoEjemplar = trim((string) ($prestamo->ejemplar->tipo ?? ''));
                        $codigoInternoEjemplar = trim((string) ($prestamo->ejemplar->codigo_interno ?? ''));
                        $codigoEjemplar = $codigoDeweyEjemplar !== ''
                            ? $codigoDeweyEjemplar . $tipoEjemplar . $codigoInternoEjemplar
                            : ($codigoAntiguoEjemplar !== ''
                                ? $codigoAntiguoEjemplar
                                : ($codigoQntEjemplar !== '' ? $codigoQntEjemplar : 'Sin código'));
                        $diasPrestado = $fechaInicio ? max(1, $fechaInicio->copy()->startOfDay()->diffInDays($fechaFin->copy()->startOfDay()) + 1) : null;
                        $badgeClass = match (true) {
                            $estadoGeneral === 1 => 'is-active',
                            $estadoPrestamo === 2 || $estadoPrestamo === 3 => 'is-late',
                            default => 'is-returned',
                        };
                        $badgeText = match (true) {
                            $estadoGeneral === 1 => 'En curso',
                            $estadoPrestamo === 1 => 'Devuelto',
                            $estadoPrestamo === 2 => 'Devuelto con tardanza',
                            $estadoPrestamo === 3 => 'Deterioro registrado',
                            default => 'Finalizado',
                        };
                        $estadoGeneralTexto = match ($estadoGeneral) {
                            1 => 'Iniciado',
                            2 => 'Finalizado',
                            default => 'Desconocido',
                        };
                        $estadoPrestamoTexto = match ($estadoPrestamo) {
                            0 => 'Prestado',
                            1 => 'Devuelto',
                            2 => 'Tardanza',
                            3 => 'Deterioro',
                            default => 'Desconocido',
                        };
                        $tipoPrestamo = (int) ($prestamo->prestamo_lugar ?? $prestamo->prestamo ?? 0);
                    @endphp

                    <article class="loan-history__card">
                        <div class="loan-history__card-head">
                            <div>
                                <div class="loan-history__book">{{ $prestamo->ejemplar->libro->titulo ?? 'Libro no disponible' }}</div>
                                <div class="loan-history__meta">
                                    {{ $prestamo->ejemplar->biblioteca->nombre ?? 'Biblioteca no disponible' }}
                                    · {{ $prestamo->lector->name ?? 'Lector no disponible' }}
                                </div>
                            </div>

                            <div class="loan-history__badges">
                                <span class="loan-history__badge {{ $badgeClass }}">
                                    <i class="bi bi-check2-circle"></i>
                                    {{ $badgeText }}
                                </span>
                                <span class="loan-history__badge is-returned">
                                    <i class="bi bi-book"></i>
                                    {{ $tipoPrestamo === 1 ? 'A casa' : 'En sala' }}
                                </span>
                            </div>
                        </div>

                        <div class="loan-history__grid">
                            <div class="loan-history__item">
                                <span>Ejemplar</span>
                                <strong>{{ $codigoEjemplar }}</strong>
                            </div>
                            <div class="loan-history__item">
                                <span>Fecha de préstamo</span>
                                <strong>{{ $prestamo->fecha_prestamo?->format('d/m/Y H:i') ?? '-' }}</strong>
                            </div>
                            <div class="loan-history__item">
                                <span>Fecha de devolución</span>
                                <strong>{{ $prestamo->fecha_devolucion?->format('d/m/Y H:i') ?? '-' }}</strong>
                            </div>
                            <div class="loan-history__item">
                                <span>Días prestado</span>
                                <strong>{{ $diasPrestado ? $diasPrestado . ' día' . ($diasPrestado === 1 ? '' : 's') : '-' }}</strong>
                            </div>
                            <div class="loan-history__item">
                                <span>Estado general</span>
                                <strong>{{ $estadoGeneralTexto }}</strong>
                            </div>
                            <div class="loan-history__item">
                                <span>Estado del préstamo</span>
                                <strong>{{ $estadoPrestamoTexto }}</strong>
                            </div>
                            <div class="loan-history__item">
                                <span>Registrado por</span>
                                <strong>{{ $prestamo->bibliotecario->name ?? 'No disponible' }}</strong>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="loan-history__pagination">
                {{ $historial->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
