@extends('layouts.biblioteca')

@section('content')

<div class="hero mb-4 text-center">
    <h1>Sistema de Bibliotecas</h1>
    <input type="text" class="form-control w-50 mt-3" placeholder="Buscar libros...">
</div>

<h4>🏛️ Bibliotecas</h4>

<div class="row g-3">
@foreach($bibliotecas as $b)
<div class="col-md-4">
    <div class="card card-hover"
         onclick="window.location='{{ route('biblioteca.show',$b->id) }}'">

        <img src="{{ $b->imagen ?? '/img/default.jpg' }}" height="160">

        <div class="p-2">
            <h6>{{ $b->nombre }}</h6>
            <small>{{ $b->descripcion }}</small>
        </div>
    </div>
</div>
@endforeach
</div>

<h4 class="mt-5">📖 Libros Recientes</h4>

<div class="row g-3">
@foreach($libros as $l)
<div class="col-md-3">
    <div class="card card-hover text-center"
         onclick="window.location='{{ route('libro.show',$l->id) }}'">

        <img src="{{ $l->imagen ?? '/img/libro.png' }}" height="180">

        <div class="p-2">
            <h6>{{ $l->titulo }}</h6>
            <small>{{ $l->autor }}</small>
        </div>
    </div>
</div>
@endforeach
</div>

@endsection