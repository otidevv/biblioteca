@extends('layouts.biblioteca')

@section('content')

<!-- HERO -->
<div class="hero mb-4 text-center">

    <h2 class="fw-bold">Bibliotecas UNAMAD</h2>

    <input type="text"
           class="form-control w-75 w-md-50 mt-3"
           placeholder="🔍 Buscar libros, autor...">

</div>

<!-- BIBLIOTECAS -->
<h4>🏛️ Bibliotecas</h4>

<div class="row g-3">
@foreach($bibliotecas as $b)
<div class="col-12 col-sm-6 col-md-4 col-lg-3">

    <div class="card card-hover h-100"
         onclick="window.location='{{ route('biblioteca.show',$b->id) }}'">

        <img src="{{ $b->imagen ?? '/img/default.jpg' }}" class="img-fluid"  style="height:160px; object-fit:cover;">

        <div class="p-2">
            <h6>{{ $b->nombre }}</h6>
            <small>{{ $b->descripcion }}</small>
        </div>

    </div>

</div>
@endforeach
</div>

<!-- LIBROS -->
<h4 class="mt-5">📖 Libros Recientes</h4>

<div class="row g-3">
@foreach($libros as $l)
<div class="col-6 col-md-4 col-lg-3">

    <div class="card card-hover text-center h-100" onclick="window.location='{{ route('libro.show',$l->id) }}'">

        <img src="{{ $l->imagen ?? '/img/libro.png' }}" class="img-fluid" style="height:180px; object-fit:cover;">

        <div class="p-2">
            <h6>{{ $l->titulo }}</h6>
            <small>{{ $l->autor }}</small>
        </div>

    </div>

</div>
@endforeach
</div>

@endsection