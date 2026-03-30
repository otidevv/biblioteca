@extends('layouts.admin')

@section('page-title', 'Gestion de lectores')

@section('css')
    <link href="{{ asset('css/lectores/registro_lectores.css') }}?v={{ filemtime(public_path('css/lectores/registro_lectores.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/lectores/lectores.js') }}?v={{ filemtime(public_path('js/lectores/lectores.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Lectores</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Registro</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <span class="reader-page__eyebrow"><i class="bi bi-people"></i> Comunidad lectora</span>
                <h2 class="admin-panel__title">Gestion de lectores</h2>
                <p class="admin-panel__copy">Administra el registro institucional, consulta datos de contacto y mantén al día la informacion academica del lector.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevo" class="admin-btn admin-btn--primary">
                    <i class="bi bi-person-plus"></i>
                    <span>Agregar lector</span>
                </button>
            </div>
        </div>

        <div class="reader-summary">
            <div class="reader-summary__card">
                <span class="reader-summary__icon"><i class="bi bi-person-vcard"></i></span>
                <div>
                    <div class="reader-summary__title">Registro unificado</div>
                    <div class="reader-summary__copy">Crea o actualiza lectores desde una sola ficha.</div>
                </div>
            </div>
            <div class="reader-summary__card">
                <span class="reader-summary__icon"><i class="bi bi-search"></i></span>
                <div>
                    <div class="reader-summary__title">Consulta por DNI</div>
                    <div class="reader-summary__copy">Autocompleta datos cuando la fuente externa responde.</div>
                </div>
            </div>
            <div class="reader-summary__card">
                <span class="reader-summary__icon"><i class="bi bi-mortarboard"></i></span>
                <div>
                    <div class="reader-summary__title">Perfil academico</div>
                    <div class="reader-summary__copy">Activa carrera y estado academico solo para estudiantes.</div>
                </div>
            </div>
        </div>

        <div class="admin-table-shell reader-table-shell table-responsive">
            <table id="tabla-lectores" class="table table-hover table-bordered align-middle datatable w-100 reader-table">
                <thead>
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
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalLector" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content reader-modal">
            <form id="formLector">
                @csrf
                <input type="hidden" id="id" name="id">
                <input type="hidden" id="persona_id" name="persona_id">

                <div class="modal-header reader-modal__header">
                    <div>
                        <span class="reader-modal__eyebrow"><i class="bi bi-person-badge"></i> Registro de lector</span>
                        <h5 class="modal-title fw-semibold mb-0">Datos personales y acceso</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body reader-modal__body">
                    <div class="reader-modal__section">
                        <div class="reader-modal__section-head">
                            <span class="reader-modal__section-icon"><i class="bi bi-person-lines-fill"></i></span>
                            <div>
                                <div class="reader-modal__section-title">Datos personales</div>
                                <p class="reader-modal__section-copy">Identifica al lector y completa la informacion base.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">Tipo persona</label>
                                <select id="tipo_persona" name="tipo_persona" class="form-select validar_select">
                                    <option value="0">Seleccione</option>
                                    <option value="ESTUDIANTE">ESTUDIANTE</option>
                                    <option value="DOCENTE">DOCENTE</option>
                                    <option value="ADMINISTRATIVO">ADMINISTRATIVO</option>
                                    <option value="EXTERNO">EXTERNO</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">DNI</label>
                                <div class="input-group">
                                    <input type="text" id="dni" name="dni" class="form-control validar_numero" placeholder="Ingrese DNI">
                                    <button class="btn btn-outline-primary" type="button" id="btnBuscarDni">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">Sexo</label>
                                <select id="sexo" name="sexo" class="form-select validar_select">
                                    <option value="0">Seleccione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">Nombres</label>
                                <input type="text" id="nombres" name="nombres" class="form-control mayuscula">
                            </div>

                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">Apellido paterno</label>
                                <input type="text" id="apellido_paterno" name="apellido_paterno" class="form-control mayuscula">
                            </div>

                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">Apellido materno</label>
                                <input type="text" id="apellido_materno" name="apellido_materno" class="form-control mayuscula">
                            </div>
                        </div>
                    </div>

                    <div class="reader-modal__section" id="bloqueEstudiante">
                        <div class="reader-modal__section-head">
                            <span class="reader-modal__section-icon"><i class="bi bi-mortarboard"></i></span>
                            <div>
                                <div class="reader-modal__section-title">Informacion academica</div>
                                <p class="reader-modal__section-copy">Solo se habilita cuando el tipo persona es estudiante.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4 form-group" id="grupoCodigoInstitucional">
                                <label class="form-label">Codigo institucional</label>
                                <input type="text" id="codigo_institucional" name="codigo_institucional" class="form-control mayuscula">
                            </div>

                            <div class="col-md-4 form-group" id="grupoCarrera">
                                <label class="form-label">Carrera</label>
                                <select id="carrera_id" name="carrera_id" class="form-select mayuscula">
                                    <option value="0">Seleccione</option>
                                    @foreach($carreras as $carrera)
                                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 form-group" id="grupoEstadoAcademico">
                                <label class="form-label">Estado academico</label>
                                <select id="estado_academico" name="estado_academico" class="form-select mayuscula">
                                    <option value="0">Seleccione</option>
                                    <option value="1">ESTUDIANTE</option>
                                    <option value="2">EGRESADO</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="reader-modal__section">
                        <div class="reader-modal__section-head">
                            <span class="reader-modal__section-icon"><i class="bi bi-telephone"></i></span>
                            <div>
                                <div class="reader-modal__section-title">Datos de contacto</div>
                                <p class="reader-modal__section-copy">Canales para comunicacion y seguimiento.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">Telefono</label>
                                <input type="text" id="telefono" name="telefono" class="form-control validar_numero">
                            </div>

                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">Correo personal</label>
                                <input type="email" id="email_personal" name="email_personal" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Direccion</label>
                                <input type="text" id="direccion" name="direccion" class="form-control mayuscula">
                            </div>
                        </div>
                    </div>

                    <div class="reader-modal__section">
                        <div class="reader-modal__section-head">
                            <span class="reader-modal__section-icon"><i class="bi bi-shield-lock"></i></span>
                            <div>
                                <div class="reader-modal__section-title">Acceso al sistema</div>
                                <p class="reader-modal__section-copy">Se genera el usuario lector y la contraseña inicial.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Usuario</label>
                                <input type="email" id="email" name="email" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-4 form-group password-group form-required">
                                <label class="form-label">Contraseña</label>
                                <input type="password" id="password" name="password" class="form-control">
                            </div>

                            <div class="col-md-4 form-group password-group form-required">
                                <label class="form-label">Repetir contraseña</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer reader-modal__footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-save me-1"></i>
                        <span>Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
