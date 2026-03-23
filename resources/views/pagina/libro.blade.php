@extends('layouts.biblioteca')

@section('content')

<style>
.libro-card{
    background:#fff;
    border-radius:12px;
    padding:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

.libro-img{
    transition:0.3s;
}
.libro-img:hover{
    transform:scale(1.05);
}

.info-box{
    background:#f8f9fa;
    border-radius:8px;
    padding:12px;
    margin-bottom:10px;
}

/* COMENTARIOS */
.comentario{
    border-radius:10px;
    padding:12px;
    background:#fff;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
}

.comentario-user{
    font-weight:bold;
}

.comentario-time{
    font-size:12px;
    color:#888;
}
</style>

<div class="libro-card">

<div class="row">

    <!-- IMAGEN -->
    <div class="col-md-4 text-center">
        <img src="{{ $libro->imagen ?? '/img/libro.png' }}"
             class="img-fluid rounded shadow libro-img"
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
            <button class="btn btn-success">
                <i class="bi bi-book"></i> Solicitar préstamo
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

@forelse($libros as $r)
<div class="col-6 col-md-3">

    <div class="card card-hover text-center h-100"
         onclick="window.location='{{ route('libro.show',$r->id) }}'">

        <img src="{{ $r->imagen ?? '/img/libro.png' }}"
             class="img-fluid"
             style="height:160px; object-fit:cover;">

        <div class="p-2">
            <h6>{{ $r->titulo }}</h6>
        </div>

    </div>

</div>
@empty
<div class="col-12">
    <div class="alert alert-info">
        No hay libros relacionados disponibles
    </div>
</div>
@endforelse

</div>

<!-- COMENTARIOS -->
<h4 class="mt-5">💬 Comentarios</h4>

<div class="row mt-3">

    <!-- LISTA -->
    <div class="col-md-8">

        @forelse($libro->comentarios as $c)
        <div class="comentario mb-2">

            <div class="d-flex justify-content-between">
                <span class="comentario-user">
                    <i class="bi bi-person-circle"></i> {{ $c->user->name }}
                </span>

                <span class="comentario-time">
                    {{ $c->created_at->diffForHumans() }}
                </span>
            </div>

            <p class="mt-2 mb-0">{{ $c->comentario }}</p>

        </div>
        @empty
        <div class="alert alert-info">
            No hay comentarios aún
        </div>
        @endforelse

    </div>

    <!-- FORM -->
    <div class="col-md-4">

        @auth
        <div class="card p-3 shadow-sm">

            <h6 class="mb-2">✍️ Agregar comentario</h6>

            <form action="{{ route('comentario.store') }}" method="POST">
                @csrf

                <input type="hidden" name="libro_id" value="{{ $libro->id }}">

                <textarea name="comentario"
                          class="form-control mb-2"
                          rows="3"
                          placeholder="Escribe tu comentario..."
                          required></textarea>

                <button class="btn btn-success w-100">
                    <i class="bi bi-chat-dots"></i> Comentar
                </button>

            </form>

        </div>
        @else
        <div class="alert alert-warning">
            Debes <a href="{{ route('login') }}">iniciar sesión</a> para comentar
        </div>
        @endauth

    </div>

</div>

@endsection