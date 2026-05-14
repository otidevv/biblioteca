<table class="table table-hover">
    <thead class="table-light">
        <tr>
            <th>Biblioteca</th>
            <th>Total</th>
            <th>Disponibles</th>
            <th>Estado</th>
        </tr>
    </thead>

    <tbody>
        @php
            $ejemplaresConBiblioteca = $libro->ejemplares->filter(fn($ejemplar) => !is_null($ejemplar->biblioteca_id));
        @endphp

        @forelse($ejemplaresConBiblioteca->groupBy(fn($ejemplar) => $ejemplar->biblioteca?->nombre) as $biblioteca => $ejemplares)

        @php
            $total = $ejemplares->count();
            $disponibles = $ejemplares->where('estado','1')->count();
        @endphp

        <tr>
            <td>{{ $biblioteca }}</td>
            <td>{{ $total }}</td>
            <td>{{ $disponibles }}</td>
            <td>
                @if($disponibles > 0)
                    <span class="badge text-bg-success">Disponible</span>
                @else
                    <span class="badge text-bg-danger">No disponible</span>
                @endif
            </td>
        </tr>

        @empty
        <tr>
            <td colspan="4" class="text-center text-muted py-4">No hay ejemplares con biblioteca asignada.</td>
        </tr>
        @endforelse
    </tbody>
</table>
