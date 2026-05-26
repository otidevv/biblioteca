@php
    $visibleLimit   = 5;
    $totalComentarios = $comentarios->count();
@endphp

<div
    class="book-comments-list {{ $totalComentarios > $visibleLimit ? 'is-collapsed' : '' }}"
    data-comments-list
    data-visible-limit="{{ $visibleLimit }}">

    @forelse($comentarios as $comentario)
        @php
            $usuario      = $comentario->usuario;
            $nombreUsuario = trim((string) ($usuario->name ?? 'Lector'));
            $inicial       = strtoupper(\Illuminate\Support\Str::substr($nombreUsuario, 0, 1)) ?: 'L';
            $calificacion  = max(0, min(5, (int) ($comentario->calificacion ?? 0)));
            $esOwner       = auth()->check() && auth()->id() === $comentario->user_id;
        @endphp

        <article
            class="book-comment-card {{ $loop->iteration > $visibleLimit ? 'is-extra-comment' : '' }}"
            data-comment-id="{{ $comentario->id }}">

            <div class="book-comment-header">
                <div class="book-comment-user">
                    <span class="book-comment-avatar">{{ $inicial }}</span>
                    <div>
                        <div class="book-comment-author">{{ $nombreUsuario !== '' ? $nombreUsuario : 'Lector' }}</div>
                        <div class="book-comment-date">
                            {{ optional($comentario->created_at)->diffForHumans() ?: 'Fecha no disponible' }}
                        </div>
                    </div>
                </div>

                <div class="book-comment-header-right">
                    {{-- Estrellas --}}
                    @if($calificacion > 0)
                        <div class="book-comment-rating" aria-label="Calificación {{ $calificacion }} de 5">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $calificacion ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>
                    @endif

                    {{-- Acciones del propietario --}}
                    @if($esOwner)
                        <div class="book-comment-actions">
                            <button
                                type="button"
                                class="book-comment-action-btn"
                                data-comment-action="edit"
                                title="Editar comentario">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button
                                type="button"
                                class="book-comment-action-btn book-comment-action-btn--danger"
                                data-comment-action="delete"
                                title="Eliminar comentario">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Vista normal del comentario --}}
            <div class="book-comment-view">
                <p class="book-comment-text">{{ $comentario->comentario }}</p>
            </div>

            {{-- Formulario de edición inline (solo propietario) --}}
            @if($esOwner)
                <div class="book-comment-edit-form" hidden>
                    <div class="book-comment-edit-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <button
                                type="button"
                                class="book-edit-star {{ $i <= $calificacion ? 'is-active' : '' }}"
                                data-comment-action="edit-star"
                                data-value="{{ $i }}"
                                aria-label="{{ $i }} estrella{{ $i !== 1 ? 's' : '' }}">
                                <i class="bi {{ $i <= $calificacion ? 'bi-star-fill' : 'bi-star' }}"></i>
                            </button>
                        @endfor
                        <input type="hidden" class="book-edit-rating-val" value="{{ $calificacion }}">
                    </div>
                    <textarea
                        class="form-control book-comment-edit-textarea"
                        rows="3"
                        maxlength="2000">{{ $comentario->comentario }}</textarea>
                    <div class="book-comment-edit-actions">
                        <button type="button" class="btn btn-sm book-action-primary" data-comment-action="save-edit">
                            Guardar
                        </button>
                        <button type="button" class="btn btn-sm book-action-secondary" data-comment-action="cancel-edit">
                            Cancelar
                        </button>
                    </div>
                </div>
            @endif
        </article>

    @empty
        <div class="book-comment-empty">
            <i class="bi bi-chat-square-dots"></i>
            <p>Todavía no hay comentarios para este libro.</p>
            <small>Sé el primero en compartir tu experiencia de lectura.</small>
        </div>
    @endforelse
</div>

@if($totalComentarios > $visibleLimit)
    <div class="book-comments-actions">
        <button
            type="button"
            class="btn book-action-secondary book-comments-toggle"
            data-comments-toggle
            data-show-text="Ver {{ $totalComentarios - $visibleLimit }} comentario{{ ($totalComentarios - $visibleLimit) !== 1 ? 's' : '' }} más"
            data-hide-text="Ver menos comentarios">
            Ver {{ $totalComentarios - $visibleLimit }} comentario{{ ($totalComentarios - $visibleLimit) !== 1 ? 's' : '' }} más
        </button>
    </div>
@endif
