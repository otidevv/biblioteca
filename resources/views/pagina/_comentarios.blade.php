<div class="book-comments-list">
    @forelse($comentarios as $c)
        <article class="book-comment-card">
            <div class="book-comment-header">
                <div class="book-comment-user">
                    <span class="book-comment-avatar">
                        <i class="bi bi-person-circle"></i>
                    </span>
                    <div class="book-comment-author">
                        {{ $c->usuario->name ?? 'Usuario anonimo' }}
                    </div>
                </div>
                <span class="book-comment-date">{{ $c->created_at->diffForHumans() }}</span>
            </div>

            <div class="book-comment-stars" aria-label="Calificacion {{ $c->calificacion }} de 5">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $c->calificacion)
                        <i class="bi bi-star-fill"></i>
                    @else
                        <i class="bi bi-star"></i>
                    @endif
                @endfor
            </div>

            <p class="book-comment-text">{{ $c->comentario }}</p>
        </article>
    @empty
        <div class="book-empty-state">
            No hay comentarios aun.
        </div>
    @endforelse
</div>
