@extends('layouts.admin')

@section('page-title', 'Panel de operación')

@php
    $propio = fn ($texto) => \App\Services\DashboardAdminService::nombrePropio($texto);
    // Con datos personales se saluda por el primer nombre; las cuentas institucionales, por su nombre completo.
    $primerNombre = optional($usuario->persona)->nombres
        ? $propio(\Illuminate\Support\Str::before(trim($usuario->persona->nombres) . ' ', ' '))
        : $propio($usuario->name);
    $num = fn ($n) => number_format((float) $n);

    // Los registros anteriores a guardar la hora quedaron a las 00:00: se muestra solo la fecha.
    $cuando = function ($fecha) {
        if (! $fecha) {
            return '';
        }
        if ($fecha->format('H:i:s') === '00:00:00') {
            return $fecha->isToday() ? 'Hoy' : ($fecha->isYesterday() ? 'Ayer' : $fecha->locale('es')->isoFormat('D MMM'));
        }

        return $fecha->locale('es')->diffForHumans();
    };

    $diaReferencia = today()->subWeek()->locale('es')->isoFormat('ddd');
    $delta = function (int $diferencia, int $referencia) use ($diaReferencia) {
        if ($diferencia === 0 && $referencia === 0) {
            return null;
        }
        if ($diferencia === 0) {
            return ['dsh-delta--igual', 'bi-dash', 'Igual que el ' . $diaReferencia . ' pasado'];
        }

        return $diferencia > 0
            ? ['dsh-delta--sube', 'bi-arrow-up-short', '+' . $diferencia . ' vs. ' . $diaReferencia . ' pasado']
            : ['dsh-delta--baja', 'bi-arrow-down-short', $diferencia . ' vs. ' . $diaReferencia . ' pasado'];
    };

    $enlace = fn (string $permiso, string $ruta) => $puede($permiso) ? url($ruta) : null;
    $multiples = $alcance['ids'] === null || count($alcance['ids']) > 1;

    $graficos = [
        'circulacion' => [
            'etiquetas' => array_column($circulacion['dias'], 'etiqueta'),
            'largas' => array_column($circulacion['dias'], 'etiqueta_larga'),
            'sala' => array_column($circulacion['dias'], 'sala'),
            'casa' => array_column($circulacion['dias'], 'casa'),
        ],
        'visitas' => [
            'etiquetas' => array_column($visitas['serie'], 'etiqueta'),
            'largas' => array_column($visitas['serie'], 'etiqueta_larga'),
            'total' => array_column($visitas['serie'], 'total'),
        ],
    ];

    $kpiTarjetas = [
        [
            'clave' => 'prestamos_hoy', 'titulo' => 'Préstamos hoy', 'icono' => 'bi-box-arrow-up-right', 'tono' => 'marca',
            'valor' => $kpis['prestamos_hoy']['valor'],
            'detalle' => $kpis['prestamos_hoy']['sala'] . ' en sala · ' . $kpis['prestamos_hoy']['casa'] . ' a casa'
                . ($kpis['prestamos_hoy']['libres'] ? ' · ' . $kpis['prestamos_hoy']['libres'] . ' libre' . ($kpis['prestamos_hoy']['libres'] > 1 ? 's' : '') : ''),
            'delta' => $delta($kpis['prestamos_hoy']['delta'], $kpis['prestamos_hoy']['referencia']),
            'url' => $enlace('prestamos.registro', '/prestamos/registro'),
        ],
        [
            'clave' => 'devoluciones_hoy', 'titulo' => 'Devoluciones hoy', 'icono' => 'bi-box-arrow-in-down-left', 'tono' => 'marca',
            'valor' => $kpis['devoluciones_hoy']['valor'],
            'detalle' => $kpis['devoluciones_hoy']['tardanza']
                ? $kpis['devoluciones_hoy']['tardanza'] . ' con tardanza'
                : ($kpis['devoluciones_hoy']['valor'] ? 'Todas dentro del plazo' : 'Sin devoluciones registradas'),
            'delta' => $delta($kpis['devoluciones_hoy']['delta'], $kpis['devoluciones_hoy']['referencia']),
            'url' => $enlace('prestamos.registro', '/prestamos/registro'),
        ],
        [
            'clave' => 'en_circulacion', 'titulo' => 'En circulación', 'icono' => 'bi-arrow-repeat', 'tono' => 'marca',
            'valor' => $kpis['en_circulacion']['valor'],
            'detalle' => $kpis['en_circulacion']['sala'] . ' en sala · ' . $kpis['en_circulacion']['casa'] . ' a casa',
            'delta' => null,
            'url' => $enlace('prestamos.registro', '/prestamos/registro'),
        ],
        [
            'clave' => 'fuera_de_plazo', 'titulo' => 'Fuera de plazo', 'icono' => 'bi-alarm',
            'tono' => $kpis['fuera_de_plazo']['valor'] ? 'critico' : 'ok',
            'valor' => $kpis['fuera_de_plazo']['valor'],
            'detalle' => $kpis['fuera_de_plazo']['valor'] ? 'El más antiguo venció ' . $kpis['fuera_de_plazo']['mas_antiguo'] : 'Todo dentro del plazo',
            'delta' => null,
            'url' => $enlace('prestamos.registro', '/prestamos/registro'),
        ],
        [
            'clave' => 'reservas', 'titulo' => 'Reservas por recoger', 'icono' => 'bi-bookmark-check',
            'tono' => $kpis['reservas']['vencen_hoy'] ? 'alerta' : 'marca',
            'valor' => $kpis['reservas']['valor'],
            'detalle' => $kpis['reservas']['vencen_hoy'] ? $kpis['reservas']['vencen_hoy'] . ' vencen hoy a las 20:00' : ($kpis['reservas']['valor'] ? 'Ninguna vence hoy' : 'Sin reservas pendientes'),
            'delta' => null,
            'url' => $enlace('prestamos.reservas', '/prestamos/reservas'),
        ],
        [
            'clave' => 'sanciones', 'titulo' => 'Sanciones vigentes', 'icono' => 'bi-shield-exclamation', 'tono' => 'marca',
            'valor' => $kpis['sanciones']['valor'],
            'detalle' => $kpis['sanciones']['mes'] . ' registrada' . ($kpis['sanciones']['mes'] === 1 ? '' : 's') . ' este mes',
            'delta' => null,
            'url' => $enlace('prestamos.multas', '/prestamos/multas'),
        ],
    ];

    foreach ($kpiTarjetas as $i => $kpi) {
        $kpiTarjetas[$i]['etiqueta'] = $kpi['url'] ? 'a' : 'div';
    }

    $pendientes = $atencion['vencidos_total'] + $atencion['reservas_total'];
    $semanaMax = max(collect($semana['dias'])->max('total'), 1);
@endphp

@section('css')
    <link href="{{ asset('lib/apexcharts/dist/apexcharts.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/administracion/dashboard.css') }}?v={{ filemtime(public_path('css/administracion/dashboard.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script type="application/json" id="dsh-datos">@json($graficos)</script>
    <script src="{{ asset('lib/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('js/administracion/dashboard.js') }}?v={{ filemtime(public_path('js/administracion/dashboard.js')) }}"></script>
@endsection

@section('content')
<div class="dsh">

    {{-- Encabezado --}}
    <header class="dsh-head">
        <div class="dsh-head__main">
            <div class="dsh-head__chips">
                <span class="dsh-chip dsh-chip--scope"><i class="bi bi-building"></i> {{ $alcance['nombre'] }}</span>
                <span class="dsh-chip {{ $sala['abierta'] ? 'dsh-chip--abierta' : 'dsh-chip--cerrada' }}">
                    <span class="dsh-dot" aria-hidden="true"></span>
                    @if($sala['abierta'])
                        Sala abierta · cierra a las {{ $sala['cierre'] }} (en {{ $sala['restante'] }})
                    @else
                        Atención en sala cerrada desde las {{ $sala['cierre'] }}
                    @endif
                </span>
            </div>
            <h2 class="dsh-head__title">{{ $saludo }}, {{ $primerNombre }}</h2>
            <p class="dsh-head__date">{{ $fechaLarga }}</p>
            @if(! $sala['abierta'] && $sala['en_sala'] > 0)
                <p class="dsh-head__warn">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ $sala['en_sala'] }} {{ $sala['en_sala'] === 1 ? 'préstamo en sala sigue' : 'préstamos en sala siguen' }} sin devolver después del cierre.
                </p>
            @endif
        </div>

        <div class="dsh-head__side">
            @if($alcance['puedeFiltrar'])
                <form method="get" action="{{ route('dashboard') }}" class="dsh-filtro" data-dsh-filtro>
                    <label for="dshBiblioteca" class="dsh-filtro__label">Ver indicadores de</label>
                    <select id="dshBiblioteca" name="biblioteca" class="dsh-filtro__select">
                        <option value="">{{ $alcance['global'] ? 'Todas las bibliotecas' : 'Todas mis bibliotecas' }}</option>
                        @foreach($alcance['visibles'] as $biblioteca)
                            <option value="{{ $biblioteca->id }}" @selected($alcance['seleccionada'] === $biblioteca->id)>{{ $propio($biblioteca->nombre) }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="dsh-btn dsh-btn--ghost">Aplicar</button></noscript>
                </form>
            @endif

            @if(count($acciones))
                <div class="dsh-head__actions">
                    @foreach($acciones as $i => $accion)
                        <a href="{{ $accion['url'] }}" class="dsh-btn {{ $i === 0 ? 'dsh-btn--primary' : 'dsh-btn--ghost' }}">
                            <i class="bi {{ $accion['icono'] }}"></i> {{ $accion['titulo'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </header>

    {{-- Indicadores del dia --}}
    <section class="dsh-kpis" aria-label="Indicadores del día">
        @foreach($kpiTarjetas as $kpi)
            <{{ $kpi['etiqueta'] }} @if($kpi['url']) href="{{ $kpi['url'] }}" @endif class="dsh-kpi dsh-kpi--{{ $kpi['tono'] }}" data-kpi="{{ $kpi['clave'] }}">
                <span class="dsh-kpi__top">
                    <span class="dsh-kpi__icon"><i class="bi {{ $kpi['icono'] }}"></i></span>
                    <span class="dsh-kpi__label">{{ $kpi['titulo'] }}</span>
                </span>
                <span class="dsh-kpi__value">{{ $num($kpi['valor']) }}</span>
                <span class="dsh-kpi__detail">{{ $kpi['detalle'] }}</span>
                @if($kpi['delta'])
                    <span class="dsh-delta {{ $kpi['delta'][0] }}" title="Comparado con el mismo día de la semana pasada"><i class="bi {{ $kpi['delta'][1] }}"></i>{{ $kpi['delta'][2] }}</span>
                @endif
            </{{ $kpi['etiqueta'] }}>
        @endforeach
    </section>

    <div class="dsh-grid">

        {{-- Circulacion --}}
        <article class="dsh-card dsh-span-8">
            <header class="dsh-card__head">
                <div>
                    <span class="dsh-card__eyebrow">Circulación</span>
                    <h3 class="dsh-card__title">Préstamos de los últimos 30 días</h3>
                </div>
                <ul class="dsh-legend" aria-label="Leyenda">
                    <li><span class="dsh-swatch dsh-swatch--sala"></span>En sala</li>
                    <li><span class="dsh-swatch dsh-swatch--casa"></span>A casa</li>
                </ul>
            </header>

            <dl class="dsh-summary">
                <div>
                    <dt>Préstamos</dt>
                    <dd>{{ $num($circulacion['total']) }}</dd>
                    <small>{{ $num($circulacion['sala']) }} en sala · {{ $num($circulacion['casa']) }} a casa</small>
                </div>
                <div>
                    <dt>Lectores atendidos</dt>
                    <dd>{{ $num($circulacion['lectores']) }}</dd>
                    <small>Lectores distintos</small>
                </div>
                <div>
                    <dt>Promedio diario</dt>
                    <dd>{{ number_format($circulacion['promedio'], 1) }}</dd>
                    <small>En {{ $circulacion['dias_atencion'] }} {{ $circulacion['dias_atencion'] === 1 ? 'día' : 'días' }} con préstamos</small>
                </div>
                <div>
                    <dt>Devueltos a tiempo</dt>
                    <dd>{{ $circulacion['puntualidad'] !== null ? $circulacion['puntualidad'] . '%' : '—' }}</dd>
                    <small>{{ $circulacion['evaluados'] ? 'De ' . $num($circulacion['evaluados']) . ' devoluciones' : 'Sin devoluciones aún' }}</small>
                </div>
            </dl>

            @if($circulacion['total'] > 0)
                <div class="dsh-chart" id="dshChartCirculacion" role="img"
                     aria-label="Préstamos por día en los últimos 30 días: {{ $circulacion['sala'] }} en sala y {{ $circulacion['casa'] }} a casa."></div>
            @else
                <div class="dsh-empty"><i class="bi bi-bar-chart"></i>Aún no hay préstamos en los últimos 30 días.</div>
            @endif

            @if($circulacion['total'] > 0)
            <details class="dsh-table-toggle" data-dsh-tabla>
                <summary>Ver datos en tabla</summary>
                <div class="dsh-table-wrap">
                    <table class="dsh-table">
                        <thead><tr><th>Día</th><th>En sala</th><th>A casa</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach(array_reverse($circulacion['dias']) as $dia)
                                <tr>
                                    <td>{{ $dia['etiqueta_larga'] }}</td>
                                    <td>{{ $dia['sala'] }}</td>
                                    <td>{{ $dia['casa'] }}</td>
                                    <td><strong>{{ $dia['sala'] + $dia['casa'] }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
            @endif
        </article>

        {{-- Requiere atencion --}}
        <article class="dsh-card dsh-span-4 dsh-attention">
            <header class="dsh-card__head">
                <div>
                    <span class="dsh-card__eyebrow">Hoy</span>
                    <h3 class="dsh-card__title">Requiere atención</h3>
                </div>
                <span class="dsh-count {{ $pendientes ? 'dsh-count--alerta' : '' }}">{{ $pendientes }}</span>
            </header>

            <h4 class="dsh-subtitle">
                Préstamos fuera de plazo
                @if($atencion['vencidos_total'] > $atencion['vencidos']->count())
                    <small>{{ $atencion['vencidos']->count() }} de {{ $atencion['vencidos_total'] }}</small>
                @endif
            </h4>
            @forelse($atencion['vencidos'] as $prestamo)
                <div class="dsh-item">
                    <span class="dsh-item__icon dsh-item__icon--critico"><i class="bi bi-alarm"></i></span>
                    <div class="dsh-item__body">
                        <strong title="{{ optional(optional($prestamo->ejemplar)->libro)->titulo }}">{{ optional(optional($prestamo->ejemplar)->libro)->titulo ?? 'Ejemplar sin título' }}</strong>
                        <span>
                            {{ $propio(optional($prestamo->lector)->name) }}
                            · {{ (int) $prestamo->prestamo_lugar === \App\Models\Prestamo::A_CASA ? 'A casa' : 'En sala' }}
                            @if($multiples && optional($prestamo->ejemplar)->biblioteca)
                                · {{ $propio($prestamo->ejemplar->biblioteca->nombre) }}
                            @endif
                        </span>
                    </div>
                    <span class="dsh-tag dsh-tag--critico" title="Venció el {{ $prestamo->fecha_limite_real->format('d/m/Y H:i') }}">
                        {{ $prestamo->fecha_limite_real->locale('es')->diffForHumans(['parts' => 1]) }}
                    </span>
                </div>
            @empty
                <div class="dsh-ok"><i class="bi bi-check-circle-fill"></i>No hay préstamos fuera de plazo.</div>
            @endforelse

            <h4 class="dsh-subtitle">
                Reservas por recoger
                @if($atencion['reservas_total'] > $atencion['reservas']->count())
                    <small>{{ $atencion['reservas']->count() }} de {{ $atencion['reservas_total'] }}</small>
                @endif
            </h4>
            @forelse($atencion['reservas'] as $reserva)
                <div class="dsh-item">
                    <span class="dsh-item__icon"><i class="bi bi-bookmark"></i></span>
                    <div class="dsh-item__body">
                        <strong>{{ optional(optional($reserva->ejemplar)->libro)->titulo ?? 'Ejemplar sin título' }}</strong>
                        <span>{{ $propio(optional($reserva->lector)->name) }}</span>
                    </div>
                    <span class="dsh-tag {{ $reserva->fecha_limite->isToday() ? 'dsh-tag--alerta' : '' }}">
                        {{ $reserva->fecha_limite->isToday() ? 'Vence hoy' : 'Vence ' . $reserva->fecha_limite->locale('es')->isoFormat('D MMM') }}
                    </span>
                </div>
            @empty
                <div class="dsh-ok"><i class="bi bi-check-circle-fill"></i>No hay reservas pendientes de entrega.</div>
            @endforelse

            @if($puede('prestamos.registro') || $puede('prestamos.reservas'))
                <footer class="dsh-card__foot">
                    @if($puede('prestamos.registro'))
                        <a href="{{ url('/prestamos/registro') }}">Ir a préstamos <i class="bi bi-arrow-right"></i></a>
                    @endif
                    @if($puede('prestamos.reservas'))
                        <a href="{{ url('/prestamos/reservas') }}">Ir a reservas <i class="bi bi-arrow-right"></i></a>
                    @endif
                </footer>
            @endif
        </article>

        {{-- Libros mas solicitados --}}
        <article class="dsh-card dsh-span-4">
            <header class="dsh-card__head">
                <div>
                    <span class="dsh-card__eyebrow">Últimos 30 días</span>
                    <h3 class="dsh-card__title">Libros más solicitados</h3>
                </div>
            </header>
            @if($librosTop->isNotEmpty())
                <ol class="dsh-rank">
                    @foreach($librosTop as $i => $libro)
                        <li class="dsh-rank__item">
                            <span class="dsh-rank__pos">{{ $i + 1 }}</span>
                            <div class="dsh-rank__body">
                                <strong title="{{ $libro->titulo }}">{{ $libro->titulo }}</strong>
                                <span class="dsh-bar" title="{{ $libro->total }} préstamos"><span style="width: {{ $libro->pct }}%"></span></span>
                            </div>
                            <span class="dsh-rank__value">{{ $libro->total }}<small>{{ $libro->total === 1 ? 'préstamo' : 'préstamos' }}</small></span>
                        </li>
                    @endforeach
                </ol>
            @else
                <div class="dsh-empty"><i class="bi bi-book"></i>Sin préstamos en este periodo.</div>
            @endif
        </article>

        {{-- Carreras --}}
        <article class="dsh-card dsh-span-4">
            <header class="dsh-card__head">
                <div>
                    <span class="dsh-card__eyebrow">Últimos 30 días</span>
                    <h3 class="dsh-card__title">Carreras que más leen</h3>
                </div>
            </header>
            @if($carreras['top']->isNotEmpty())
                <ol class="dsh-rank">
                    @foreach($carreras['top'] as $i => $carrera)
                        <li class="dsh-rank__item">
                            <span class="dsh-rank__pos">{{ $i + 1 }}</span>
                            <div class="dsh-rank__body">
                                <strong title="{{ $carrera->nombre }}">{{ $carrera->nombre }}</strong>
                                <span class="dsh-bar" title="{{ $carrera->lectores }} lectores, {{ $carrera->prestamos }} préstamos"><span style="width: {{ $carrera->pct }}%"></span></span>
                            </div>
                            <span class="dsh-rank__value">{{ $carrera->lectores }}<small>{{ $carrera->lectores === 1 ? 'lector' : 'lectores' }}</small></span>
                        </li>
                    @endforeach
                </ol>
                @if($carreras['sin_carrera'])
                    <p class="dsh-note">{{ $carreras['sin_carrera'] }} {{ $carreras['sin_carrera'] === 1 ? 'lector no tiene' : 'lectores no tienen' }} carrera registrada.</p>
                @endif
            @else
                <div class="dsh-empty"><i class="bi bi-mortarboard"></i>Sin lectores atendidos en este periodo.</div>
            @endif
        </article>

        {{-- Demanda por dia de la semana --}}
        <article class="dsh-card dsh-span-4">
            <header class="dsh-card__head">
                <div>
                    <span class="dsh-card__eyebrow">Últimos 90 días</span>
                    <h3 class="dsh-card__title">Demanda por día</h3>
                </div>
            </header>
            @if($semana['total'] > 0)
                <div class="dsh-week" role="img" aria-label="Préstamos por día de la semana en los últimos 90 días">
                    @foreach($semana['dias'] as $dia)
                        <div class="dsh-week__col {{ $dia['dia'] === $semana['pico'] ? 'is-pico' : '' }}" title="{{ $dia['dia'] }}: {{ $dia['total'] }} préstamos">
                            <span class="dsh-week__value">{{ $dia['total'] }}</span>
                            <span class="dsh-week__track"><span class="dsh-week__bar" style="height: {{ max(round($dia['total'] * 100 / $semanaMax), $dia['total'] ? 3 : 0) }}%"></span></span>
                            <span class="dsh-week__label">{{ $dia['dia'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="dsh-note">
                    <i class="bi bi-lightning-charge"></i>
                    El <strong>{{ $semana['pico_largo'] }}</strong>
                    es el día de mayor demanda ({{ $num($semana['total']) }} préstamos en total).
                </p>
            @else
                <div class="dsh-empty"><i class="bi bi-calendar-week"></i>Sin préstamos en este periodo.</div>
            @endif
        </article>

        {{-- Coleccion --}}
        <article class="dsh-card dsh-span-4">
            <header class="dsh-card__head">
                <div>
                    <span class="dsh-card__eyebrow">Colección</span>
                    <h3 class="dsh-card__title">Estado del acervo</h3>
                </div>
            </header>
            <div class="dsh-stock">
                <div>
                    <span class="dsh-stock__value">{{ $num($coleccion['ejemplares']) }}</span>
                    <span class="dsh-stock__label">ejemplares</span>
                </div>
                <div>
                    <span class="dsh-stock__value">{{ $num($coleccion['titulos']) }}</span>
                    <span class="dsh-stock__label">títulos</span>
                </div>
            </div>
            <div class="dsh-meter" title="{{ $coleccion['pct_disponible'] }}% de ejemplares disponibles">
                <div class="dsh-meter__head"><span>Disponibles para préstamo</span><strong>{{ $coleccion['pct_disponible'] }}%</strong></div>
                <span class="dsh-bar dsh-bar--lg"><span style="width: {{ $coleccion['pct_disponible'] }}%"></span></span>
            </div>
            <ul class="dsh-list">
                <li><span><i class="bi bi-check2-circle"></i>Disponibles</span><strong>{{ $num($coleccion['disponibles']) }}</strong></li>
                <li><span><i class="bi bi-box-arrow-up-right"></i>Prestados</span><strong>{{ $num($coleccion['prestados']) }}</strong></li>
                <li><span><i class="bi bi-bookmark"></i>Reservados</span><strong>{{ $num($coleccion['reservados']) }}</strong></li>
                <li><span><i class="bi bi-truck"></i>En traslado</span><strong>{{ $num($coleccion['traslado']) }}</strong></li>
                <li><span><i class="bi bi-plus-circle"></i>Incorporados este mes</span><strong>{{ $num($coleccion['nuevos_mes']) }}</strong></li>
            </ul>
            @if($coleccion['recientes']->isNotEmpty())
                <h4 class="dsh-subtitle">Últimos títulos registrados</h4>
                <ul class="dsh-recent">
                    @foreach($coleccion['recientes'] as $libro)
                        <li>
                            @if($puede('administracion.libros'))
                                <a href="{{ url('/administracion/ejemplares/' . $libro->id) }}" title="{{ $libro->titulo }}">{{ $libro->titulo }}</a>
                            @else
                                <span title="{{ $libro->titulo }}">{{ $libro->titulo }}</span>
                            @endif
                            <small>{{ optional($libro->created_at)->locale('es')->isoFormat('D MMM') }}</small>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>

        {{-- Movimientos recientes --}}
        <article class="dsh-card dsh-span-8">
            <header class="dsh-card__head">
                <div>
                    <span class="dsh-card__eyebrow">En tiempo real</span>
                    <h3 class="dsh-card__title">Últimos movimientos</h3>
                </div>
                @if($puede('prestamos.registro'))
                    <a href="{{ url('/prestamos/registro') }}" class="dsh-link">Ver todos <i class="bi bi-arrow-right"></i></a>
                @endif
            </header>
            @if($movimientos->isNotEmpty())
                <ul class="dsh-feed">
                    @foreach($movimientos as $mov)
                        <li class="dsh-feed__item">
                            <span class="dsh-feed__icon dsh-feed__icon--{{ $mov->tipo }}">
                                <i class="bi {{ $mov->tipo === 'prestamo' ? 'bi-box-arrow-up-right' : 'bi-box-arrow-in-down-left' }}"></i>
                            </span>
                            <div class="dsh-feed__body">
                                <strong>
                                    @if($mov->tipo === 'prestamo')
                                        {{ (int) $mov->prestamo->prestamo_lugar === \App\Models\Prestamo::A_CASA ? 'Préstamo a casa' : 'Préstamo en sala' }}
                                    @else
                                        Devolución
                                    @endif
                                    <span class="dsh-feed__book">· {{ optional(optional($mov->prestamo->ejemplar)->libro)->titulo ?? 'Ejemplar sin título' }}</span>
                                </strong>
                                <span>
                                    {{ $propio(optional($mov->prestamo->lector)->name) ?: 'Lector no disponible' }}
                                    @if($mov->prestamo->bibliotecario)
                                        · atendido por {{ $propio($mov->prestamo->bibliotecario->name) }}
                                    @endif
                                </span>
                            </div>
                            <div class="dsh-feed__meta">
                                @if($mov->tipo === 'devolucion' && (int) $mov->prestamo->estado_prestamo === 2)
                                    <span class="dsh-tag dsh-tag--alerta">Tardanza</span>
                                @elseif($mov->tipo === 'devolucion' && (int) $mov->prestamo->estado_prestamo === 3)
                                    <span class="dsh-tag dsh-tag--critico">Deterioro</span>
                                @elseif($mov->prestamo->es_libre)
                                    <span class="dsh-tag">Libre</span>
                                @endif
                                <time datetime="{{ $mov->momento->toIso8601String() }}" title="{{ $mov->momento->format('d/m/Y H:i') }}">{{ $cuando($mov->momento) }}</time>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="dsh-empty"><i class="bi bi-activity"></i>Todavía no hay movimientos registrados.</div>
            @endif
        </article>

        {{-- Comparativo por biblioteca --}}
        @if($bibliotecas->isNotEmpty())
            <article class="dsh-card dsh-span-12">
                <header class="dsh-card__head">
                    <div>
                        <span class="dsh-card__eyebrow">Red de bibliotecas</span>
                        <h3 class="dsh-card__title">Comparativo por biblioteca</h3>
                    </div>
                    <span class="dsh-muted">Toca una biblioteca para ver solo sus indicadores</span>
                </header>
                <div class="dsh-table-wrap">
                    <table class="dsh-table dsh-table--bibliotecas">
                        <thead>
                            <tr>
                                <th>Biblioteca</th>
                                <th class="num">Ejemplares</th>
                                <th class="num">Disponibles</th>
                                <th class="num">En circulación</th>
                                <th class="num">Fuera de plazo</th>
                                <th>Préstamos (30 días)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bibliotecas as $fila)
                                <tr>
                                    <td>
                                        @if($alcance['puedeFiltrar'])
                                            <a href="{{ route('dashboard', ['biblioteca' => $fila->id]) }}">{{ $fila->nombre }}</a>
                                        @else
                                            {{ $fila->nombre }}
                                        @endif
                                    </td>
                                    <td class="num">{{ $num($fila->ejemplares) }}</td>
                                    <td class="num">{{ $num($fila->disponibles) }}</td>
                                    <td class="num">{{ $num($fila->activos) }}</td>
                                    <td class="num">
                                        @if($fila->vencidos)
                                            <span class="dsh-tag dsh-tag--critico">{{ $fila->vencidos }}</span>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        <span class="dsh-inline-bar">
                                            <span class="dsh-bar"><span style="width: {{ $fila->pct }}%"></span></span>
                                            <strong>{{ $num($fila->prestamos30) }}</strong>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @endif

        {{-- Visitas web --}}
        <article class="dsh-card dsh-span-8">
            <header class="dsh-card__head">
                <div>
                    <span class="dsh-card__eyebrow">Sitio público · todas las sedes</span>
                    <h3 class="dsh-card__title">Visitas a la web de la biblioteca</h3>
                </div>
                <span class="dsh-muted">Sesiones únicas por día</span>
            </header>
            <dl class="dsh-summary">
                <div><dt>Hoy</dt><dd>{{ $num($visitas['hoy']) }}</dd><small>Promedio: {{ $num($visitas['promedio']) }}/día</small></div>
                <div><dt>Esta semana</dt><dd>{{ $num($visitas['semana']) }}</dd><small>Desde el lunes</small></div>
                <div><dt>Este mes</dt><dd>{{ $num($visitas['mes']) }}</dd><small>{{ \Illuminate\Support\Str::ucfirst(now()->locale('es')->isoFormat('MMMM')) }}</small></div>
                <div><dt>Histórico</dt><dd>{{ $num($visitas['total']) }}</dd><small>Desde que se mide</small></div>
            </dl>
            @if(collect($visitas['serie'])->sum('total') > 0)
                <div class="dsh-chart dsh-chart--sm" id="dshChartVisitas" role="img" aria-label="Visitas diarias a la web en los últimos 30 días"></div>
            <details class="dsh-table-toggle" data-dsh-tabla>
                <summary>Ver datos en tabla</summary>
                <div class="dsh-table-wrap">
                    <table class="dsh-table">
                        <thead><tr><th>Día</th><th>Visitas</th></tr></thead>
                        <tbody>
                            @foreach(array_reverse($visitas['serie']) as $dia)
                                <tr><td>{{ $dia['etiqueta_larga'] }}</td><td>{{ $num($dia['total']) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
            @else
                <div class="dsh-empty"><i class="bi bi-globe2"></i>Sin visitas registradas en los últimos 30 días.</div>
            @endif
        </article>

        {{-- Agenda --}}
        <article class="dsh-card dsh-span-4 dsh-agenda-card">
            <header class="dsh-card__head">
                <div>
                    <span class="dsh-card__eyebrow">Agenda</span>
                    <h3 class="dsh-card__title">Próximas actividades</h3>
                </div>
            </header>
            @if($agenda->isNotEmpty())
                <ul class="dsh-agenda">
                    @foreach($agenda as $actividad)
                        <li>
                            <a href="{{ route('evento') }}#evento-{{ $actividad->id }}" class="dsh-agenda__item">
                                <span class="dsh-agenda__date">
                                    <strong>{{ $actividad->fecha_inicio->format('d') }}</strong>
                                    <small>{{ $actividad->mesCorto() }}</small>
                                </span>
                                <span class="dsh-agenda__body">
                                    <strong>{{ $actividad->titulo }}</strong>
                                    <small>
                                        {{ $actividad->estadoAgenda() === 'en_curso' ? 'En curso' : 'Próxima' }}
                                        @if($actividad->horario()) · {{ $actividad->horario() }} @endif
                                        @if($actividad->categoria) · {{ $actividad->categoria->nombre }} @endif
                                    </small>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="dsh-empty dsh-empty--compact"><i class="bi bi-calendar-event"></i>No hay actividades programadas.</div>
            @endif
            @if($puede('administracion.actividades'))
                <footer class="dsh-card__foot">
                    <a href="{{ url('/administracion/actividades') }}">Gestionar actividades <i class="bi bi-arrow-right"></i></a>
                </footer>
            @endif
        </article>

        {{-- Accesos rapidos: solo los modulos que el usuario puede abrir --}}
        @if(count($accesos))
            <article class="dsh-card dsh-span-12">
                <header class="dsh-card__head">
                    <div>
                        <span class="dsh-card__eyebrow">Accesos rápidos</span>
                        <h3 class="dsh-card__title">Tus módulos</h3>
                    </div>
                </header>
                <div class="dsh-modules">
                    @foreach($accesos as $grupo => $items)
                        <section class="dsh-modules__group">
                            <h4 class="dsh-subtitle">{{ $grupo }}</h4>
                            <div class="dsh-modules__grid">
                                @foreach($items as $item)
                                    <a href="{{ $item['url'] }}" class="dsh-module">
                                        <span class="dsh-module__icon"><i class="bi {{ $item['icono'] }}"></i></span>
                                        <span class="dsh-module__body">
                                            <strong>{{ $item['titulo'] }}</strong>
                                            <small>{{ $item['copia'] }}</small>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </article>
        @endif
    </div>

    <p class="dsh-foot">
        Datos actualizados al {{ now()->format('d/m/Y H:i') }}.
        Los préstamos en sala vencen a las {{ $sala['cierre'] }}; los préstamos a casa, a la misma hora de su último día.
    </p>
</div>
@endsection
