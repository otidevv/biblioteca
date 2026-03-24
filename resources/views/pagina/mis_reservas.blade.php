@extends('layouts.biblioteca')
@section('js')
<script>
    document.addEventListener('click', function(e){

    if(e.target.classList.contains('btn-cancelar')){

        let id = e.target.dataset.id;

        if(!confirm('¿Cancelar esta reserva?')) return;

        fetch(`/pagina/reserva/${id}/cancelar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(res => res.json())
        .then(res => {

            if(res.error){
                alert(res.error);
                return;
            }

            alert(res.ok);
            location.reload();

        });
    }

});
</script>
@endsection
@section('content')

<div class="container mt-4">

    <h3 class="mb-4">📚 Mis Reservas</h3>

    @if($reservas->isEmpty())
        <div class="alert alert-info">
            No tienes reservas registradas.
        </div>
    @else

    <div class="table-responsive">
        <table class="table table-hover align-middle">

            <thead class="table-dark">
                <tr>
                    <th>Libro</th>
                    <th>Biblioteca</th>
                    <th>Tipo</th>
                    <th>Reserva</th>
                    <th>Vence</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach($reservas as $r)

                @php
                    $vencido = now()->gt($r->fecha_limite);
                @endphp

                <tr>

                    <!-- 📚 LIBRO -->
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $r->ejemplar->libro->imagen ? '/'.$r->ejemplar->libro->imagen : '/img/libro.png' }}"
                                 width="40" class="rounded">

                            <div>
                                <strong>{{ $r->ejemplar->libro->titulo }}</strong><br>
                                <small class="text-muted">
                                    {{ $r->ejemplar->codigo ?? 'Sin código' }}
                                </small>
                            </div>
                        </div>
                    </td>

                    <!-- 🏢 BIBLIOTECA -->
                    <td>
                        {{ $r->ejemplar->biblioteca->nombre ?? '-' }}
                    </td>

                    <!-- 🏷 TIPO -->
                    <td>
                        @if($r->tipo_prestamo == 'casa')
                            <span class="badge bg-primary">🏠 Casa</span>
                        @else
                            <span class="badge bg-secondary">📖 Sala</span>
                        @endif
                    </td>

                    <!-- 📅 RESERVA -->
                    <td>
                        {{ \Carbon\Carbon::parse($r->fecha_reservacion)->format('d/m/Y H:i') }}
                    </td>

                    <!-- ⏳ LIMITE -->
                    <td>
                        {{ \Carbon\Carbon::parse($r->fecha_limite)->format('d/m/Y H:i') }}
                    </td>

                    <!-- 🚦 ESTADO -->
                    @php
                        $vencido = now()->gt($r->fecha_limite);
                    @endphp

                    <td>
                        @if($r->estado == 0 && $vencido)
                            <span class="badge bg-danger">Vencido</span>

                        @elseif($r->estado == 0)
                            <span class="badge bg-warning text-dark">En espera</span>

                        @elseif($r->estado == 1)
                            <span class="badge bg-success">Atendido</span>

                        @elseif($r->estado == 2)
                            <span class="badge bg-secondary">Cancelado</span>
                        @endif
                    </td>
                    <td>
                    @if($r->estado == 0 && now()->lt($r->fecha_limite))
                        <button class="btn btn-sm btn-danger btn-cancelar"
                                data-id="{{ $r->id }}">
                            ❌ Cancelar
                        </button>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>

                </tr>

                @endforeach
            </tbody>

        </table>
    </div>

    @endif

</div>

@endsection