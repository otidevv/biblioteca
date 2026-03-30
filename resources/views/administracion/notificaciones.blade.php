@extends('layouts.admin')

@section('page-title', 'Gestion de notificaciones')

@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/administracion/notificaciones.css') }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/administracion/notificaciones.js') }}?v={{ filemtime(public_path('js/administracion/notificaciones.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Notificaciones</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Centro de notificaciones</h2>
                <p class="admin-panel__copy">Gestiona mensajes para personal interno, lectores o usuarios concretos. Tambien puedes vincular una notificacion con una actividad publicada.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevaNotificacion" class="admin-btn admin-btn--primary">Crear notificacion</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-notificaciones" class="table table-hover table-bordered align-middle datatable w-100">
                <thead>
                    <tr>
                        <th>Titulo</th>
                        <th>Tipo</th>
                        <th>Audiencia</th>
                        <th>Destino</th>
                        <th>Publicacion</th>
                        <th>Expiracion</th>
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
<div class="modal fade" id="modalNotificacion" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form id="formNotificacion" class="modal-content shadow-sm notifications-modal">
            <input type="hidden" id="id" name="id">
            <div class="modal-header notifications-modal__header">
                <div>
                    <span class="notifications-modal__eyebrow">
                        <i class="bi bi-bell-fill"></i>
                        Mensajeria institucional
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de notificacion</h5>
                    <p class="notifications-modal__copy mb-0">Crea avisos generales, mensajes por audiencia o comunicaciones personales para usuarios concretos.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body notifications-modal__body">
                <div class="notifications-modal__intro">
                    <div class="notifications-modal__intro-icon">
                        <i class="bi bi-send-check"></i>
                    </div>
                    <div>
                        <strong>Listo para personal interno y lectores</strong>
                        <p class="mb-0">Usa este modulo para segmentar avisos por roles reales del sistema, crear mensajes personales y dejar preparado el flujo de notificacion por actividad o disponibilidad de libros.</p>
                    </div>
                </div>

                <div class="admin-modal-section notifications-modal__section">
                    <h6 class="notifications-modal__section-title">Contenido del mensaje</h6>
                    <div class="row g-3">
                        <div class="col-md-8 form-group form-required">
                            <label class="form-label">Titulo</label>
                            <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Nuevo aviso para lectores o personal interno">
                        </div>
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Tipo</label>
                            <select id="tipo" name="tipo" class="form-select">
                                <option value="aviso">Aviso</option>
                                <option value="actividad">Actividad</option>
                                <option value="recordatorio">Recordatorio</option>
                                <option value="personal">Personal</option>
                                <option value="critico">Critico</option>
                                <option value="disponibilidad_libro">Disponibilidad de libro</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group form-required">
                            <label class="form-label">Contenido</label>
                            <textarea id="contenido" name="contenido" rows="4" class="form-control" placeholder="Describe el mensaje que vera el usuario en la campana o en su centro de avisos."></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-modal-section notifications-modal__section">
                    <h6 class="notifications-modal__section-title">Destino y publicacion</h6>
                    <div class="row g-3">
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Audiencia</label>
                            <select id="audiencia" name="audiencia" class="form-select">
                                <option value="admins">Personal interno</option>
                                <option value="lectores">Lectores</option>
                                <option value="personal">Personal</option>
                                <option value="global">Global</option>
                            </select>
                            <small class="form-text">Personal interno incluye Programador, Administrador, Encargado y Atencion a Estudiantes.</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Publicar desde</label>
                            <input type="datetime-local" id="fecha_publicacion" name="fecha_publicacion" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Expira en</label>
                            <input type="datetime-local" id="fecha_expiracion" name="fecha_expiracion" class="form-control">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">URL de accion</label>
                            <input type="text" id="accion_url" name="accion_url" class="form-control" placeholder="/evento o ruta interna relacionada">
                        </div>
                        <div class="col-md-12 form-group d-none notifications-modal__recipients" id="grupoDestinatarios">
                            <label class="form-label">Destinatarios personales</label>
                            <select id="user_ids" name="user_ids[]" class="form-select" multiple data-placeholder="Buscar usuarios por nombre, correo o rol"></select>
                            <small class="form-text">Busca por nombre, correo o rol. La lista se agrupa en personal interno, lectores y otros usuarios.</small>
                        </div>
                        <div class="col-md-12 form-group d-none" id="grupoActividad">
                            <label class="form-label">Actividad relacionada</label>
                            <select id="actividad_id" name="actividad_id" class="form-select">
                                <option value="">Sin actividad vinculada</option>
                            </select>
                            <small class="form-text">Si seleccionas una actividad, la notificacion puede redirigir a la pagina de eventos.</small>
                        </div>
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Estado</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer notifications-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
