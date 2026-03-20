@extends('layouts.pagina')

@section('css')
@endsection
@section('js')
<script src="{{ asset('js/pagina/index.js') }}"></script>
<script>
document.addEventListener('click', function(e) {
    let link = e.target.closest('.pagination a');
    if (link) {
        e.preventDefault();
        fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                document.querySelector('#libros-container').innerHTML = html;
            });
    }
});
</script>
@endsection
@section('content')   
<div class="row">
    <!-- Sidebar de filtros -->
    <div class="col-md-3">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold">Filtros de búsqueda</h6>
                <!-- Búsqueda general -->
                <div class="mb-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" id="search" class="form-control" placeholder="Título, autor, descripción...">
                </div>
                <!-- Tipo de registro -->
                <div class="mb-3">
                    <label for="registro_id" class="form-label">Tipo de registro</label>
                    <select id="registro_id" class="form-select select2">
                    </select>
                </div>
                <!-- Idioma -->
                <div class="mb-3">
                    <label for="idioma_id" class="form-label">Idioma</label>
                    <select id="idioma_id" class="form-select select2">
                    </select>
                </div>
                <!-- Autor -->
                <div class="mb-3">
                    <label for="autor" class="form-label">Autor</label>
                    <select id="autor_id" class="form-select select2">
                    </select>
                </div>
                <!-- Materia -->
                <div class="mb-3">
                    <label for="materia_id" class="form-label">Materia</label>
                    <select id="materia_id" class="form-select select2">
                    </select>
                </div>
                <!-- Botones -->
                <button id="reset" class="btn btn-secondary w-100 mb-2">Restablecer</button>
                <button id="apply" class="btn btn-primary w-100">Aplicar filtros</button>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="col-md-9">
        <h4 class="section-title">📚 Libros Destacados</h4>
        <div id="libros-container">
            @include('pagina._libros')
        </div>
    </div>
</div>
@endsection