@php
    $lista = ($ejemplares ?? collect())->sortByDesc(fn($e) => match((int) $e->estado) {
        1 => 3,   // disponible primero
        2 => 2,   // reservado
        3 => 1,   // traslado
        default => 0, // prestado al final
    });
@endphp

@forelse($lista as $e)
    @php
        $partes        = array_filter([$e->codigo_dewey, $e->codigo_interno ? '/' . $e->codigo_interno : null]);
        $codigoDisplay = implode('', $partes) ?: ($e->codigo_ant ?: '—');
        $rowClass = match((int) $e->estado) {
            1 => 'book-copy-row--available',
            0 => 'book-copy-row--lent',
            3 => 'book-copy-row--transfer',
            default => 'book-copy-row--reserved',
        };
    @endphp

    <div class="book-copy-row {{ $rowClass }}">
        <span class="book-copy-row-icon">
            <i class="bi bi-file-text-fill"></i>
        </span>

        <div class="book-copy-row-body">
            <div class="book-copy-row-code">{{ $codigoDisplay }}</div>
            <div class="book-copy-row-meta">
                @if($e->tipo)
                    <span>{{ ucfirst($e->tipo) }}</span>
                @endif
                @if($e->siaf)
                    <span class="bk-sep">·</span>
                    <span>SIAF: {{ $e->siaf }}</span>
                @endif
                @if($e->adquisicion)
                    <span class="bk-sep">·</span>
                    <span>Adq. {{ $e->adquisicion }}</span>
                @endif
                @if($e->biblioteca)
                    <span class="bk-sep">·</span>
                    <span class="book-copy-row-lib">
                        <i class="bi bi-building"></i>
                        {{ $e->biblioteca->nombre }}
                    </span>
                @endif
            </div>
        </div>

        @if($e->estado == 1)
            <span class="badge rounded-pill text-bg-success">Disponible</span>
        @elseif($e->estado == 0)
            <span class="badge rounded-pill text-bg-danger">Prestado</span>
        @elseif($e->estado == 3)
            <span class="badge rounded-pill text-bg-secondary">Traslado</span>
        @else
            <span class="badge rounded-pill text-bg-warning">Reservado</span>
        @endif
    </div>
@empty
    <div class="book-empty-state">
        No hay ejemplares con biblioteca asignada para este libro.
    </div>
@endforelse
