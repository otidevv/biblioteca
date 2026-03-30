@extends('layouts.admin')

@section('page-title', 'Penalizaciones')

@section('css')
    <link href="{{ asset('css/lectores/penalizaciones.css') }}?v={{ filemtime(public_path('css/lectores/penalizaciones.css')) }}" rel="stylesheet" />
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Lectores</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Penalizaciones</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Sanciones registradas</h2>
                <p class="admin-panel__copy">Consulta sanciones activas y cerradas por lector, tipo, libro relacionado y periodo de aplicacion.</p>
            </div>
        </div>

        <section class="penalty-overview">
            <article class="penalty-stat">
                <span class="penalty-stat__label">Total registradas</span>
                <strong class="penalty-stat__value">{{ number_format($resumen['total']) }}</strong>
            </article>
            <article class="penalty-stat">
                <span class="penalty-stat__label">Activas</span>
                <strong class="penalty-stat__value">{{ number_format($resumen['activas']) }}</strong>
            </article>
            <article class="penalty-stat">
                <span class="penalty-stat__label">Cerradas</span>
                <strong class="penalty-stat__value">{{ number_format($resumen['cerradas']) }}</strong>
            </article>
        </section>

        <section class="admin-modal-section penalty-filters">
            <form method="GET" class="penalty-filters__grid">
                <div class="penalty-field penalty-field--wide">
                    <label for="q">Busqueda</label>
                    <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Lector, libro, motivo, codigo de pago o ID">
                </div>
                <div class="penalty-field">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="1" @selected(request('estado') === '1')>Activa</option>
                        <option value="2" @selected(request('estado') === '2')>Cerrada</option>
                    </select>
                </div>
                <div class="penalty-field">
                    <label for="tipo">Tipo</label>
                    <input type="text" id="tipo" name="tipo" value="{{ request('tipo') }}" placeholder="Tardanza, deterioro, reserva...">
                </div>
                <div class="penalty-field">
                    <label for="fecha_desde">Fecha desde</label>
                    <input type="date" id="fecha_desde" name="fecha_desde" value="{{ request('fecha_desde') }}">
                </div>
                <div class="penalty-field">
                    <label for="fecha_hasta">Fecha hasta</label>
                    <input type="date" id="fecha_hasta" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
                </div>
                <div class="penalty-actions">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ url('/lectores/penalizaciones') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </section>

        @if($penalizaciones->isEmpty())
            <section class="penalty-empty">
                No se encontraron sanciones con los filtros aplicados.
            </section>
        @else
            <section class="penalty-list">
                @foreach($penalizaciones as $sancion)
                    @php
                        $esActiva = (int) ($sancion->estado ?? 0) === 1;
                        $estadoTexto = $esActiva ? 'Activa' : 'Cerrada';
                        $estadoClase = $esActiva ? 'is-active' : 'is-closed';
                        $referenciaPrestamo = $sancion->prestamo;
                        $referenciaReserva = $sancion->reservacion;
                        $libroRelacionado = $referenciaPrestamo?->ejemplar?->libro?->titulo
                            ?? $referenciaReserva?->ejemplar?->libro?->titulo
                            ?? 'Sin libro relacionado';
                        $bibliotecaRelacionada = $referenciaPrestamo?->ejemplar?->biblioteca?->nombre
                            ?? $referenciaReserva?->ejemplar?->biblioteca?->nombre
                            ?? 'Biblioteca no disponible';
                        $origen = $referenciaPrestamo ? 'Prestamo' : ($referenciaReserva ? 'Reservacion' : 'Registro manual');
                        $duracionTexto = $sancion->duracion ? $sancion->duracion . ' dia' . ((int) $sancion->duracion === 1 ? '' : 's') : '-';
                    @endphp

                    <article class="penalty-card">
                        <div class="penalty-card__head">
                            <div>
                                <div class="penalty-card__title">{{ $sancion->motivo ?: ($sancion->tipo ?: 'Sancion registrada') }}</div>
                                <div class="penalty-card__meta">
                                    {{ $sancion->usuario->name ?? 'Lector no disponible' }}
                                    · {{ $bibliotecaRelacionada }}
                                </div>
                            </div>

                            <div class="penalty-card__badges">
                                <span class="penalty-badge {{ $estadoClase }}">{{ $estadoTexto }}</span>
                                <span class="penalty-badge is-neutral">{{ $origen }}</span>
                            </div>
                        </div>

                        <div class="penalty-card__book">{{ $libroRelacionado }}</div>

                        <div class="penalty-card__grid">
                            <div class="penalty-card__item">
                                <span>Tipo</span>
                                <strong>{{ $sancion->tipo ?: '-' }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Codigo de pago</span>
                                <strong>{{ $sancion->codigo_pago ?: '-' }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Fecha inicio</span>
                                <strong>{{ $sancion->fecha_inicio?->format('d/m/Y') ?? '-' }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Fecha fin</span>
                                <strong>{{ $sancion->fecha_fin?->format('d/m/Y') ?? '-' }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Duracion</span>
                                <strong>{{ $duracionTexto }}</strong>
                            </div>
                            <div class="penalty-card__item">
                                <span>Registrado por</span>
                                <strong>{{ $sancion->bibliotecario->name ?? '-' }}</strong>
                            </div>
                        </div>

                        @if($sancion->observaciones || $sancion->detalles_termino)
                            <div class="penalty-card__notes">
                                @if($sancion->observaciones)
                                    <p><strong>Observaciones:</strong> {{ $sancion->observaciones }}</p>
                                @endif
                                @if($sancion->detalles_termino)
                                    <p><strong>Detalle de cierre:</strong> {{ $sancion->detalles_termino }}</p>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>

            <div class="mt-4">
                {{ $penalizaciones->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
