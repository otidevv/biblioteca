@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/usuario.js') }}"></script>
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
        <table id="tabla-usuarios" class="w-full table-auto border-collapse">
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
 <div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="formUsuario">
            @csrf
            <input type="hidden" id="id">
            <input type="hidden" id="persona_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registro de Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label>Tipo Persona</label>
                            <select name="tipo_persona" id="tipo_persona" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <option value="ESTUDIANTE">ESTUDIANTE</option>
                                <option value="DOCENTE">DOCENTE</option>
                                <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                                <option value="EXTERNO">EXTERNO</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>DNI</label>
                            <input type="text" id="dni" class="form-control">
                        </div>
                        
                        <div class="col-md-4">
                            <label>Nombres</label>
                            <input type="text" id="nombres" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label>Apellido Paterno</label>
                            <input type="text" id="apellido_paterno" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label>Apellido Materno</label>
                            <input type="text" id="apellido_materno" class="form-control">
                        </div>


                        <div class="col-md-4">
                            <label>Código </label>
                            <input type="text" id="codigo_institucional" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Carrera</label>
                            @if(count($carreras) > 0)
                                <select id="carrera_id" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($carreras as $carrera)
                                        <option value="{{ $carrera['id'] }}">{{ $carrera['nombre'] }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label>Estado Académico</label>
                            <input type="text" id="estado_academico" class="form-control"
                                   placeholder="ESTUDIANTE, EGRESADO">
                        </div>
                        <div class="col-md-3">
                            <label>Sexo</label>
                            <select id="sexo" class="form-select">
                                <option value="">--</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="O">Otro</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Teléfono</label>
                            <input type="text" id="telefono" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Correo</label>
                            <input type="email" id="email_personal" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Dirección</label>
                            <input type="text" id="direccion" class="form-control">
                        </div>


                    </div>

                    <!-- ================= DATOS USUARIO ================= -->
                    <h6 class="border-bottom pb-2 mt-4 mb-3">Acceso al Sistema</h6>

                    <div class="row g-2">

                        <div class="col-md-4">
                            <label>Usuario</label>
                            <input type="text" id="name" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Email Sistema</label>
                            <input type="email" id="email" class="form-control">
                        </div>

                        <div class="col-md-4 password-group">
                            <label>Password</label>
                            <input type="password" id="password" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Tipo Usuario</label>
                            <select id="tipo_usuario" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <option value="ADMIN">ADMIN</option>
                                <option value="BIBLIOTECARIO">BIBLIOTECARIO</option>
                                <option value="LECTOR">LECTOR</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success" type="submit">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

