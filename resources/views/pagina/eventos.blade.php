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
    <!-- SECCIÓN HERO -->
    <section class="events-hero">
        <span class="events-eyebrow"><i class="bi bi-star-fill"></i> Agenda Cultural</span>
        <h1 class="events-title">Eventos y Actividades</h1>
        <p class="events-subtitle">Participa en talleres, encuentros, sesiones de formación y actividades especiales organizadas por la biblioteca para la comunidad universitaria</p>

        <div class="events-hero-grid">
            <div class="events-callout">
                <h2>📚 Tu espacio de aprendizaje y participación</h2>
                <p>Aquí encontrarás todas las actividades que organiza la biblioteca. Filtra por categoría, modalidad o fecha para descubrir los eventos que más te interesen.</p>
            </div>
            <div class="events-summary-stack">
                <div class="events-summary-card events-summary-primary">
                    <span>🎯 Próximo evento</span>
                    <strong>{{ $proximoEvento ? $proximoEvento->fecha_inicio->format('d M') : 'Por definir' }}</strong>
                </div>
                <div class="events-summary-card">
                    <span>📋 Eventos disponibles</span>
                    <strong>{{ $eventosActivos }}</strong>
                </div>
                <div class="events-summary-card">
                    <span>🏷️ Categorías</span>
                    <strong>{{ $categoriasActivas }}</strong>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE FILTROS -->
    <section class="events-section events-filters-section">
        <div class="events-filters-container">
            <h3>🔍 Buscar y filtrar eventos</h3>
            <div class="events-filters-grid">
                <div class="events-filter-group">
                    <label for="filter-category">Categoría</label>
                    <select id="filter-category" class="events-filter-select">
                        <option value="">Todas las categorías</option>
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
                    <input type="text" id="filter-search" class="events-filter-input" placeholder="Búsqueda rápida...">
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE ESTADÍSTICAS & INSIGHTS -->
    <section class="events-section events-insights-section">
        <div class="events-section-header">
            <div>
                <h3>📊 Información de la agenda</h3>
                <p>Un vistazo rápido a lo que está sucediendo en la biblioteca</p>
            </div>
        </div>

        <div class="events-insight-grid">
            <article class="events-insight-card events-insight-featured">
                <span class="events-insight-icon"><i class="bi bi-pin-angle-fill"></i></span>
                <strong>{{ $eventosDestacadosActivos }}</strong>
                <p>
                    @if($eventosDestacadosActivos === 1)
                        Actividad destacada de especial interés
                    @else
                        {{ $eventosDestacadosActivos }} actividades destacadas para no perder
                    @endif
                </p>
            </article>

            <article class="events-insight-card">
                <span class="events-insight-icon"><i class="bi bi-tv"></i></span>
                <strong>{{ $modalidadesActivas }}</strong>
                <p>
                    Forma{{ $modalidadesActivas === 1 ? '' : 's' }} de participación: presencial, virtual o híbrida
                </p>
            </article>

            <article class="events-insight-card">
                <span class="events-insight-icon"><i class="bi bi-fire"></i></span>
                <strong>{{ $categoriaLider?->nombre ?? '—' }}</strong>
                <p>
                    @if($categoriaLider)
                        Categoría con más actividades actualmente ({{ $categoriaLider->actividades_count }})
                    @else
                        Categoría en tendencia
                    @endif
                </p>
            </article>
        </div>
    </section>


    <!-- SECCIÓN DE EVENTOS DESTACADOS -->
    <section class="events-section events-featured-section">
        <div class="events-section-header">
            <div>
                <h3>⭐ Eventos destacados</h3>
                <p>Las actividades más importantes que no deberías perder</p>
            </div>
        </div>

        @if($eventosDestacados->isEmpty())
            <div class="events-empty">
                <i class="bi bi-calendar-x"></i>
                <p>No hay eventos destacados en este momento</p>
                <small>Vuelve pronto para conocer nuevas actividades</small>
            </div>
        @else
            <div class="events-grid">
                @foreach($eventosDestacados as $evento)
                    <article class="events-card events-card-featured" data-category="{{ $evento->categoria->id ?? '' }}" data-modality="{{ $evento->modalidad ?? '' }}">
                        <div class="events-card-header">
                            <span class="events-card-icon"><i class="bi bi-calendar-event"></i></span>
                            @if($evento->destacado)
                                <span class="events-card-badge">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            @endif
                        </div>

                        <div class="events-card-body">
                            <span class="events-card-category">
                                <i class="bi bi-tag"></i>
                                {{ $evento->categoria->nombre ?? 'Evento' }}
                            </span>

                            <div class="events-card-date-block">
                                <strong>{{ $evento->fecha_inicio?->format('d M') ?? '--' }}</strong>
                                <span>{{ $evento->fecha_inicio?->format('Y') ?? '' }}</span>
                            </div>

                            <h4 class="events-card-title">{{ $evento->titulo }}</h4>
                            <p class="events-card-description">{{ $evento->resumen ?: \Illuminate\Support\Str::limit(strip_tags((string) $evento->contenido), 160) }}</p>

                            <div class="events-card-tags">
                                @if($evento->modalidad)
                                    <span class="events-tag"><i class="bi bi-laptop"></i>{{ $evento->modalidad }}</span>
                                @endif
                                @if($evento->lugar)
                                    <span class="events-tag"><i class="bi bi-geo-alt"></i>{{ $evento->lugar }}</span>
                                @endif
                            </div>

                            <div class="events-card-meta">
                                @if($evento->hora_inicio)
                                    <span><i class="bi bi-clock"></i>{{ \Carbon\Carbon::parse($evento->hora_inicio)->format('H:i') }}@if($evento->hora_fin) - {{ \Carbon\Carbon::parse($evento->hora_fin)->format('H:i') }}@endif</span>
                                @endif
                                @if($evento->fecha_fin && !$evento->fecha_fin->isSameDay($evento->fecha_inicio))
                                    <span><i class="bi bi-calendar3"></i>Hasta {{ $evento->fecha_fin->format('d/m/Y') }}</span>
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


    <!-- SECCIÓN DE CATEGORÍAS -->
    <section class="events-section events-categories-section">
        <div class="events-section-header">
            <div>
                <h3>🏷️ Categorías de actividades</h3>
                <p>Explora los diferentes tipos de eventos que organiza la biblioteca</p>
            </div>
        </div>

        @if($categorias->isEmpty())
            <div class="events-empty">
                <i class="bi bi-inbox"></i>
                <p>No hay categorías registradas</p>
            </div>
        @else
            <div class="events-category-grid">
                @foreach($categorias as $categoria)
                    <article class="events-category-card" data-category-id="{{ $categoria->id }}">
                        <div class="events-category-header">
                            <small class="events-category-code">{{ $categoria->abreviatura }}</small>
                            <span class="events-category-count">
                                <i class="bi bi-collection"></i>
                                {{ $categoria->actividades_count }}
                            </span>
                        </div>
                        <h4 class="events-category-name">{{ $categoria->nombre }}</h4>
                        <p class="events-category-description">{{ $categoria->descripcion ?: 'Categoría disponible para actividades y eventos de la biblioteca.' }}</p>
                        <button class="events-btn-secondary" data-category="{{ $categoria->id }}">
                            Ver actividades
                        </button>
                    </article>
                @endforeach
            </div>
        @endif
    </section>


    <!-- SECCIÓN DE CRONOGRAMA -->
    <section class="events-section events-timeline-section">
        <div class="events-section-header">
            <div>
                <h3>📅 Próximos eventos (Cronograma)</h3>
                <p>Todas las actividades ordenadas por fecha para planificar tu participación</p>
            </div>
        </div>

        @if($agenda->isEmpty())
            <div class="events-empty">
                <i class="bi bi-calendar-event"></i>
                <p>No hay eventos programados en este momento</p>
                <small>La agenda se actualizará con nuevas actividades próximamente</small>
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
                                <span class="events-badge-category">
                                    <i class="bi bi-bookmark"></i>
                                    {{ $item->categoria->nombre ?? 'Actividad' }}
                                </span>
                                @if($item->modalidad)
                                    <span class="events-badge-modality">
                                        <i class="bi bi-broadcast"></i>
                                        {{ $item->modalidad }}
                                    </span>
                                @endif
                            </div>

                            <h5 class="events-agenda-title">{{ $item->titulo }}</h5>
                            <p class="events-agenda-description">{{ $item->resumen ?: \Illuminate\Support\Str::limit(strip_tags((string) $item->contenido), 140) }}</p>

                            <div class="events-agenda-details">
                                @if($item->hora_inicio)
                                    <span class="events-detail-item">
                                        <i class="bi bi-clock-history"></i>
                                        {{ \Carbon\Carbon::parse($item->hora_inicio)->format('H:i') }}@if($item->hora_fin) - {{ \Carbon\Carbon::parse($item->hora_fin)->format('H:i') }}@endif
                                    </span>
                                @endif
                                @if($item->lugar)
                                    <span class="events-detail-item">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ $item->lugar }}
                                    </span>
                                @endif
                                @if($item->referencia)
                                    <span class="events-detail-item">
                                        <i class="bi bi-link-45deg"></i>
                                        <a href="{{ $item->referencia }}" target="_blank">Más información</a>
                                    </span>
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
