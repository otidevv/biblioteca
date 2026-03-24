@extends('layouts.biblioteca')
@section('js')
    <script src="{{ asset('/js/pagina/catalogo.js') }}"></script>

@endsection
@section('content')
<style>
.select2-container .select2-selection--single{
    height:38px;
    padding:5px;
}
.pagination svg{
    width:18px !important;
    height:18px !important;
}

.pagination li{
    display:inline-block;
}

.pagination{
    align-items:center;
}
</style>

<h4 class="mb-3">📚 Catálogo de Libros</h4>

<div class="card p-3 mb-4 shadow-sm">

<form method="GET" action="{{ route('catalogo') }}">

<div class="row g-2">

    <!-- TITULO -->
    <div class="col-md-3">
        <input type="text" name="titulo" class="form-control"
               placeholder="Título"
               value="{{ request('titulo') }}">
    </div>

    <!-- AUTOR -->
    <div class="col-md-3">
        <select name="autor_id" id="autor_id" class="form-control select2"></select>
    </div>

    <!-- EDITORIAL -->
    <div class="col-md-2">
       <select name="idioma_id" id="idioma_id" class="form-control select2"></select>
    </div>

    <!-- MATERIA -->
    <div class="col-md-2">
        <select name="materia_id" id="materia_id" class="form-control select2"></select>
    </div>

    <!-- BOTON -->
    <div class="col-md-2 d-grid">
        <button class="btn btn-primary">
            🔍 Buscar
        </button>
    </div>

</div>

</form>

</div>

<!-- LIBROS -->
<div class="row g-3">

    <div id="contenedor-libros">
        @include('pagina._libros')
    </div>

</div>

@endsection