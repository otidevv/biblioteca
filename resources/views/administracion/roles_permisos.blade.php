@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/rol.js') }}"></script>
@endsection
@section('content')
<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h1>
        <button id="btnNuevo"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            ➕ Agregar Usuario
        </button>
    </div>

    {{-- Select para tipos de usuario --}}
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
    
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formUsuario">
            <input type="hidden" id="id">
            <input type="hidden" id="persona_id">

            <div class="modal-content shadow-sm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold">Registro de Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- ================= DATOS PERSONA ================= -->
                    <h6 class="text-primary mb-2">Datos personales</h6>
                    <div class="row g-3 mb-3">

                        <div class="col-md-4">
                            <label class="form-label">DNI</label>
                            <input type="text" id="dni" name="dni" class="form-control">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Nombres</label>
                            <input type="text" id="nombres" name="nombres" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido paterno</label>
                            <input type="text" id="apellido_paterno" name="apellido_paterno" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Apellido materno</label>
                            <input type="text" id="apellido_materno" name="apellido_materno" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sexo</label>
                            <select id="sexo" name="sexo" class="form-select">
                                <option value="">Seleccione</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="O">Otro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" id="direccion" name="direccion" class="form-control">
                        </div>


                    </div>
                    <hr>
                    <h6 class="text-primary mb-2 mt-2">Acceso y roles</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Correo</label>
                            <input type="email" id="correo" name="correo" class="form-control">
                        </div>
                        <div class="col-md-6 password-group">
                            <label class="form-label">Contraseña</label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-6 password-group">
                            <label class="form-label">Confirmar 
                                contraseña</label>
                            <input type="password" id="re_password" class="form-control">
                        </div>
                    <hr>

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