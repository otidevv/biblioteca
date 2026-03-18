@extends('layouts.pagina')

@section('css')
@endsection
@section('js')
@endsection
@section('content')
            <h4 class="section-title">📚 Libros Destacados</h4>
            <div class="row g-4" id="contenedorLibros">

                @forelse($libros as $libro)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card book-card h-100">

                        <img src="{{ $libro->imagen }}" class="book-cover">

                        <div class="card-body d-flex flex-column">

                            <h6 class="mb-1">{{ $libro->titulo }}</h6>

                            <p class="text-muted small mb-2">
                                @foreach($libro->autores as $autor)
                                {{ $autor->nombres }} {{ $autor->apellidos }}@if(!$loop->last), @endif
                                @endforeach
                            </p>

                            <div class="stars mb-2">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star-half-alt"></i>
                                <i class="fa-regular fa-star"></i>
                            </div>

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
            <!-- PAGINACIÓN -->
            <div class="d-flex justify-content-center mt-4">
                <ul class="pagination pagination-sm">
                    {{ $libros->links() }}
                </ul>
            </div>

@endsection