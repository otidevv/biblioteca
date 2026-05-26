@extends('layouts.admin')

@section('page-title', 'Gestion de usuarios')

@section('css')
    <link href="{{ asset('css/administracion/usuario.css') }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/usuario.js') }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Usuarios</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <div class="user-page__eyebrow">
                    <i class="bi bi-people-fill"></i>
                    Gestion institucional
                </div>
                <h2 class="admin-panel__title">Usuarios y roles</h2>
                <p class="admin-panel__copy">Administra cuentas, roles asignados y datos de contacto desde una vista más clara.</p>
            </div>

            <div class="admin-actions">
                <button id="btnNuevo" class="admin-btn admin-btn--primary">
                    <i class="bi bi-person-plus-fill"></i>
                    Agregar usuario
                </button>
            </div>
        </div>

        <div class="admin-toolbar user-toolbar">
            <div class="admin-field user-filter">
                <label for="tipo_usuario" class="admin-field__label user-filter__label">Filtrar por rol</label>
                <div class="user-filter__inline">
                    <i class="bi bi-funnel-fill user-filter__icon"></i>
                    <select id="tipo_usuario" class="form-select user-filter__select">
                        <option value="">Todos</option>
                        @foreach($tiposUsuarios as $tipo)
                            @if(strtoupper($tipo->nombre) === 'LECTOR')
                                @continue
                            @endif
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="admin-table-shell table-responsive user-table-shell">
            <table id="tabla-usuarios" class="table table-hover table-bordered align-middle text-nowrap datatable w-100 user-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formUsuario" class="modal-content shadow-sm user-modal">
            <input type="hidden" id="id" name="id">
            <div class="modal-header user-modal__header">
                <div>
                    <span class="user-modal__eyebrow">
                        <i class="bi bi-person-badge-fill"></i>
                        Gestion de cuentas
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de usuario</h5>
                    <p class="user-modal__header-copy mb-0">Completa la información personal, acceso y roles para habilitar la cuenta en el sistema.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body user-modal__body">
                <div class="user-modal__intro">
                    <div class="user-modal__intro-icon">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <div>
                        <strong>Alta institucional</strong>
                        <p class="mb-0">Usa este formulario para registrar personal con acceso administrativo y asociarlo a una biblioteca y uno o varios roles.</p>
                    </div>
                </div>

                <div class="admin-modal-section user-modal__section">
                    <h6 class="user-modal__section-title">Datos personales</h6>
                    <p class="user-modal__section-copy">Datos de identificacion y contacto que se mostraran en la gestion interna.</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">DNI</label>
                            <input type="text" id="dni" name="dni" class="form-control validar_numero">
                            <div class="form-text">Documento unico del usuario.</div>
                        </div>

                        <div class="col-md-8 form-group form-required">
                            <label class="form-label">Nombres</label>
                            <input type="text" id="nombres" name="nombres" class="form-control">
                        </div>

                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Apellido paterno</label>
                            <input type="text" id="apellido_paterno" name="apellido_paterno" class="form-control">
                        </div>

                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Apellido materno</label>
                            <input type="text" id="apellido_materno" name="apellido_materno" class="form-control">
                        </div>

                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Sexo</label>
                            <select id="sexo" name="sexo" class="form-select validar_select">
                                <option value="0">Seleccione</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="O">Otro</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Biblioteca</label>
                            <select id="biblioteca" name="biblioteca" class="form-select validar_select">
                                <option value="0">Seleccione</option>
                                <option value="">Todos</option>
                                @foreach ($bibliotecas as $biblioteca)
                                    <option value="{{ $biblioteca->id }}">{{ $biblioteca->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Define el alcance operativo del usuario.</div>
                        </div>

                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Telefono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control validar_numero">
                        </div>

                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Direccion</label>
                            <input type="text" id="direccion" name="direccion" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="admin-modal-section user-modal__section user-modal__section--soft" id="div_credenciales">
                    <h6 class="user-modal__section-title">Credenciales de acceso</h6>
                    <p class="user-modal__section-copy">Solo se solicitan al crear la cuenta. Luego podrás actualizar la contraseña desde acciones.</p>
                    <div class="row g-3 mt-1 mb-0">
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Correo</label>
                            <input type="email" id="correo" name="correo" class="form-control">
                            <div class="form-text">Se usará como usuario de inicio de sesión.</div>
                        </div>
                        <div class="col-md-6 form-group password-group form-required">
                            <label class="form-label">Contrasena</label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-6 form-group password-group form-required">
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" id="re_password" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="admin-modal-section user-modal__section user-modal__section--roles" id="div_roles">
                    <h6 class="user-modal__section-title">Roles asignados</h6>
                    <p class="user-modal__section-copy">Selecciona uno o varios perfiles segun las tareas que realizara el usuario dentro del sistema.</p>
                    <div class="row g-2">
                        @foreach ($tiposUsuarios as $rol)
                            <div class="col-md-4">
                                <label class="form-check user-role-option">
                                    <input class="form-check-input" type="checkbox" name="roles[]" id="rol_{{ $rol->id }}" value="{{ $rol->id }}">
                                    <span class="user-role-option__check">
                                        <i class="bi bi-check2"></i>
                                    </span>
                                    <span class="user-role-option__content">
                                        <span class="form-check-label fw-medium user-role-option__name">
                                            {{ $rol->nombre }}
                                        </span>
                                        <span class="user-role-option__hint">Permite acceso a funciones relacionadas.</span>
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modal-footer user-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success px-4" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalContrasena" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="formContrasena" class="modal-content shadow-lg border-0 user-password-modal">
            <input type="hidden" id="password_user_id" name="id">
            <div class="modal-header user-password-modal__header">
                <div>
                    <span class="user-modal__eyebrow">
                        <i class="bi bi-shield-lock-fill"></i>
                        Seguridad de acceso
                    </span>
                    <h5 class="modal-title fw-semibold mb-0">Cambiar contraseña</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-3 user-password-modal__body">
                <div class="user-password-modal__account">
                    <label class="form-label fw-semibold text-muted">Usuario</label>
                    <input id="p_apodo" type="text" class="form-control" disabled>
                </div>

                <div class="user-password-modal__divider"></div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nueva contraseña</label>
                    <div class="input-group">
                        <input id="pchange" type="password" class="form-control validar_minimo:8" placeholder="Minimo 8 caracteres">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="pchange">Ver</button>
                    </div>
                    <div class="form-text">
                        <span id="password-strength" class="small text-muted">Usa al menos 8 caracteres</span>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold">Confirmar contraseña</label>
                    <div class="input-group">
                        <input id="pchange_confirmed" type="password" class="form-control validar_igual:pchange" placeholder="Repetir contraseña">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="pchange_confirmed">Ver</button>
                    </div>
                    <div class="form-text" id="password-match-status">
                        <span class="text-muted small">Las contraseñas deben coincidir</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer user-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
