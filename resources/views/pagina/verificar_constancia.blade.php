@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Verificar constancia')

@section('css')
<link href="{{ asset('css/pagina/panel_lector.css') }}?v={{ filemtime(public_path('css/pagina/panel_lector.css')) }}" rel="stylesheet">
@endsection

@section('content')
@php
    $fecha = fn ($valor) => \Illuminate\Support\Carbon::parse($valor)->locale('es')->translatedFormat('j \d\e F \d\e Y');
    $d = $constancia?->datos ?? [];
@endphp

<div class="lp lp-verify">
    <section class="lp-panel lp-verify__box">
        <header class="lp-panel__head">
            <div>
                <span class="lp-kicker"><i class="bi bi-patch-check"></i> Biblioteca UNAMAD</span>
                <h1 class="lp-verify__title">Verificar constancia de lector</h1>
                <p>Ingresa el código que aparece en la constancia (por ejemplo, CL-ABCD-2345).</p>
            </div>
        </header>

        <form method="GET" action="{{ route('constancias.verificar') }}" class="lp-verify__form">
            <label for="codigo" class="visually-hidden">Código de verificación</label>
            <input type="text" id="codigo" name="codigo" value="{{ $codigo }}" placeholder="CL-XXXX-XXXX"
                   maxlength="12" autocomplete="off" spellcheck="false" required>
            <button type="submit" class="lp-btn lp-btn--primary"><i class="bi bi-search"></i> Verificar</button>
        </form>

        @if($codigo && ! $constancia)
            <div class="lp-alert lp-alert--critical lp-verify__result" role="alert">
                <i class="bi bi-x-octagon-fill" aria-hidden="true"></i>
                <div>
                    <strong>No encontramos una constancia con el código {{ $codigo }}</strong>
                    <span>Revisa que el código esté bien escrito. Si el problema continúa, consulta en la biblioteca.</span>
                </div>
            </div>
        @elseif($constancia)
            @if($constancia->estaVigente())
                <div class="lp-verify__status is-valid" role="status">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    <div>
                        <strong>Constancia válida</strong>
                        <span>Emitida por el Sistema de Biblioteca UNAMAD. Vigente hasta el {{ $fecha($constancia->vence_at) }}.</span>
                    </div>
                </div>
            @else
                <div class="lp-alert lp-alert--warning lp-verify__result" role="status">
                    <i class="bi bi-hourglass-bottom" aria-hidden="true"></i>
                    <div>
                        <strong>Constancia auténtica, pero vencida</strong>
                        <span>Venció el {{ $fecha($constancia->vence_at) }}. La persona titular puede emitir una nueva desde su panel.</span>
                    </div>
                </div>
            @endif

            <dl class="lp-verify__data">
                <div><dt>Titular</dt><dd>{{ mb_strtoupper($d['nombre'] ?? '-') }}</dd></div>
                <div><dt>DNI</dt><dd>{{ $constancia->dniEnmascarado() ?? '-' }}</dd></div>
                @if(!empty($d['codigo_estudiante']))
                    <div><dt>Código de estudiante</dt><dd>{{ $d['codigo_estudiante'] }}</dd></div>
                @endif
                @if(!empty($d['carrera']))
                    <div><dt>Carrera</dt><dd>{{ \Illuminate\Support\Str::title(mb_strtolower($d['carrera'])) }}</dd></div>
                @endif
                <div><dt>Nivel certificado</dt><dd>{{ $d['nivel_nombre'] ?? '-' }}</dd></div>
                <div><dt>Periodo evaluado</dt><dd>{{ $fecha($d['periodo_desde']) }} al {{ $fecha($d['periodo_hasta']) }}</dd></div>
                <div><dt>Préstamos en el periodo</dt><dd>{{ $d['prestamos_periodo'] ?? 0 }}</dd></div>
                <div><dt>Meses con actividad</dt><dd>{{ $d['meses_activos'] ?? 0 }} de {{ $d['meses_periodo'] ?? 6 }}</dd></div>
                <div><dt>Emitida el</dt><dd>{{ $fecha($constancia->emitida_at) }}</dd></div>
                <div><dt>Código</dt><dd>{{ $constancia->codigo }}</dd></div>
            </dl>
        @endif
    </section>
</div>
@endsection
