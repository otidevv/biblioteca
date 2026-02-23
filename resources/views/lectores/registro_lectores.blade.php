@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/lectores/lectores.js') }}"></script>
@endsection
@section('content')

<nav class="mb-4 text-sm text-gray-600">
    <ol class="flex items-center space-x-2">
        <li class="font-semibold text-gray-800">
            Administración
        </li>
        <li class="text-gray-400">›</li>
        <li class="text-emerald-700 font-semibold">
            Lectores
        </li>
    </ol>
</nav>
<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de lectores</h1>
        <button id="btnNuevo"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            ➕ Agregar lector
        </button>
    </div>
    <div class="overflow-x-auto">
        <table id="tabla-lectores" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
            <thead class="bg-gray-100">
                <tr>
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Usuario</th>
                    <th>Tipo</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


<!-- MODAL -->
 <div class="modal fade" id="modalLector" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div id="div_form">
            <form id="formLector">
                @csrf

                <input type="hidden" id="id" name="id">
                <input type="hidden" id="persona_id" name="persona_id">

                <div class="modal-content shadow-lg border-0">

                    <!-- HEADER -->
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-person-plus me-2"></i> Registro de Lector
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body">

                        <!-- ================= DATOS PERSONALES ================= -->
                        <div class="card mb-3 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-person-lines-fill me-1"></i> Datos Personales
                                </h6>

                                <div class="row g-3">

                                    <div class="col-md-4 form-group form-required">
                                        <label class="form-label">Tipo Persona</label>
                                        <select id="tipo_persona" name="tipo_persona"
                                                class="form-select validar_select">
                                            <option value="0">-- Seleccionar --</option>
                                            <option value="ESTUDIANTE">ESTUDIANTE</option>
                                            <option value="DOCENTE">DOCENTE</option>
                                            <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                                            <option value="EXTERNO">EXTERNO</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group form-required">
                                        <label class="form-label">DNI</label>
                                        <div class="input-group">
                                            <input type="text" id="dni" name="dni"
                                                   class="form-control validar_numero"
                                                   placeholder="Ingrese DNI">
                                            <button class="btn btn-outline-primary"
                                                    type="button"
                                                    id="btnBuscarDni">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-4 form-group form-required">
                                        <label class="form-label">Sexo</label>
                                        <select id="sexo" name="sexo"
                                                class="form-select mayuscula validar_select">
                                            <option value="0">Selecciona</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                            <option value="O">Otro</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group form-required">
                                        <label class="form-label">Nombres</label>
                                        <input type="text" id="nombres" name="nombres"
                                               class="form-control mayuscula">
                                    </div>

                                    <div class="col-md-4 form-group form-required">
                                        <label class="form-label">Apellido Paterno</label>
                                        <input type="text" id="apellido_paterno"
                                               name="apellido_paterno"
                                               class="form-control mayuscula">
                                    </div>

                                    <div class="col-md-4 form-group form-required">
                                        <label class="form-label">Apellido Materno</label>
                                        <input type="text" id="apellido_materno"
                                               name="apellido_materno"
                                               class="form-control mayuscula">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ================= DATOS ESTUDIANTE ================= -->
                        <div class="card mb-3 border-0 bg-light" id="bloqueEstudiante">
                            <div class="card-body">
                                <h6 class="text-success mb-3">
                                    <i class="bi bi-mortarboard me-1"></i> Información Académica
                                </h6>

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label">Código Institucional</label>
                                        <input type="text" id="codigo_institucional"
                                               name="codigo_institucional"
                                               class="form-control mayuscula">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Carrera</label>
                                        <select id="carrera_id" name="carrera_id"
                                                class="form-select mayuscula">
                                            <option value="0">-- Seleccionar --</option>
                                            @foreach($carreras as $carrera)
                                                <option value="{{ $carrera['id'] }}">
                                                    {{ $carrera['nombre'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Estado Académico</label>
                                        <select type="text" id="estado_academico"
                                               name="estado_academico"
                                               class="form-control mayuscula">
                                            <option value="0">-- Seleccionar --</option>
                                            <option value="1">ESTUDIANTE</option>   
                                            <option value="2">EGRESADO</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ================= CONTACTO ================= -->
                        <div class="card mb-3 border-0 bg-light">
                            <div class="card-body">
                                <h6 class="text-warning mb-3">
                                    <i class="bi bi-telephone me-1"></i> Datos de Contacto
                                </h6>

                                <div class="row g-3">

                                    <div class="col-md-4 form-group form-required">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" id="telefono" name="telefono"
                                               class="form-control validar_numero">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Correo Personal</label>
                                        <input type="email" id="email_personal"
                                               name="email_personal"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Dirección</label>
                                        <input type="text" id="direccion"
                                               name="direccion"
                                               class="form-control mayuscula">
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ================= ACCESO AL SISTEMA ================= -->
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="text-danger mb-3">
                                    <i class="bi bi-shield-lock me-1"></i> Acceso al Sistema
                                </h6>

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label">Usuario</label>
                                        <input type="email" id="email" name="email"
                                               class="form-control bg-light" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" id="password"
                                               name="password"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Repetir Contraseña</label>
                                        <input type="password"
                                               id="password_confirmation"
                                               name="password_confirmation"
                                               class="form-control">
                                    </div>

                                </div>
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
                        <button type="submit"
                                class="btn btn-success px-4">
                            <i class="bi bi-save me-1"></i> Guardar
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>


@endsection

