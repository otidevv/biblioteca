@extends('layouts.admin')

@section('page-title', 'Reportes estadisticos')

@section('css')
    <link href="{{ asset('css/reportes/grafico.css') }}?v={{ time() }}" rel="stylesheet" />
@endsection

@section('content')
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
                Este espacio resume los cuadros que hoy puedes convertir en reportes operativos o exportaciones. El siguiente paso natural es
                conectar cada bloque con graficos comparativos por fecha, biblioteca, lector o coleccion.
            </p>
        </div>
    </section>
</div>
@endsection
