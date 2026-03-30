@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Inicio')
@section('meta_description', 'Portada de la Biblioteca UNAMAD con acceso al catalogo, bibliotecas disponibles y libros recientes.')

@section('css')
<link href="{{ asset('css/pagina/index.css') }}" rel="stylesheet">
@endsection

@section('content')
@php
    $totalBibliotecas = $bibliotecas->count();
    $totalLibrosRecientes = $libros->count();
    $totalActividades = $actividades->count();
@endphp



<section class="home-hero">
    <span class="home-eyebrow">
        <i class="bi bi-mortarboard-fill"></i>
        Universidad Nacional Amazonica de Madre de Dios
    </span>

    <h2>Biblioteca UNAMAD para descubrir, consultar y reservar conocimiento.</h2>
    <p>
        Accede al catÃ¡logo institucional, revisa disponibilidad por biblioteca y encuentra publicaciones recientes
        desde una portada mÃ¡s clara, rÃ¡pida y orientada a la consulta.
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
                <span><i class="bi bi-calendar-event-fill"></i></span>
                <h3>{{ $totalActividades }}</h3>
                <p>Actividades activas o proximas dentro de la agenda de biblioteca.</p>
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
            <h3 class="home-section-title">Actividades en curso</h3>
            <p class="home-section-subtitle">Talleres, encuentros y avisos activos para la comunidad universitaria.</p>
        </div>
        <a href="{{ route('evento') }}" class="home-link">
            Ver agenda completa <i class="bi bi-arrow-right-short"></i>
        </a>
    </div>

    <div class="row g-4">
        @forelse($actividades as $actividad)
        <div class="col-12 col-md-6 col-xl-3">
            <article class="home-activity-card">
                <div class="home-activity-meta">
                    <span class="home-activity-badge">
                        <i class="bi bi-calendar2-week"></i>
                        {{ $actividad->categoria->nombre ?? 'Actividad' }}
                    </span>
                    <span class="home-activity-date">
                        {{ $actividad->fecha_inicio?->format('d/m/Y') ?? 'Fecha por definir' }}
                    </span>
                </div>

                <h6>{{ \Illuminate\Support\Str::limit($actividad->titulo, 70) }}</h6>
                <div class="home-activity-copy">
                    {{ \Illuminate\Support\Str::limit(strip_tags($actividad->contenido ?: $actividad->referencia ?: 'Actividad disponible en la agenda de biblioteca.'), 120) }}
                </div>

                <div class="home-activity-footer">
                    <span>{{ $actividad->referencia ?: 'Biblioteca UNAMAD' }}</span>
                    <a href="{{ route('evento') }}" class="home-link">Ver mas</a>
                </div>
            </article>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-light border rounded-4 mb-0">
                No hay actividades activas para mostrar en este momento.
            </div>
        </div>
        @endforelse
    </div>
</section>
<section class="home-section">
    <div class="home-section-header">
        <div>
            <h3 class="home-section-title">Libros recientes</h3>
            <p class="home-section-subtitle">Una selecciÃ³n rÃ¡pida para empezar a explorar el catÃ¡logo.</p>
        </div>
        <a href="{{ route('catalogo') }}" class="home-link">
            Ir al catalogo <i class="bi bi-arrow-right-short"></i>
        </a>
    </div>

    <div class="row g-4">
        @forelse($libros as $libro)
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            @php
                $autoresLibro = $libro->autores
                    ->map(fn($autor) => trim($autor->nombres . ' ' . $autor->apellidos))
                    ->filter()
                    ->implode(', ');
            @endphp
            <a href="{{ route('libro.show', $libro->id) }}"
               class="home-book-card"
               aria-label="Ver detalle del libro {{ $libro->titulo }}">
                <div class="home-book-cover-wrap">
                    <img src="{{ $libro->imagen_url }}"
                         alt="{{ $libro->titulo }}"
                         class="home-book-cover"
                         onerror="this.onerror=null;this.src='{{ asset('img/libro-placeholder.png') }}';"
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
                        {{ $autoresLibro !== '' ? $autoresLibro : 'Autor no disponible' }}
                    </div>

                    <div class="home-book-rating">
                        <x-rating-stars :rating="$libro->rating_promedio" :count="$libro->comentarios_count" />
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


