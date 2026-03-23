@extends('layouts.biblioteca')
@section('js')

<script>
document.addEventListener('DOMContentLoaded', function(){

    // ⭐ COMENTARIOS (igual que tienes)
    let formComentario = document.getElementById('formComentario');

    if(formComentario){
        formComentario.addEventListener('submit', function(e){
            e.preventDefault();

            let data = new FormData(formComentario);

            fetch("{{ route('comentario') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: data
            })
            .then(res => res.text())
            .then(html => {
                document.getElementById('listaComentarios').innerHTML = html;
                formComentario.reset();
            });
        });
    }

    // 📦 SELECT DE EJEMPLAR
    const select = document.getElementById('ejemplar_select');
    const ubicacion = document.getElementById('ubicacion');
    const ejemplar_id = document.getElementById('ejemplar_id');

    if(select){
        select.addEventListener('change', function(){
            let option = this.options[this.selectedIndex];

            let id = this.value;
            let biblioteca = option.getAttribute('data-biblioteca');

            ejemplar_id.value = id;
            ubicacion.value = biblioteca;
        });
    }

    //  RESERVA
    const formReserva = document.getElementById('formReserva');

    if(formReserva){
        formReserva.addEventListener('submit', function(e){
            e.preventDefault();

            let data = new FormData(formReserva);

            fetch("{{ route('reservar') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: data
            })
            .then(res => res.json())
            .then(res => {

                if(res.error){
                    alert(res.error);
                    return;
                }

                alert(res.ok);

                formReserva.reset();

                // cerrar modal
                let modal = bootstrap.Modal.getInstance(document.getElementById('modalReserva'));
                modal.hide();

            });

        });
    }    

});
document.getElementById('biblioteca_select').addEventListener('change', function(){

    let id = this.value;

    let selectEjemplar = document.getElementById('ejemplar_select');

    selectEjemplar.innerHTML = '<option>Cargando...</option>';

    fetch('/ejemplares/' + id)
        .then(res => res.json())
        .then(data => {

            let html = '<option value="">-- Seleccionar ejemplar --</option>';

            data.forEach(e => {
                html += `<option value="${e.id}">
                            ${e.codigo}
                         </option>`;
            });

            selectEjemplar.innerHTML = html;

        });

});
</script>
@endsection
@section('content')

<style>
    .libro-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .libro-img {
        transition: 0.3s;
    }

    .libro-img:hover {
        transform: scale(1.05);
    }

    .info-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
    }

    /* COMENTARIOS */
    .comentario {
        border-radius: 10px;
        padding: 12px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .comentario-user {
        font-weight: bold;
    }

    .comentario-time {
        font-size: 12px;
        color: #888;
    }
</style>
<div class="libro-card">

    <div class="row">

        <!-- IMAGEN -->
        <div class="col-md-4 text-center">
            <img src="{{'/'. $libro->imagen ?? '/img/libro.png' }}" class="img-fluid rounded shadow libro-img"
                style="max-height:380px; object-fit:cover;">
        </div>

        <!-- DETALLE -->
        <div class="col-md-8">

            <h2 class="fw-bold">{{ $libro->titulo }}</h2>

            <p class="text-muted mb-1">
                ✍️
                @foreach($libro->autores as $autor)
                {{ $autor->nombres.' '.$autor->apellidos }}
                @endforeach
            </p>

            <div class="mb-3">
                <span class="badge bg-primary">Edición {{ $libro->edicion }}</span>
                <span class="badge bg-secondary">
                    {{ $libro->editoria->nombre ?? 'Editorial desconocida' }}
                </span>
            </div>

            <div class="info-box">
                <strong>📖 Descripción</strong>
                <p class="mb-0">
                    {{ $libro->descripcion ?? 'Sin descripción disponible' }}
                </p>
            </div>

            <div class="mt-3">
                <button class="btn btn-success"
                        data-bs-toggle="modal"
                        data-bs-target="#modalReserva">
                    📚 Solicitar préstamo
                </button>

                <button class="btn btn-outline-primary">
                    <i class="bi bi-bookmark"></i> Guardar
                </button>
            </div>

        </div>

    </div>
</div>

<!-- DISPONIBILIDAD -->
<div class="card mt-4 shadow-sm">
    <div class="card-body">

        <h5>📊 Disponibilidad por biblioteca</h5>

        <div class="table-responsive">
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
        </div>

    </div>
</div>

<!-- EJEMPLARES -->
<h4 class="mt-5">📦 Ejemplares</h4>

<div class="row g-3">

    @foreach($libro->ejemplares as $e)
    <div class="col-6 col-md-3">

        <div class="card card-hover p-3 text-center h-100">

            <h6 class="fw-bold">{{ $e->codigo }}</h6>

            <small class="text-muted">{{ $e->biblioteca->nombre }}</small>

            <div class="mt-2">
                @if($e->estado == '1')
                <span class="badge bg-success">Disponible</span>
                @else
                <span class="badge bg-danger">Prestado</span>
                @endif
            </div>

        </div>

    </div>
    @endforeach

</div>

<!-- LIBROS RELACIONADOS -->
<h4 class="mt-5">📚 Libros Relacionados</h4>

<div class="row g-3">
    @forelse($libros as $libro)

    <div class="col-12 col-sm-6 col-md-4 col-lg-3">

        <div class="card book-card h-100">

            <!-- IMAGEN -->
           <img src="{{ $libro->imagen ? '/'.$libro->imagen : '/img/libro.png' }}">

            <div class="card-body d-flex flex-column">

                <!-- TITULO -->
                <h6 class="mb-1" data-bs-toggle="tooltip" title="{{ $libro->titulo }}">
                    {{ \Illuminate\Support\Str::limit($libro->titulo, 40) }}
                </h6>

                <!-- AUTORES -->
                <p class="text-muted small mb-2">
                    @foreach($libro->autores as $autor)
                    {{ $autor->nombres }} {{ $autor->apellidos }}@if(!$loop->last), @endif
                    @endforeach
                </p>

                <!-- ESTRELLAS -->
                <div class="stars mb-2">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                    <i class="fa-regular fa-star"></i>
                </div>

                <!-- BOTÓN -->
                <a href="/{{ $libro->id }}/libro" class="btn btn-libro mt-auto btn-sm w-100">
                    Ver detalle
                </a>

            </div>

        </div>

    </div>

    @empty

    <div class="col-12 text-center">
        <p class="text-muted">No hay libros disponibles</p>
    </div>

    @endforelse

</div>

<!-- COMENTARIOS -->
<h4 class="mt-5">💬 Comentarios</h4>

<div class="row mt-3">

    <!-- LISTA -->
    <div class="col-md-8">

        <div id="listaComentarios">
            @include('pagina._comentarios', ['comentarios' => $libro->comentarios])
        </div>

    </div>

    <!-- FORM -->
    <div class="col-md-4">

        @auth
        <div class="card p-3 shadow-sm">

            <h6 class="mb-2">✍️ Agregar comentario</h6>

            <form id="formComentario">
                @csrf

                <input type="hidden" name="libro_id" value="{{ $libro->id }}">

                <!-- ⭐ RATING -->
                <div class="mb-2">
                    <label class="form-label">Calificación</label>

                    <div class="rating">
                        @for($i=5; $i>=1; $i--)
                            <input type="radio"
                                   name="rating"
                                   value="{{ $i }}"
                                   id="star{{ $libro->id }}_{{ $i }}">

                            <label for="star{{ $libro->id }}_{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <!-- COMENTARIO -->
                <textarea name="comentario"
                          class="form-control mb-2"
                          rows="3"
                          placeholder="Escribe tu comentario..."
                          required></textarea>

                <button class="btn btn-success w-100">
                    💬 Comentar
                </button>

            </form>

        </div>
        @else
        <div class="alert alert-warning">
            Debes <a href="{{ route('login') }}">iniciar sesión</a>
        </div>
        @endauth

    </div>

</div>


@endsection
@section('modal')
<div class="modal fade" id="modalReserva" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">📩 Reservar libro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                @auth
                <form id="formReserva">
                    @csrf

                    <input type="hidden" name="libro_id" value="{{ $libro->id }}">
                    <input type="hidden" name="ejemplar_id" id="ejemplar_id">

                    <!-- 📍 UBICACIÓN -->
                    <div class="mb-3">
                        <div class="mb-3">
                            <label>Biblioteca</label>

                            <select id="biblioteca_select" class="form-control" required>
                                <option value="">-- Seleccionar biblioteca --</option>

                                @foreach($bibliotecas as $b)
                                    <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!--  LISTA DE EJEMPLARES -->
                    <div class="mb-3">
                        <label>Ejemplar</label>

                        <select name="ejemplar_id" id="ejemplar_select" class="form-control" required>
                            <option value="">-- Seleccione una biblioteca primero --</option>
                        </select>
                    </div>


                    <!-- ⏳ DURACIÓN -->
                    <div class="mb-3">
                        <label>Duración (días)</label>
                        <input type="number" name="duracion" class="form-control" value="7" min="1">
                    </div>

                    <button class="btn btn-primary w-100">
                        📩 Confirmar reserva
                    </button>

                </form>
                @else
                <div class="alert alert-warning">
                    Debes iniciar sesión
                </div>
                @endauth

            </div>

        </div>
    </div>
</div>
@endsection