@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Mis préstamos')

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

    $activos = $prestamos->where('estado', 1)->sortBy(fn ($p) => $p->fecha_limite_real)->values();
    $vencidos = $activos->filter(fn ($p) => $p->fecha_limite_real && now()->gte($p->fecha_limite_real));
    $proximo = $activos->first(fn ($p) => $p->fecha_limite_real && now()->lt($p->fecha_limite_real));
    $historial = $prestamos->where('estado', 2)->values();
    $conteo = $historial->countBy(fn ($p) => $H::situacionPrestamo($p)['clave']);
    $grupos = $historial->groupBy(fn ($p) => $p->fecha_prestamo?->format('Y-m') ?? 'sin-fecha');
    $e = $estadisticas;

    $iconos = ['a-tiempo' => 'bi-check-circle-fill', 'tardanza' => 'bi-clock-history', 'deterioro' => 'bi-exclamation-diamond-fill', 'curso' => 'bi-book-half', 'vencido' => 'bi-exclamation-octagon-fill'];
@endphp

<div class="lp me">
    <section class="me-hero">
        <div>
            <span class="lp-kicker"><i class="bi bi-arrow-left-right"></i> Mi espacio</span>
            <h1 class="lp-title">Mis préstamos</h1>
            <p class="lp-lead">Lo que tienes ahora, hasta cuándo devolverlo y tu historial completo de lectura.</p>
        </div>
        <div class="me-hero__actions">
            <a href="{{ route('lector.reporte') }}" target="_blank" rel="noopener" class="lp-btn lp-btn--ghost">
                <i class="bi bi-filetype-pdf"></i> Descargar reporte
            </a>
            <a href="{{ route('catalogo') }}" class="lp-btn lp-btn--primary">
                <i class="bi bi-search"></i> Buscar un libro
            </a>
        </div>
    </section>

    <section class="lp-kpis" aria-label="Resumen de préstamos">
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-book-half"></i></span>
            <span class="lp-kpi__label">En curso</span>
            <strong class="lp-kpi__value">{{ $activos->count() }}</strong>
            <span class="lp-kpi__meta">{{ $proximo ? 'Próximo vence el ' . $proximo->fecha_limite_real->format('d/m') : 'Sin devoluciones pendientes' }}</span>
        </div>
        <div class="lp-kpi {{ $vencidos->isNotEmpty() ? 'is-danger' : '' }}">
            <span class="lp-kpi__icon"><i class="bi bi-exclamation-octagon"></i></span>
            <span class="lp-kpi__label">Fuera de plazo</span>
            <strong class="lp-kpi__value">{{ $vencidos->count() }}</strong>
            <span class="lp-kpi__meta">{{ $vencidos->isNotEmpty() ? 'Devuélvelos cuanto antes' : 'Todo al día' }}</span>
        </div>
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-journal-check"></i></span>
            <span class="lp-kpi__label">Devueltos</span>
            <strong class="lp-kpi__value">{{ $historial->count() }}</strong>
            <span class="lp-kpi__meta">{{ $e['libros_distintos'] }} libro{{ $e['libros_distintos'] === 1 ? '' : 's' }} distinto{{ $e['libros_distintos'] === 1 ? '' : 's' }}</span>
        </div>
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-stopwatch"></i></span>
            <span class="lp-kpi__label">Puntualidad</span>
            <strong class="lp-kpi__value">{{ $e['puntualidad'] !== null ? $e['puntualidad'] . '%' : '—' }}</strong>
            <span class="lp-kpi__meta">{{ ($conteo['a-tiempo'] ?? 0) + ($conteo['deterioro'] ?? 0) }} de {{ $historial->count() }} a tiempo</span>
        </div>
    </section>

    @if($vencidos->isNotEmpty())
        <div class="lp-alert lp-alert--critical" role="alert">
            <i class="bi bi-exclamation-octagon-fill" aria-hidden="true"></i>
            <div>
                <strong>Tienes {{ $vencidos->count() }} préstamo{{ $vencidos->count() === 1 ? '' : 's' }} fuera de plazo</strong>
                <span>Devuélvelo{{ $vencidos->count() === 1 ? '' : 's' }} en la biblioteca lo antes posible: cada día de retraso puede generar una sanción.</span>
            </div>
        </div>
    @endif

    {{-- ─────────── Lo que tiene ahora ─────────── --}}
    <section class="lp-panel">
        <header class="lp-panel__head">
            <div>
                <h2>Ahora tienes</h2>
                <p>{{ $activos->count() ? $activos->count() . ' libro' . ($activos->count() === 1 ? '' : 's') . ' en préstamo.' : 'No tienes libros en préstamo.' }}</p>
            </div>
        </header>

        @if($activos->isNotEmpty())
            <div class="me-cards">
                @foreach($activos as $prestamo)
                    @php($situacion = $H::situacionPrestamo($prestamo))
                    @php($autores = $H::autores($prestamo->ejemplar?->libro))
                    @php($codigo = $H::codigoEjemplar($prestamo->ejemplar))
                    @php($enSala = (int) $prestamo->prestamo_lugar === \App\Models\Prestamo::EN_SALA)
                    <article class="me-card {{ $situacion['clave'] === 'vencido' ? 'is-late' : '' }}">
                        <img src="{{ $prestamo->ejemplar?->libro?->imagen_url ?? $placeholder }}" alt="" class="me-card__cover"
                             onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        <div class="me-card__body">
                            <h3 class="me-card__title">{{ $prestamo->ejemplar?->libro?->titulo ?? 'Libro no disponible' }}</h3>
                            @if($autores)<p class="me-card__authors">{{ $autores }}</p>@endif
                            <div class="me-tags">
                                <span class="lp-tag"><i class="bi {{ $enSala ? 'bi-building' : 'bi-house-door' }}"></i>{{ $enSala ? 'En sala' : 'A casa' }}</span>
                                <span class="lp-tag"><i class="bi bi-bank"></i>{{ \Illuminate\Support\Str::title(mb_strtolower($prestamo->ejemplar?->biblioteca?->nombre ?? '-')) }}</span>
                                @if($codigo)<span class="lp-tag"><i class="bi bi-upc"></i>{{ $codigo }}</span>@endif
                            </div>
                            <dl class="me-dates">
                                <div><dt>Prestado</dt><dd>{{ $H::fecha($prestamo->fecha_prestamo) }}</dd></div>
                                <div><dt>Devolver hasta</dt><dd>{{ $prestamo->fecha_limite_real->format('d/m/Y') }} · 20:00</dd></div>
                            </dl>
                            @if($prestamo->observaciones_prestamo)
                                <p class="me-note"><i class="bi bi-chat-left-text"></i>{{ $prestamo->observaciones_prestamo }}</p>
                            @endif
                        </div>
                        <div class="me-card__side">
                            <span class="lp-countdown" data-fecha="{{ $prestamo->fecha_limite_real->format('Y-m-d\TH:i:s') }}"></span>
                            @if($retraso = $H::retraso($prestamo))
                                <span class="me-late">{{ $retraso }}</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="lp-empty">
                <i class="bi bi-journal-check"></i>
                <p>Cuando se te registre un préstamo aparecerá aquí con su fecha límite.</p>
                <a href="{{ route('catalogo') }}" class="lp-link">Explorar el catálogo <i class="bi bi-arrow-right"></i></a>
            </div>
        @endif
    </section>

    {{-- ─────────── Historial ─────────── --}}
    <section class="lp-panel" data-me-historial>
        <header class="lp-panel__head">
            <div>
                <h2>Historial de préstamos</h2>
                <p>{{ $historial->count() }} préstamo{{ $historial->count() === 1 ? '' : 's' }} devuelto{{ $historial->count() === 1 ? '' : 's' }}.</p>
            </div>
        </header>

        @if($historial->isNotEmpty())
            <div class="me-toolbar">
                <div class="me-filters" role="group" aria-label="Filtrar historial">
                    <button type="button" class="me-filter is-active" data-me-filtro="todos" aria-pressed="true">Todos <span>{{ $historial->count() }}</span></button>
                    <button type="button" class="me-filter" data-me-filtro="a-tiempo" aria-pressed="false">A tiempo <span>{{ $conteo['a-tiempo'] ?? 0 }}</span></button>
                    <button type="button" class="me-filter" data-me-filtro="tardanza" aria-pressed="false">Con tardanza <span>{{ $conteo['tardanza'] ?? 0 }}</span></button>
                    <button type="button" class="me-filter" data-me-filtro="deterioro" aria-pressed="false">Deterioro <span>{{ $conteo['deterioro'] ?? 0 }}</span></button>
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
                            {{ $clave === 'sin-fecha' ? 'Sin fecha' : $H::mes($items->first()->fecha_prestamo) }}
                            <span>{{ $items->count() }}</span>
                        </h3>

                        @foreach($items as $prestamo)
                            @php($situacion = $H::situacionPrestamo($prestamo))
                            @php($autores = $H::autores($prestamo->ejemplar?->libro))
                            @php($codigo = $H::codigoEjemplar($prestamo->ejemplar))
                            @php($enSala = (int) $prestamo->prestamo_lugar === \App\Models\Prestamo::EN_SALA)
                            @php($biblioteca = \Illuminate\Support\Str::title(mb_strtolower($prestamo->ejemplar?->biblioteca?->nombre ?? '-')))
                            @php($titulo = $prestamo->ejemplar?->libro?->titulo ?? 'Libro no disponible')
                            <article class="me-item" data-me-estado="{{ $situacion['clave'] }}"
                                     data-me-texto="{{ mb_strtolower($titulo . ' ' . $autores . ' ' . $biblioteca) }}">
                                <img src="{{ $prestamo->ejemplar?->libro?->imagen_url ?? $placeholder }}" alt="" class="me-item__cover"
                                     onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                <div class="me-item__body">
                                    <div class="me-item__title">{{ $titulo }}</div>
                                    @if($autores)<div class="me-item__authors">{{ $autores }}</div>@endif
                                    <div class="me-item__meta">
                                        <span>{{ $biblioteca }}</span>
                                        <span>{{ $enSala ? 'En sala' : 'A casa' }}</span>
                                        @if($codigo)<span>{{ $codigo }}</span>@endif
                                    </div>
                                    <div class="me-item__when">
                                        <span><i class="bi bi-box-arrow-right"></i> {{ $H::fecha($prestamo->fecha_prestamo) }}</span>
                                        <span><i class="bi bi-box-arrow-in-left"></i> {{ $H::fecha($prestamo->fecha_devolucion) }}</span>
                                        @if($duracion = $H::duracion($prestamo))<span><i class="bi bi-hourglass-split"></i> {{ $duracion }}</span>@endif
                                    </div>
                                    @if($prestamo->observaciones_prestamo)
                                        <p class="me-note"><i class="bi bi-chat-left-text"></i><span><b>Al prestar:</b> {{ $prestamo->observaciones_prestamo }}</span></p>
                                    @endif
                                    @if($prestamo->observaciones_devolucion)
                                        <p class="me-note"><i class="bi bi-chat-left-dots"></i><span><b>Al devolver:</b> {{ $prestamo->observaciones_devolucion }}</span></p>
                                    @endif
                                    @foreach($sancionesPorPrestamo->get($prestamo->id, collect()) as $sancion)
                                        @php($estadoSancion = $H::estadoSancion($sancion))
                                        <p class="me-note me-note--sancion is-{{ $estadoSancion['clave'] }}">
                                            <i class="bi bi-slash-circle"></i>
                                            <span><b>Sanción:</b> {{ rtrim((string) $sancion->motivo, '. ') }} · {{ $estadoSancion['texto'] }}</span>
                                        </p>
                                    @endforeach
                                </div>
                                <div class="me-item__side">
                                    <span class="me-status me-status--{{ $situacion['clave'] }}">
                                        <i class="bi {{ $iconos[$situacion['clave']] }}" aria-hidden="true"></i>{{ $situacion['texto'] }}
                                    </span>
                                    @if($retraso = $H::retraso($prestamo))
                                        <span class="me-late">{{ $retraso }}</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="lp-empty lp-empty--compact me-noresults" data-me-sin-resultados hidden>
                <i class="bi bi-funnel"></i>
                <p>No hay préstamos con ese filtro o búsqueda.</p>
            </div>
        @else
            <div class="lp-empty">
                <i class="bi bi-clock-history"></i>
                <p>Aún no tienes préstamos devueltos.</p>
            </div>
        @endif
    </section>
</div>
@endsection
