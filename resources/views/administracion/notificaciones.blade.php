@extends('layouts.admin')

@section('page-title', 'Gestión de notificaciones')

@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/administracion/notificaciones.css') }}?v={{ filemtime(public_path('css/administracion/notificaciones.css')) }}" rel="stylesheet" />
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
        <span>Administración</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Notificaciones</span>
    </div>

    {{-- Resumen: se actualiza con cada recarga de la tabla --}}
    <section class="admin-dashboard__stats" aria-label="Resumen de notificaciones">
        <article class="admin-stat notifications-stat--live">
            <div class="admin-stat__icon"><i class="bi bi-broadcast"></i></div>
            <div class="admin-stat__label">Publicadas</div>
            <div class="admin-stat__value" data-resumen="publicadas">—</div>
            <div class="admin-stat__hint">Visibles ahora en la campana.</div>
        </article>
        <article class="admin-stat notifications-stat--scheduled">
            <div class="admin-stat__icon"><i class="bi bi-calendar-event"></i></div>
            <div class="admin-stat__label">Programadas</div>
            <div class="admin-stat__value" data-resumen="programadas">—</div>
            <div class="admin-stat__hint">Se publicarán en su fecha.</div>
        </article>
        <article class="admin-stat notifications-stat--expired">
            <div class="admin-stat__icon"><i class="bi bi-hourglass-bottom"></i></div>
            <div class="admin-stat__label">Expiradas</div>
            <div class="admin-stat__value" data-resumen="expiradas">—</div>
            <div class="admin-stat__hint">Ya no se muestran.</div>
        </article>
        <article class="admin-stat notifications-stat--off">
            <div class="admin-stat__icon"><i class="bi bi-bell-slash"></i></div>
            <div class="admin-stat__label">Inactivas</div>
            <div class="admin-stat__value" data-resumen="inactivas">—</div>
            <div class="admin-stat__hint">Guardadas sin publicar.</div>
        </article>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Centro de notificaciones</h2>
                <p class="admin-panel__copy">Envía avisos al personal, a los lectores o a personas concretas. Las actividades publicadas generan su propia notificación automáticamente.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevaNotificacion" class="admin-btn admin-btn--primary"><i class="bi bi-plus-circle"></i> Crear notificación</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-notificaciones" class="table table-hover table-bordered align-middle datatable w-100">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Audiencia</th>
                        <th>Destino</th>
                        <th>Publicación</th>
                        <th>Expiración</th>
                        <th>Vigencia</th>
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
                        Mensajería institucional
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de notificación</h5>
                    <p class="notifications-modal__copy mb-0">Crea avisos generales, mensajes por audiencia o comunicaciones personales para usuarios concretos.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body notifications-modal__body">
                <div class="notifications-preview">
                    <span class="notifications-preview__label">Así se verá en la campana</span>
                    <div class="notifications-preview__card" id="previewTarjeta">
                        <span class="notifications-preview__icon"><i id="previewIcono" class="bi bi-bell-fill"></i></span>
                        <div>
                            <strong id="previewTitulo">Título de la notificación</strong>
                            <p id="previewContenido">Aquí aparecerá el mensaje que verá el usuario.</p>
                            <small id="previewEnlace" class="d-none"></small>
                        </div>
                    </div>
                </div>

                <div class="admin-modal-section notifications-modal__section">
                    <h6 class="notifications-modal__section-title">Contenido del mensaje</h6>
                    <div class="row g-3">
                        <div class="col-md-8 form-group form-required">
                            <label class="form-label">Título</label>
                            <input type="text" id="titulo" name="titulo" class="form-control" maxlength="255" placeholder="Ej.: Horario especial por feriado">
                        </div>
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Tipo</label>
                            <select id="tipo" name="tipo" class="form-select">
                                <option value="aviso">Aviso</option>
                                <option value="actividad">Actividad</option>
                                <option value="recordatorio">Recordatorio</option>
                                <option value="personal">Personal</option>
                                <option value="critico">Crítico</option>
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
                    <h6 class="notifications-modal__section-title">Destino y publicación</h6>
                    <div class="row g-3">
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Audiencia</label>
                            <select id="audiencia" name="audiencia" class="form-select">
                                <option value="admins">Personal interno</option>
                                <option value="lectores">Lectores</option>
                                <option value="personal">Personal</option>
                                <option value="global">Global</option>
                            </select>
                            <small class="form-text" id="ayudaAudiencia"></small>
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
                            <label class="form-label">Enlace de acción <span class="text-muted fw-normal">(opcional)</span></label>
                            <input type="text" id="accion_url" name="accion_url" class="form-control" maxlength="255" placeholder="/evento, /catalogo o https://...">
                            <small class="form-text">Ruta interna que empiece con / o dirección http(s). Se abre al tocar la notificación.</small>
                        </div>
                        <div class="col-md-12 form-group d-none notifications-modal__recipients" id="grupoDestinatarios">
                            <label class="form-label">Destinatarios personales</label>
                            <select id="user_ids" name="user_ids[]" class="form-select" multiple data-placeholder="Escribe al menos 2 letras: nombre, correo o DNI"></select>
                            <small class="form-text">Busca entre todos los usuarios. Los resultados se agrupan en personal interno, lectores y otros.</small>
                        </div>
                        <div class="col-md-12 form-group d-none" id="grupoActividad">
                            <label class="form-label">Actividad relacionada</label>
                            <select id="actividad_id" name="actividad_id" class="form-select">
                                <option value="">Sin actividad vinculada</option>
                            </select>
                            <small class="form-text">La notificación llevará a la página de eventos.</small>
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
