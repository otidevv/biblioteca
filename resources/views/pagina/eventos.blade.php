@extends('layouts.biblioteca')

@section('css')
<link href="{{ asset('css/pagina/eventos.css') }}" rel="stylesheet">
@endsection

@section('content')
@php
    $eventosActivos = $eventosDestacados->count();
    $categoriasActivas = $categorias->count();
    $proximoEvento = $agenda->sortBy('fecha_inicio')->first();
    $eventosDestacadosActivos = $eventosDestacados->where('destacado', true)->count();
    $modalidadesActivas = $agenda->pluck('modalidad')->filter()->map(fn($item) => trim((string) $item))->filter()->unique()->count();
    $categoriaLider = $categorias->sortByDesc('actividades_count')->first();
@endphp

<div class="events-shell">
    <section class="events-hero">
        <span class="events-eyebrow"><i class="bi bi-stars"></i> Agenda cultural</span>
        <h1 class="events-title">Eventos y actividades de biblioteca</h1>
        <p class="events-subtitle">Descubre actividades reales registradas por la biblioteca: talleres, encuentros, sesiones de formacion y avisos de participacion para la comunidad universitaria.</p>

        <div class="events-hero-grid">
            <div class="events-callout">
                <h2>Una agenda conectada con la vida universitaria</h2>
                <p>Esta seccion reune las actividades publicadas por la biblioteca y las organiza para que los usuarios encuentren con facilidad proximos eventos, categorias activas y espacios de participacion.</p>
            </div>
            <div class="events-summary-stack">
                <div class="events-summary-card"><span>Eventos activos</span><strong>{{ $eventosActivos }}</strong></div>
                <div class="events-summary-card"><span>Categorias</span><strong>{{ $categoriasActivas }}</strong></div>
                <div class="events-summary-card"><span>Proximo evento</span><strong>{{ $proximoEvento ? $proximoEvento->fecha_inicio->format('d/m/Y') : 'Sin programacion' }}</strong></div>
            </div>
        </div>
    </section>

    <section class="events-section">
        <div class="events-section-header">
            <div>
                <h3>Panorama de la agenda</h3>
                <p>Una lectura rapida del movimiento actual de actividades para la comunidad universitaria.</p>
            </div>
        </div>

        <div class="events-insight-grid">
            <article class="events-insight-card">
                <span class="events-insight-icon"><i class="bi bi-megaphone-fill"></i></span>
                <strong>{{ $eventosDestacadosActivos }}</strong>
                <p>actividad{{ $eventosDestacadosActivos === 1 ? '' : 'es' }} marcada{{ $eventosDestacadosActivos === 1 ? '' : 's' }} como destacada{{ $eventosDestacadosActivos === 1 ? '' : 's' }} dentro de la agenda visible.</p>
            </article>

            <article class="events-insight-card">
                <span class="events-insight-icon"><i class="bi bi-easel2-fill"></i></span>
                <strong>{{ $modalidadesActivas }}</strong>
                <p>modalidad{{ $modalidadesActivas === 1 ? '' : 'es' }} activa{{ $modalidadesActivas === 1 ? '' : 's' }} entre las publicaciones programadas por la biblioteca.</p>
            </article>

            <article class="events-insight-card">
                <span class="events-insight-icon"><i class="bi bi-bookmarks-fill"></i></span>
                <strong>{{ $categoriaLider?->nombre ?? 'Sin categoria lider' }}</strong>
                <p>
                    {{ $categoriaLider ? 'Es la categoria con mayor presencia actual, con ' . $categoriaLider->actividades_count . ' actividad' . ($categoriaLider->actividades_count === 1 ? '' : 'es') . ' activas.' : 'La agenda aun no tiene suficiente informacion para destacar una categoria principal.' }}
                </p>
            </article>
        </div>
    </section>

    <section class="events-section">
        <div class="events-section-header">
            <div>
                <h3>Eventos destacados</h3>
                <p>Actividades publicadas actualmente por la biblioteca.</p>
            </div>
        </div>

        @if($eventosDestacados->isEmpty())
            <div class="events-empty">No hay actividades publicadas por el momento.</div>
        @else
            <div class="events-grid">
                @foreach($eventosDestacados as $evento)
                    <article class="events-card">
                        <span class="events-card-icon"><i class="bi bi-calendar-event"></i></span>
                        <div class="events-card-head">
                            <span class="events-card-tag">
                                <i class="bi bi-bookmark-star"></i>
                                {{ $evento->categoria->nombre ?? 'Actividad' }}
                            </span>
                            @if($evento->destacado)
                                <span class="events-card-pill">
                                    <i class="bi bi-pin-angle-fill"></i>
                                    Destacado
                                </span>
                            @endif
                        </div>
                        <div class="events-card-date">
                            <strong>{{ $evento->fecha_inicio?->format('d M') ?? '--' }}</strong>
                            <span>{{ $evento->fecha_inicio?->format('Y') ?? '' }}</span>
                        </div>
                        <h4>{{ $evento->titulo }}</h4>
                        <p>{{ $evento->resumen ?: \Illuminate\Support\Str::limit(strip_tags((string) $evento->contenido), 160) }}</p>
                        <div class="events-card-meta">
                            <span><i class="bi bi-calendar3"></i>{{ $evento->fecha_inicio?->format('d/m/Y') }}@if($evento->fecha_fin && !$evento->fecha_fin->isSameDay($evento->fecha_inicio)) al {{ $evento->fecha_fin->format('d/m/Y') }}@endif</span>
                            @if($evento->hora_inicio)
                                <span><i class="bi bi-clock"></i>{{ \Carbon\Carbon::parse($evento->hora_inicio)->format('H:i') }}@if($evento->hora_fin) - {{ \Carbon\Carbon::parse($evento->hora_fin)->format('H:i') }}@endif</span>
                            @endif
                            @if($evento->lugar)
                                <span><i class="bi bi-geo-alt"></i>{{ $evento->lugar }}</span>
                            @endif
                            @if($evento->modalidad)
                                <span><i class="bi bi-easel2"></i>{{ $evento->modalidad }}</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="events-section">
        <div class="events-section-header">
            <div>
                <h3>Categorias activas</h3>
                <p>Una vista rapida de las lineas de actividad que hoy tiene la biblioteca.</p>
            </div>
        </div>

        @if($categorias->isEmpty())
            <div class="events-empty">No hay categorias de actividades registradas.</div>
        @else
            <div class="events-category-grid">
                @foreach($categorias as $categoria)
                    <article class="events-category-card">
                        <small>{{ $categoria->abreviatura }}</small>
                        <strong>{{ $categoria->nombre }}</strong>
                        <span>{{ $categoria->descripcion ?: 'Categoria disponible para actividades y noticias de la biblioteca.' }}</span>
                        <span class="mt-2 d-inline-flex align-items-center gap-2 text-success fw-semibold">
                            <i class="bi bi-collection"></i>{{ $categoria->actividades_count }} actividad{{ $categoria->actividades_count === 1 ? '' : 'es' }}
                        </span>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="events-section">
        <div class="events-section-header">
            <div>
                <h3>Proximas fechas</h3>
                <p>Orden cronologico de las publicaciones activas para ayudar a planificar la participacion.</p>
            </div>
        </div>

        @if($agenda->isEmpty())
            <div class="events-empty">No hay fechas programadas en este momento.</div>
        @else
            <div class="events-agenda">
                @foreach($agenda as $item)
                    <article class="events-agenda-item">
                        <div class="events-agenda-day">{{ $item->fecha_inicio?->format('d M') ?? '--' }}</div>
                        <div class="events-agenda-copy">
                            <div class="events-agenda-top">
                                <span class="events-card-pill">
                                    <i class="bi bi-bookmark"></i>
                                    {{ $item->categoria->nombre ?? 'Actividad' }}
                                </span>
                                @if($item->modalidad)
                                    <span class="events-agenda-mode">{{ $item->modalidad }}</span>
                                @endif
                            </div>
                            <h5>{{ $item->titulo }}</h5>
                            <p>{{ $item->resumen ?: \Illuminate\Support\Str::limit(strip_tags((string) $item->contenido), 140) }}</p>
                            <div class="events-agenda-detail">
                                @if($item->hora_inicio)
                                    <span><i class="bi bi-clock"></i>{{ \Carbon\Carbon::parse($item->hora_inicio)->format('H:i') }}@if($item->hora_fin) - {{ \Carbon\Carbon::parse($item->hora_fin)->format('H:i') }}@endif</span>
                                @endif
                                @if($item->lugar)
                                    <span><i class="bi bi-geo-alt"></i>{{ $item->lugar }}</span>
                                @endif
                                @if($item->referencia)
                                    <span><i class="bi bi-link-45deg"></i>{{ $item->referencia }}</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="events-cta">
        <div class="events-participation-card">
            <h3>Como aprovechar esta agenda</h3>
            <div class="events-participation-list">
                <div>
                    <i class="bi bi-search-heart"></i>
                    <span>Revisa categorias para identificar talleres, concursos, avisos o encuentros activos.</span>
                </div>
                <div>
                    <i class="bi bi-calendar2-check"></i>
                    <span>Consulta fechas y modalidad para organizar tu participacion con tiempo.</span>
                </div>
                <div>
                    <i class="bi bi-bell"></i>
                    <span>Las actividades activas pueden integrarse al centro de mensajes para avisar a la comunidad.</span>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('js')
<script src="{{ asset('js/pagina/eventos.js') }}"></script>
@endsection
