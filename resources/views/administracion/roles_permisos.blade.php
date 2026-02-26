@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/rol.js') }}"></script>
@endsection
@section('content')
<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de roles y permisos</h1>
        <button id="btnNuevo"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            ➕ Agregar Rol
        </button>
    </div>

    <div class="overflow-x-auto">
        <table id="tabla-roles" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
            <thead class="bg-gray-100">
                <tr>
                    <th>Nombre</th>
                    <th>Usuarios</th>
                    <th>Permisos</th>
                    <th>Opciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


<!-- MODAL -->

@endsection
@section('modal')
    
<div class="modal fade" id="modalRoles" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formRoles">
            <input type="hidden" id="id" name="id">
            <div class="modal-content shadow-sm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold">Registro de Roles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- ================= DATOS PERSONA ================= -->
                    <h6 class="text-primary mb-2">Datos del Rol </h6>
                    <div class="row g-3 mb-3">

                        <div class="col-md-12">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="form-control"></textarea>
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
<div class="modal fade" id="modalPermisos" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="formPermisos">
            <input type="hidden" id="rol_id" name="rol_id">
            <div class="modal-content shadow-sm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold">Registro de Permisos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @foreach ($permisos as $padre)
                            <div class="col-md-6 col-lg-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <!-- PADRE SOLO TITULO -->
                                    <div class="fw-bold text-primary mb-2">
                                        {{ $padre->nombre }}
                                    </div>
                                    <!-- HIJOS -->
                                    <div class="ms-3">
                                        @foreach ($padre->hijos as $hijo)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permisos[]" id="permiso_{{ $hijo->id }}" value="{{ $hijo->id }}">
                                                <label class="form-check-label" for="permiso_{{ $hijo->id }}">
                                                    {{ $hijo->nombre }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        @endforeach
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
 @endsection