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

<svg class="events-icon-sprite" aria-hidden="true" focusable="false">
    <symbol id="events-icon-sparkles" viewBox="0 0 24 24"><path d="M12 3l1.35 3.65L17 8l-3.65 1.35L12 13l-1.35-3.65L7 8l3.65-1.35L12 3z"></path><path d="M19 13l.78 2.22L22 16l-2.22.78L19 19l-.78-2.22L16 16l2.22-.78L19 13z"></path><path d="M5 14l.94 2.56L8.5 17.5l-2.56.94L5 21l-.94-2.56L1.5 17.5l2.56-.94L5 14z"></path></symbol>
    <symbol id="events-icon-book-open" viewBox="0 0 24 24"><path d="M12 7c-2.7-1.8-5.7-2.34-9-1.62V18c3.3-.72 6.3-.18 9 1.62"></path><path d="M12 7c2.7-1.8 5.7-2.34 9-1.62V18c-3.3-.72-6.3-.18-9 1.62"></path><path d="M12 7v12.62"></path></symbol>
    <symbol id="events-icon-calendar-days" viewBox="0 0 24 24"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M3 10h18"></path><path d="M8 14h.01"></path><path d="M12 14h.01"></path><path d="M16 14h.01"></path><path d="M8 18h.01"></path><path d="M12 18h.01"></path><path d="M16 18h.01"></path></symbol>
    <symbol id="events-icon-folders" viewBox="0 0 24 24"><path d="M4 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v1"></path><path d="M4 9h16a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H4"></path><path d="M4 9a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2"></path></symbol>
    <symbol id="events-icon-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></symbol>
    <symbol id="events-icon-chart" viewBox="0 0 24 24"><path d="M3 3v18h18"></path><path d="M7 15l3-3 3 2 5-6"></path><path d="M18 8h0"></path></symbol>
    <symbol id="events-icon-megaphone" viewBox="0 0 24 24"><path d="M3 11v2a2 2 0 0 0 2 2h2l3 5h2l-1.4-5H13l6 3V6l-6 3H5a2 2 0 0 0-2 2z"></path></symbol>
    <symbol id="events-icon-monitor-play" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"></rect><path d="M8 21h8"></path><path d="M12 17v4"></path><path d="M10 8l5 3-5 3V8z"></path></symbol>
    <symbol id="events-icon-trending" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 7-7"></path><path d="M14 8h6v6"></path></symbol>
    <symbol id="events-icon-star" viewBox="0 0 24 24"><path d="M12 3.5l2.8 5.68 6.27.91-4.53 4.42 1.07 6.24L12 17.77l-5.61 2.98 1.07-6.24-4.53-4.42 6.27-.91L12 3.5z"></path></symbol>
    <symbol id="events-icon-calendar-event" viewBox="0 0 24 24"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M3 10h18"></path><path d="M12 13l1 2 2.2.32-1.6 1.56.38 2.2L12 18l-1.98 1.08.38-2.2-1.6-1.56 2.2-.32 1-2z"></path></symbol>
    <symbol id="events-icon-tag" viewBox="0 0 24 24"><path d="M20 10l-8.59 8.59a2 2 0 0 1-2.82 0L2 12V4h8z"></path><path d="M7 7h.01"></path></symbol>
    <symbol id="events-icon-presentation" viewBox="0 0 24 24"><path d="M2 3h20"></path><path d="M6 3v11a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V3"></path><path d="M12 16v5"></path><path d="M8 21h8"></path><path d="M10 8l4 2-4 2V8z"></path></symbol>
    <symbol id="events-icon-map-pin" viewBox="0 0 24 24"><path d="M12 21s-6-4.35-6-10a6 6 0 1 1 12 0c0 5.65-6 10-6 10z"></path><circle cx="12" cy="11" r="2.5"></circle></symbol>
    <symbol id="events-icon-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></symbol>
    <symbol id="events-icon-calendar-check" viewBox="0 0 24 24"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M3 10h18"></path><path d="M9 15l2 2 4-4"></path></symbol>
    <symbol id="events-icon-inbox" viewBox="0 0 24 24"><path d="M4 5h16l2 8v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-4z"></path><path d="M3 13h5l2 3h4l2-3h5"></path></symbol>
    <symbol id="events-icon-layers" viewBox="0 0 24 24"><path d="M12 3l9 5-9 5-9-5 9-5z"></path><path d="M3 12l9 5 9-5"></path><path d="M3 16l9 5 9-5"></path></symbol>
    <symbol id="events-icon-bookmark" viewBox="0 0 24 24"><path d="M7 4h10a2 2 0 0 1 2 2v14l-7-4-7 4V6a2 2 0 0 1 2-2z"></path></symbol>
    <symbol id="events-icon-link" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 1 0-7.07-7.07L11 4"></path><path d="M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 0 0 7.07 7.07L13 19"></path></symbol>
</svg>

<div class="events-shell">
    <section class="events-hero">
        <span class="events-eyebrow"><svg class="events-glyph"><use href="#events-icon-sparkles"></use></svg> Agenda Cultural</span>
        <h1 class="events-title">Eventos y Actividades</h1>
        <p class="events-subtitle">Participa en talleres, encuentros, sesiones de formacion y actividades especiales organizadas por la biblioteca para la comunidad universitaria</p>

        <div class="events-hero-grid">
            <div class="events-callout">
                <h2 class="events-callout-title"><span class="events-title-icon events-title-icon--book"><svg class="events-glyph"><use href="#events-icon-book-open"></use></svg></span><span>Tu espacio de aprendizaje y participacion</span></h2>
                <p>Aqui encontraras todas las actividades que organiza la biblioteca. Filtra por categoria, modalidad o fecha para descubrir los eventos que mas te interesen.</p>
            </div>
            <div class="events-summary-stack">
                <div class="events-summary-card events-summary-primary">
                    <span class="events-summary-label"><svg class="events-glyph"><use href="#events-icon-calendar-days"></use></svg> Proximo evento</span>
                    <strong>{{ $proximoEvento ? $proximoEvento->fecha_inicio->format('d M') : 'Por definir' }}</strong>
                </div>
                <div class="events-summary-card">
                    <span class="events-summary-label"><svg class="events-glyph"><use href="#events-icon-calendar-event"></use></svg> Eventos disponibles</span>
                    <strong>{{ $eventosActivos }}</strong>
                </div>
                <div class="events-summary-card">
                    <span class="events-summary-label"><svg class="events-glyph"><use href="#events-icon-folders"></use></svg> Categorias</span>
                    <strong>{{ $categoriasActivas }}</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="events-section events-filters-section">
        <div class="events-filters-container">
            <h3 class="events-section-title"><span class="events-title-icon events-title-icon--search"><svg class="events-glyph"><use href="#events-icon-search"></use></svg></span><span>Buscar y filtrar eventos</span></h3>
            <div class="events-filters-grid">
                <div class="events-filter-group">
                    <label for="filter-category">Categoria</label>
                    <select id="filter-category" class="events-filter-select">
                        <option value="">Todas las categorias</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @if($modalidadesActivas > 0)
                <div class="events-filter-group">
                    <label for="filter-modality">Modalidad</label>
                    <select id="filter-modality" class="events-filter-select">
                        <option value="">Todas las modalidades</option>
                        @php
                            $modalidades = $agenda->pluck('modalidad')->filter()->map(fn($item) => trim((string) $item))->filter()->unique()->sort();
                        @endphp
                        @foreach($modalidades as $mod)
                            <option value="{{ $mod }}">{{ $mod }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="events-filter-group">
                    <label for="filter-search">Buscar</label>
                    <input type="text" id="filter-search" class="events-filter-input" placeholder="Busqueda rapida...">
                </div>
            </div>
        </div>
    </section>

    <section class="events-section events-insights-section">
        <div class="events-section-header">
            <div>
                <h3 class="events-section-title"><span class="events-title-icon events-title-icon--insight"><svg class="events-glyph"><use href="#events-icon-chart"></use></svg></span><span>Informacion de la agenda</span></h3>
                <p>Un vistazo rapido a lo que esta sucediendo en la biblioteca</p>
            </div>
        </div>

        <div class="events-insight-grid">
            <article class="events-insight-card events-insight-featured">
                <span class="events-insight-icon"><svg class="events-glyph"><use href="#events-icon-megaphone"></use></svg></span>
                <strong>{{ $eventosDestacadosActivos }}</strong>
                <p>
                    @if($eventosDestacadosActivos === 1)
                        Actividad destacada de especial interes
                    @else
                        {{ $eventosDestacadosActivos }} actividades destacadas para no perder
                    @endif
                </p>
            </article>

            <article class="events-insight-card">
                <span class="events-insight-icon"><svg class="events-glyph"><use href="#events-icon-monitor-play"></use></svg></span>
                <strong>{{ $modalidadesActivas }}</strong>
                <p>Forma{{ $modalidadesActivas === 1 ? '' : 's' }} de participacion: presencial, virtual o hibrida</p>
            </article>

            <article class="events-insight-card">
                <span class="events-insight-icon"><svg class="events-glyph"><use href="#events-icon-trending"></use></svg></span>
                <strong>{{ $categoriaLider?->nombre ?? '-' }}</strong>
                <p>
                    @if($categoriaLider)
                        Categoria con mas actividades actualmente ({{ $categoriaLider->actividades_count }})
                    @else
                        Categoria en tendencia
                    @endif
                </p>
            </article>
        </div>
    </section>

    <section class="events-section events-featured-section">
        <div class="events-section-header">
            <div>
                <h3 class="events-section-title"><span class="events-title-icon events-title-icon--star"><svg class="events-glyph"><use href="#events-icon-star"></use></svg></span><span>Eventos destacados</span></h3>
                <p>Las actividades mas importantes que no deberias perder</p>
            </div>
        </div>

        @if($eventosDestacados->isEmpty())
            <div class="events-empty">
                <span class="events-empty-icon"><svg class="events-glyph"><use href="#events-icon-calendar-check"></use></svg></span>
                <p>No hay eventos destacados en este momento</p>
                <small>Vuelve pronto para conocer nuevas actividades</small>
            </div>
        @else
            <div class="events-grid">
                @foreach($eventosDestacados as $evento)
                    <article class="events-card events-card-featured" data-category="{{ $evento->categoria->id ?? '' }}" data-modality="{{ $evento->modalidad ?? '' }}">
                        <div class="events-card-header">
                            <span class="events-card-icon"><svg class="events-glyph"><use href="#events-icon-calendar-event"></use></svg></span>
                            @if($evento->destacado)
                                <span class="events-card-badge"><svg class="events-glyph"><use href="#events-icon-star"></use></svg></span>
                            @endif
                        </div>

                        <div class="events-card-body">
                            <span class="events-card-category"><svg class="events-glyph"><use href="#events-icon-tag"></use></svg>{{ $evento->categoria->nombre ?? 'Evento' }}</span>

                            <div class="events-card-date-block">
                                <strong>{{ $evento->fecha_inicio?->format('d M') ?? '--' }}</strong>
                                <span>{{ $evento->fecha_inicio?->format('Y') ?? '' }}</span>
                            </div>

                            <h4 class="events-card-title">{{ $evento->titulo }}</h4>
                            <p class="events-card-description">{{ $evento->resumen ?: \Illuminate\Support\Str::limit(strip_tags((string) $evento->contenido), 160) }}</p>

                            <div class="events-card-tags">
                                @if($evento->modalidad)
                                    <span class="events-tag"><svg class="events-glyph"><use href="#events-icon-presentation"></use></svg>{{ $evento->modalidad }}</span>
                                @endif
                                @if($evento->lugar)
                                    <span class="events-tag"><svg class="events-glyph"><use href="#events-icon-map-pin"></use></svg>{{ $evento->lugar }}</span>
                                @endif
                            </div>

                            <div class="events-card-meta">
                                @if($evento->hora_inicio)
                                    <span><svg class="events-glyph"><use href="#events-icon-clock"></use></svg>{{ \Carbon\Carbon::parse($evento->hora_inicio)->format('H:i') }}@if($evento->hora_fin) - {{ \Carbon\Carbon::parse($evento->hora_fin)->format('H:i') }}@endif</span>
                                @endif
                                @if($evento->fecha_fin && !$evento->fecha_fin->isSameDay($evento->fecha_inicio))
                                    <span><svg class="events-glyph"><use href="#events-icon-calendar-check"></use></svg>Hasta {{ $evento->fecha_fin->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="events-card-footer">
                            <button class="events-btn-primary">Ver detalles</button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="events-section events-categories-section">
        <div class="events-section-header">
            <div>
                <h3 class="events-section-title"><span class="events-title-icon events-title-icon--folders"><svg class="events-glyph"><use href="#events-icon-folders"></use></svg></span><span>Categorias de actividades</span></h3>
                <p>Explora los diferentes tipos de eventos que organiza la biblioteca</p>
            </div>
        </div>

        @if($categorias->isEmpty())
            <div class="events-empty">
                <span class="events-empty-icon"><svg class="events-glyph"><use href="#events-icon-inbox"></use></svg></span>
                <p>No hay categorias registradas</p>
            </div>
        @else
            <div class="events-category-grid">
                @foreach($categorias as $categoria)
                    <article class="events-category-card" data-category-id="{{ $categoria->id }}">
                        <div class="events-category-header">
                            <small class="events-category-code">{{ $categoria->abreviatura }}</small>
                            <span class="events-category-count"><svg class="events-glyph"><use href="#events-icon-layers"></use></svg>{{ $categoria->actividades_count }}</span>
                        </div>
                        <h4 class="events-category-name">{{ $categoria->nombre }}</h4>
                        <p class="events-category-description">{{ $categoria->descripcion ?: 'Categoria disponible para actividades y eventos de la biblioteca.' }}</p>
                        <button class="events-btn-secondary" data-category="{{ $categoria->id }}">Ver actividades</button>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="events-section events-timeline-section">
        <div class="events-section-header">
            <div>
                <h3 class="events-section-title"><span class="events-title-icon events-title-icon--timeline"><svg class="events-glyph"><use href="#events-icon-calendar-days"></use></svg></span><span>Proximos eventos (Cronograma)</span></h3>
                <p>Todas las actividades ordenadas por fecha para planificar tu participacion</p>
            </div>
        </div>

        @if($agenda->isEmpty())
            <div class="events-empty">
                <span class="events-empty-icon"><svg class="events-glyph"><use href="#events-icon-calendar-event"></use></svg></span>
                <p>No hay eventos programados en este momento</p>
                <small>La agenda se actualizara con nuevas actividades proximamente</small>
            </div>
        @else
            <div class="events-agenda">
                @foreach($agenda as $item)
                    <article class="events-agenda-item" data-category="{{ $item->categoria->id ?? '' }}" data-modality="{{ $item->modalidad ?? '' }}">
                        <div class="events-agenda-date">
                            <div class="events-agenda-day">{{ $item->fecha_inicio?->format('d') ?? '--' }}</div>
                            <div class="events-agenda-month">{{ $item->fecha_inicio?->format('M') ?? '--' }}</div>
                        </div>

                        <div class="events-agenda-content">
                            <div class="events-agenda-badges">
                                <span class="events-badge-category"><svg class="events-glyph"><use href="#events-icon-bookmark"></use></svg>{{ $item->categoria->nombre ?? 'Actividad' }}</span>
                                @if($item->modalidad)
                                    <span class="events-badge-modality"><svg class="events-glyph"><use href="#events-icon-monitor-play"></use></svg>{{ $item->modalidad }}</span>
                                @endif
                            </div>

                            <h5 class="events-agenda-title">{{ $item->titulo }}</h5>
                            <p class="events-agenda-description">{{ $item->resumen ?: \Illuminate\Support\Str::limit(strip_tags((string) $item->contenido), 140) }}</p>

                            <div class="events-agenda-details">
                                @if($item->hora_inicio)
                                    <span class="events-detail-item"><svg class="events-glyph"><use href="#events-icon-clock"></use></svg>{{ \Carbon\Carbon::parse($item->hora_inicio)->format('H:i') }}@if($item->hora_fin) - {{ \Carbon\Carbon::parse($item->hora_fin)->format('H:i') }}@endif</span>
                                @endif
                                @if($item->lugar)
                                    <span class="events-detail-item"><svg class="events-glyph"><use href="#events-icon-map-pin"></use></svg>{{ $item->lugar }}</span>
                                @endif
                                @if($item->referencia)
                                    <span class="events-detail-item"><svg class="events-glyph"><use href="#events-icon-link"></use></svg><a href="{{ $item->referencia }}" target="_blank">Mas informacion</a></span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection

@section('js')
<script src="{{ asset('js/pagina/eventos.js') }}"></script>
@endsection
