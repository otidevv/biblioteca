@extends('layouts.admin')
@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
@endsection
@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/inventario/libro_nuevo.js') }}"></script>
@endsection
@section('content')
<nav class="mb-4 text-sm text-gray-600">
    <ol class="flex items-center space-x-2">
        <li class="font-semibold text-gray-800">
            Administración
        </li>
        <li class="text-gray-400">›</li>
        <li class="text-emerald-700 font-semibold">
            Compras
        </li>
    </ol>
</nav>

<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="overflow-x-auto">
        <form id="formLibro" enctype="multipart/form-data">

            <div class="row g-3">

                <!-- ================= DATOS GENERALES ================= -->

                <div class="col-md-4">
                    <label class="form-label">Código *</label>
                    <input type="text" name="codigo" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Código Dewey</label>
                    <input type="text" name="codigo_dewey" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">ISBN</label>
                    <input type="text" name="isbn" class="form-control">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Título *</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Páginas</label>
                    <input type="number" name="paginas" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha publicación</label>
                    <input type="date" name="fecha_publicacion" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Lugar publicación</label>
                    <input type="text" name="lugar_publicacion" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Idioma</label>
                    <input type="text" name="idioma" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Edición</label>
                    <input type="text" name="edicion" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Año edición</label>
                    <input type="number" name="anio_edicion" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Editorial</label>
                    <select name="editorial_id" id="editorial_id" class="form-select"></select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tipo Registro</label>
                    <select name="tipo_registro_id" class="form-select">
                        <option value="0">Selecione...</option>
                        @foreach ($tipo_registros as $tr)
                            <option value="{{ $tr->id }}">{{ $tr->nombre }}</option>    
                        @endforeach
                        <!-- cargar dinámicamente -->
                    </select>
                </div>

                <!-- ================= RELACIONES ================= -->

                <div class="col-md-6">
                    <label class="form-label">Autor(es)</label>
                    <select name="autores[]" id="autores" class="form-select" multiple></select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Materia(s)</label>
                    <select name="materias[]" id="materias" class="form-select" multiple></select>
                </div>

                <!-- ================= DESCRIPCIÓN ================= -->

                <div class="col-md-12">
                    <label class="form-label">Resumen</label>
                    <textarea name="resumen" rows="3" class="form-control"></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Anotaciones</label>
                    <textarea name="anotaciones" rows="3" class="form-control"></textarea>
                </div>

                <!-- ================= ARCHIVOS ================= -->

                <div class="col-md-6">
                    <label class="form-label">Imagen portada</label>
                    <input type="file" name="imagen" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Archivo índice (PDF)</label>
                    <input type="file" name="archivo_indice" class="form-control">
                </div>

            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success px-4">
                    Guardar Libro
                </button>
            </div>

        </form>
    </div>
</div>
 @endsection
 @section('modal')
@endsection