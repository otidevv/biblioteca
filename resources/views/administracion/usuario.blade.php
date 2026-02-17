@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/usuario.js') }}"></script>
@endsection
@section('content')
<nav class="mb-4 text-sm text-gray-600">
    <ol class="flex items-center space-x-2">
        <li class="font-semibold text-gray-800">
            Administración
        </li>
        <li class="text-gray-400">›</li>
        <li class="text-emerald-700 font-semibold">
            Usuarios
        </li>
    </ol>
</nav>

<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h1>
        <button id="btnNuevo"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            ➕ Agregar Usuario
        </button>
    </div>

    {{-- Select para tipos de usuario --}}
    <div class="mb-4">
        <label for="tipo_usuario" class="block text-gray-700 font-semibold mb-1">Tipo de Usuario:</label>
        <select id="tipo_usuario" class="w-full md:w-1/3 border-gray-300 rounded-lg p-2">
            <option value="">Todos</option>
            @foreach($tiposUsuarios as $tipo)
                <option value="{{ $tipo['id'] }}">{{ $tipo['nombre'] }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto">
        <table id="tabla-usuarios" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
            <thead class="bg-gray-100">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


<!-- MODAL -->

@endsection
@section('modal')
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div id="div_form">
            <form id="formUsuario">
                <input type="hidden" id="id">
                    <div class="modal-content shadow-sm">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-semibold">Registro de bibliotecas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="row g-3 mb-3">

                            <div class="col-md-4 form-group">
                                <label class="form-label">Abreviatura</label>
                                <input type="hidden" id="id" name="id">
                                <input type="text" id="abreviatura" name="abreviatura" class="form-control">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Nombre</label>
                                <input type="text" id="nombre" name="nombre" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Direccion</label>
                                <input type="text" id="direccion" name="direccion" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Descripcion</label>
                                <textarea type="text" id="descripcion" name="descripcion" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn btn-success px-4" type="submit">
                            Guardar
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
 @endsection