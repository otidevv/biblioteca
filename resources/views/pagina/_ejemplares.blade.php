@forelse(($ejemplares ?? collect()) as $e)
    <div>
        <div class="book-copy-card">
            <div class="book-copy-card-inner">
                <span class="book-copy-icon">
                    <i class="bi bi-file-text-fill"></i>
                </span>

                <div class="book-copy-code">{{ $e->codigo }}</div>

                <div class="book-copy-library">{{ $e->biblioteca?->nombre }}</div>

                <div class="book-copy-status">
                    @if($e->estado == '1')
                        <span class="badge rounded-pill text-bg-success">Disponible</span>
                    @elseif($e->estado == '0')
                        <span class="badge rounded-pill text-bg-danger">Prestado</span>
                    @else
                        <span class="badge rounded-pill text-bg-warning text-dark">Reservado</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="book-empty-state">
        No hay ejemplares con biblioteca asignada para este libro.
    </div>
@endforelse
