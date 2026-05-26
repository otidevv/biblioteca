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
<script>
(function () {
    // Botón compartir
    document.getElementById('btnShare')?.addEventListener('click', function () {
        const icon = this.querySelector('i');
        if (navigator.share) {
            navigator.share({ title: document.title, url: location.href });
        } else {
            navigator.clipboard.writeText(location.href).then(() => {
                icon.className = 'bi bi-check2';
                setTimeout(() => { icon.className = 'bi bi-share'; }, 2000);
            });
        }
    });

    // Expandir/colapsar descripción
    document.getElementById('btnToggleDesc')?.addEventListener('click', function () {
        const desc = document.getElementById('bookDescText');
        const expanded = desc.classList.toggle('book-desc-expanded');
        this.innerHTML = expanded
            ? '<i class="bi bi-chevron-up"></i> Ver menos'
            : '<i class="bi bi-chevron-down"></i> Ver descripción completa';
    });

    // Ocultar sticky CTA cuando el botón principal está visible
    const heroCta = document.querySelector('.book-action-row .book-action-primary');
    const stickyCta = document.getElementById('bookStickyCta');
    if (heroCta && stickyCta && 'IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            stickyCta.classList.toggle('book-sticky-hidden', entry.isIntersecting);
        }, { threshold: 0.5 }).observe(heroCta);
    }

    // Contador de caracteres del comentario
    const textarea = document.getElementById('comentarioTextarea');
    const charCount = document.getElementById('comentarioCharCount');
    if (textarea && charCount) {
        const actualizarContador = (len) => {
            charCount.textContent = len;
            charCount.closest('.book-char-counter').classList.toggle('book-char-counter--warn', len >= 450);
            charCount.closest('.book-char-counter').classList.toggle('book-char-counter--max', len >= 500);
        };
        actualizarContador(textarea.value.length);
        textarea.addEventListener('input', function () { actualizarContador(this.value.length); });
    }
})();
</script>
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
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <small>Disponible</small>
                            <strong>{{ $ejemplaresDisponibles }}/{{ $ejemplaresTotales }}</strong>
                        </div>
                        <div class="book-stat-item">
                            <div class="book-stat-badge">
                                <i class="bi bi-doorway"></i>
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
                        <i class="bi bi-book-fill"></i>
                        {{ $tipoRegistro }}
                    </span>

                    <h1 class="book-main-title">{{ $libro->titulo }}</h1>

                    <div class="book-authors">
                        <i class="bi bi-quill"></i>
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
                            <i class="bi bi-star-fill"></i>
                            {{ $editorialNombre }}
                        </span>
                        <span class="book-chip">
                            <i class="bi bi-globe"></i>
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

                    @php $longDesc = mb_strlen($descripcionLibro) > 350; @endphp
                    <div class="book-description-card">
                        <h2 class="book-section-title">Resumen</h2>
                        <p class="book-description-text{{ $longDesc ? ' book-desc-clamped' : '' }}" id="bookDescText">{{ $descripcionLibro }}</p>
                        @if($longDesc)
                            <button type="button" class="book-desc-toggle" id="btnToggleDesc">
                                <i class="bi bi-chevron-down"></i> Ver descripción completa
                            </button>
                        @endif
                    </div>

                    <div class="book-quick-specs">
                        <div class="book-spec-item">
                            <span class="book-spec-icon"><i class="bi bi-file-text-fill"></i></span>
                            <div>
                                <small>Páginas</small>
                                <strong>{{ $libro->paginas ?: 'N/D' }}</strong>
                            </div>
                        </div>
                        <div class="book-spec-item">
                            <span class="book-spec-icon"><i class="bi bi-qr-code"></i></span>
                            <div>
                                <small>ISBN</small>
                                <strong>{{ $libro->isbn ?: 'Sin registro' }}</strong>
                            </div>
                        </div>
                        <div class="book-spec-item">
                            <span class="book-spec-icon"><i class="bi bi-diagram-3-fill"></i></span>
                            <div>
                                <small>Código Dewey</small>
                                <strong>{{ $libro->codigo_dewey ?: 'Pendiente' }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($materiasLibro->isNotEmpty())
                        <div class="book-info-card">
                            <h2 class="book-section-title">Materias y temas</h2>
                            <div class="book-topic-list">
                                @foreach($materiasLibro as $materia)
                                    <span class="book-topic">
                                        <i class="bi bi-bookmark-heart-fill"></i>
                                        {{ $materia }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="book-action-row">
                        @if($ejemplaresDisponibles > 0)
                            <button
                                type="button"
                                class="btn book-action-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalReserva">
                                <i class="bi bi-bookmark-plus"></i>
                                Solicitar préstamo
                                <span class="book-btn-badge">{{ $ejemplaresDisponibles }}</span>
                            </button>
                        @else
                            <button type="button" class="btn book-action-primary book-action-unavailable" disabled>
                                <i class="bi bi-bookmark-x"></i>
                                Sin ejemplares disponibles
                            </button>
                        @endif

                        <a href="{{ route('catalogo') }}" class="btn book-action-secondary">
                            <i class="bi bi-arrow-left"></i>
                            Volver al catálogo
                        </a>

                        <button type="button" class="btn book-action-secondary book-share-btn" id="btnShare" title="Compartir este libro">
                            <i class="bi bi-share"></i>
                        </button>
                    </div>

                    <div class="book-info-alert">
                        <div class="book-alert-icon">
                            <i class="bi bi-info-circle-fill"></i>
                        </div>
                        <div class="book-alert-content">
                            <strong>Disponibilidad en tiempo real</strong>
                            <p class="mb-0">
                                <a href="#seccion-disponibilidad" class="book-alert-link">Revisa las sedes con ejemplares disponibles</a>
                                y solicita tu préstamo desde esta misma página.
                            </p>
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
                        <span>{{ $libro->codigo_ant ?: 'Sin codigo' }}</span>
                    </div>
                    <div class="book-info-item">
                        <strong>Tipo de registro</strong>
                        <span>{{ $tipoRegistro }}</span>
                    </div>
                </div>
            </div>

            <div class="book-info-card" id="seccion-disponibilidad">
                <h2 class="book-section-title">Disponibilidad por biblioteca</h2>
                <div id="tablaDisponibilidad">
                    @include('pagina._disponibilidad', ['libro' => $libro])
                </div>
            </div>
        </div>

        <div class="book-subgrid">
            <div class="book-info-card">
                <div class="book-section-heading">
                    <h2 class="book-section-title">Ejemplares registrados</h2>
                    <span class="book-section-badge">
                        <i class="bi bi-collection-fill"></i>
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
        <div class="book-section-heading">
            <h2 class="book-section-title">Comentarios y valoraciones</h2>
            @if($libro->comentarios_count > 0)
                <span class="book-section-badge">
                    <i class="bi bi-chat-fill"></i>
                    {{ $libro->comentarios_count }} reseña{{ $libro->comentarios_count !== 1 ? 's' : '' }}
                </span>
            @endif
        </div>

        <div class="book-comments-layout">
            <div>
                <div class="book-rating-panel" id="bookRatingSummary">
                    @include('pagina._rating_summary', [
                        'libro' => $libro,
                        'ratingSize' => '1.05rem',
                    ])
                </div>

                <div id="bookCommentsList">
                    @include('pagina._comentarios', ['comentarios' => $libro->comentarios])
                </div>
            </div>

            <div>
                @auth
                    <div class="book-comment-form-card">
                        @if($miComentario)
                            <h3 class="book-section-title mb-1">Tu reseña</h3>
                            <p class="book-comment-form-hint">Ya has valorado este libro. Puedes actualizar tu reseña aquí o editarla directamente desde el listado.</p>
                        @else
                            <h3 class="book-section-title mb-1">Tu reseña</h3>
                            <p class="book-comment-form-hint">Califica y comparte tu experiencia con este libro.</p>
                        @endif

                        <form id="formComentario" novalidate>
                            @csrf
                            <input type="hidden" name="libro_id" value="{{ $libro->id }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Calificación</label>
                                <div class="rating">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input
                                            type="radio"
                                            name="rating"
                                            value="{{ $i }}"
                                            id="star{{ $libro->id }}_{{ $i }}"
                                            {{ $miComentario && (int)$miComentario->calificacion === $i ? 'checked' : '' }}>
                                        <label for="star{{ $libro->id }}_{{ $i }}" title="{{ $i }} estrella{{ $i !== 1 ? 's' : '' }}">&#9733;</label>
                                    @endfor
                                </div>
                                <small class="book-modal-field-help mt-1">Selecciona cuántas estrellas merece este libro.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Comentario</label>
                                <textarea
                                    name="comentario"
                                    id="comentarioTextarea"
                                    class="form-control"
                                    rows="4"
                                    maxlength="500"
                                    placeholder="Comparte tu experiencia de lectura..."
                                    required>{{ $miComentario ? $miComentario->comentario : '' }}</textarea>
                                <div class="book-char-counter">
                                    <span id="comentarioCharCount">{{ $miComentario ? mb_strlen($miComentario->comentario) : 0 }}</span>/500
                                </div>
                            </div>

                            <button class="btn book-action-primary w-100" type="submit" id="btnPublicarComentario">
                                @if($miComentario)
                                    <i class="bi bi-arrow-repeat me-2"></i>
                                    Actualizar reseña
                                @else
                                    <i class="bi bi-chat-square-text me-2"></i>
                                    Publicar reseña
                                @endif
                            </button>
                        </form>
                    </div>
                @else
                    <div class="book-guest-card">
                        <span class="book-guest-badge">
                            <i class="bi bi-stars"></i>
                            Comunidad lectora
                        </span>

                        <h3 class="book-guest-title">¿Leíste este libro?</h3>

                        <p class="book-guest-text">
                            Comparte tu opinión y ayuda a otros lectores a descubrir mejores libros. Tu calificación aporta a la comunidad.
                        </p>

                        <div class="book-guest-points">
                            <div class="book-guest-point">
                                <i class="bi bi-star-fill"></i>
                                <span>Califica con estrellas según tu experiencia</span>
                            </div>
                            <div class="book-guest-point">
                                <i class="bi bi-chat-quote-fill"></i>
                                <span>Escribe una reseña breve para orientar a otros</span>
                            </div>
                        </div>

                        <div class="book-guest-actions">
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn book-action-primary book-guest-login">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Iniciar sesión
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </section>
</div>

@if($ejemplaresDisponibles > 0)
<div class="book-sticky-cta d-lg-none" id="bookStickyCta">
    <button type="button" class="btn book-action-primary w-100" data-bs-toggle="modal" data-bs-target="#modalReserva">
        <i class="bi bi-bookmark-plus me-2"></i>
        Solicitar préstamo ·
        <strong>{{ $ejemplaresDisponibles }} disponible{{ $ejemplaresDisponibles === 1 ? '' : 's' }}</strong>
    </button>
</div>
@endif
@endsection

@section('modal')
<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content book-modal-content">

            <div class="modal-header book-modal-header border-0">
                <div>
                    <h5 class="modal-title book-modal-title" id="modalReservaTitle">
                        <i class="bi bi-bookmark-plus me-2"></i>Solicitar préstamo
                    </h5>
                    <small class="book-modal-subtitle">
                        {{ \Illuminate\Support\Str::limit($libro->titulo, 60) }}
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body book-modal-body">
                @auth
                    {{-- Stats rápidos --}}
                    <div class="book-modal-summary">
                        <div class="book-modal-stat">
                            <span>Disponibles</span>
                            <strong>{{ $ejemplaresDisponibles }} ejemplar{{ $ejemplaresDisponibles === 1 ? '' : 'es' }}</strong>
                        </div>
                        <div class="book-modal-stat">
                            <span>Sedes</span>
                            <strong>{{ $bibliotecasDisponibles }} sede{{ $bibliotecasDisponibles === 1 ? '' : 's' }}</strong>
                        </div>
                        <div class="book-modal-stat">
                            <span>Reserva válida</span>
                            <strong>Hasta mañana 20:00</strong>
                        </div>
                    </div>

                    {{-- Indicador de pasos --}}
                    <div class="book-modal-steps" id="modalSteps">
                        <div class="book-modal-step book-modal-step--done" id="step1">
                            <span class="book-modal-step-num">1</span>
                            <span>Sede</span>
                        </div>
                        <span class="book-modal-step-line"></span>
                        <div class="book-modal-step" id="step2">
                            <span class="book-modal-step-num">2</span>
                            <span>Ejemplar</span>
                        </div>
                        <span class="book-modal-step-line"></span>
                        <div class="book-modal-step book-modal-step--done" id="step3">
                            <span class="book-modal-step-num">3</span>
                            <span>Modalidad</span>
                        </div>
                    </div>

                    <form id="formReserva" class="book-modal-form" novalidate>
                        @csrf

                        {{-- Paso 1: Sede --}}
                        <div class="mb-3">
                            <label for="biblioteca_select" class="form-label fw-semibold">
                                <i class="bi bi-building me-1 opacity-50"></i>Sede / Biblioteca
                            </label>
                            <select id="biblioteca_select" class="form-control" required>
                                <option value="">Seleccionar sede...</option>
                                @foreach($bibliotecas as $bib)
                                    @php
                                        $dispBib = $libro->ejemplares
                                            ->where('biblioteca_id', $bib->id)
                                            ->where('estado', 1)
                                            ->count();
                                    @endphp
                                    <option value="{{ $bib->id }}" {{ $dispBib === 0 ? 'data-sin-stock=1' : '' }}>
                                        {{ $bib->nombre }}
                                        @if($dispBib > 0)
                                            — {{ $dispBib }} disponible{{ $dispBib !== 1 ? 's' : '' }}
                                        @else
                                            — sin ejemplares disponibles
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Paso 2: Ejemplar --}}
                        <div class="mb-3">
                            <label for="ejemplar_select" class="form-label fw-semibold">
                                <i class="bi bi-file-text me-1 opacity-50"></i>Ejemplar
                            </label>
                            <select name="ejemplar_id" id="ejemplar_select" class="form-control" disabled required>
                                <option value="">Primero selecciona una sede</option>
                            </select>
                        </div>

                        {{-- Paso 3: Modalidad --}}
                        <div class="mb-0">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-tag me-1 opacity-50"></i>Modalidad de préstamo
                            </label>
                            <div class="book-loan-types">
                                <label class="book-loan-type">
                                    <input type="radio" name="tipo_prestamo" value="0" checked>
                                    <span class="book-loan-type-card">
                                        <i class="bi bi-building"></i>
                                        <strong>En sala</strong>
                                        <small>Consulta el libro dentro de la biblioteca, sin límite de tiempo.</small>
                                    </span>
                                </label>
                                <label class="book-loan-type">
                                    <input type="radio" name="tipo_prestamo" value="1">
                                    <span class="book-loan-type-card">
                                        <i class="bi bi-house-door"></i>
                                        <strong>A domicilio</strong>
                                        <small>Lleva el libro a casa. Sujeto a plazo de devolución.</small>
                                    </span>
                                </label>
                            </div>
                        </div>

                    </form>

                    <div class="book-modal-footer">
                        <button type="button" class="btn book-modal-cancel" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn book-action-primary flex-grow-1" type="submit" form="formReserva" id="btnConfirmarReserva">
                            <i class="bi bi-journal-check me-2"></i>
                            Confirmar reserva
                        </button>
                    </div>
                @else
                    {{-- Estado no autenticado --}}
                    <div class="book-modal-guest">
                        <div class="book-modal-guest-icon">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <h6 class="book-modal-guest-title">Inicia sesión para continuar</h6>
                        <p class="book-modal-guest-text">
                            Para solicitar el préstamo de
                            <strong>«{{ \Illuminate\Support\Str::limit($libro->titulo, 50) }}»</strong>
                            necesitas acceder con tu cuenta universitaria.
                        </p>
                        <div class="book-modal-guest-points">
                            <div class="book-modal-guest-point">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Reserva ejemplares en cualquier sede</span>
                            </div>
                            <div class="book-modal-guest-point">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Consulta y gestiona tus préstamos activos</span>
                            </div>
                            <div class="book-modal-guest-point">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Accede a tu historial de lecturas</span>
                            </div>
                        </div>
                        <div class="book-modal-guest-actions">
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn book-action-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Iniciar sesión
                            </a>
                            <button type="button" class="btn book-modal-cancel" data-bs-dismiss="modal">
                                Ahora no
                            </button>
                        </div>
                    </div>
                @endauth
            </div>

        </div>
    </div>
</div>
@endsection
