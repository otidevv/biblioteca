@extends('layouts.admin')

@section('page-title', 'Resumen del sistema')

@section('content')
<div class="admin-dashboard">
    <section class="admin-dashboard__hero">
        <div class="admin-dashboard__eyebrow">Biblioteca UNAMAD</div>
        <h2 class="admin-dashboard__headline">Administra catálogos, lectores y circulación desde un panel más claro y rápido.</h2>
        <p class="admin-dashboard__copy">
            Centraliza la operación diaria de la biblioteca, revisa el estado general del sistema y entra rápido a los módulos que más usas.
        </p>

        <div class="admin-dashboard__actions">
            <a href="{{ url('/administracion/libros') }}" class="admin-cta admin-cta--primary">Gestionar libros</a>
            <a href="{{ url('/prestamos/registro') }}" class="admin-cta admin-cta--secondary">Ir a préstamos</a>
        </div>
    </section>

    <section class="admin-dashboard__stats">
        <article class="admin-stat">
            <div class="admin-stat__label">Catálogo</div>
            <div class="admin-stat__value">{{ number_format($totalLibros ?? 0) }}</div>
            <div class="admin-stat__hint">Registros bibliográficos disponibles en el sistema.</div>
        </article>

        <article class="admin-stat">
            <div class="admin-stat__label">Usuarios</div>
            <div class="admin-stat__value">{{ number_format($totalUsuarios ?? 0) }}</div>
            <div class="admin-stat__hint">Cuentas activas para administración y atención a lectores.</div>
        </article>

        <article class="admin-stat">
            <div class="admin-stat__label">Préstamos activos</div>
            <div class="admin-stat__value">{{ number_format($prestamosActivos ?? 0) }}</div>
            <div class="admin-stat__hint">Material actualmente en circulación o pendiente de devolución.</div>
        </article>

        <article class="admin-stat">
            <div class="admin-stat__label">Bibliotecas</div>
            <div class="admin-stat__value">{{ number_format($totalBibliotecas ?? 0) }}</div>
            <div class="admin-stat__hint">Sedes y puntos de atención disponibles en la red.</div>
        </article>
    </section>

    <section class="admin-dashboard__grid">
        <article class="admin-card">
            <div class="admin-card__header">
                <div>
                    <div class="admin-card__eyebrow">Accesos rápidos</div>
                    <h3 class="admin-card__title">Módulos principales</h3>
                </div>
            </div>

            <div class="admin-quick-links">
                <a href="{{ url('/administracion/libros') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">L</div>
                    <div class="admin-quick-link__title">Libros</div>
                    <div class="admin-quick-link__copy">Consulta, edita y organiza el catálogo bibliográfico.</div>
                </a>

                <a href="{{ url('/administracion/libros_nuevo') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">+</div>
                    <div class="admin-quick-link__title">Nuevo libro</div>
                    <div class="admin-quick-link__copy">Registra material nuevo y completa sus datos técnicos.</div>
                </a>

                <a href="{{ url('/lectores/registro') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">U</div>
                    <div class="admin-quick-link__title">Lectores</div>
                    <div class="admin-quick-link__copy">Gestiona lectores, historiales y seguimiento de usuarios.</div>
                </a>

                <a href="{{ url('/prestamos/reservas') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">P</div>
                    <div class="admin-quick-link__title">Reservas y préstamos</div>
                    <div class="admin-quick-link__copy">Atiende la circulación diaria y el estado del material.</div>
                </a>
            </div>
        </article>

        <article class="admin-card">
            <div class="admin-card__header">
                <div>
                    <div class="admin-card__eyebrow">Actividad reciente</div>
                    <h3 class="admin-card__title">Últimos libros registrados</h3>
                </div>
            </div>

            @if(($librosRecientes ?? collect())->isNotEmpty())
                <div class="admin-feed">
                    @foreach($librosRecientes as $libro)
                        <div class="admin-feed__item">
                            <div class="admin-feed__badge">{{ strtoupper(substr($libro->titulo, 0, 1)) }}</div>
                            <div>
                                <div class="admin-feed__title">{{ $libro->titulo }}</div>
                                <div class="admin-feed__meta">
                                    {{ $libro->autores->pluck('nombres')->join(', ') ?: 'Sin autor registrado' }}
                                    @if($libro->editorial)
                                        · {{ $libro->editorial->nombre }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="admin-empty">Aún no hay libros recientes para mostrar en este panel.</div>
            @endif
        </article>
    </section>

    <div class="admin-footer-note">
        Panel institucional de gestión bibliotecaria UNAMAD.
    </div>
</div>
@endsection
