@extends('layouts.app')

@section('content')

<h3>{{ $biblioteca->nombre }}</h3>
<p>{{ $biblioteca->descripcion }}</p>

<div class="row">
@foreach($libros as $grupo)

@php $libro = $grupo->first()->libro; @endphp

<div class="col-md-3 mb-3">
    <div class="card card-hover"
         onclick="window.location='{{ route('libro.show',$libro->id) }}'">

        <img src="{{ $libro->imagen }}" height="180">

        <div class="p-2">
            <h6>{{ $libro->titulo }}</h6>
            <small>Ejemplares: {{ $grupo->count() }}</small>
        </div>
    </div>
</div>

@endforeach
</div>

@endsection