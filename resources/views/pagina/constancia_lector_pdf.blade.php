<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Constancia {{ $constancia->codigo }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1f2c27; }
        .band { height: 12px; background: #184d3b; }
        .band-gold { height: 3px; background: #d8b15c; }
        .page { padding: 34px 58px 0; }
        .head td { vertical-align: middle; }
        .logo { width: 62px; height: 62px; }
        .inst { padding-left: 14px; }
        .inst strong { display: block; font-size: 12px; line-height: 1.35; color: #0f3025; letter-spacing: 0.3px; }
        .inst span { display: block; margin-top: 2px; font-size: 9.5px; color: #5b6b65; letter-spacing: 1.2px; text-transform: uppercase; }
        .code-box { text-align: right; font-size: 8.5px; color: #5b6b65; }
        .code-box b { display: block; margin-top: 2px; font-size: 11px; color: #184d3b; letter-spacing: 1px; }
        .title { margin: 34px 0 6px; text-align: center; font-size: 19px; font-weight: bold; letter-spacing: 3px; color: #184d3b; }
        .subtitle { margin: 0 auto 26px; width: 90px; height: 2px; background: #d8b15c; }
        p { margin: 0 0 12px; line-height: 1.75; text-align: justify; }
        .name { font-weight: bold; color: #0f3025; }
        .level { font-weight: bold; color: #184d3b; letter-spacing: 0.5px; }
        .criteria { font-size: 9.5px; color: #5b6b65; }
        table.stats { width: 100%; margin: 6px 0 18px; border-collapse: collapse; }
        table.stats th, table.stats td { padding: 7px 10px; border-bottom: 1px solid #e3e9e6; text-align: left; vertical-align: top; }
        table.stats th { width: 44%; color: #4a5c55; font-weight: normal; }
        table.stats td { color: #13241d; font-weight: bold; }
        table.stats tr.alt th, table.stats tr.alt td { background: #f5f8f6; }
        .place { margin-top: 18px; text-align: right; }
        .verify { margin-top: 30px; border: 1px solid #d8e2dd; border-left: 4px solid #d8b15c; background: #fbfaf5; }
        .verify td { padding: 12px 14px; vertical-align: top; font-size: 9.5px; line-height: 1.6; color: #4a5c55; }
        .verify .code { font-size: 15px; font-weight: bold; letter-spacing: 2px; color: #184d3b; }
        .verify .url { color: #184d3b; word-wrap: break-word; }
        .footer { position: fixed; left: 0; right: 0; bottom: 0; }
        .footer .note { padding: 8px 58px; font-size: 8px; color: #7b8a84; text-align: center; }
    </style>
</head>
<body>
@php
    $d = $constancia->datos;
    $fecha = fn ($valor) => \Illuminate\Support\Carbon::parse($valor)->locale('es')->translatedFormat('j \d\e F \d\e Y');
    $nivelMayus = mb_strtoupper($d['nivel_nombre']);
    $regla = \App\Services\EstadisticasLectorService::NIVELES[$constancia->nivel] ?? null;
@endphp

<div class="band"></div>
<div class="band-gold"></div>

<div class="page">
    <table width="100%" class="head">
        <tr>
            <td width="62"><img src="{{ $logo }}" class="logo" alt=""></td>
            <td class="inst">
                <strong>UNIVERSIDAD NACIONAL AMAZÓNICA<br>DE MADRE DE DIOS</strong>
                <span>Biblioteca Universitaria</span>
            </td>
            <td class="code-box" width="150">
                Código de verificación
                <b>{{ $constancia->codigo }}</b>
            </td>
        </tr>
    </table>

    <div class="title">CONSTANCIA DE {{ $nivelMayus }}</div>
    <div class="subtitle"></div>

    @php
        $identificacion = implode(' y ', array_filter([
            !empty($d['dni']) ? 'con DNI N.° ' . $d['dni'] : null,
            !empty($d['codigo_estudiante']) ? 'código de estudiante ' . $d['codigo_estudiante'] : null,
        ]));
        $carreraTexto = !empty($d['carrera'])
            ? ', de la carrera profesional de ' . \Illuminate\Support\Str::title(mb_strtolower($d['carrera']))
            : '';
    @endphp
    <p>
        La Biblioteca de la Universidad Nacional Amazónica de Madre de Dios hace constar que
        <span class="name">{{ mb_strtoupper($d['nombre']) }}</span>{{ $identificacion ? ', ' . $identificacion : '' }}{{ $carreraTexto }},
        figura en el registro de lectores de la Biblioteca y, de acuerdo con su historial de préstamos
        entre el {{ $fecha($d['periodo_desde']) }} y el {{ $fecha($d['periodo_hasta']) }}, cumple los criterios de
        <span class="level">{{ $nivelMayus }}</span>.
    </p>

    @if($regla)
        <p class="criteria">
            Criterio: al menos {{ $regla['prestamos'] }} préstamos con actividad en {{ $regla['meses'] }} meses distintos
            dentro de un periodo de {{ $d['meses_periodo'] }} meses.
        </p>
    @endif

    <table class="stats">
        <tr><th>Préstamos en el periodo evaluado</th><td>{{ $d['prestamos_periodo'] }}</td></tr>
        <tr class="alt"><th>Meses con actividad</th><td>{{ $d['meses_activos'] }} de {{ $d['meses_periodo'] }}</td></tr>
        <tr><th>Préstamos registrados en total</th><td>{{ $d['total_prestamos'] }}</td></tr>
        <tr class="alt"><th>Libros distintos consultados</th><td>{{ $d['libros_distintos'] }}</td></tr>
        <tr><th>Puntualidad en devoluciones</th><td>{{ $d['puntualidad'] !== null ? $d['puntualidad'] . '% de devoluciones a tiempo' : 'Sin devoluciones registradas' }}</td></tr>
        @if(!empty($d['bibliotecas']))
            <tr class="alt"><th>Bibliotecas utilizadas</th><td>{{ collect($d['bibliotecas'])->map(fn ($b) => \Illuminate\Support\Str::title(mb_strtolower($b)))->implode(', ') }}</td></tr>
        @endif
        @if(!empty($d['materias']))
            <tr><th>Materias más consultadas</th><td>{{ implode(', ', $d['materias']) }}</td></tr>
        @endif
        <tr class="alt"><th>Sanciones vigentes a la fecha de emisión</th><td>{{ $d['sanciones_vigentes'] ? $d['sanciones_vigentes'] : 'Ninguna' }}</td></tr>
    </table>

    <p>Se expide la presente constancia a solicitud de la persona interesada, para los fines que estime conveniente.</p>

    <div class="place">Puerto Maldonado, {{ $fecha($constancia->emitida_at) }}</div>

    <table width="100%" class="verify">
        <tr>
            <td width="190">
                Código de verificación<br>
                <span class="code">{{ $constancia->codigo }}</span>
            </td>
            <td>
                Verifique la autenticidad de esta constancia en:<br>
                <span class="url">{{ $urlVerificacion }}</span><br>
                Vigente hasta el {{ $fecha($constancia->vence_at) }}. Los datos corresponden a la fecha de emisión.
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    <div class="note">Documento generado por el Sistema de Biblioteca UNAMAD. Su validez puede comprobarse con el código de verificación.</div>
    <div class="band-gold"></div>
    <div class="band"></div>
</div>
</body>
</html>
