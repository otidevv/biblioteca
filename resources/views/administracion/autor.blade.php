@extends('layouts.admin')

@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
@endsection
@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/administracion/autor.js') }}"></script>
@endsection
@section('content')
<nav class="mb-4 text-sm text-gray-600">
    <ol class="flex items-center space-x-2">
        <li class="font-semibold text-gray-800">
            Administración
        </li>
        <li class="text-gray-400">›</li>
        <li class="text-emerald-700 font-semibold">
            Autores
        </li>
    </ol>
</nav>

<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de autores</h1>
        <button id="btnNuevo"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            ➕ Agregar Autor
        </button>
    </div>
    <div class="overflow-x-auto">
        <table id="tabla-autor" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
            <thead class="bg-gray-100">
                <tr>
                    <th>Nombre y Apellidos</th>
                    <th>País</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


<!-- MODAL -->

@endsection
@section('modal')
<div class="modal fade" id="modalAutor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-sm">

            <!-- HEADER -->
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-semibold">Registro de Autor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAutor">
                <input type="hidden" id="id" name="id">

                <!-- BODY -->
                <div class="modal-body">

                    <div class="row g-3">

                        <!-- Razón social -->
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control">
                        </div>

                        <!-- Responsable -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">Apellidos</label>
                            <input type="text" id="apellidos" name="apellidos" class="form-control">
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label">País</label>
                            <select id="pais" name="pais" class="form-select select2">
                                <option value="0">Seleccione</option>
                                @foreach($paises as $pais)
                                    <option value="{{$pais->id}}">{{$pais->nombre}}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer bg-light">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success px-4">
                        Guardar
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


 @endsection