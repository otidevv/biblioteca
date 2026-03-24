@extends('layouts.biblioteca')
@section('js')
<script>
let reservaId = null;

document.addEventListener('click', function(e){

    // ABRIR MODAL
    if(e.target.classList.contains('btn-cancelar')){

        reservaId = e.target.dataset.id;

        let modal = new bootstrap.Modal(document.getElementById('modalCancelar'));
        modal.show();
    }

});

// CONFIRMAR CANCELACIÓN
document.getElementById('confirmarCancelacion').addEventListener('click', function(){

    if(!reservaId) return;

    fetch(`/pagina/reserva/${reservaId}/cancelar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        }
    })
    .then(res => res.json())
    .then(res => {

        if(res.error){
            alerta(res.error,false);
            return;
        }

        alerta(res.ok, true);

        // cerrar modal
        let modal = bootstrap.Modal.getInstance(document.getElementById('modalCancelar'));
        modal.hide();

        // recargar
        setTimeout(() => {
            location.reload();
        }, 800);

    });

});
function iniciarCountdown(){

    const elementos = document.querySelectorAll('.countdown');

    elementos.forEach(el => {

        const fechaLimite = new Date(el.dataset.fecha).getTime();

        function actualizar(){

            const ahora = new Date().getTime();
            const diferencia = fechaLimite - ahora;

            if(diferencia <= 0){
                el.innerHTML = "⛔ Vencido";
                el.classList.remove('text-success','text-warning');
                el.classList.add('text-danger');
                return;
            }

            let horas = Math.floor(diferencia / (1000 * 60 * 60));
            let minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
            let segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

            el.innerHTML = `⏳ ${horas}h ${minutos}m ${segundos}s`;

            // 🎨 colores dinámicos
            if(horas < 1){
                el.classList.add('text-danger');
            } else if(horas < 3){
                el.classList.add('text-warning');
            } else {
                el.classList.add('text-success');
            }
        }

        actualizar();
        setInterval(actualizar, 1000);
    });

}

document.addEventListener('DOMContentLoaded', iniciarCountdown);
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
                            @if($r->estado == 0)
                                <span class="countdown"
                                    data-fecha="{{ $r->fecha_limite }}">
                                    ⏳ Calculando...
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
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
@section('modal')
<!-- MODAL CONFIRMAR CANCELACIÓN -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⚠️ Cancelar reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">

                <div style="font-size:50px;">📚</div>

                <p class="mt-3 mb-1 fw-bold">
                    ¿Seguro que deseas cancelar esta reserva?
                </p>

                <small class="text-muted">
                    Esta acción liberará el ejemplar automáticamente.
                </small>

            </div>

            <div class="modal-footer justify-content-center border-0">

                <button type="button" class="btn btn-secondary px-4"
                        data-bs-dismiss="modal">
                    No
                </button>

                <button type="button" class="btn btn-danger px-4"
                        id="confirmarCancelacion">
                    Sí, cancelar
                </button>

            </div>

        </div>
    </div>
</div>
@endsection