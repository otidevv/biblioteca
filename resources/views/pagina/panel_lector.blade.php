@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Mi panel')

@section('css')
<link href="{{ asset('css/pagina/panel_lector.css') }}?v={{ filemtime(public_path('css/pagina/panel_lector.css')) }}" rel="stylesheet">
@endsection

@section('js')
<script src="{{ asset('js/pagina/panel_lector.js') }}?v={{ filemtime(public_path('js/pagina/panel_lector.js')) }}"></script>
@endsection

@section('content')
@php
    $persona = $user->persona;
    $primerNombre = Str::ucfirst(mb_strtolower(Str::before(trim($persona?->nombres ?: $user->name), ' ') ?: $user->name));
    $nombreCompleto = Str::title(mb_strtolower($user->name));
    $carnetDuplicado = $persona?->tieneCarnetDuplicado();
    $placeholder = asset('img/libro-placeholder.png');
    $conHora = fn ($fecha) => $fecha->format($fecha->format('H:i:s') === '00:00:00' ? 'd/m/Y' : 'd/m/Y H:i');

    $e = $estadisticas;
    $nivel = $e['nivel'];
    $siguiente = $e['siguiente'];
    $niveles = \App\Services\EstadisticasLectorService::NIVELES;

    // Grafico mensual: eje con 0, mitad y tope; se rotulan solo el maximo y el mes actual.
    $serie = $e['serie_mensual'];
    $valoresSerie = array_column($serie, 'total');
    $maxMes = max($valoresSerie);
    $tope = max(2, (int) (ceil($maxMes / 2) * 2));
    $indiceMax = $maxMes > 0 ? array_search($maxMes, $valoresSerie, true) : null;
    $maxMateria = collect($e['materias'])->max('total') ?: 1;
    // Tarjeta de imagen (Exportar reporte > Imagen PNG).
    // Nota: no agregar otro bloque php de apertura/cierre mas abajo; la vista usa php en linea
    // dentro de los bucles y Blade confundiria ambos.
    $maxMesImagen = max($maxMes, 1);
    $mesAnio = fn ($f) => Str::ucfirst($f->locale('es')->translatedFormat('F Y'));

    $hayAvisos = $sanciones->isNotEmpty() || $fueraDePlazo->isNotEmpty() || $carnetDuplicado;
@endphp

<div class="lp">

    {{-- ─────────── Encabezado + carnet digital ─────────── --}}
    <section class="lp-hero">
        <div class="lp-hero__intro">
            <span class="lp-kicker"><i class="bi bi-speedometer2"></i> Mi panel de lector</span>
            <h1 class="lp-title">Hola, {{ $primerNombre }}</h1>
            <p class="lp-lead">
                Tu actividad en la Biblioteca UNAMAD: lo que tienes en préstamo, lo que te espera
                y cómo va tu historial como lector.
            </p>

            <div class="lp-level-pill lp-level-pill--{{ $nivel['clave'] }}">
                <i class="bi bi-award-fill" aria-hidden="true"></i>
                <span>
                    <strong>{{ $nivel['nombre'] }}</strong>
                    <small>según tus últimos {{ $e['periodo']['meses'] }} meses</small>
                </span>
            </div>

            <div class="lp-actions">
                @if($e['puede_constancia'])
                    <form method="POST" action="{{ route('lector.constancia.emitir') }}" target="_blank">
                        @csrf
                        <button type="submit" class="lp-btn lp-btn--primary">
                            <i class="bi bi-file-earmark-check"></i> Descargar constancia
                        </button>
                    </form>
                @else
                    <a href="#lp-nivel" class="lp-btn lp-btn--primary">
                        <i class="bi bi-graph-up-arrow"></i> Ver mi progreso
                    </a>
                @endif
                <div class="dropdown">
                    <button class="lp-btn lp-btn--ghost dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-box-arrow-down"></i> Exportar reporte
                    </button>
                    <ul class="dropdown-menu lp-export-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('lector.reporte') }}" target="_blank" rel="noopener">
                                <i class="bi bi-filetype-pdf" aria-hidden="true"></i>
                                <span><strong>Documento PDF</strong><small>Para imprimir o adjuntar</small></span>
                            </a>
                        </li>
                        <li>
                            <button class="dropdown-item" type="button" data-lp-exportar-imagen>
                                <i class="bi bi-image" aria-hidden="true"></i>
                                <span><strong>Imagen PNG</strong><small>Para compartir</small></span>
                            </button>
                        </li>
                    </ul>
                </div>
                <a href="{{ route('catalogo') }}" class="lp-btn lp-btn--ghost">
                    <i class="bi bi-search"></i> Explorar catálogo
                </a>
            </div>
            <p class="lp-export-status" role="status" aria-live="polite" data-lp-export-estado></p>
        </div>

        <article class="lp-card" aria-label="Carnet digital de lector">
            <div class="lp-card__top">
                <img src="{{ asset('img/logo_unamad.png') }}" alt="" class="lp-card__logo">
                <div>
                    <strong>Biblioteca UNAMAD</strong>
                    <small>Carnet de lector</small>
                </div>
                @if($persona?->tipo_carnet)
                    <span class="lp-card__chip {{ $carnetDuplicado ? 'is-warning' : '' }}">
                        {{ $carnetDuplicado ? 'Duplicado' : 'Original' }}
                    </span>
                @endif
            </div>

            <div class="lp-card__name">{{ $nombreCompleto }}</div>
            <div class="lp-card__career">{{ $persona?->carrera?->nombre ? Str::title(mb_strtolower($persona->carrera->nombre)) : 'Lector de la biblioteca' }}</div>

            <dl class="lp-card__grid">
                <div>
                    <dt>Código</dt>
                    <dd>{{ $persona?->codigo_institucional ?: '—' }}</dd>
                </div>
                <div>
                    <dt>DNI</dt>
                    <dd>{{ $persona?->dni ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Miembro desde</dt>
                    <dd>{{ $e['miembro_desde'] ? Str::ucfirst($e['miembro_desde']->locale('es')->translatedFormat('F Y')) : '—' }}</dd>
                </div>
                <div>
                    <dt>Nivel</dt>
                    <dd>{{ $nivel['nombre'] }}</dd>
                </div>
            </dl>
        </article>
    </section>

    @if(session('panel_error'))
        <div class="lp-alert lp-alert--warning" role="alert">
            <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
            <div><strong>Constancia no disponible</strong><span>{{ session('panel_error') }}</span></div>
        </div>
    @endif

    {{-- ─────────── Avisos ─────────── --}}
    @if($hayAvisos)
        <section class="lp-alerts" aria-label="Avisos">
            @foreach($sanciones as $sancion)
                <div class="lp-alert lp-alert--critical" role="alert">
                    <i class="bi bi-slash-circle-fill" aria-hidden="true"></i>
                    <div>
                        <strong>Sanción vigente hasta el {{ $sancion->fecha_fin->format('d/m/Y') }}</strong>
                        <span>{{ rtrim((string) $sancion->motivo, '. ') }}. No puedes recibir préstamos ni hacer reservas en {{ $sancion->biblioteca?->nombre ?? 'ninguna biblioteca' }} mientras esté vigente.</span>
                    </div>
                </div>
            @endforeach

            @if($fueraDePlazo->isNotEmpty())
                <div class="lp-alert lp-alert--critical" role="alert">
                    <i class="bi bi-exclamation-octagon-fill" aria-hidden="true"></i>
                    <div>
                        <strong>Tienes {{ $fueraDePlazo->count() }} préstamo{{ $fueraDePlazo->count() === 1 ? '' : 's' }} fuera de plazo</strong>
                        <span>Devuélvelo{{ $fueraDePlazo->count() === 1 ? '' : 's' }} cuanto antes para evitar una sanción.</span>
                    </div>
                </div>
            @endif

            @if($carnetDuplicado)
                <div class="lp-alert lp-alert--warning">
                    <i class="bi bi-credit-card-2-front-fill" aria-hidden="true"></i>
                    <div>
                        <strong>Tu carnet vigente es un duplicado{{ $persona->fecha_carnet_duplicado ? ' (desde el ' . $persona->fecha_carnet_duplicado->format('d/m/Y') . ')' : '' }}</strong>
                        <span>El carnet original quedó anulado. Presenta siempre el duplicado; si encuentras el original, entrégalo en la biblioteca.</span>
                    </div>
                </div>
            @endif
        </section>
    @endif

    {{-- ─────────── Indicadores ─────────── --}}
    <section class="lp-kpis" aria-label="Resumen de lectura">
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-journal-bookmark-fill"></i></span>
            <span class="lp-kpi__label">Préstamos realizados</span>
            <strong class="lp-kpi__value">{{ $e['total_prestamos'] }}</strong>
            <span class="lp-kpi__meta">{{ $e['prestamos_periodo'] }} en los últimos {{ $e['periodo']['meses'] }} meses</span>
        </div>
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-book-half"></i></span>
            <span class="lp-kpi__label">Libros distintos</span>
            <strong class="lp-kpi__value">{{ $e['libros_distintos'] }}</strong>
            <span class="lp-kpi__meta">{{ count($e['bibliotecas']) }} biblioteca{{ count($e['bibliotecas']) === 1 ? '' : 's' }} visitada{{ count($e['bibliotecas']) === 1 ? '' : 's' }}</span>
        </div>
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-calendar2-check-fill"></i></span>
            <span class="lp-kpi__label">Meses con actividad</span>
            <strong class="lp-kpi__value">{{ $e['meses_activos'] }}<small> de {{ $e['periodo']['meses'] }}</small></strong>
            <span class="lp-kpi__meta">Constancia en tu lectura</span>
        </div>
        <div class="lp-kpi">
            <span class="lp-kpi__icon"><i class="bi bi-stopwatch-fill"></i></span>
            <span class="lp-kpi__label">Puntualidad</span>
            <strong class="lp-kpi__value">{{ $e['puntualidad'] !== null ? $e['puntualidad'] . '%' : '—' }}</strong>
            <span class="lp-kpi__meta">{{ $e['devueltos'] > 0 ? 'de ' . $e['devueltos'] . ' devoluciones a tiempo' : 'Aún sin devoluciones' }}</span>
        </div>
    </section>

    <div class="lp-grid">
        <div class="lp-col">

            {{-- ─────────── Prestamos en curso ─────────── --}}
            <section class="lp-panel">
                <header class="lp-panel__head">
                    <div>
                        <h2>Préstamos en curso</h2>
                        <p>{{ $prestamosActivos->count() ? 'Devuélvelos antes de la fecha límite.' : 'No tienes libros en préstamo.' }}</p>
                    </div>
                    <a href="{{ route('prestamos') }}" class="lp-link">Historial <i class="bi bi-arrow-right"></i></a>
                </header>

                @forelse($prestamosActivos as $prestamo)
                    @php($enSala = (int) $prestamo->prestamo_lugar === \App\Models\Prestamo::EN_SALA)
                    <article class="lp-row">
                        <img src="{{ $prestamo->ejemplar->libro->imagen_url ?? $placeholder }}" alt="" class="lp-row__cover"
                             onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        <div class="lp-row__body">
                            <div class="lp-row__title">{{ $prestamo->ejemplar->libro->titulo ?? 'Libro no disponible' }}</div>
                            <div class="lp-row__meta">
                                <span class="lp-tag"><i class="bi {{ $enSala ? 'bi-building' : 'bi-house-door' }}"></i>{{ $enSala ? 'En sala' : 'A casa' }}</span>
                                <span>{{ $prestamo->ejemplar->biblioteca->nombre ?? '-' }}</span>
                            </div>
                            <div class="lp-row__date">Devolver hasta el {{ $prestamo->fecha_limite_real->format('d/m/Y') }} a las 20:00</div>
                        </div>
                        <span class="lp-countdown" data-fecha="{{ $prestamo->fecha_limite_real->format('Y-m-d\TH:i:s') }}"></span>
                    </article>
                @empty
                    <div class="lp-empty">
                        <i class="bi bi-journal-check"></i>
                        <p>No tienes préstamos en curso.</p>
                        <a href="{{ route('catalogo') }}" class="lp-link">Buscar un libro <i class="bi bi-arrow-right"></i></a>
                    </div>
                @endforelse
            </section>

            {{-- ─────────── Reservas ─────────── --}}
            <section class="lp-panel">
                <header class="lp-panel__head">
                    <div>
                        <h2>Reservas por recoger</h2>
                        <p>{{ $reservasPendientes->count() ? 'Recógelas en la biblioteca antes de la hora límite.' : 'No tienes reservas pendientes.' }}</p>
                    </div>
                    <a href="{{ route('mis.reservas') }}" class="lp-link">Mis reservas <i class="bi bi-arrow-right"></i></a>
                </header>

                @forelse($reservasPendientes as $reserva)
                    @php($aCasa = (int) $reserva->prestamo === 1)
                    <article class="lp-row">
                        <img src="{{ $reserva->ejemplar->libro->imagen_url ?? $placeholder }}" alt="" class="lp-row__cover"
                             onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        <div class="lp-row__body">
                            <div class="lp-row__title">{{ $reserva->ejemplar->libro->titulo ?? 'Libro no disponible' }}</div>
                            <div class="lp-row__meta">
                                <span class="lp-tag"><i class="bi {{ $aCasa ? 'bi-house-door' : 'bi-building' }}"></i>{{ $aCasa ? 'A casa' : 'En sala' }}</span>
                                <span>{{ $reserva->ejemplar->biblioteca->nombre ?? '-' }}</span>
                            </div>
                            <div class="lp-row__date">Recógela hasta el {{ $reserva->fecha_limite_real->format('d/m/Y') }} a las 20:00</div>
                        </div>
                        <span class="lp-countdown" data-fecha="{{ $reserva->fecha_limite_real->format('Y-m-d\TH:i:s') }}"></span>
                    </article>
                @empty
                    <div class="lp-empty">
                        <i class="bi bi-bookmark"></i>
                        <p>No tienes reservas pendientes.</p>
                        <a href="{{ route('catalogo') }}" class="lp-link">Reservar desde el catálogo <i class="bi bi-arrow-right"></i></a>
                    </div>
                @endforelse
            </section>

            {{-- ─────────── Actividad mensual ─────────── --}}
            <section class="lp-panel">
                <header class="lp-panel__head">
                    <div>
                        <h2>Préstamos por mes</h2>
                        <p>Últimos {{ $e['periodo']['meses'] }} meses, incluido el actual.</p>
                    </div>
                </header>

                <div class="lp-chart" data-lp-chart>
                    <div class="lp-chart__plot">
                        <div class="lp-chart__gridline" style="bottom:100%"><span>{{ $tope }}</span></div>
                        <div class="lp-chart__gridline" style="bottom:50%"><span>{{ $tope / 2 }}</span></div>
                        <div class="lp-chart__gridline lp-chart__gridline--base" style="bottom:0"><span>0</span></div>

                        <div class="lp-chart__cols" role="list" aria-label="Préstamos por mes">
                            @foreach($serie as $i => $mes)
                                @php($etiquetar = $i === $indiceMax || $mes['actual'])
                                <div class="lp-chart__col {{ $mes['actual'] ? 'is-current' : '' }}" role="listitem" tabindex="0"
                                     data-label="{{ Str::ucfirst($mes['largo']) }}" data-value="{{ $mes['total'] }}"
                                     aria-label="{{ Str::ucfirst($mes['largo']) }}: {{ $mes['total'] }} préstamo{{ $mes['total'] === 1 ? '' : 's' }}">
                                    <span class="lp-chart__bar" style="height: {{ round($mes['total'] / $tope * 100, 2) }}%">
                                        @if($etiquetar)
                                            <span class="lp-chart__value">{{ $mes['total'] }}</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="lp-chart__axis" aria-hidden="true">
                        @foreach($serie as $mes)
                            <span class="{{ $mes['actual'] ? 'is-current' : '' }}">{{ $mes['corto'] }}</span>
                        @endforeach
                    </div>
                    <div class="lp-chart__tooltip" role="tooltip" hidden></div>
                </div>

                <details class="lp-table">
                    <summary>Ver como tabla</summary>
                    <table>
                        <thead><tr><th scope="col">Mes</th><th scope="col">Préstamos</th></tr></thead>
                        <tbody>
                            @foreach($serie as $mes)
                                <tr><td>{{ Str::ucfirst($mes['largo']) }}</td><td>{{ $mes['total'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </details>
            </section>
        </div>

        <aside class="lp-col">

            {{-- ─────────── Nivel y constancia ─────────── --}}
            <section class="lp-panel lp-level" id="lp-nivel">
                <header class="lp-panel__head">
                    <div>
                        <h2>Tu nivel de lector</h2>
                        <p>Se calcula con tus préstamos de los últimos {{ $e['periodo']['meses'] }} meses.</p>
                    </div>
                </header>

                <ol class="lp-ladder" aria-label="Niveles de lector">
                    @foreach($niveles as $clave => $regla)
                        @php($alcanzado = array_search($clave, array_keys($niveles)) <= array_search($nivel['clave'], array_keys($niveles)))
                        <li class="{{ $alcanzado ? 'is-reached' : '' }} {{ $clave === $nivel['clave'] ? 'is-current' : '' }}">
                            <span class="lp-ladder__dot" aria-hidden="true"></span>
                            <span class="lp-ladder__name">{{ Str::ucfirst(Str::after($regla['nombre'], 'Lector ')) }}</span>
                            @if($clave === $nivel['clave'])<span class="visually-hidden">(tu nivel actual)</span>@endif
                        </li>
                    @endforeach
                </ol>

                @if($siguiente)
                    <div class="lp-meter">
                        <div class="lp-meter__head">
                            <span>Hacia <strong>{{ $siguiente['nombre'] }}</strong></span>
                            <span>{{ $siguiente['progreso'] }}%</span>
                        </div>
                        <div class="lp-meter__track" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                             aria-valuenow="{{ $siguiente['progreso'] }}" aria-label="Progreso hacia {{ $siguiente['nombre'] }}">
                            <span class="lp-meter__fill" style="width: {{ $siguiente['progreso'] }}%"></span>
                        </div>
                    </div>

                    <ul class="lp-criteria">
                        <li class="{{ $siguiente['faltan_prestamos'] === 0 ? 'is-done' : '' }}">
                            <i class="bi {{ $siguiente['faltan_prestamos'] === 0 ? 'bi-check-circle-fill' : 'bi-circle' }}" aria-hidden="true"></i>
                            <span>
                                {{ $siguiente['prestamos'] }} préstamos en {{ $e['periodo']['meses'] }} meses
                                <small>Llevas {{ $e['prestamos_periodo'] }}{{ $siguiente['faltan_prestamos'] ? ' · te faltan ' . $siguiente['faltan_prestamos'] : '' }}</small>
                            </span>
                        </li>
                        <li class="{{ $siguiente['faltan_meses'] === 0 ? 'is-done' : '' }}">
                            <i class="bi {{ $siguiente['faltan_meses'] === 0 ? 'bi-check-circle-fill' : 'bi-circle' }}" aria-hidden="true"></i>
                            <span>
                                Actividad en {{ $siguiente['meses'] }} meses distintos
                                <small>Llevas {{ $e['meses_activos'] }}{{ $siguiente['faltan_meses'] ? ' · te faltan ' . $siguiente['faltan_meses'] : '' }}</small>
                            </span>
                        </li>
                    </ul>
                @else
                    <p class="lp-level__top"><i class="bi bi-stars" aria-hidden="true"></i> Alcanzaste el nivel más alto. ¡Gracias por leer con nosotros!</p>
                @endif

                <div class="lp-certificate {{ $e['puede_constancia'] ? 'is-available' : '' }}">
                    <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
                    <div>
                        @if($e['puede_constancia'])
                            <strong>Constancia de {{ mb_strtolower($nivel['nombre']) }}</strong>
                            <span>Documento verificable con código, útil para becas, prácticas o trámites. Vigente {{ \App\Services\EstadisticasLectorService::DIAS_VIGENCIA_CONSTANCIA }} días.</span>
                            <form method="POST" action="{{ route('lector.constancia.emitir') }}" target="_blank">
                                @csrf
                                <button type="submit" class="lp-btn lp-btn--primary lp-btn--block">
                                    <i class="bi bi-download"></i> Descargar constancia (PDF)
                                </button>
                            </form>
                        @else
                            <strong>Constancia de lector frecuente</strong>
                            <span>Se habilita al llegar a <em>Lector frecuente</em>: {{ $niveles['frecuente']['prestamos'] }} préstamos con actividad en {{ $niveles['frecuente']['meses'] }} meses distintos.</span>
                        @endif
                    </div>
                </div>
            </section>

            {{-- ─────────── Que lee ─────────── --}}
            <section class="lp-panel">
                <header class="lp-panel__head">
                    <div>
                        <h2>Lo que más lees</h2>
                        <p>Materias de los libros que has pedido.</p>
                    </div>
                </header>

                @if(count($e['materias']))
                    <ul class="lp-bars">
                        @foreach($e['materias'] as $materia)
                            <li>
                                <div class="lp-bars__label">
                                    <span>{{ $materia['nombre'] }}</span>
                                    <strong>{{ $materia['total'] }}</strong>
                                </div>
                                <div class="lp-bars__track"><span style="width: {{ round($materia['total'] / $maxMateria * 100, 2) }}%"></span></div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="lp-empty lp-empty--compact">
                        <p>Cuando pidas libros verás aquí tus materias favoritas.</p>
                    </div>
                @endif

                <dl class="lp-facts">
                    <div><dt>En sala</dt><dd>{{ $e['en_sala'] }}</dd></div>
                    <div><dt>A casa</dt><dd>{{ $e['a_casa'] }}</dd></div>
                    <div><dt>Último préstamo</dt><dd>{{ $e['ultimo_prestamo'] ? $e['ultimo_prestamo']->format('d/m/Y') : '—' }}</dd></div>
                </dl>

                @if(count($e['bibliotecas']))
                    <div class="lp-libraries">
                        @foreach($e['bibliotecas'] as $biblioteca)
                            <span class="lp-tag"><i class="bi bi-bank"></i>{{ Str::title(mb_strtolower($biblioteca)) }}</span>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- ─────────── Ultimas devoluciones ─────────── --}}
            <section class="lp-panel">
                <header class="lp-panel__head">
                    <div>
                        <h2>Últimas devoluciones</h2>
                        <p>{{ $totalPrestamos }} préstamo{{ $totalPrestamos === 1 ? '' : 's' }} en total.</p>
                    </div>
                </header>

                @forelse($recientes as $prestamo)
                    @php($estadoPrestamo = (int) $prestamo->estado_prestamo)
                    <div class="lp-timeline__item">
                        <span class="lp-timeline__dot {{ $estadoPrestamo === 2 ? 'is-late' : ($estadoPrestamo === 3 ? 'is-damaged' : '') }}" aria-hidden="true"></span>
                        <div>
                            <div class="lp-timeline__title">{{ $prestamo->ejemplar->libro->titulo ?? 'Libro no disponible' }}</div>
                            <div class="lp-timeline__meta">
                                Devuelto el {{ $prestamo->fecha_devolucion ? $conHora($prestamo->fecha_devolucion) : '-' }}
                                ·
                                @if($estadoPrestamo === 3)
                                    <span class="lp-status lp-status--damaged">Deterioro registrado</span>
                                @elseif($estadoPrestamo === 2)
                                    <span class="lp-status lp-status--late">Con tardanza</span>
                                @else
                                    <span class="lp-status lp-status--good">A tiempo</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="lp-empty lp-empty--compact">
                        <p>Aún no tienes devoluciones registradas.</p>
                    </div>
                @endforelse
            </section>
        </aside>
    </div>
</div>

{{-- Plantilla para "Exportar reporte > Imagen PNG": fuera de pantalla, siempre en tema claro. --}}
<div class="lp-share" id="lp-share" aria-hidden="true"
     data-archivo="reporte-lectura-{{ $persona?->codigo_institucional ?: $user->id }}-{{ now()->format('Y-m-d') }}.png"
     data-libreria="{{ asset('lib/html2canvas/html2canvas.min.js') }}">
    <div class="lp-share__band"></div>

    <div class="lp-share__head">
        <img src="{{ asset('img/logo_unamad.png') }}" alt="" class="lp-share__logo">
        <div>
            <strong>Biblioteca UNAMAD</strong>
            <span>Reporte de lectura</span>
        </div>
        <time>{{ now()->format('d/m/Y') }}</time>
    </div>

    <div class="lp-share__person">
        <div>
            <div class="lp-share__name">{{ $nombreCompleto }}</div>
            <div class="lp-share__career">
                {{ $persona?->carrera?->nombre ? Str::title(mb_strtolower($persona->carrera->nombre)) : 'Lector de la biblioteca' }}@if($persona?->codigo_institucional) · Código {{ $persona->codigo_institucional }}@endif
            </div>
        </div>
        <div class="lp-share__level {{ $e['puede_constancia'] ? 'is-top' : '' }}">
            <span>Nivel</span>
            <strong>{{ $nivel['nombre'] }}</strong>
        </div>
    </div>

    <div class="lp-share__kpis">
        <div><span>Préstamos realizados</span><strong>{{ $e['total_prestamos'] }}</strong><small>{{ $e['prestamos_periodo'] }} en los últimos {{ $e['periodo']['meses'] }} meses</small></div>
        <div><span>Libros distintos</span><strong>{{ $e['libros_distintos'] }}</strong><small>{{ count($e['bibliotecas']) }} biblioteca{{ count($e['bibliotecas']) === 1 ? '' : 's' }}</small></div>
        <div><span>Meses con actividad</span><strong>{{ $e['meses_activos'] }}<em> de {{ $e['periodo']['meses'] }}</em></strong><small>Constancia en la lectura</small></div>
        <div><span>Puntualidad</span><strong>{{ $e['puntualidad'] !== null ? $e['puntualidad'] . '%' : '—' }}</strong><small>{{ $e['devueltos'] > 0 ? 'Devoluciones a tiempo' : 'Sin devoluciones' }}</small></div>
    </div>

    <div class="lp-share__grid">
        <div class="lp-share__box">
            <h3>Préstamos por mes</h3>
            <div class="lp-share__chart">
                @foreach($serie as $mes)
                    <div class="lp-share__col">
                        <span class="lp-share__val">{{ $mes['total'] }}</span>
                        <span class="lp-share__bar" style="height: {{ $mes['total'] > 0 ? max(4, round($mes['total'] / $maxMesImagen * 190)) : 0 }}px"></span>
                        <span class="lp-share__month {{ $mes['actual'] ? 'is-current' : '' }}">{{ $mes['corto'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lp-share__box">
            <h3>Lo que más lee</h3>
            @forelse($e['materias'] as $materia)
                <div class="lp-share__subject">
                    <div><span>{{ $materia['nombre'] }}</span><strong>{{ $materia['total'] }}</strong></div>
                    <div class="lp-share__track"><span style="width: {{ round($materia['total'] / $maxMateria * 100, 2) }}%"></span></div>
                </div>
            @empty
                <p class="lp-share__empty">Aún sin materias registradas.</p>
            @endforelse
        </div>
    </div>

    <div class="lp-share__foot">
        <span>Periodo evaluado: {{ $mesAnio($e['periodo']['desde']) }} – {{ $mesAnio($e['periodo']['hasta']) }}</span>
        <span>Sistema de Biblioteca UNAMAD</span>
    </div>
</div>
@endsection
