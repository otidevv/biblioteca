@php
    $ejemplaresConBiblioteca = $libro->ejemplares->filter(fn($e) => !is_null($e->biblioteca_id));
    $porBiblioteca = $ejemplaresConBiblioteca->groupBy('biblioteca_id');
@endphp

<div class="book-avail-grid">
    @forelse($porBiblioteca as $bibliotecaId => $ejemplares)
        @php
            $bib         = $ejemplares->first()->biblioteca;
            $total       = $ejemplares->count();
            $disponibles = $ejemplares->where('estado', '1')->count();
            $collapseId  = 'avail-' . $bibliotecaId;
        @endphp

        <div class="book-avail-card">
            {{-- Cabecera: punto + info + acciones --}}
            <div class="book-avail-header">
                <span class="book-avail-dot {{ $disponibles > 0 ? 'book-avail-dot--on' : 'book-avail-dot--off' }}"></span>

                <div class="book-avail-info">
                    <div class="book-avail-name">{{ $bib?->nombre ?? '—' }}</div>

                    <div class="book-avail-meta">
                        @if($bib?->codigo)
                            <span class="book-avail-pill">
                                <i class="bi bi-upc-scan"></i>
                                {{ $bib->codigo }}
                            </span>
                        @endif
                        @if($bib?->direccion)
                            <span class="book-avail-pill book-avail-pill--muted">
                                <i class="bi bi-geo-alt"></i>
                                {{ \Illuminate\Support\Str::limit($bib->direccion, 40) }}
                            </span>
                        @endif
                    </div>

                    <div class="book-avail-counts">
                        <strong>{{ $disponibles }}</strong> disponible{{ $disponibles !== 1 ? 's' : '' }}
                        <span class="book-avail-sep">·</span>
                        {{ $total }} en total
                    </div>
                </div>

                <div class="book-avail-actions">
                    @if($disponibles > 0)
                        <span class="badge text-bg-success">Disponible</span>
                    @else
                        <span class="badge text-bg-danger">No disponible</span>
                    @endif

                    <button class="book-avail-toggle" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $collapseId }}"
                            aria-expanded="false"
                            aria-controls="{{ $collapseId }}">
                        <i class="bi bi-chevron-down"></i>
                        {{ $total }} ejemplar{{ $total !== 1 ? 'es' : '' }}
                    </button>
                </div>
            </div>

            {{-- Listado de ejemplares (colapsable) --}}
            <div class="collapse" id="{{ $collapseId }}">
                <div class="book-avail-copies">
                    @foreach($ejemplares as $e)
                        @php
                            $partes = array_filter([$e->codigo_dewey, $e->codigo_interno ? '/' . $e->codigo_interno : null]);
                            $codigoDisplay = implode('', $partes) ?: ($e->codigo_ant ?: 'Sin código');
                        @endphp
                        <div class="book-avail-copy">
                            <span class="book-avail-copy-icon">
                                <i class="bi bi-file-text-fill"></i>
                            </span>

                            <div class="book-avail-copy-body">
                                <div class="book-avail-copy-code">{{ $codigoDisplay }}</div>
                                <div class="book-avail-copy-meta">
                                    @if($e->tipo)
                                        <span>{{ ucfirst($e->tipo) }}</span>
                                    @endif
                                    @if($e->siaf)
                                        <span class="book-avail-sep">·</span>
                                        <span>SIAF: {{ $e->siaf }}</span>
                                    @endif
                                    @if($e->adquisicion)
                                        <span class="book-avail-sep">·</span>
                                        <span>Adq. {{ $e->adquisicion }}</span>
                                    @endif
                                    @if(!$e->tipo && !$e->siaf && !$e->adquisicion)
                                        <span class="text-muted">Sin datos adicionales</span>
                                    @endif
                                </div>
                            </div>

                            @if($e->estado == 1)
                                <span class="badge text-bg-success">Disponible</span>
                            @elseif($e->estado == 0)
                                <span class="badge text-bg-danger">Prestado</span>
                            @elseif($e->estado == 3)
                                <span class="badge text-bg-secondary">Traslado</span>
                            @else
                                <span class="badge text-bg-warning">Reservado</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="book-empty-state">No hay ejemplares con biblioteca asignada.</div>
    @endforelse
</div>
