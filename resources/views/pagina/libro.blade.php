@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Detalle del libro')
@section('meta_description', 'Consulta la ficha completa del libro, revisa disponibilidad por biblioteca y gestiona tu reserva.')

@section('css')
<link href="{{ asset('css/pagina/libros-grid.css') }}" rel="stylesheet">
<link href="{{ asset('css/pagina/libro.css') }}" rel="stylesheet">
@endsection

@section('js')
<script>
window.libroPage = {
    id: @json($libro->id),
    comentarioUrl: @json(route('comentario')),
    reservarUrl: @json(route('reservar'))
};
</script>
<script src="{{ asset('js/pagina/libro.js') }}"></script>
@endsection



@section('content')
@php
    $autoresLibro = $libro->autores
        ->map(fn($autor) => trim($autor->nombres . ' ' . $autor->apellidos))
        ->filter()
        ->implode(', ');

    $materiasLibro = $libro->materias
        ->pluck('nombre')
        ->filter()
        ->values();

    $editorialNombre = $libro->editorial?->nombre ?? 'Editorial desconocida';
    $idiomaNombre = $libro->idioma?->nombre ?? 'No especificado';
    $tipoRegistro = $libro->tipo_registro?->nombre ?? 'Catalogo general';
    $descripcionLibro = $libro->resumen ?: ($libro->descripcion ?? 'No hay descripcion disponible para este libro.');
    $bibliotecasDisponibles = $bibliotecas->count();
    $ejemplaresConBiblioteca = $libro->ejemplares->filter(fn($ejemplar) => !is_null($ejemplar->biblioteca_id));
    $ejemplaresDisponibles = $ejemplaresConBiblioteca->where('estado', 1)->count();
    $ejemplaresTotales = $ejemplaresConBiblioteca->count();
@endphp



<div class="book-detail-shell">
    <section class="book-hero-card">
        <div class="row g-4 align-items-start">
            <div class="col-12 col-lg-4">
                <div class="book-cover-panel">
                    <div class="book-cover-frame">
                        <img
                            src="{{ $libro->imagen_url }}"
                            alt="{{ $libro->titulo }}"
                            class="book-cover-image"
                            onerror="this.onerror=null;this.src='{{ asset('img/libro-placeholder.png') }}';">
                    </div>

                    <div class="book-cover-stats">
                        <div class="book-stat-item">
                            <div class="book-stat-badge">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <small>Disponible</small>
                            <strong>{{ $ejemplaresDisponibles }}/{{ $ejemplaresTotales }}</strong>
                        </div>
                        <div class="book-stat-item">
                            <div class="book-stat-badge">
                                <i class="bi bi-building"></i>
                            </div>
                            <small>En {{ $bibliotecasDisponibles }}</small>
                            <strong>Sede{{ $bibliotecasDisponibles === 1 ? '' : 's' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="book-summary">
                    <span class="book-eyebrow">
                        <i class="bi bi-journal-bookmark-fill"></i>
                        {{ $tipoRegistro }}
                    </span>

                    <h1 class="book-main-title">{{ $libro->titulo }}</h1>

                    <div class="book-authors">
                        <i class="bi bi-pen-fill"></i>
                        <span>{{ $autoresLibro !== '' ? $autoresLibro : 'Autor no disponible' }}</span>
                    </div>

                    <div class="book-main-rating">
                        <span class="book-main-rating-label">⭐ Calificación</span>
                        <div id="bookMainRatingValue">
                            @include('pagina._rating_summary', [
                                'libro' => $libro,
                                'ratingClass' => 'book-main-rating-stars',
                                'ratingSize' => '1rem',
                            ])
                        </div>
                    </div>

                    <div class="book-chip-row">
                        <span class="book-chip">
                            <i class="bi bi-bookmark-star"></i>
                            {{ $editorialNombre }}
                        </span>
                        <span class="book-chip">
                            <i class="bi bi-translate"></i>
                            {{ $idiomaNombre }}
                        </span>
                        @if($libro->edicion)
                            <span class="book-chip">
                                <i class="bi bi-book-half"></i>
                                Edición {{ $libro->edicion }}
                            </span>
                        @endif
                        @if($libro->anio_edicion)
                            <span class="book-chip">
                                <i class="bi bi-calendar-event"></i>
                                {{ $libro->anio_edicion }}
                            </span>
                        @endif
                    </div>

                    <div class="book-description-card">
                        <h2 class="book-section-title">📖 Resumen</h2>
                        <p class="book-description-text">{{ $descripcionLibro }}</p>
                    </div>

                    <div class="book-quick-specs">
                        <div class="book-spec-item">
                            <span class="book-spec-icon"><i class="bi bi-file-earmark-text"></i></span>
                            <div>
                                <small>Páginas</small>
                                <strong>{{ $libro->paginas ?: 'N/D' }}</strong>
                            </div>
                        </div>
                        <div class="book-spec-item">
                            <span class="book-spec-icon"><i class="bi bi-barcode"></i></span>
                            <div>
                                <small>ISBN</small>
                                <strong>{{ $libro->isbn ?: 'Sin registro' }}</strong>
                            </div>
                        </div>
                        <div class="book-spec-item">
                            <span class="book-spec-icon"><i class="bi bi-diagram-2"></i></span>
                            <div>
                                <small>Código Dewey</small>
                                <strong>{{ $libro->codigo_dewey ?: 'Pendiente' }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($materiasLibro->isNotEmpty())
                        <div class="book-info-card">
                            <h2 class="book-section-title">🏷️ Materias y temas</h2>
                            <div class="book-topic-list">
                                @foreach($materiasLibro as $materia)
                                    <span class="book-topic">
                                        <i class="bi bi-bookmark-check-fill"></i>
                                        {{ $materia }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="book-action-row">
                        <button
                            type="button"
                            class="btn book-action-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalReserva">
                            <i class="bi bi-journal-arrow-down"></i>
                            Solicitar préstamo
                        </button>

                        <a href="{{ route('catalogo') }}" class="btn book-action-secondary">
                            <i class="bi bi-arrow-left-circle"></i>
                            Volver al catálogo
                        </a>
                    </div>

                    <div class="book-info-alert">
                        <div class="book-alert-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div class="book-alert-content">
                            <strong>Disponibilidad en tiempo real</strong>
                            <p class="mb-0">Revisa las sedes con ejemplares disponibles y solicita tu préstamo desde esta misma página.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="book-layout-grid">
        <div class="book-stack">
            <div class="book-info-card">
                <h2 class="book-section-title">Ficha bibliografica</h2>
                <div class="book-info-grid">
                    <div class="book-info-item">
                        <strong>Editorial</strong>
                        <span>{{ $editorialNombre }}</span>
                    </div>
                    <div class="book-info-item">
                        <strong>Idioma</strong>
                        <span>{{ $idiomaNombre }}</span>
                    </div>
                    <div class="book-info-item">
                        <strong>Lugar de publicacion</strong>
                        <span>{{ $libro->lugar_publicacion ?: 'No especificado' }}</span>
                    </div>
                    <div class="book-info-item">
                        <strong>Fecha de publicacion</strong>
                        <span>{{ $libro->fecha_publicacion ?: 'No especificada' }}</span>
                    </div>
                    <div class="book-info-item">
                        <strong>Codigo interno</strong>
                        <span>{{ $libro->codigo ?: ($libro->codigo_ant ?: 'Sin codigo') }}</span>
                    </div>
                    <div class="book-info-item">
                        <strong>Tipo de registro</strong>
                        <span>{{ $tipoRegistro }}</span>
                    </div>
                </div>
            </div>

            <div class="book-info-card">
                <h2 class="book-section-title">Disponibilidad por biblioteca</h2>
                <div class="table-responsive" id="tablaDisponibilidad">
                    @include('pagina._disponibilidad', ['libro' => $libro])
                </div>
            </div>
        </div>

        <div class="book-subgrid">
            <div class="book-info-card">
                <div class="book-section-heading">
                    <h2 class="book-section-title">Ejemplares registrados</h2>
                    <span class="book-section-badge">
                        <i class="bi bi-collection"></i>
                        {{ $ejemplaresTotales }} ejemplar{{ $ejemplaresTotales === 1 ? '' : 'es' }}
                    </span>
                </div>
                <div class="book-copies-grid" id="listaEjemplares">
                    @include('pagina._ejemplares', ['ejemplares' => $ejemplaresConBiblioteca])
                </div>
            </div>
        </div>
    </section>

    <section class="book-related-card p-3 p-lg-4">
        <h2 class="book-section-title mb-3">Libros relacionados</h2>

        <div class="book-related-grid">
            @forelse($libros as $relacionado)
                @php
                    $autoresRelacionado = $relacionado->autores
                        ->map(fn($autor) => trim($autor->nombres . ' ' . $autor->apellidos))
                        ->filter()
                        ->implode(', ');
                @endphp

                <a href="{{ route('libro.show', $relacionado->id) }}"
                   class="catalog-grid-card"
                   aria-label="Ver detalle del libro {{ $relacionado->titulo }}">
                    <div class="catalog-grid-cover-wrap">
                        <img
                            src="{{ $relacionado->imagen_url }}"
                            alt="{{ $relacionado->titulo }}"
                            class="catalog-grid-cover"
                            onerror="this.onerror=null;this.src='{{ asset('img/libro-placeholder.png') }}';"
                            loading="lazy"
                            decoding="async">
                    </div>

                    <div class="catalog-grid-body">
                        <span class="catalog-grid-tag">
                            <i class="bi bi-book-half"></i>
                            Relacionado
                        </span>

                        <h3 class="catalog-grid-title" title="{{ $relacionado->titulo }}">
                            {{ \Illuminate\Support\Str::limit($relacionado->titulo, 46) }}
                        </h3>

                        <div class="catalog-grid-authors">
                            {{ $autoresRelacionado !== '' ? $autoresRelacionado : 'Autor no disponible' }}
                        </div>

                        <div class="catalog-grid-rating">
                            <x-rating-stars :rating="$relacionado->rating_promedio" :count="$relacionado->comentarios_count" />
                        </div>

                        <div class="catalog-grid-meta">
                            <small>
                                <i class="bi bi-journal-text me-1"></i>
                                Disponible en catalogo
                            </small>
                            <span class="catalog-grid-button">Ver detalle</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="book-empty-state">
                    No hay libros relacionados disponibles en este momento.
                </div>
            @endforelse
        </div>
    </section>

    <section class="book-comments-card">
        <h2 class="book-section-title">Comentarios y valoraciones</h2>

        <div class="book-comments-layout">
            <div>
                <div class="book-rating-panel" id="bookRatingSummary">
                    @include('pagina._rating_summary', [
                        'libro' => $libro,
                        'ratingSize' => '1rem',
                    ])
                </div>

                <div id="bookCommentsList">
                    @include('pagina._comentarios', ['comentarios' => $libro->comentarios])
                </div>
            </div>

            <div>
                @auth
                    <div class="book-comment-form-card">
                        <h3 class="book-section-title mb-2">Agregar comentario</h3>
                        <form id="formComentario">
                            @csrf

                            <input type="hidden" name="libro_id" value="{{ $libro->id }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Calificacion</label>
                                <div class="rating">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input
                                            type="radio"
                                            name="rating"
                                            value="{{ $i }}"
                                            id="star{{ $libro->id }}_{{ $i }}">
                                        <label for="star{{ $libro->id }}_{{ $i }}">&#9733;</label>
                                    @endfor
                                </div>
                            </div>

                            <textarea
                                name="comentario"
                                class="form-control mb-3"
                                rows="4"
                                placeholder="Comparte tu experiencia de lectura..."
                                required></textarea>

                            <button class="btn book-action-primary w-100" type="submit">
                                <i class="bi bi-chat-square-text me-2"></i>
                                Publicar comentario
                            </button>
                        </form>
                    </div>
                @else
                    <div class="book-guest-card">
                        <span class="book-guest-badge">
                            <i class="bi bi-stars"></i>
                            Comunidad lectora
                        </span>

                        <h3 class="book-guest-title">Participa en la valoracion</h3>

                        <p class="book-guest-text">
                            Comparte tu experiencia de lectura, ayuda a otros usuarios a descubrir mejores libros y deja una calificacion que aporte a la comunidad.
                        </p>

                        <div class="book-guest-points">
                            <div class="book-guest-point">
                                <i class="bi bi-star-fill"></i>
                                <span>Valora el libro con estrellas segun tu experiencia.</span>
                            </div>
                            <div class="book-guest-point">
                                <i class="bi bi-chat-quote-fill"></i>
                                <span>Escribe una opinion breve para orientar a otros lectores.</span>
                            </div>
                            <div class="book-guest-point">
                                <i class="bi bi-person-check-fill"></i>
                                <span>Necesitas iniciar sesion para publicar comentarios y calificaciones.</span>
                            </div>
                        </div>

                        <div class="book-guest-actions">
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn book-action-primary book-guest-login">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Iniciar sesion
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaTitle" aria-describedby="modalReservaDescription" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content book-modal-content">
            <div class="modal-header book-modal-header border-0">
                <div>
                    <h5 class="modal-title book-modal-title" id="modalReservaTitle">Solicitar prestamo</h5>
                    <small class="book-modal-subtitle" id="modalReservaDescription">Elige la sede, selecciona un ejemplar disponible y define la modalidad del prestamo.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar modal de prestamo"></button>
            </div>

            <div class="modal-body book-modal-body">
                @auth
                    <div class="book-modal-summary">
                        <div class="book-modal-stat">
                            <span>Titulo</span>
                            <strong>{{ $libro->titulo }}</strong>
                        </div>
                        <div class="book-modal-stat">
                            <span>Disponibles</span>
                            <strong>{{ $ejemplaresDisponibles }} ejemplar{{ $ejemplaresDisponibles === 1 ? '' : 'es' }}</strong>
                        </div>
                        <div class="book-modal-stat">
                            <span>Sedes</span>
                            <strong>{{ $bibliotecasDisponibles }} biblioteca{{ $bibliotecasDisponibles === 1 ? '' : 's' }}</strong>
                        </div>
                    </div>

                    <form id="formReserva" class="book-modal-form" novalidate>
                        @csrf

                        <h6 class="book-modal-section-title">Datos de la solicitud</h6>
                        <p class="book-modal-section-text">
                            Completa los siguientes campos para registrar tu reserva correctamente.
                        </p>

                        <div class="mb-3">
                            <label for="biblioteca_select" class="form-label fw-semibold">Biblioteca</label>
                            <select id="biblioteca_select" class="form-control" aria-describedby="bibliotecaSelectHelp" required>
                                <option value="">-- Seleccionar biblioteca --</option>
                                @foreach($bibliotecas as $biblioteca)
                                    <option value="{{ $biblioteca->id }}">{{ $biblioteca->nombre }}</option>
                                @endforeach
                            </select>
                            <small id="bibliotecaSelectHelp" class="book-modal-field-help">
                                Selecciona la sede donde deseas consultar la disponibilidad del ejemplar.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="ejemplar_select" class="form-label fw-semibold">Ejemplar</label>
                            <select name="ejemplar_id" id="ejemplar_select" class="form-control" aria-describedby="ejemplarSelectHelp" disabled required>
                                <option value="">-- Seleccione una biblioteca primero --</option>
                            </select>
                            <small id="ejemplarSelectHelp" class="book-modal-field-help">
                                La lista se habilitara cuando elijas una biblioteca con ejemplares disponibles.
                            </small>
                        </div>

                        <div class="mb-0">
                            <label for="tipo_prestamo" class="form-label fw-semibold">Tipo de prestamo</label>
                            <select name="tipo_prestamo" id="tipo_prestamo" class="form-control" aria-describedby="tipoPrestamoHelp" required>
                                <option value="0">Prestamo en sala</option>
                                <option value="1">Prestamo a casa</option>
                            </select>
                            <small id="tipoPrestamoHelp" class="book-modal-field-help">
                                El tipo de prestamo define las condiciones de uso y entrega del ejemplar.
                            </small>
                        </div>

                    </form>

                    <div class="book-modal-help">
                        <i class="bi bi-clock-history"></i>
                        <div>
                            La reserva sera valida hasta manana a las <strong>20:00</strong>. Selecciona primero una biblioteca para ver los ejemplares disponibles en esa sede.
                        </div>
                    </div>

                    <div class="book-modal-footer">
                        <button type="button" class="btn book-modal-cancel" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn book-action-primary flex-grow-1" type="submit" form="formReserva">
                            <i class="bi bi-journal-check me-2"></i>
                            Confirmar reserva
                        </button>
                    </div>
                @else
                    <div class="alert alert-warning rounded-4 border-0 mb-0">
                        Debes iniciar sesion para solicitar un prestamo de este libro.
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
