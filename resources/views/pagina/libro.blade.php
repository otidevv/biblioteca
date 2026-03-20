@extends('layouts.app')

@section('content')

<div class="row">

    <div class="col-md-4">
        <img src="{{ $libro->portada }}" class="img-fluid">
    </div>

    <div class="col-md-8">
        <h3>{{ $libro->titulo }}</h3>
        <p><b>Autor:</b> {{ $libro->autor }}</p>
        <p>{{ $libro->descripcion }}</p>

        <button class="btn btn-success">Reservar ejemplar</button>
    </div>

</div>

<h5 class="mt-4">Ejemplares</h5>

<table class="table">
<tr>
    <th>Código</th>
    <th>Biblioteca</th>
    <th>Estado</th>
</tr>

@foreach($libro->ejemplares as $e)
<tr>
    <td>{{ $e->codigo }}</td>
    <td>{{ $e->biblioteca->nombre }}</td>
    <td>
        @if($e->estado == 'disponible')
            <span class="badge bg-success">Disponible</span>
        @else
            <span class="badge bg-danger">Prestado</span>
        @endif
    </td>
</tr>
@endforeach

</table>

@endsection