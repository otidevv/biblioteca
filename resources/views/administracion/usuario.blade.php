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

                            <div class="col-md-4 form-group">
                                <label class="form-label">DNI</label>
                                <input type="hidden" id="id" name="id">
                                <input type="text" id="dni" name="dni" class="form-control validar_numero">
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
                            <div class="col-md-6 ">
                                <label class="form-label">Sexo</label>
                                <select id="sexo" name="sexo" class="form-select validar_select">
                                    <option value="0">Seleccione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="form-label">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" class="form-control validar_numero">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Dirección</label>
                                <input type="text" id="direccion" name="direccion" class="form-control">
                            </div>
                        </div>
                        <div class="row g-3 mb-3" id="div_credenciales">
                            <hr>
                            <h6 class="text-primary mb-2 mt-2">Credenciales de acceso</h6>
                            <div class="col-md-6">
                                <label class="form-label">Correo</label>
                                <input type="email" id="correo" name="correo" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Biblioteca</label>
                                <select id="biblioteca" name="biblioteca" class="form-select validar_select">
                                    <option value="0">Seleccione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
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
                        </div>
                        <div class="col-md-12" id="div_roles">
                            <hr>
                            <h6 class="text-primary mb-2 mt-2">Roles asignados</h6>
                            <div class="row  ps-6">
                                @foreach ($tiposUsuarios as $rol)
                                    <div class="col-md-4">
                                        <div class="form-check  rounded p-2 mb-2">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="roles[]"
                                                id="rol_{{ $rol->id }}"
                                                value="{{ $rol->id }}"
                                            >
                                            <label class="form-check-label fw-medium" for="rol_{{ $rol->id }}">
                                                {{ $rol->nombre }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
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