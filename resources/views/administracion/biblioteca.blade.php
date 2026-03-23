@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/biblioteca.js') }}"></script>
@endsection
@section('content')
<nav class="mb-4 text-sm text-gray-600">
    <ol class="flex items-center space-x-2">
        <li class="font-semibold text-gray-800">
            Administración
        </li>
        <li class="text-gray-400">›</li>
        <li class="text-emerald-700 font-semibold">
            Bibliotecas
        </li>
    </ol>
</nav>

<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Bibliotecas</h1>
        <button id="btnNuevo"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            ➕ Agregar biblioteca
        </button>
    </div>
    <div class="overflow-x-auto">
        <table id="tabla-biblioteca" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
            <thead class="bg-gray-100">
                <tr>
                    <th>Abrev.</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


<!-- MODAL -->

@endsection
@section('modal')
<div class="modal fade" id="modalBiblioteca" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        
        <form id="formBiblioteca" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="id" name="id">

            <div class="modal-content shadow-sm">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold">Registro de Biblioteca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <h6 class="text-primary mb-2">Datos biblioteca</h6>

                    <div class="row g-3 mb-3">

                        <div class="col-md-4">
                            <label class="form-label">Abrev.</label>
                            <input type="text" id="codigo" name="codigo" class="form-control">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <textarea id="direccion" name="direccion" class="form-control"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="form-control"></textarea>
                        </div>

                        <!-- IMAGEN -->
                        <div class="col-md-12">
                            <label class="form-label">Imagen</label>
                            <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-12 text-center">
                            <img id="previewImagen" src="" 
                                 class="img-fluid rounded shadow-sm d-none mt-2"
                                 style="max-height:200px;">
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success px-4" type="submit">Guardar</button>
                </div>

            </div>
        </form>
    </div>
</div>

 @endsection