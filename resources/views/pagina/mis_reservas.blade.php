@extends('layouts.biblioteca')

@section('css')
<link href="{{ asset('css/pagina/mis_reservas.css') }}" rel="stylesheet">
@endsection

@section('js')
<script src="{{ asset('js/pagina/mis_reservas.js') }}"></script>
@endsection



@section('content')
@php
    $pendientes = $reservas->where('estado', 0)->filter(fn($r) => now()->lt($r->fecha_limite_real))->count();
    $atendidas = $reservas->where('estado', 1)->count();
    $vencidas = $reservas->where('estado', 0)->filter(fn($r) => now()->gte($r->fecha_limite_real))->count();
    $proximaReserva = $reservas->where('estado', 0)->sortBy('fecha_limite_real')->first();
@endphp



<div class="reservas-shell">
    <section class="reservas-hero">
        <span class="reservas-eyebrow">
            <i class="bi bi-journal-check"></i>
            Seguimiento de reservas
        </span>

        <h1 class="reservas-title">Mis reservas</h1>
        <p class="reservas-subtitle">
            Consulta el estado de tus solicitudes, verifica el tiempo disponible para recoger cada ejemplar y administra tus reservas pendientes desde un solo lugar.
        </p>

        <div class="reservas-stats">
            <div class="reservas-stat-card">
                <span>Pendientes</span>
                <strong>{{ $pendientes }}</strong>
            </div>
            <div class="reservas-stat-card">
                <span>Atendidas</span>
                <strong>{{ $atendidas }}</strong>
            </div>
            <div class="reservas-stat-card">
                <span>Vencidas</span>
                <strong>{{ $vencidas }}</strong>
            </div>
        </div>

        @if($proximaReserva && now()->lt($proximaReserva->fecha_limite_real))
            <div class="reservas-highlight">
                <span class="reservas-highlight-icon">
                    <i class="bi bi-hourglass-split"></i>
                </span>
                <div>
                    <strong>Proxima reserva por vencer</strong>
                    <span>
                        "{{ $proximaReserva->ejemplar->libro->titulo }}" debe recogerse hasta el
                        {{ $proximaReserva->fecha_limite_real->format('d/m/Y') }} a las 20:00.
                    </span>
                </div>
            </div>
        @endif
    </section>

    @if($reservas->isEmpty())
        <section class="reservas-empty">
            <div class="reservas-empty-icon">
                <i class="bi bi-journal-x"></i>
            </div>
            <h3>Aun no tienes reservas registradas</h3>
            <p>
                Explora el catalogo, encuentra un libro disponible y solicita tu primera reserva desde la ficha del ejemplar.
            </p>
        </section>
    @else
        <section class="reservas-card">
            <div class="reservas-card-header">
                <div>
                    <h2>Historial de reservas</h2>
                    <p>Visualiza la biblioteca, el tipo de prestamo y el estado actualizado de cada solicitud.</p>
                </div>
                <span class="reservas-time">
                    <i class="bi bi-collection"></i>
                    {{ $reservas->count() }} registro{{ $reservas->count() === 1 ? '' : 's' }}
                </span>
            </div>

            <div class="reservas-list">
                @foreach($reservas as $r)
                    @php
                        $fechaLimiteReal = $r->fecha_limite_real;
                        $vencido = now()->gte($fechaLimiteReal);
                        $tipoPrestamo = (int) ($r->prestamo ?? 0);
                    @endphp

                    <article class="reservas-item {{ $r->estado == 1 ? 'is-complete' : ($r->estado == 2 ? 'is-cancelled' : ($vencido ? 'is-expired' : '')) }}">
                        <div class="reservas-item-main">
                            <div class="reservas-item-top">
                                <div class="reservas-book">
                                    <img
                                        src="{{ $r->ejemplar->libro->imagen_url }}"
                                        alt="{{ $r->ejemplar->libro->titulo }}"
                                        onerror="this.onerror=null;this.src='{{ asset('img/libro-placeholder.png') }}';">

                                    <div>
                                        <div class="reservas-book-title">{{ $r->ejemplar->libro->titulo }}</div>
                                        <div class="reservas-book-meta">
                                            {{ $r->ejemplar->codigo ?? 'Sin codigo' }}
                                        </div>
                                        <div class="reservas-book-subtitle">
                                            Recoge tu reserva en {{ $r->ejemplar->biblioteca->nombre ?? 'biblioteca no disponible' }} antes de la hora limite.
                                        </div>
                                    </div>
                                </div>

                                <span class="reservas-order">#{{ $loop->iteration }}</span>
                            </div>

                            <div class="reservas-meta-grid">
                                <div class="reservas-meta-card">
                                    <span>Biblioteca</span>
                                    <strong>{{ $r->ejemplar->biblioteca->nombre ?? '-' }}</strong>
                                </div>
                                <div class="reservas-meta-card">
                                    <span>Tipo</span>
                                    <strong>{{ $tipoPrestamo === 1 ? 'Prestamo a casa' : 'Prestamo en sala' }}</strong>
                                </div>
                                <div class="reservas-meta-card">
                                    <span>Reserva registrada</span>
                                    <strong>{{ $r->fecha_reservacion?->format('d/m/Y H:i') ?? '-' }}</strong>
                                </div>
                                <div class="reservas-meta-card">
                                    <span>Fecha limite</span>
                                    <strong>{{ $fechaLimiteReal->format('d/m/Y') }} a las 20:00</strong>
                                </div>
                            </div>
                        </div>

                        <div class="reservas-item-side">
                            <div>
                                @if($tipoPrestamo === 1)
                                    <span class="reservas-chip is-home">
                                        <i class="bi bi-house-door-fill"></i>
                                        Casa
                                    </span>
                                @else
                                    <span class="reservas-chip is-room">
                                        <i class="bi bi-book-half"></i>
                                        Sala
                                    </span>
                                @endif
                            </div>

                            <div>
                                @if($r->estado == 0 && $vencido)
                                    <span class="reservas-chip is-expired">Vencido</span>
                                @elseif($r->estado == 0)
                                    <span class="reservas-chip is-pending">En espera</span>
                                @elseif($r->estado == 1)
                                    <span class="reservas-chip is-complete">Atendido</span>
                                @elseif($r->estado == 2)
                                    <span class="reservas-chip is-cancelled">Cancelado</span>
                                @endif
                            </div>

                            @if($r->estado == 0)
                                <div>
                                    <span class="reservas-countdown-label">Tiempo restante</span>
                                    <span class="countdown" data-fecha="{{ $fechaLimiteReal->format('Y-m-d H:i:s') }}"></span>
                                </div>
                            @endif

                            @if($r->estado == 0 && now()->lt($fechaLimiteReal))
                                <button
                                    class="btn btn-outline-danger reservas-action-btn btn-cancelar"
                                    data-id="{{ $r->id }}">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Cancelar reserva
                                </button>
                            @else
                                <span class="text-muted">No hay acciones disponibles</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-labelledby="modalCancelarTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content reservas-modal-content border-0">
            <div class="modal-header reservas-modal-header border-0">
                <div>
                    <h5 class="modal-title reservas-modal-title" id="modalCancelarTitle">Cancelar reserva</h5>
                    <small class="text-muted">Esta accion liberara el ejemplar y dejara la solicitud sin efecto.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar modal de cancelacion"></button>
            </div>

            <div class="modal-body reservas-modal-body text-center">
                <div class="reservas-modal-icon">
                    <i class="bi bi-journal-x"></i>
                </div>

                <p class="mb-2 fw-bold text-dark">
                    Seguro que deseas cancelar esta reserva?
                </p>

                <p class="mb-0 text-muted">
                    Si confirmas, el sistema liberara el ejemplar automaticamente para que vuelva a estar disponible.
                </p>
            </div>

            <div class="reservas-modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Volver
                </button>

                <button type="button" class="btn btn-danger" id="confirmarCancelacion">
                    Si, cancelar reserva
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


