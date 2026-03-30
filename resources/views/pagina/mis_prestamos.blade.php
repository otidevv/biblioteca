@extends('layouts.biblioteca')

@section('css')
<link href="{{ asset('css/pagina/mis_prestamos.css') }}" rel="stylesheet">
@endsection

@section('js')
<script src="{{ asset('js/pagina/mis_prestamos.js') }}"></script>
@endsection



@section('content')
@php
    $activos = $prestamos->where('estado', 1)->count();
    $finalizados = $prestamos->where('estado', 2)->count();
    $fueraDePlazo = $prestamos->where('estado', 1)->filter(fn($p) => $p->fecha_limite_real && now()->gte($p->fecha_limite_real))->count();
    $proximoVencimiento = $prestamos->where('estado', 1)->filter(fn($p) => $p->fecha_limite_real && now()->lt($p->fecha_limite_real))->sortBy('fecha_limite_real')->first();
@endphp



<div class="prestamos-shell">
    <section class="prestamos-hero">
        <span class="prestamos-eyebrow">
            <i class="bi bi-journal-bookmark-fill"></i>
            Seguimiento de préstamos
        </span>

        <h1 class="prestamos-title">Mis préstamos</h1>
        <p class="prestamos-subtitle">
            Revisa tus ejemplares en curso, controla la fecha límite de devolución y consulta el historial reciente de préstamos realizados.
        </p>

        <div class="prestamos-stats">
            <div class="prestamos-stat-card">
                <span>Activos</span>
                <strong>{{ $activos }}</strong>
            </div>
            <div class="prestamos-stat-card">
                <span>Finalizados</span>
                <strong>{{ $finalizados }}</strong>
            </div>
            <div class="prestamos-stat-card">
                <span>Fuera de plazo</span>
                <strong>{{ $fueraDePlazo }}</strong>
            </div>
        </div>

        @if($proximoVencimiento)
            <div class="prestamos-highlight">
                <span class="prestamos-highlight-icon">
                    <i class="bi bi-hourglass-split"></i>
                </span>
                <div>
                    <strong>Próximo préstamo por vencer</strong>
                    <span>
                        "{{ $proximoVencimiento->ejemplar->libro->titulo }}" debe devolverse hasta el
                        {{ $proximoVencimiento->fecha_limite_real->format('d/m/Y') }} a las 20:00.
                    </span>
                </div>
            </div>
        @endif
    </section>

    @if($prestamos->isEmpty())
        <section class="prestamos-empty">
            <div class="prestamos-empty-icon">
                <i class="bi bi-journal-x"></i>
            </div>
            <h3>Aún no tienes préstamos registrados</h3>
            <p>
                Cuando una reserva sea atendida o se te registre un préstamo, podrás consultarlo aquí con su estado y fecha límite.
            </p>
        </section>
    @else
        <section class="prestamos-card">
            <div class="prestamos-card-header">
                <div>
                    <h2>Historial de préstamos</h2>
                    <p>Consulta el libro, la biblioteca, el tipo de préstamo y el estado actual de cada ejemplar.</p>
                </div>
                <span class="prestamos-stat-badge">
                    <i class="bi bi-collection"></i>
                    {{ $prestamos->count() }} registro{{ $prestamos->count() === 1 ? '' : 's' }}
                </span>
            </div>

            <div class="prestamos-list">
                @foreach($prestamos as $prestamo)
                    @php
                        $fechaLimiteReal = $prestamo->fecha_limite_real;
                        $fueraDePlazoActual = $prestamo->estado == 1 && $fechaLimiteReal && now()->gte($fechaLimiteReal);
                        $tipoPrestamo = (int) ($prestamo->prestamo_lugar ?? $prestamo->prestamo ?? 0);
                        $estadoPrestamo = (int) ($prestamo->estado_prestamo ?? 0);
                        $itemClass = $prestamo->estado == 2 ? 'is-returned' : ($fueraDePlazoActual ? 'is-late' : '');
                    @endphp

                    <article class="prestamos-item {{ $itemClass }}">
                        <div class="prestamos-item-main">
                            <div class="prestamos-item-top">
                                <div class="prestamos-book">
                                    <img
                                        src="{{ $prestamo->ejemplar->libro->imagen_url }}"
                                        alt="{{ $prestamo->ejemplar->libro->titulo }}"
                                        onerror="this.onerror=null;this.src='{{ asset('img/libro-placeholder.png') }}';">

                                    <div>
                                        <div class="prestamos-book-title">{{ $prestamo->ejemplar->libro->titulo }}</div>
                                        <div class="prestamos-book-meta">
                                            {{ $prestamo->ejemplar->codigo ?? 'Sin código' }}
                                        </div>
                                        <div class="prestamos-book-subtitle">
                                            Préstamo gestionado desde {{ $prestamo->ejemplar->biblioteca->nombre ?? 'biblioteca no disponible' }}.
                                        </div>
                                    </div>
                                </div>

                                <span class="prestamos-order">#{{ $loop->iteration }}</span>
                            </div>

                            <div class="prestamos-meta-grid">
                                <div class="prestamos-meta-card">
                                    <span>Biblioteca</span>
                                    <strong>{{ $prestamo->ejemplar->biblioteca->nombre ?? '-' }}</strong>
                                </div>
                                <div class="prestamos-meta-card">
                                    <span>Tipo</span>
                                    <strong>{{ $tipoPrestamo === 1 ? 'Préstamo a casa' : 'Préstamo en sala' }}</strong>
                                </div>
                                <div class="prestamos-meta-card">
                                    <span>Fecha de préstamo</span>
                                    <strong>{{ $prestamo->fecha_prestamo?->format('d/m/Y H:i') ?? '-' }}</strong>
                                </div>
                                <div class="prestamos-meta-card">
                                    <span>Fecha límite</span>
                                    <strong>{{ $fechaLimiteReal ? $fechaLimiteReal->format('d/m/Y') . ' a las 20:00' : '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="prestamos-item-side">
                            <div>
                                @if($tipoPrestamo === 1)
                                    <span class="prestamos-chip is-home">
                                        <i class="bi bi-house-door-fill"></i>
                                        Casa
                                    </span>
                                @else
                                    <span class="prestamos-chip is-room">
                                        <i class="bi bi-book-half"></i>
                                        Sala
                                    </span>
                                @endif
                            </div>

                            <div>
                                @if($prestamo->estado == 1 && $fueraDePlazoActual)
                                    <span class="prestamos-chip is-late">Fuera de plazo</span>
                                @elseif($prestamo->estado == 1)
                                    <span class="prestamos-chip is-active">En curso</span>
                                @else
                                    <span class="prestamos-chip is-returned">Finalizado</span>
                                @endif
                            </div>

                            <div>
                                @if($estadoPrestamo === 3)
                                    <span class="prestamos-chip is-damaged">Deterioro registrado</span>
                                @elseif($estadoPrestamo === 2)
                                    <span class="prestamos-chip is-late">Devuelto con tardanza</span>
                                @elseif($prestamo->estado == 2)
                                    <span class="prestamos-chip is-returned">Devuelto</span>
                                @endif
                            </div>

                            @if($prestamo->estado == 1 && $fechaLimiteReal)
                                <div>
                                    <span class="prestamos-countdown-label">Tiempo restante</span>
                                    <span class="prestamos-countdown" data-fecha="{{ $fechaLimiteReal->format('Y-m-d H:i:s') }}"></span>
                                </div>
                            @elseif($prestamo->fecha_devolucion)
                                <span class="prestamos-stat-badge">
                                    <i class="bi bi-check2-circle"></i>
                                    Devuelto el {{ $prestamo->fecha_devolucion->format('d/m/Y H:i') }}
                                </span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

