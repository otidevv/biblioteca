@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Eventos y actividades')
@section('meta_description', 'Agenda de talleres, concursos y actividades de la Biblioteca UNAMAD.')

@section('css')
<link href="{{ asset('css/pagina/panel_lector.css') }}?v={{ filemtime(public_path('css/pagina/panel_lector.css')) }}" rel="stylesheet">
<link href="{{ asset('css/pagina/mi_espacio.css') }}?v={{ filemtime(public_path('css/pagina/mi_espacio.css')) }}" rel="stylesheet">
<link href="{{ asset('css/pagina/eventos.css') }}?v={{ filemtime(public_path('css/pagina/eventos.css')) }}" rel="stylesheet">
@endsection

@section('js')
<script src="{{ asset('js/pagina/eventos.js') }}?v={{ filemtime(public_path('js/pagina/eventos.js')) }}"></script>
@endsection

@section('content')
@php
    // Solo este bloque php de apertura/cierre: mas abajo se usa php en linea y Blade confundiria ambos.
    $enCurso = $vigentes->filter(fn ($a) => $a->estadoAgenda() === 'en_curso');
    $esteMes = $vigentes->filter(fn ($a) => $a->fecha_inicio?->isSameMonth(now()));
    $porMes = $vigentes->groupBy(fn ($a) => $a->fecha_inicio?->format('Y-m') ?? 'sin-fecha');
    $conteoCategoria = $vigentes->countBy('actividad_categoria_id');
    $modalidades = $vigentes->pluck('modalidad')->filter()->map(fn ($m) => trim($m))->unique()->sort()->values();
    $mesTitulo = fn ($a) => \Illuminate\Support\Str::ucfirst($a->fecha_inicio->locale('es')->translatedFormat('F Y'));
    $resumenDe = fn ($a) => $a->resumen ?: \Illuminate\Support\Str::limit(strip_tags((string) $a->contenido), 170);
    $todas = $vigentes->concat($pasados);
@endphp

<div class="lp ev">
    {{-- ─────────── Portada ─────────── --}}
    <section class="ev-hero">
        <div class="ev-hero__intro">
            <span class="lp-kicker"><i class="bi bi-stars"></i> Agenda de la biblioteca</span>
            <h1 class="lp-title">Eventos y actividades</h1>
            <p class="lp-lead">Talleres, concursos y encuentros abiertos a la comunidad universitaria. Guarda los que te interesen en tu calendario.</p>

            <dl class="ev-stats">
                <div><dt>Próximas</dt><dd>{{ $vigentes->count() }}</dd></div>
                <div><dt>Este mes</dt><dd>{{ $esteMes->count() }}</dd></div>
                <div><dt>En curso hoy</dt><dd>{{ $enCurso->count() }}</dd></div>
            </dl>
        </div>

        @if($destacado)
            <article class="ev-feature" id="evento-destacado">
                <div class="ev-feature__media">
                    @if($destacado->imagen_url)
                        <img src="{{ $destacado->imagen_url }}" alt="">
                    @else
                        <span class="ev-art" aria-hidden="true"><i class="bi bi-calendar2-heart"></i></span>
                    @endif
                    <span class="ev-date">
                        <strong>{{ $destacado->fecha_inicio?->format('d') }}</strong>
                        <span>{{ $destacado->mesCorto() }}</span>
                    </span>
                </div>
                <div class="ev-feature__body">
                    <div class="ev-chips">
                        @if($destacado->estadoAgenda() === 'en_curso')
                            <span class="ev-chip ev-chip--live"><i class="bi bi-broadcast"></i>En curso</span>
                        @endif
                        @if($destacado->destacado)
                            <span class="ev-chip ev-chip--star"><i class="bi bi-star-fill"></i>Destacado</span>
                        @endif
                        <span class="ev-chip">{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($destacado->categoria?->nombre ?? 'Actividad')) }}</span>
                    </div>
                    <h2 class="ev-feature__title">{{ $destacado->titulo }}</h2>
                    <p class="ev-feature__text">{{ $resumenDe($destacado) }}</p>
                    <ul class="ev-meta">
                        <li><i class="bi bi-calendar3"></i>{{ $destacado->rangoFechas() }}</li>
                        @if($destacado->horario())<li><i class="bi bi-clock"></i>{{ $destacado->horario() }}</li>@endif
                        @if($destacado->lugar)<li><i class="bi bi-geo-alt"></i>{{ $destacado->lugar }}</li>@endif
                        @if($destacado->modalidad)<li><i class="bi bi-display"></i>{{ $destacado->modalidad }}</li>@endif
                    </ul>
                    <div class="ev-actions">
                        <button type="button" class="lp-btn lp-btn--primary" data-ev-detalle="{{ $destacado->id }}"><i class="bi bi-info-circle"></i> Ver detalles</button>
                        <a href="{{ route('evento.calendario', $destacado) }}" class="lp-btn lp-btn--ghost"><i class="bi bi-calendar-plus"></i> Agregar a mi calendario</a>
                    </div>
                </div>
            </article>
        @else
            <div class="ev-feature ev-feature--empty">
                <span class="ev-art" aria-hidden="true"><i class="bi bi-calendar2-week"></i></span>
                <div>
                    <h2 class="ev-feature__title">Pronto publicaremos nuevas actividades</h2>
                    <p class="ev-feature__text">Mientras tanto, explora el catálogo o revisa las actividades anteriores.</p>
                    <a href="{{ route('catalogo') }}" class="lp-btn lp-btn--primary"><i class="bi bi-search"></i> Explorar el catálogo</a>
                </div>
            </div>
        @endif
    </section>

    {{-- ─────────── Agenda ─────────── --}}
    <section class="lp-panel" data-ev-agenda>
        <header class="lp-panel__head">
            <div>
                <h2>Próximas actividades</h2>
                <p>{{ $vigentes->count() ? 'Ordenadas por fecha. Toca una para ver el detalle.' : 'No hay actividades programadas por ahora.' }}</p>
            </div>
        </header>

        @if($vigentes->isNotEmpty())
            <div class="me-toolbar ev-toolbar">
                <div class="me-filters" role="group" aria-label="Filtrar por categoría">
                    <button type="button" class="me-filter is-active" data-ev-categoria="" aria-pressed="true">Todas <span>{{ $vigentes->count() }}</span></button>
                    @foreach($categorias as $categoria)
                        @if($conteoCategoria->get($categoria->id))
                            <button type="button" class="me-filter" data-ev-categoria="{{ $categoria->id }}" aria-pressed="false">
                                {{ \Illuminate\Support\Str::ucfirst(mb_strtolower($categoria->nombre)) }} <span>{{ $conteoCategoria->get($categoria->id) }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
                <div class="ev-toolbar__end">
                    @if($modalidades->count() > 1)
                        <label class="ev-select">
                            <span class="visually-hidden">Modalidad</span>
                            <select data-ev-modalidad>
                                <option value="">Todas las modalidades</option>
                                @foreach($modalidades as $modalidad)
                                    <option value="{{ mb_strtolower($modalidad) }}">{{ $modalidad }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <label class="me-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <span class="visually-hidden">Buscar actividades</span>
                        <input type="search" placeholder="Buscar por título o lugar" data-ev-buscar>
                    </label>
                </div>
            </div>

            @foreach($porMes as $clave => $items)
                <div class="ev-month" data-ev-grupo>
                    <h3 class="me-group__title">{{ $clave === 'sin-fecha' ? 'Sin fecha' : $mesTitulo($items->first()) }} <span>{{ $items->count() }}</span></h3>
                    <div class="ev-grid">
                        @foreach($items as $actividad)
                            @php($agenda = $actividad->estadoAgenda())
                            <article class="ev-card" id="evento-{{ $actividad->id }}"
                                     data-ev-cat="{{ $actividad->actividad_categoria_id }}"
                                     data-ev-mod="{{ mb_strtolower(trim((string) $actividad->modalidad)) }}"
                                     data-ev-texto="{{ mb_strtolower($actividad->titulo . ' ' . $actividad->lugar . ' ' . $actividad->resumen . ' ' . ($actividad->categoria?->nombre)) }}">
                                <button type="button" class="ev-card__media" data-ev-detalle="{{ $actividad->id }}" aria-label="Ver detalles de {{ $actividad->titulo }}">
                                    @if($actividad->imagen_url)
                                        <img src="{{ $actividad->imagen_url }}" alt="" loading="lazy">
                                    @else
                                        <span class="ev-art" aria-hidden="true"><i class="bi bi-calendar2-event"></i></span>
                                    @endif
                                    <span class="ev-date">
                                        <strong>{{ $actividad->fecha_inicio?->format('d') }}</strong>
                                        <span>{{ $actividad->mesCorto() }}</span>
                                    </span>
                                </button>
                                <div class="ev-card__body">
                                    <div class="ev-chips">
                                        @if($agenda === 'en_curso')
                                            <span class="ev-chip ev-chip--live"><i class="bi bi-broadcast"></i>En curso</span>
                                        @endif
                                        @if($actividad->destacado)
                                            <span class="ev-chip ev-chip--star"><i class="bi bi-star-fill"></i>Destacado</span>
                                        @endif
                                        <span class="ev-chip">{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($actividad->categoria?->nombre ?? 'Actividad')) }}</span>
                                    </div>
                                    <h4 class="ev-card__title">{{ $actividad->titulo }}</h4>
                                    <p class="ev-card__text">{{ $resumenDe($actividad) }}</p>
                                    <ul class="ev-meta">
                                        <li><i class="bi bi-calendar3"></i>{{ $actividad->rangoFechas() }}</li>
                                        @if($actividad->horario())<li><i class="bi bi-clock"></i>{{ $actividad->horario() }}</li>@endif
                                        @if($actividad->lugar)<li><i class="bi bi-geo-alt"></i>{{ $actividad->lugar }}</li>@endif
                                        @if($actividad->modalidad)<li><i class="bi bi-display"></i>{{ $actividad->modalidad }}</li>@endif
                                    </ul>
                                    <div class="ev-actions">
                                        <button type="button" class="lp-link ev-link" data-ev-detalle="{{ $actividad->id }}">Ver detalles <i class="bi bi-arrow-right"></i></button>
                                        <a href="{{ route('evento.calendario', $actividad) }}" class="ev-icon-btn" title="Agregar a mi calendario" aria-label="Agregar {{ $actividad->titulo }} a mi calendario"><i class="bi bi-calendar-plus"></i></a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="lp-empty lp-empty--compact me-noresults" data-ev-sin-resultados hidden>
                <i class="bi bi-funnel"></i>
                <p>No hay actividades con ese filtro o búsqueda.</p>
            </div>
        @else
            <div class="lp-empty">
                <i class="bi bi-calendar2-week"></i>
                <p>Cuando la biblioteca publique talleres o concursos aparecerán aquí.</p>
            </div>
        @endif
    </section>

    {{-- ─────────── Anteriores ─────────── --}}
    @if($pasados->isNotEmpty())
        <section class="lp-panel">
            <header class="lp-panel__head">
                <div>
                    <h2>Actividades anteriores</h2>
                    <p>Lo que se realizó recientemente en la biblioteca.</p>
                </div>
            </header>
            <div class="ev-past">
                @foreach($pasados as $actividad)
                    <button type="button" class="ev-past__item" id="evento-{{ $actividad->id }}" data-ev-detalle="{{ $actividad->id }}">
                        <span class="ev-date ev-date--small">
                            <strong>{{ $actividad->fecha_inicio?->format('d') }}</strong>
                            <span>{{ $actividad->mesCorto() }}</span>
                        </span>
                        <span class="ev-past__body">
                            <strong>{{ $actividad->titulo }}</strong>
                            <small>{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($actividad->categoria?->nombre ?? 'Actividad')) }} · {{ $actividad->rangoFechas() }}</small>
                        </span>
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </button>
                @endforeach
            </div>
        </section>
    @endif
</div>

{{-- Detalle de cada actividad: el JS lo copia al modal. El contenido se escapa en el servidor. --}}
@foreach($todas as $actividad)
    <template id="ev-detalle-{{ $actividad->id }}">
        @if($actividad->imagen_url)
            <img src="{{ $actividad->imagen_url }}" alt="" class="ev-modal__image">
        @endif
        <div class="ev-modal__content">
            <div class="ev-chips">
                @if($actividad->estadoAgenda() === 'en_curso')<span class="ev-chip ev-chip--live"><i class="bi bi-broadcast"></i>En curso</span>@endif
                @if($actividad->estadoAgenda() === 'finalizada')<span class="ev-chip">Finalizada</span>@endif
                <span class="ev-chip">{{ \Illuminate\Support\Str::ucfirst(mb_strtolower($actividad->categoria?->nombre ?? 'Actividad')) }}</span>
            </div>
            <h2 class="ev-modal__title">{{ $actividad->titulo }}</h2>
            <ul class="ev-meta ev-meta--block">
                <li><i class="bi bi-calendar3"></i>{{ $actividad->rangoFechas() }}</li>
                @if($actividad->horario())<li><i class="bi bi-clock"></i>{{ $actividad->horario() }}</li>@endif
                @if($actividad->lugar)<li><i class="bi bi-geo-alt"></i>{{ $actividad->lugar }}</li>@endif
                @if($actividad->modalidad)<li><i class="bi bi-display"></i>{{ $actividad->modalidad }}</li>@endif
                @if($actividad->referencia)
                    <li><i class="bi bi-link-45deg"></i>
                        @if($actividad->referencia_url)
                            <a href="{{ $actividad->referencia_url }}" target="_blank" rel="noopener noreferrer">Más información</a>
                        @else
                            {{ $actividad->referencia }}
                        @endif
                    </li>
                @endif
            </ul>
            @if($actividad->resumen)<p class="ev-modal__lead">{{ $actividad->resumen }}</p>@endif
            @if($actividad->contenido)<div class="ev-modal__text">{!! nl2br(e($actividad->contenido)) !!}</div>@endif
            @if($actividad->estadoAgenda() !== 'finalizada')
                <div class="ev-actions">
                    <a href="{{ route('evento.calendario', $actividad) }}" class="lp-btn lp-btn--primary"><i class="bi bi-calendar-plus"></i> Agregar a mi calendario</a>
                </div>
            @endif
        </div>
    </template>
@endforeach
@endsection

@section('modal')
<div class="modal fade" id="evModal" tabindex="-1" aria-label="Detalle de la actividad" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ev-modal">
            <button type="button" class="btn-close ev-modal__close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            <div class="modal-body p-0" data-ev-modal-cuerpo></div>
        </div>
    </div>
</div>
@endsection
