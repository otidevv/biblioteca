@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Mis reservas')

@section('css')
<link href="{{ asset('css/pagina/panel_lector.css') }}?v={{ filemtime(public_path('css/pagina/panel_lector.css')) }}" rel="stylesheet">
<link href="{{ asset('css/pagina/mi_espacio.css') }}?v={{ filemtime(public_path('css/pagina/mi_espacio.css')) }}" rel="stylesheet">
@endsection

@section('js')
<script src="{{ asset('js/pagina/panel_lector.js') }}?v={{ filemtime(public_path('js/pagina/panel_lector.js')) }}"></script>
<script src="{{ asset('js/pagina/mi_espacio.js') }}?v={{ filemtime(public_path('js/pagina/mi_espacio.js')) }}"></script>
@endsection

@section('content')
@php
    // Solo este bloque php de apertura/cierre: mas abajo se usa php en linea y Blade confundiria ambos.
    $H = \App\Support\HistorialLector::class;
    $placeholder = asset('img/libro-placeholder.png');
    $persona = $user->persona;
    $carnetDuplicado = $persona?->tieneCarnetDuplicado();

    $porRecoger = $reservas->filter(fn ($r) => $H::situacionReserva($r)['clave'] === 'pendiente')
        ->sortBy(fn ($r) => $r->fecha_limite_real)->values();
    $historial = $reservas->reject(fn ($r) => $H::situacionReserva($r)['clave'] === 'pendiente')->values();
    $conteo = $historial->countBy(fn ($r) => $H::situacionReserva($r)['clave']);
    $grupos = $historial->groupBy(fn ($r) => $r->fecha_reservacion?->format('Y-m') ?? 'sin-fecha');

    $entregadas = $conteo['entregada'] ?? 0;
    $noRecogidas = $conteo['vencida'] ?? 0;
    $tasaRecojo = ($entregadas + $noRecogidas) > 0 ? (int) round($entregadas / ($entregadas + $noRecogidas) * 100) : null;

    $iconos = ['entregada' => 'bi-check-circle-fill', 'cancelada' => 'bi-x-circle', 'vencida' => 'bi-hourglass-bottom', 'pendiente' => 'bi-hourglass-split'];
@endphp

<div class="lp me" data-me-csrf="{{ csrf_token() }}">
    <section class="me-hero">
        <div>
            <span class="lp-kicker"><i class="bi bi-bookmark-star"></i> Mi espacio</span>
            <h1 class="lp-title">Mis reservas</h1>
            <p class="lp-lead">Tus libros apartados, hasta cuándo puedes recogerlos y qué pasó con cada solicitud.</p>
        </div>
        <div class="me-hero__actions">
            <a href="{{ route('catalogo') }}" class="lp-btn lp-btn--primary">
                <i class="bi bi-bookmark-plus"></i> Reservar un libro
            </a>
        </div>
    </section>

    <section class="lp-kpis" aria-label="Resumen de reservas">
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-hourglass-split"></i></span>
            <span class="lp-kpi__label">Por recoger</span>
            <strong class="lp-kpi__value">{{ $porRecoger->count() }}</strong>
            <span class="lp-kpi__meta">{{ $porRecoger->isNotEmpty() ? 'La primera vence el ' . $porRecoger->first()->fecha_limite_real->format('d/m') . ' a las 20:00' : 'Nada pendiente' }}</span>
        </div>
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-check2-circle"></i></span>
            <span class="lp-kpi__label">Entregadas</span>
            <strong class="lp-kpi__value">{{ $entregadas }}</strong>
            <span class="lp-kpi__meta">{{ $tasaRecojo !== null ? 'Recogiste el ' . $tasaRecojo . '% de tus reservas' : 'Aún sin reservas cerradas' }}</span>
        </div>
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-x-circle"></i></span>
            <span class="lp-kpi__label">Canceladas</span>
            <strong class="lp-kpi__value">{{ $conteo['cancelada'] ?? 0 }}</strong>
            <span class="lp-kpi__meta">Por ti o por la biblioteca</span>
        </div>
        <div class="lp-kpi {{ $noRecogidas ? 'is-danger' : '' }}">
            <span class="lp-kpi__icon"><i class="bi bi-hourglass-bottom"></i></span>
            <span class="lp-kpi__label">No recogidas</span>
            <strong class="lp-kpi__value">{{ $noRecogidas }}</strong>
            <span class="lp-kpi__meta">Pueden generar una sanción</span>
        </div>
    </section>

    {{-- ─────────── Como funcionan ─────────── --}}
    <ol class="me-steps" aria-label="Cómo funcionan las reservas">
        <li>
            <span class="me-steps__n">1</span>
            <div><strong>Reserva desde el catálogo</strong><span>Elige un ejemplar disponible y si lo quieres en sala o a casa.</span></div>
        </li>
        <li>
            <span class="me-steps__n">2</span>
            <div><strong>Recógelo a tiempo</strong><span>Tienes hasta el día siguiente a las 20:00. Lleva tu carnet{{ $carnetDuplicado ? ' duplicado' : '' }}.</span></div>
        </li>
        <li>
            <span class="me-steps__n">3</span>
            <div><strong>Si no puedes ir, cancélala</strong><span>Una reserva no recogida vence y puede generar una sanción.</span></div>
        </li>
    </ol>

    {{-- ─────────── Por recoger ─────────── --}}
    <section class="lp-panel">
        <header class="lp-panel__head">
            <div>
                <h2>Por recoger</h2>
                <p>{{ $porRecoger->count() ? $porRecoger->count() . ' libro' . ($porRecoger->count() === 1 ? '' : 's') . ' te espera' . ($porRecoger->count() === 1 ? '' : 'n') . ' en la biblioteca.' : 'No tienes reservas pendientes.' }}</p>
            </div>
        </header>

        @if($porRecoger->isNotEmpty())
            <div class="me-cards">
                @foreach($porRecoger as $reserva)
                    @php($autores = $H::autores($reserva->ejemplar?->libro))
                    @php($codigo = $H::codigoEjemplar($reserva->ejemplar))
                    @php($aCasa = (int) $reserva->prestamo === 1)
                    @php($biblioteca = $reserva->ejemplar?->biblioteca)
                    <article class="me-card">
                        <img src="{{ $reserva->ejemplar?->libro?->imagen_url ?? $placeholder }}" alt="" class="me-card__cover"
                             onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        <div class="me-card__body">
                            <h3 class="me-card__title">{{ $reserva->ejemplar?->libro?->titulo ?? 'Libro no disponible' }}</h3>
                            @if($autores)<p class="me-card__authors">{{ $autores }}</p>@endif
                            <div class="me-tags">
                                <span class="lp-tag"><i class="bi {{ $aCasa ? 'bi-house-door' : 'bi-building' }}"></i>{{ $aCasa ? 'A casa' : 'En sala' }}</span>
                                @if($codigo)<span class="lp-tag"><i class="bi bi-upc"></i>{{ $codigo }}</span>@endif
                            </div>
                            <dl class="me-dates">
                                <div><dt>Reservada</dt><dd>{{ $reserva->fecha_reservacion?->format('d/m/Y') ?? '—' }}</dd></div>
                                <div><dt>Recoger hasta</dt><dd>{{ $reserva->fecha_limite_real->format('d/m/Y') }} · 20:00</dd></div>
                            </dl>
                            <p class="me-where">
                                <i class="bi bi-geo-alt"></i>
                                <span>
                                    <b>{{ \Illuminate\Support\Str::title(mb_strtolower($biblioteca?->nombre ?? 'Biblioteca')) }}</b>
                                    @if($biblioteca?->direccion) · {{ \Illuminate\Support\Str::title(mb_strtolower($biblioteca->direccion)) }}@endif
                                </span>
                            </p>
                        </div>
                        <div class="me-card__side">
                            <span class="lp-countdown" data-urgente-horas="3" data-fecha="{{ $reserva->fecha_limite_real->format('Y-m-d\TH:i:s') }}"></span>
                            <button type="button" class="lp-btn lp-btn--danger-ghost" data-me-cancelar="{{ $reserva->id }}"
                                    data-me-titulo="{{ $reserva->ejemplar?->libro?->titulo }}">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="lp-empty">
                <i class="bi bi-bookmark"></i>
                <p>Reserva un libro disponible desde el catálogo y recógelo en la biblioteca.</p>
                <a href="{{ route('catalogo') }}" class="lp-link">Ir al catálogo <i class="bi bi-arrow-right"></i></a>
            </div>
        @endif
    </section>

    {{-- ─────────── Historial ─────────── --}}
    <section class="lp-panel" data-me-historial>
        <header class="lp-panel__head">
            <div>
                <h2>Historial de reservas</h2>
                <p>{{ $historial->count() }} reserva{{ $historial->count() === 1 ? '' : 's' }} cerrada{{ $historial->count() === 1 ? '' : 's' }}.</p>
            </div>
        </header>

        @if($historial->isNotEmpty())
            <div class="me-toolbar">
                <div class="me-filters" role="group" aria-label="Filtrar historial">
                    <button type="button" class="me-filter is-active" data-me-filtro="todos" aria-pressed="true">Todas <span>{{ $historial->count() }}</span></button>
                    <button type="button" class="me-filter" data-me-filtro="entregada" aria-pressed="false">Entregadas <span>{{ $entregadas }}</span></button>
                    <button type="button" class="me-filter" data-me-filtro="cancelada" aria-pressed="false">Canceladas <span>{{ $conteo['cancelada'] ?? 0 }}</span></button>
                    <button type="button" class="me-filter" data-me-filtro="vencida" aria-pressed="false">No recogidas <span>{{ $noRecogidas }}</span></button>
                </div>
                <label class="me-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span class="visually-hidden">Buscar en el historial</span>
                    <input type="search" placeholder="Buscar por título, autor o biblioteca" data-me-buscar>
                </label>
            </div>

            <div class="me-groups">
                @foreach($grupos as $clave => $items)
                    <div class="me-group" data-me-grupo>
                        <h3 class="me-group__title">
                            {{ $clave === 'sin-fecha' ? 'Sin fecha' : $H::mes($items->first()->fecha_reservacion) }}
                            <span>{{ $items->count() }}</span>
                        </h3>

                        @foreach($items as $reserva)
                            @php($situacion = $H::situacionReserva($reserva))
                            @php($autores = $H::autores($reserva->ejemplar?->libro))
                            @php($codigo = $H::codigoEjemplar($reserva->ejemplar))
                            @php($biblioteca = \Illuminate\Support\Str::title(mb_strtolower($reserva->ejemplar?->biblioteca?->nombre ?? '-')))
                            @php($titulo = $reserva->ejemplar?->libro?->titulo ?? 'Libro no disponible')
                            @php($prestamo = $prestamoDeReserva->get($reserva->id))
                            @php($sancion = $sancionPorReserva->get($reserva->id))
                            <article class="me-item" data-me-estado="{{ $situacion['clave'] }}"
                                     data-me-texto="{{ mb_strtolower($titulo . ' ' . $autores . ' ' . $biblioteca) }}">
                                <img src="{{ $reserva->ejemplar?->libro?->imagen_url ?? $placeholder }}" alt="" class="me-item__cover"
                                     onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                <div class="me-item__body">
                                    <div class="me-item__title">{{ $titulo }}</div>
                                    @if($autores)<div class="me-item__authors">{{ $autores }}</div>@endif
                                    <div class="me-item__meta">
                                        <span>{{ $biblioteca }}</span>
                                        <span>{{ (int) $reserva->prestamo === 1 ? 'A casa' : 'En sala' }}</span>
                                        @if($codigo)<span>{{ $codigo }}</span>@endif
                                    </div>
                                    <div class="me-item__when">
                                        <span><i class="bi bi-bookmark"></i> Reservada el {{ $reserva->fecha_reservacion?->format('d/m/Y') ?? '—' }}</span>
                                        <span><i class="bi bi-alarm"></i> Límite {{ $reserva->fecha_limite_real->format('d/m/Y') }} · 20:00</span>
                                    </div>
                                    @if($prestamo)
                                        @php($situacionPrestamo = $H::situacionPrestamo($prestamo))
                                        <p class="me-note">
                                            <i class="bi bi-arrow-left-right"></i>
                                            <span><b>Se entregó como préstamo</b> el {{ $H::fecha($prestamo->fecha_prestamo) }} · {{ $situacionPrestamo['texto'] }}</span>
                                        </p>
                                    @endif
                                    @if($sancion)
                                        @php($estadoSancion = $H::estadoSancion($sancion))
                                        <p class="me-note me-note--sancion is-{{ $estadoSancion['clave'] }}">
                                            <i class="bi bi-slash-circle"></i>
                                            <span><b>Generó una sanción:</b> {{ $estadoSancion['texto'] }}</span>
                                        </p>
                                    @endif
                                </div>
                                <div class="me-item__side">
                                    <span class="me-status me-status--{{ $situacion['clave'] }}">
                                        <i class="bi {{ $iconos[$situacion['clave']] }}" aria-hidden="true"></i>{{ $situacion['texto'] }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="lp-empty lp-empty--compact me-noresults" data-me-sin-resultados hidden>
                <i class="bi bi-funnel"></i>
                <p>No hay reservas con ese filtro o búsqueda.</p>
            </div>
        @else
            <div class="lp-empty">
                <i class="bi bi-clock-history"></i>
                <p>Aún no tienes reservas cerradas.</p>
            </div>
        @endif
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalCancelarReserva" tabindex="-1" aria-labelledby="modalCancelarReservaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content me-modal">
            <div class="modal-body">
                <span class="me-modal__icon"><i class="bi bi-bookmark-x"></i></span>
                <h2 class="me-modal__title" id="modalCancelarReservaTitulo">¿Cancelar esta reserva?</h2>
                <p class="me-modal__book" data-me-modal-titulo></p>
                <p class="me-modal__copy">El ejemplar quedará libre para otros lectores. Cancelarla a tiempo evita una sanción por no recogerla.</p>
            </div>
            <div class="me-modal__actions">
                <button type="button" class="lp-btn lp-btn--ghost" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="lp-btn lp-btn--danger" data-me-confirmar>
                    <i class="bi bi-x-circle"></i> Sí, cancelar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
