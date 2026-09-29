<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de lectura</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1f2c27; }
        .band { height: 10px; background: #184d3b; }
        .band-gold { height: 3px; background: #d8b15c; }
        .page { padding: 26px 44px 60px; }
        .head td { vertical-align: middle; }
        .logo { width: 50px; height: 50px; }
        .inst { padding-left: 12px; }
        .inst strong { display: block; font-size: 11px; color: #0f3025; line-height: 1.35; }
        .inst span { display: block; margin-top: 2px; font-size: 8.5px; color: #5b6b65; letter-spacing: 1.2px; text-transform: uppercase; }
        .doc { text-align: right; }
        .doc strong { display: block; font-size: 14px; color: #184d3b; letter-spacing: 1.5px; }
        .doc span { display: block; margin-top: 3px; font-size: 8.5px; color: #5b6b65; }

        .person { margin-top: 18px; border: 1px solid #dfe7e3; background: #f7faf8; }
        .person td { padding: 12px 14px; vertical-align: top; }
        .name { font-size: 14px; font-weight: bold; color: #0f3025; }
        .career { margin-top: 2px; color: #4a5c55; }
        .meta { margin-top: 8px; color: #4a5c55; }
        .meta b { color: #13241d; }
        .level { text-align: right; }
        .level .badge { display: inline-block; padding: 6px 12px; border: 1px solid #d8b15c; background: #fbf4e2; color: #7a5b12; font-weight: bold; font-size: 11px; }
        .level .badge.base { border-color: #bcd8cb; background: #e3f1ea; color: #1d654c; }
        .level small { display: block; margin-top: 5px; color: #7b8a84; font-size: 8px; }

        h2 { margin: 20px 0 8px; font-size: 11px; color: #184d3b; letter-spacing: 1px; text-transform: uppercase; }
        .kpis td { width: 25%; padding: 10px 12px; border: 1px solid #dfe7e3; vertical-align: top; }
        .kpis .label { color: #4a5c55; font-size: 8.5px; }
        .kpis .value { margin-top: 4px; font-size: 20px; font-weight: bold; color: #13241d; }
        .kpis .value small { font-size: 10px; color: #7b8a84; font-weight: normal; }
        .kpis .sub { margin-top: 2px; color: #7b8a84; font-size: 8px; }

        .chart td { text-align: center; padding: 0 6px; }
        .chart .plot td { vertical-align: top; }
        .chart .bar { width: 22px; margin: 0 auto; background: #1d7f57; }
        .chart .val { height: 13px; font-size: 9px; font-weight: bold; color: #13241d; }
        .chart .mes td { padding-top: 5px; border-top: 1px solid #c7d0cc; color: #7b8a84; font-size: 8.5px; vertical-align: top; }
        .chart .mes td.actual { color: #13241d; font-weight: bold; }

        .cols td { vertical-align: top; }
        .subjects td { padding: 4px 0; }
        .subjects .track { height: 5px; background: #e6ebe8; }
        .subjects .fill { height: 5px; background: #1d7f57; }
        .subjects .n { width: 24px; text-align: right; font-weight: bold; }
        .facts td { padding: 5px 0; border-bottom: 1px solid #e6ebe8; }
        .facts td.v { text-align: right; font-weight: bold; }

        table.hist { width: 100%; border-collapse: collapse; }
        table.hist th { padding: 6px 7px; background: #184d3b; color: #fff; text-align: left; font-size: 8.5px; font-weight: bold; }
        table.hist td { padding: 6px 7px; border-bottom: 1px solid #e6ebe8; vertical-align: top; }
        table.hist tr.alt td { background: #f7faf8; }
        .late { color: #b42f2f; font-weight: bold; }
        .ok { color: #1d654c; font-weight: bold; }
        .dam { color: #5a35a0; font-weight: bold; }
        .muted { color: #7b8a84; }

        .footer { position: fixed; left: 0; right: 0; bottom: 0; }
        .footer .note { padding: 7px 44px; font-size: 7.5px; color: #7b8a84; text-align: center; }
    </style>
</head>
<body>
@php
    $persona = $user->persona;
    $e = $estadisticas;
    $serie = $e['serie_mensual'];
    $maxMes = max(array_column($serie, 'total')) ?: 1;
    $maxMateria = collect($e['materias'])->max('total') ?: 1;
    $esMayor = in_array($e['nivel']['clave'], ['frecuente', 'destacado'], true);
    $mesAnio = fn ($f) => \Illuminate\Support\Str::ucfirst($f->locale('es')->translatedFormat('F Y'));
    $conHora = fn ($f) => $f->format($f->format('H:i:s') === '00:00:00' ? 'd/m/Y' : 'd/m/Y H:i');
@endphp

<div class="band"></div>
<div class="band-gold"></div>

<div class="page">
    <table width="100%" class="head">
        <tr>
            <td width="50"><img src="{{ $logo }}" class="logo" alt=""></td>
            <td class="inst">
                <strong>UNIVERSIDAD NACIONAL AMAZÓNICA<br>DE MADRE DE DIOS</strong>
                <span>Biblioteca Universitaria</span>
            </td>
            <td class="doc" width="190">
                <strong>REPORTE DE LECTURA</strong>
                <span>Generado el {{ now()->format('d/m/Y H:i') }}</span>
            </td>
        </tr>
    </table>

    <table width="100%" class="person">
        <tr>
            <td>
                <div class="name">{{ \Illuminate\Support\Str::title(mb_strtolower($user->name)) }}</div>
                <div class="career">{{ $persona?->carrera?->nombre ? \Illuminate\Support\Str::title(mb_strtolower($persona->carrera->nombre)) : 'Lector de la biblioteca' }}</div>
                <div class="meta">
                    @if($persona?->codigo_institucional)Código <b>{{ $persona->codigo_institucional }}</b> &nbsp;·&nbsp; @endif
                    @if($persona?->dni)DNI <b>{{ $persona->dni }}</b> &nbsp;·&nbsp; @endif
                    @if($persona?->tipo_carnet)Carnet <b>{{ $persona->tieneCarnetDuplicado() ? 'duplicado' : 'original' }}</b> &nbsp;·&nbsp; @endif
                    Miembro desde <b>{{ $e['miembro_desde'] ? $mesAnio($e['miembro_desde']) : '—' }}</b>
                </div>
            </td>
            <td class="level" width="170">
                <span class="badge {{ $esMayor ? '' : 'base' }}">{{ $e['nivel']['nombre'] }}</span>
                <small>Según los últimos {{ $e['periodo']['meses'] }} meses<br>({{ $mesAnio($e['periodo']['desde']) }} – {{ $mesAnio($e['periodo']['hasta']) }})</small>
            </td>
        </tr>
    </table>

    <h2>Resumen</h2>
    <table width="100%" class="kpis" cellspacing="0">
        <tr>
            <td>
                <div class="label">Préstamos realizados</div>
                <div class="value">{{ $e['total_prestamos'] }}</div>
                <div class="sub">{{ $e['prestamos_periodo'] }} en los últimos {{ $e['periodo']['meses'] }} meses</div>
            </td>
            <td>
                <div class="label">Libros distintos</div>
                <div class="value">{{ $e['libros_distintos'] }}</div>
                <div class="sub">{{ count($e['bibliotecas']) }} biblioteca{{ count($e['bibliotecas']) === 1 ? '' : 's' }} visitada{{ count($e['bibliotecas']) === 1 ? '' : 's' }}</div>
            </td>
            <td>
                <div class="label">Meses con actividad</div>
                <div class="value">{{ $e['meses_activos'] }} <small>de {{ $e['periodo']['meses'] }}</small></div>
                <div class="sub">Constancia en la lectura</div>
            </td>
            <td>
                <div class="label">Puntualidad</div>
                <div class="value">{{ $e['puntualidad'] !== null ? $e['puntualidad'] . '%' : '—' }}</div>
                <div class="sub">{{ $e['devueltos'] > 0 ? 'de ' . $e['devueltos'] . ' devoluciones a tiempo' : 'Sin devoluciones' }}</div>
            </td>
        </tr>
    </table>

    <table width="100%" class="cols">
        <tr>
            <td width="58%" style="padding-right: 18px;">
                <h2>Préstamos por mes</h2>
                <table width="100%" class="chart" cellspacing="0">
                    <tr class="plot">
                        @foreach($serie as $mes)
                            @php
                                $alto = $mes['total'] > 0 ? max(2, (int) round($mes['total'] / $maxMes * 96)) : 0;
                            @endphp
                            <td>
                                <div style="height: {{ 96 - $alto }}px"></div>
                                <div class="val">{{ $mes['total'] }}</div>
                                @if($alto > 0)
                                    <div class="bar" style="height: {{ $alto }}px"></div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr class="mes">
                        @foreach($serie as $mes)
                            <td class="{{ $mes['actual'] ? 'actual' : '' }}">{{ $mes['corto'] }}</td>
                        @endforeach
                    </tr>
                </table>
            </td>
            <td width="42%">
                <h2>Lo que más lee</h2>
                @if(count($e['materias']))
                    <table width="100%" class="subjects" cellspacing="0">
                        @foreach($e['materias'] as $materia)
                            <tr>
                                <td>
                                    {{ $materia['nombre'] }}
                                    <div class="track"><div class="fill" style="width: {{ round($materia['total'] / $maxMateria * 100) }}%"></div></div>
                                </td>
                                <td class="n">{{ $materia['total'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <p class="muted">Sin materias registradas todavía.</p>
                @endif

                <table width="100%" class="facts" cellspacing="0" style="margin-top: 8px;">
                    <tr><td>Préstamos en sala</td><td class="v">{{ $e['en_sala'] }}</td></tr>
                    <tr><td>Préstamos a casa</td><td class="v">{{ $e['a_casa'] }}</td></tr>
                    <tr><td>Bibliotecas</td><td class="v">{{ count($e['bibliotecas']) ? collect($e['bibliotecas'])->map(fn ($b) => \Illuminate\Support\Str::title(mb_strtolower($b)))->implode(', ') : '—' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <h2>Préstamos recientes</h2>
    @if($historial->isEmpty())
        <p class="muted">Aún no hay préstamos registrados.</p>
    @else
        <table class="hist">
            <thead>
                <tr><th width="70">Fecha</th><th>Libro</th><th width="120">Biblioteca</th><th width="46">Tipo</th><th width="92">Estado</th></tr>
            </thead>
            <tbody>
                @foreach($historial as $prestamo)
                    @php
                        $vencido = (int) $prestamo->estado === 1 && $prestamo->fecha_limite_real && now()->gte($prestamo->fecha_limite_real);
                        $estadoPrestamo = (int) $prestamo->estado_prestamo;
                    @endphp
                    <tr class="{{ $loop->even ? 'alt' : '' }}">
                        <td>{{ $prestamo->fecha_prestamo ? $conHora($prestamo->fecha_prestamo) : '-' }}</td>
                        <td>{{ $prestamo->ejemplar->libro->titulo ?? 'Libro no disponible' }}</td>
                        <td>{{ \Illuminate\Support\Str::title(mb_strtolower($prestamo->ejemplar->biblioteca->nombre ?? '-')) }}</td>
                        <td>{{ (int) $prestamo->prestamo_lugar === \App\Models\Prestamo::EN_SALA ? 'Sala' : 'Casa' }}</td>
                        <td>
                            @if((int) $prestamo->estado === 1)
                                <span class="{{ $vencido ? 'late' : 'ok' }}">{{ $vencido ? 'Fuera de plazo' : 'En curso' }}</span>
                            @elseif($estadoPrestamo === 3)
                                <span class="dam">Deterioro</span>
                            @elseif($estadoPrestamo === 2)
                                <span class="late">Con tardanza</span>
                            @else
                                <span class="ok">A tiempo</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($e['total_prestamos'] > $historial->count())
            <p class="muted" style="margin-top: 6px;">Se muestran los {{ $historial->count() }} préstamos más recientes de {{ $e['total_prestamos'] }}.</p>
        @endif
    @endif
</div>

<div class="footer">
    <div class="note">Reporte generado por el Sistema de Biblioteca UNAMAD con la actividad registrada a la fecha. No reemplaza a la constancia de lector frecuente.</div>
    <div class="band-gold"></div>
    <div class="band"></div>
</div>
</body>
</html>
