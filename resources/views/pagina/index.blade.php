@extends('layouts.biblioteca')

@section('content')

<!-- HERO -->
<div class="hero mb-4 text-center">

    <h2 class="fw-bold">📚 Bibliotecas UNAMAD</h2>

    <!-- BUSCADOR FUNCIONAL -->
    <form action="{{ route('catalogo') }}" method="GET" class="w-75 w-md-50 mt-3">
        <input type="text"
               name="titulo"
               class="form-control"
               placeholder="🔍 Buscar libros, autor..."
               value="{{ request('titulo') }}">
    </form>

</div>

<!-- BIBLIOTECAS -->
<h4 class="mb-3">🏛️ Bibliotecas</h4>

<div class="row g-3">
@foreach($bibliotecas as $b)
<div class="col-12 col-sm-6 col-md-4 col-lg-3">

    <div class="card card-hover h-100"
         onclick="window.location='{{ route('biblioteca.show',$b->id) }}'">

        <img src="{{ $b->imagen ?? '/img/default.jpg' }}"
             style="height:160px; object-fit:cover; width:100%;">

        <div class="p-2">
            <h6>{{ $b->nombre }}</h6>
            <small class="text-muted">{{ $b->descripcion }}</small>
        </div>

    </div>

</div>
@endforeach
</div>

<!-- LIBROS -->
<h4 class="mt-5 mb-3">📖 Libros Recientes</h4>

<div class="row g-3">
@foreach($libros as $libro)

<div class="col-12 col-sm-6 col-md-4 col-lg-3">

    <!-- 🔥 CARD COMPLETA CLICKEABLE -->
    <div class="card book-card h-100"
         onclick="window.location='{{ route('libro.show',$libro->id) }}'">

        <!-- IMAGEN -->
        <img src="{{ $libro->imagen ?? '/img/libro.png' }}" class="libro-img">

        <div class="card-body d-flex flex-column">

            <!-- TITULO -->
            <h6 class="mb-1" title="{{ $libro->titulo }}">
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
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star-half-alt"></i>
                <i class="fa-regular fa-star"></i>
            </div>

            <!-- BOTÓN -->
            <button class="btn btn-libro mt-auto btn-sm w-100">
                Ver detalle
            </button>

        </div>

    </div>

</div>

@endforeach
</div>

@endsection