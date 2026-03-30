@extends('layouts.admin')

@section('page-title', 'Gestion de roles y permisos')

@section('css')
    <link href="{{ asset('css/administracion/roles_permisos.css') }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/rol.js') }}?v={{ filemtime(public_path('js/administracion/rol.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Roles y permisos</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Roles y permisos</h2>
                <p class="admin-panel__copy">Administra perfiles del sistema, revisa cuantos usuarios los usan y controla sus permisos desde una sola tabla.</p>
            </div>

            <div class="admin-actions">
                <button id="btnNuevo" class="admin-btn admin-btn--primary">
                    Agregar rol
                </button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-roles" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuarios</th>
                        <th>Permisos</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalRoles" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formRoles" class="modal-content shadow-sm role-modal">
            <input type="hidden" id="id" name="id">

            <div class="modal-header role-modal__header">
                <div>
                    <span class="role-modal__eyebrow">
                        <i class="bi bi-shield-lock-fill"></i>
                        Configuracion de acceso
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de roles</h5>
                    <p class="role-modal__header-copy mb-0">Define el nombre del perfil y describe claramente el alcance de permisos que tendra dentro del sistema.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body role-modal__body">
                <div class="role-modal__intro">
                    <div class="role-modal__intro-icon">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <div>
                        <strong>Perfil operativo</strong>
                        <p class="mb-0">Usa roles claros y breves para facilitar la asignacion posterior a usuarios, bibliotecas y permisos del sistema.</p>
                    </div>
                </div>

                <div class="admin-modal-section role-modal__section">
                    <h6 class="role-modal__section-title">Datos del rol</h6>
                    <p class="role-modal__section-copy">Completa una descripcion simple para que otros administradores entiendan de inmediato para que sirve este perfil.</p>
                    <div class="row g-3">
                        <div class="col-md-12 form-group">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ejemplo: Coordinador de biblioteca">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Descripcion</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="4" placeholder="Describe responsabilidades, alcance y tipo de acceso del rol."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer role-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-success px-4" type="submit">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalPermisos" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formPermisos" class="modal-content shadow-sm role-modal role-modal--permissions">
            <input type="hidden" id="rol_id" name="rol_id">

            <div class="modal-header role-modal__header">
                <div>
                    <span class="role-modal__eyebrow">
                        <i class="bi bi-shield-check"></i>
                        Matriz de permisos
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Asignacion de permisos</h5>
                    <p class="role-modal__header-copy mb-0">Activa o desactiva permisos por modulo. Puedes marcar un grupo completo o seleccionar solo accesos puntuales.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body role-modal__body">
                <div class="role-permissions__toolbar">
                    <div class="role-permissions__legend">
                        <span><i class="bi bi-check2-square"></i> Marca padres para seleccionar hijos</span>
                        <span><i class="bi bi-list-task"></i> Ajusta modulos individualmente cuando haga falta</span>
                    </div>
                </div>

                <div class="row g-3">
                    @foreach ($permisos as $padre)
                        <div class="col-md-6 col-lg-6 mb-3">
                            <div class="admin-modal-section role-permission-card h-100">
                                <div class="role-permission-card__header">
                                    <label class="role-permission-card__parent">
                                        <input class="form-check-input permiso-padre" type="checkbox" id="permiso_padre_{{ $padre->id }}" value="{{ $padre->id }}">
                                        <span class="role-permission-card__parent-check">
                                            <i class="bi bi-check2"></i>
                                        </span>
                                        <span>
                                            <strong>{{ $padre->nombre }}</strong>
                                            <small>Seleccionar todos los permisos de este modulo</small>
                                        </span>
                                    </label>
                                </div>
                                <div class="role-permission-card__body">
                                    @foreach ($padre->hijos as $hijo)
                                        <label class="role-permission-item" for="permiso_{{ $hijo->id }}">
                                            <input class="form-check-input permiso-hijo permiso-hijo-{{ $padre->id }}" type="checkbox" name="permisos[]" id="permiso_{{ $hijo->id }}" value="{{ $hijo->id }}">
                                            <span class="role-permission-item__check">
                                                <i class="bi bi-check2"></i>
                                            </span>
                                            <span class="role-permission-item__label">{{ $hijo->nombre }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer role-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-success px-4" type="submit">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
