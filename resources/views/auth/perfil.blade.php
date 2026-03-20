@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar perfil</h2>
    <form method="POST" action="{{ route('perfil.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name">Nombre</label>
            <input id="name" type="text" name="name" value="{{ Auth::user()->name }}" class="form-control">
        </div>

        <div class="mb-3">
            <label for="email">Correo</label>
            <input id="email" type="email" name="email" value="{{ Auth::user()->email }}" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
