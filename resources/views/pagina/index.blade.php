@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Inicio')
@section('meta_description', 'Portada de la Biblioteca UNAMAD con acceso al catalogo, bibliotecas disponibles y libros recientes.')

@section('content')
@php
    $totalBibliotecas = $bibliotecas->count();
    $totalLibrosRecientes = $libros->count();
@endphp

<style>
.home-hero {
    position: relative;
    overflow: hidden;
    padding: 2.25rem;
    border-radius: 1.8rem;
    background:
        linear-gradient(135deg, rgba(16, 53, 40, 0.96), rgba(34, 103, 76, 0.9)),
        url('{{ asset('img/banner1.png') }}') center/cover;
    color: #fff;
    box-shadow: 0 22px 55px rgba(16, 53, 40, 0.22);
}

.home-hero::after {
    content: "";
    position: absolute;
    inset: auto -90px -90px auto;
    width: 240px;
    height: 240px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(244, 206, 120, 0.34), transparent 68%);
}

.home-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    color: #f8e3a8;
    font-size: 0.82rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.home-hero h2 {
    margin-top: 1rem;
    margin-bottom: 0.85rem;
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 800;
    line-height: 1.06;
}

.home-hero p {
    max-width: 680px;
    margin-bottom: 1.5rem;
    color: rgba(255, 255, 255, 0.84);
    font-size: 1rem;
}

.home-search-card {
    position: relative;
    z-index: 1;
    padding: 1rem;
    border-radius: 1.35rem;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.14);
    backdrop-filter: blur(10px);
}

.home-search-card .input-group-text,
.home-search-card .form-control,
.home-search-card .btn {
    border: 0;
    min-height: 52px;
}

.home-search-card .input-group-text {
    color: #1b4f3d;
    background: #fff;
}

.home-search-card .form-control {
    box-shadow: none;
}

.home-search-card .btn {
    padding-inline: 1.3rem;
    border-radius: 1rem !important;
    background: linear-gradient(135deg, #f2cf82, #dfb451);
    color: #173d2f;
    font-weight: 700;
}

.home-stats {
    margin-top: 1.5rem;
}

.home-stat-card {
    height: 100%;
    padding: 1.1rem 1.15rem;
    border-radius: 1.25rem;
    background: rgba(255, 255, 255, 0.76);
    border: 1px solid rgba(24, 77, 59, 0.08);
    box-shadow: 0 16px 30px rgba(24, 77, 59, 0.08);
}

.home-stat-card span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    margin-bottom: 0.9rem;
    border-radius: 14px;
    color: #fff;
    font-size: 1.05rem;
    background: linear-gradient(135deg, #2b7a5d, #123b2e);
}

.home-stat-card h3 {
    margin: 0;
    color: #163c2e;
    font-size: 1.85rem;
    font-weight: 800;
}

.home-stat-card p {
    margin: 0.35rem 0 0;
    color: #60726b;
}

.home-section {
    margin-top: 2rem;
}

.home-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.home-section-title {
    margin: 0;
    color: #173d2f;
    font-size: 1.45rem;
    font-weight: 800;
}

.home-section-subtitle {
    margin: 0.3rem 0 0;
    color: #6c7d76;
    font-size: 0.95rem;
}

.home-link {
    color: #1d654c;
    text-decoration: none;
    font-weight: 700;
}

.home-library-card {
    display: block;
    height: 100%;
    overflow: hidden;
    border: 0;
    border-radius: 1.4rem;
    background: rgba(255, 255, 255, 0.84);
    box-shadow: 0 16px 36px rgba(24, 77, 59, 0.1);
    color: inherit;
    text-decoration: none;
    transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.home-library-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 22px 44px rgba(24, 77, 59, 0.16);
}

.home-library-cover {
    width: 100%;
    height: 180px;
    object-fit: cover;
    background: linear-gradient(135deg, #dde9e2, #f4f2ea);
}

.home-library-body {
    padding: 1rem 1rem 1.15rem;
}

.home-library-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.65rem;
    margin-bottom: 0.75rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: #205842;
    font-size: 0.78rem;
    font-weight: 700;
}

.home-library-card h5 {
    color: #173d2f;
    font-weight: 800;
}

.home-library-card p {
    color: #6a7b74;
    margin-bottom: 0.9rem;
}

.home-library-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #1f674d;
    font-weight: 700;
}

.home-book-card {
    display: block;
    height: 100%;
    border: 0;
    border-radius: 1.45rem;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.88);
    box-shadow: 0 16px 36px rgba(24, 77, 59, 0.08);
    transition: transform 0.22s ease, box-shadow 0.22s ease;
    color: inherit;
    text-decoration: none;
}

.home-book-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 24px 50px rgba(24, 77, 59, 0.16);
}

.home-book-cover-wrap {
    padding: 1rem 1rem 0;
}

.home-book-cover {
    width: 100%;
    height: 300px;
    object-fit: contain;
    border-radius: 1rem;
    background: linear-gradient(180deg, #fbfaf5, #eef2ec);
    padding: 0.85rem;
}

.home-book-body {
    padding: 1rem 1rem 1.1rem;
}

.home-book-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.32rem 0.6rem;
    margin-bottom: 0.65rem;
    border-radius: 999px;
    background: rgba(216, 177, 92, 0.16);
    color: #946418;
    font-size: 0.77rem;
    font-weight: 700;
}

.home-book-card h6 {
    margin-bottom: 0.45rem;
    color: #1c4032;
    font-weight: 800;
    font-size: 1rem;
}

.home-book-authors {
    min-height: 2.7rem;
    color: #6c7d76;
    font-size: 0.9rem;
}

.home-book-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-top: 1rem;
}

.home-book-meta small {
    color: #688078;
}

.home-book-button {
    padding: 0.55rem 0.85rem;
    border-radius: 0.9rem;
    color: #fff;
    background: linear-gradient(135deg, #2b7a5d, #11392c);
    font-size: 0.88rem;
    font-weight: 700;
}

.home-library-card:focus-visible,
.home-book-card:focus-visible,
.home-link:focus-visible {
    outline: 3px solid rgba(43, 122, 93, 0.45);
    outline-offset: 4px;
}

@media (max-width: 767.98px) {
    .home-hero {
        padding: 1.5rem;
    }

    .home-section-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<section class="home-hero">
    <span class="home-eyebrow">
        <i class="bi bi-mortarboard-fill"></i>
        Universidad Nacional Amazonica de Madre de Dios
    </span>

    <h2>Biblioteca UNAMAD para descubrir, consultar y reservar conocimiento.</h2>
    <p>
        Accede al catálogo institucional, revisa disponibilidad por biblioteca y encuentra publicaciones recientes
        desde una portada más clara, rápida y orientada a la consulta.
    </p>

    <div class="home-search-card">
        <form action="{{ route('catalogo') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text"
                       name="titulo"
                       class="form-control"
                       placeholder="Buscar por titulo, autor o palabra clave"
                       value="{{ request('titulo') }}">
                <button class="btn" type="submit">
                    Buscar ahora
                </button>
            </div>
        </form>
    </div>
</section>

<section class="home-stats">
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="home-stat-card">
                <span><i class="bi bi-buildings-fill"></i></span>
                <h3>{{ $totalBibliotecas }}</h3>
                <p>Bibliotecas disponibles para consulta y recorrido.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="home-stat-card">
                <span><i class="bi bi-journal-richtext"></i></span>
                <h3>{{ $totalLibrosRecientes }}</h3>
                <p>Títulos recientes destacados en esta portada.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="home-stat-card">
                <span><i class="bi bi-lightning-charge-fill"></i></span>
                <h3>24/7</h3>
                <p>Consulta rápida del catálogo desde cualquier momento.</p>
            </div>
        </div>
    </div>
</section>

<section class="home-section">
    <div class="home-section-header">
        <div>
            <h3 class="home-section-title">Bibliotecas</h3>
            <p class="home-section-subtitle">Explora las sedes y espacios de consulta disponibles.</p>
        </div>
        <a href="{{ route('catalogo') }}" class="home-link">
            Ver catalogo completo <i class="bi bi-arrow-right-short"></i>
        </a>
    </div>

    <div class="row g-4">
        @forelse($bibliotecas as $b)
        <div class="col-12 col-sm-6 col-xl-4">
            <a href="{{ route('biblioteca.show', $b->id) }}"
               class="home-library-card"
               aria-label="Explorar biblioteca {{ $b->nombre }}">
                <img src="{{ $b->imagen ?: asset('img/banner.png') }}"
                     alt="{{ $b->nombre }}"
                     class="home-library-cover"
                     loading="lazy"
                     decoding="async">

                <div class="home-library-body">
                    <span class="home-library-badge">
                        <i class="bi bi-geo-alt-fill"></i>
                        Sede bibliotecaria
                    </span>
                    <h5>{{ $b->nombre }}</h5>
                    <p>{{ \Illuminate\Support\Str::limit($b->descripcion ?: 'Espacio de acceso bibliografico de la universidad.', 110) }}</p>

                    <div class="home-library-footer">
                        <span>Explorar biblioteca</span>
                        <i class="bi bi-arrow-up-right-circle-fill"></i>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-light border rounded-4 mb-0">
                No hay bibliotecas registradas todavia.
            </div>
        </div>
        @endforelse
    </div>
</section>

<section class="home-section">
    <div class="home-section-header">
        <div>
            <h3 class="home-section-title">Libros recientes</h3>
            <p class="home-section-subtitle">Una selección rápida para empezar a explorar el catálogo.</p>
        </div>
        <a href="{{ route('catalogo') }}" class="home-link">
            Ir al catalogo <i class="bi bi-arrow-right-short"></i>
        </a>
    </div>

    <div class="row g-4">
        @forelse($libros as $libro)
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <a href="{{ route('libro.show', $libro->id) }}"
               class="home-book-card"
               aria-label="Ver detalle del libro {{ $libro->titulo }}">
                <div class="home-book-cover-wrap">
                    <img src="{{ $libro->imagen ?: asset('img/banner1.png') }}"
                         alt="{{ $libro->titulo }}"
                         class="home-book-cover"
                         loading="lazy"
                         decoding="async">
                </div>

                <div class="home-book-body">
                    <span class="home-book-tag">
                        <i class="bi bi-book-half"></i>
                        Recomendado
                    </span>

                    <h6 title="{{ $libro->titulo }}">
                        {{ \Illuminate\Support\Str::limit($libro->titulo, 56) }}
                    </h6>

                    <div class="home-book-authors">
                        @forelse($libro->autores as $autor)
                            {{ $autor->nombres }} {{ $autor->apellidos }}@if(! $loop->last), @endif
                        @empty
                            Autor no disponible
                        @endforelse
                    </div>

                    <div class="home-book-meta">
                        <small>
                            <i class="bi bi-clock-history me-1"></i>
                            Reciente
                        </small>
                        <span class="home-book-button">Ver detalle</span>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-light border rounded-4 mb-0">
                No hay libros recientes para mostrar por ahora.
            </div>
        </div>
        @endforelse
    </div>
</section>
@endsection
