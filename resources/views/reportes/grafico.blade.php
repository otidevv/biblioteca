@extends('layouts.admin')

@section('page-title', 'Reportes estadisticos')

@section('css')
    <link href="{{ asset('css/reportes/grafico.css') }}?v={{ time() }}" rel="stylesheet" />
@endsection

@section('content')
@php
    $circulacion = $graficos['circulacion'];
    $inventario = $graficos['inventario'];
    $comunidad = $graficos['comunidad'];
    $tendencia = $graficos['tendencia'];
    $bibliotecas = $graficos['bibliotecas'];
@endphp
<div class="admin-section report-statistics">
    <div class="admin-breadcrumb">
        <span>Reportes</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Reportes estadisticos</span>
    </div>

    <section class="admin-panel report-statistics__hero">
        <div>
            <span class="report-statistics__eyebrow">Tablero institucional</span>
            <h2 class="admin-panel__title">Reportes estadisticos del sistema</h2>
            <p class="admin-panel__copy">
                Aqui puedes visualizar todos los cuadros de reporte que hoy se pueden construir con la informacion del sistema:
                circulacion, inventario, lectores, actividades, notificaciones y exportaciones.
            </p>
        </div>

        <div class="report-statistics__hero-actions">
            <a href="{{ route('reportes.descargas') }}" class="admin-btn admin-btn--ghost">
                <i class="bi bi-cloud-arrow-down"></i>
                Centro de descargas
            </a>
        </div>
    </section>

    <section class="report-statistics__summary">
        <article class="report-statistics__summary-card">
            <span>Libros</span>
            <strong>{{ number_format($resumen['libros']) }}</strong>
            <small>Base bibliografica registrada</small>
        </article>
        <article class="report-statistics__summary-card">
            <span>Ejemplares</span>
            <strong>{{ number_format($resumen['ejemplares']) }}</strong>
            <small>Unidades fisicas controladas</small>
        </article>
        <article class="report-statistics__summary-card">
            <span>Lectores</span>
            <strong>{{ number_format($resumen['lectores']) }}</strong>
            <small>Usuarios lectores identificados</small>
        </article>
        <article class="report-statistics__summary-card">
            <span>Prestamos activos</span>
            <strong>{{ number_format($resumen['prestamos_activos']) }}</strong>
            <small>Circulacion actualmente abierta</small>
        </article>
        <article class="report-statistics__summary-card">
            <span>Reservas pendientes</span>
            <strong>{{ number_format($resumen['reservas_pendientes']) }}</strong>
            <small>Solicitudes por atender</small>
        </article>
        <article class="report-statistics__summary-card">
            <span>Compras</span>
            <strong>{{ number_format($resumen['compras']) }}</strong>
            <small>Procesos de adquisicion registrados</small>
        </article>
        <article class="report-statistics__summary-card">
            <span>Actividades activas</span>
            <strong>{{ number_format($resumen['actividades_activas']) }}</strong>
            <small>Agenda institucional vigente</small>
        </article>
        <article class="report-statistics__summary-card">
            <span>Notificaciones vigentes</span>
            <strong>{{ number_format($resumen['notificaciones_activas']) }}</strong>
            <small>Mensajes hoy visibles para usuarios</small>
        </article>
    </section>

    <section class="report-statistics__analytics">
        <article class="admin-panel report-chart report-chart--focus">
            <div class="report-chart__header">
                <div>
                    <span class="report-chart__eyebrow">Composicion</span>
                    <h3 class="report-chart__title">Circulacion actual</h3>
                </div>
                <span class="report-chart__badge">{{ number_format($circulacion['total']) }} registros</span>
            </div>

            <div class="report-chart__body report-chart__body--split">
                <div class="report-donut">
                    <div class="report-donut__ring" style="background: {{ $circulacion['style'] }};">
                        <div class="report-donut__center">
                            <strong>{{ number_format($circulacion['total']) }}</strong>
                            <span>Total</span>
                        </div>
                    </div>
                </div>

                <div class="report-legend">
                    @forelse ($circulacion['items'] as $item)
                        <div class="report-legend__item">
                            <span class="report-legend__swatch" style="background: {{ $item['color'] }};"></span>
                            <div class="report-legend__content">
                                <strong>{{ $item['label'] }}</strong>
                                <span>{{ number_format($item['value']) }} · {{ rtrim(rtrim(number_format($item['percentage'], 1), '0'), '.') }}%</span>
                                @if ($item['descripcion'])
                                    <small>{{ $item['descripcion'] }}</small>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="report-chart__empty">Todavia no hay datos suficientes para graficar la circulacion.</p>
                    @endforelse
                </div>
            </div>
        </article>

        <article class="admin-panel report-chart">
            <div class="report-chart__header">
                <div>
                    <span class="report-chart__eyebrow">Estado</span>
                    <h3 class="report-chart__title">Inventario por disponibilidad</h3>
                </div>
            </div>

            <div class="report-bars">
                @foreach ($inventario['items'] as $item)
                    <div class="report-bars__item">
                        <div class="report-bars__topline">
                            <strong>{{ $item['label'] }}</strong>
                            <span>{{ number_format($item['value']) }}</span>
                        </div>
                        <div class="report-bars__track">
                            <div class="report-bars__fill" style="width: {{ $item['relative'] }}%; background: {{ $item['color'] }};"></div>
                        </div>
                        @if ($item['descripcion'])
                            <small>{{ $item['descripcion'] }}</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>

        <article class="admin-panel report-chart report-chart--wide">
            <div class="report-chart__header">
                <div>
                    <span class="report-chart__eyebrow">Tendencia</span>
                    <h3 class="report-chart__title">Movimiento de los ultimos 6 meses</h3>
                </div>
                <span class="report-chart__badge">Prestamos, reservas y compras</span>
            </div>

            @if ($tendencia['empty'])
                <p class="report-chart__empty">No hay movimiento registrado en los ultimos meses para construir una tendencia.</p>
            @else
                <div class="report-trend">
                    @foreach ($tendencia['labels'] as $index => $label)
                        <div class="report-trend__month">
                            <div class="report-trend__bars">
                                @foreach ($tendencia['series'] as $serie)
                                    @php
                                        $value = $serie['values'][$index];
                                        $height = $tendencia['max'] > 0 ? round(($value / $tendencia['max']) * 100, 2) : 0;
                                        $visibleHeight = $value > 0 ? max($height, 6) : 0;
                                    @endphp
                                    <div class="report-trend__bar-wrap">
                                        <span class="report-trend__value">{{ number_format($value) }}</span>
                                        <div class="report-trend__column">
                                            <div
                                                class="report-trend__bar{{ $value > 0 ? ' is-active' : '' }}"
                                                style="height: {{ $visibleHeight }}%; background: {{ $serie['color'] }};"
                                            ></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <span class="report-trend__label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="report-trend__legend">
                    @foreach ($tendencia['series'] as $serie)
                        <span class="report-trend__legend-item">
                            <i style="background: {{ $serie['color'] }};"></i>
                            {{ $serie['label'] }}
                        </span>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="admin-panel report-chart">
            <div class="report-chart__header">
                <div>
                    <span class="report-chart__eyebrow">Comunidad</span>
                    <h3 class="report-chart__title">Base activa del ecosistema</h3>
                </div>
            </div>

            <div class="report-bars">
                @foreach ($comunidad['items'] as $item)
                    <div class="report-bars__item">
                        <div class="report-bars__topline">
                            <strong>{{ $item['label'] }}</strong>
                            <span>{{ number_format($item['value']) }}</span>
                        </div>
                        <div class="report-bars__track">
                            <div class="report-bars__fill" style="width: {{ $item['relative'] }}%; background: {{ $item['color'] }};"></div>
                        </div>
                        @if ($item['descripcion'])
                            <small>{{ $item['descripcion'] }}</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>

        <article class="admin-panel report-chart">
            <div class="report-chart__header">
                <div>
                    <span class="report-chart__eyebrow">Ranking</span>
                    <h3 class="report-chart__title">Bibliotecas con mayor volumen</h3>
                </div>
            </div>

            @if ($bibliotecas['empty'])
                <p class="report-chart__empty">No hay ejemplares asociados a bibliotecas para construir este ranking.</p>
            @else
                <div class="report-ranking">
                    @foreach ($bibliotecas['items'] as $item)
                        <div class="report-ranking__item">
                            <div class="report-ranking__meta">
                                <strong>{{ $item['label'] }}</strong>
                                <span>{{ number_format($item['value']) }} ejemplares</span>
                            </div>
                            <div class="report-ranking__track">
                                <div class="report-ranking__fill" style="width: {{ $item['relative'] }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>
    </section>

    <section class="report-statistics__grid">
        @foreach ($modulos as $modulo)
            <article class="admin-panel report-statistics__module">
                <div class="report-statistics__module-top">
                    <div class="report-statistics__module-icon">
                        <i class="bi {{ $modulo['icono'] }}"></i>
                    </div>
                    <div>
                        <h3 class="report-statistics__module-title">{{ $modulo['titulo'] }}</h3>
                        <p class="report-statistics__module-copy">{{ $modulo['descripcion'] }}</p>
                    </div>
                </div>

                <div class="report-statistics__metrics">
                    @foreach ($modulo['metricas'] as $metrica)
                        <div class="report-statistics__metric">
                            <span>{{ $metrica['etiqueta'] }}</span>
                            <strong>{{ number_format($metrica['valor']) }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="report-statistics__actions">
                    @foreach ($modulo['acciones'] as $accion)
                        <a href="{{ $accion['url'] }}" class="report-statistics__action-link">
                            <i class="bi bi-arrow-up-right"></i>
                            {{ $accion['texto'] }}
                        </a>
                    @endforeach
                </div>
            </article>
        @endforeach
    </section>

    <section class="admin-panel report-statistics__footnote">
        <div>
            <h3 class="report-statistics__footnote-title">Como usar este tablero</h3>
            <p class="admin-panel__copy mb-0">
                Este tablero te ayuda a leer la operacion de la biblioteca desde varios angulos: circulacion actual, estado del inventario,
                comportamiento mensual y concentracion por biblioteca. Usa las tarjetas superiores para una lectura rapida y las graficas para
                detectar tendencias, cuellos de botella y oportunidades de gestion antes de exportar o profundizar en cada modulo.
            </p>
        </div>
    </section>
</div>
@endsection
