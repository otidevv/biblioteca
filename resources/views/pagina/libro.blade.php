@extends('layouts.pagina')

@section('css')
<style>
    .libro-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .libro-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .libro-img {
        transition: transform 0.3s ease;
    }

    .libro-card:hover .libro-img {
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')
<div class="container my-5">

    <!-- DETALLE DEL LIBRO -->
    <div class="row mb-4">
        <!-- Portada -->
        <div class="col-md-4 text-center">
            @if($libro->imagen)
            <img src="{{ asset($libro->imagen) }}" class="img-fluid rounded shadow-lg mb-3" alt="Portada del libro">
            @else
            <img src="{{ asset('images/no-cover.png') }}" class="img-fluid rounded shadow-lg mb-3" alt="Sin portada">
            @endif
        </div>
        <!-- Información -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="fw-bold">{{ $libro->titulo }}</h3>
                    <p class="text-muted">{{ $libro->descripcion }}</p>
                    <p><strong>Autor(es):</strong> {{ $libro->autores->pluck('nombres')->join(', ') }}</p>
                    <p><strong>Materia:</strong> {{ $libro->materias->pluck('nombre')->join(', ') }}</p>
                    <p><strong>Idioma:</strong> {{ $libro->idioma->nombre ?? 'N/A' }}</p>
                    <p><strong>Editorial:</strong> {{ $libro->editorial->nombre ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- EJEMPLARES -->
    <div class="mt-5">
        <h4>Ejemplares disponibles</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Código interno</th>
                    <th>Biblioteca</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($libro->ejemplares as $ejemplar)
                <tr>
                    <td>
                        {{ $ejemplar->codigo_dewey
                        ? $ejemplar->codigo_dewey . ' ' . $ejemplar->tipo . ' ' . $ejemplar->codigo_interno
                        : $ejemplar->codigo_ant}}
                    </td>
                    <td>{{ $ejemplar->biblioteca->nombre ?? 'No asignada' }}</td>
                    <td>
                        @if($ejemplar->estado === 1)
                        <span class="badge bg-success">Disponible</span>
                        @else
                        <span class="badge bg-danger">No disponible</span>
                        @endif
                    </td>
                    <td>
                        @if($ejemplar->estado === 1)
                        @auth
                        <!-- Botón que abre el modal -->
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalReserva{{ $ejemplar->id }}">
                            Reservar
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="modalReserva{{ $ejemplar->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Reserva de ejemplar</h5>
                                        <button type="button" class="btn-close btn-close-white"
                                            data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Título del libro:</strong> {{ $libro->titulo }}</p>
                                        <p><strong>Código ejemplar:</strong>
                                            {{ $ejemplar->codigo_dewey
                                            ? $ejemplar->codigo_dewey.' '.$ejemplar->tipo.' '.$ejemplar->codigo_interno
                                            : $ejemplar->codigo_ant }}
                                        </p>
                                        <p><strong>Biblioteca:</strong> {{ $ejemplar->biblioteca->nombre ?? 'No
                                            asignada' }}</p>
                                        <p><strong>Estado:</strong> Disponible</p>
                                        <p><strong>Inicio de reserva:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                                        <p><strong>Fecha límite:</strong> {{ now()->addDay()->setHour(20)->format('d/m/Y
                                            H:i') }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <form method="POST">
                                            @csrf
                                            <input type="hidden" name="ejemplar_id" value="{{ $ejemplar->id }}">
                                            <button type="submit" class="btn btn-success">Confirmar reserva</button>
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancelar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Si no está logueado -->
                        <a href="{{ route('login') }}?redirect={{ route('pagina.libro',$libro->id) }}"
                            class="btn btn-sm btn-outline-primary">
                            Inicia sesión para reservar
                        </a>
                        @endauth
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No hay ejemplares registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- COMENTARIOS -->
    <div class="mt-5">
        <h4>Opiniones de lectores</h4>
        <div class="list-group">
            @forelse($libro->comentarios as $comentario)
            <div class="list-group-item">
                <strong>{{ $comentario->usuario->nombre }}</strong>
                <span class="text-muted small">({{ $comentario->created_at->format('d/m/Y') }})</span>
                <div class="text-warning">
                    {!! str_repeat('<i class="fa fa-star"></i>', $comentario->calificacion) !!}
                    {!! str_repeat('<i class="fa fa-regular fa-star"></i>', 5 - $comentario->calificacion) !!}
                    <span class="ms-2">({{ $comentario->calificacion }}/5)</span>
                </div>
                <p>{{ $comentario->comentario }}</p>
            </div>
            @empty
            <p class="text-muted">No hay comentarios aún.</p>
            @endforelse
        </div>
    </div>

    <!-- LIBROS RELACIONADOS -->
    @if($relacionados->count())
    <div class="mt-5">
        <h4>📖 Libros relacionados</h4>
        <div class="row g-4">
            @foreach($relacionados as $rel)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card libro-card h-100">
                    <img src="{{ '/'.$rel->imagen ?? asset('images/no-cover.png') }}" class="libro-img card-img-top">
                    <div class="card-body d-flex flex-column">
                        <h6 class="mb-1">{{ $rel->titulo }}</h6>
                        <p class="text-muted small mb-2">
                            {{ $rel->autores->pluck('nombres')->join(', ') }}
                        </p>
                        <div class="stars mb-2">
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-half-alt"></i>
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <a href="/{{ $libro->id }}/libro" class="btn btn-sm btn-outline-primary mt-auto w-100">
                            Ver detalle
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection