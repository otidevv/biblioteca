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
        @foreach($libro->ejemplares->groupBy('biblioteca.nombre') as $biblioteca => $ejemplares)

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
                    <span class="badge bg-success">Disponible</span>
                @else
                    <span class="badge bg-danger">No disponible</span>
                @endif
            </td>
        </tr>

        @endforeach
    </tbody>
</table>