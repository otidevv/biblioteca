@extends('layouts.admin')

@section('page-title', 'Gestión de actividades')

@section('css')
    <link href="{{ asset('css/administracion/actividades.css') }}?v={{ filemtime(public_path('css/administracion/actividades.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/actividades.js') }}?v={{ filemtime(public_path('js/administracion/actividades.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administración</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Actividades</span>
    </div>

    {{-- Resumen: se actualiza con cada recarga de la tabla --}}
    <section class="admin-dashboard__stats" aria-label="Resumen de actividades">
        <article class="admin-stat activities-stat--upcoming">
            <div class="admin-stat__icon"><i class="bi bi-calendar-plus"></i></div>
            <div class="admin-stat__label">Próximas</div>
            <div class="admin-stat__value" data-resumen="proximas">—</div>
            <div class="admin-stat__hint">Publicadas y aún no empiezan.</div>
        </article>
        <article class="admin-stat activities-stat--live">
            <div class="admin-stat__icon"><i class="bi bi-broadcast"></i></div>
            <div class="admin-stat__label">En curso</div>
            <div class="admin-stat__value" data-resumen="en_curso">—</div>
            <div class="admin-stat__hint">Se están realizando hoy.</div>
        </article>
        <article class="admin-stat activities-stat--past">
            <div class="admin-stat__icon"><i class="bi bi-calendar-check"></i></div>
            <div class="admin-stat__label">Finalizadas</div>
            <div class="admin-stat__value" data-resumen="finalizadas">—</div>
            <div class="admin-stat__hint">Aparecen como anteriores en la agenda.</div>
        </article>
        <article class="admin-stat activities-stat--off">
            <div class="admin-stat__icon"><i class="bi bi-eye-slash"></i></div>
            <div class="admin-stat__label">Inactivas</div>
            <div class="admin-stat__value" data-resumen="inactivas">—</div>
            <div class="admin-stat__hint">No se publican ni notifican.</div>
        </article>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Agenda de actividades</h2>
                <p class="admin-panel__copy">Publica talleres, encuentros y avisos de biblioteca. Las actividades activas aparecen en la agenda pública y generan una notificación para toda la comunidad.</p>
            </div>
            <div class="admin-actions">
                <a href="{{ route('evento') }}" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost">
                    <i class="bi bi-box-arrow-up-right"></i>
                    Ver agenda pública
                </a>
                <button id="btnNuevaCategoriaActividad" class="admin-btn admin-btn--ghost">
                    <i class="bi bi-tags"></i>
                    Gestionar categorías
                </button>
                <button id="btnNuevaActividad" class="admin-btn admin-btn--primary">
                    <i class="bi bi-plus-circle"></i>
                    Crear actividad
                </button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-actividades" class="table table-hover table-bordered align-middle datatable w-100">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Fecha</th>
                        <th>Agenda</th>
                        <th>Modalidad</th>
                        <th>Destacado</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <section class="admin-panel activities-categories-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Categorías de actividad</h2>
                <p class="admin-panel__copy">Define los grupos que se usaran al publicar talleres, eventos y avisos dentro del sistema.</p>
            </div>
            <div class="activities-categories-panel__hint">
                <i class="bi bi-info-circle"></i>
                <span>La creación y edición se realizan desde <strong>Gestionar categorías</strong>.</span>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-categorias-actividad" class="table table-hover table-bordered align-middle datatable w-100">
                <thead>
                    <tr>
                        <th>Abreviatura</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Actividades</th>
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
<div class="modal fade" id="modalActividad" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form id="formActividad" enctype="multipart/form-data" class="modal-content shadow-sm activities-modal">
            @csrf
            <input type="hidden" id="id" name="id">
            <div class="modal-header activities-modal__header">
                <div>
                    <span class="activities-modal__eyebrow">
                        <i class="bi bi-calendar-event-fill"></i>
                        Programación cultural
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de actividad</h5>
                    <p class="activities-modal__copy mb-0">Configura información, fechas y visibilidad de una actividad. Si queda activa, también se notificará a los usuarios.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body activities-modal__body">
                <div class="activities-modal__intro">
                    <div class="activities-modal__intro-icon">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <strong>Publicación con notificación</strong>
                        <p class="mb-0">Este módulo alimenta la página de eventos y el centro de notificaciones. Solo las actividades activas se publican y notifican.</p>
                    </div>
                </div>

                <div class="admin-modal-section activities-modal__section">
                    <h6 class="activities-modal__section-title">Contenido principal</h6>
                    <div class="row g-3">
                        <div class="col-md-8 form-group form-required">
                            <label class="form-label">Título</label>
                            <input type="text" id="titulo" name="titulo" class="form-control" maxlength="150" placeholder="Taller de alfabetización informacional">
                        </div>
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Categoría</label>
                            <select id="actividad_categoria_id" name="actividad_categoria_id" class="form-select">
                                <option value="">Seleccione</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label d-flex justify-content-between">
                                <span>Resumen</span>
                                <small class="text-muted" data-contador="resumen">0 / 500</small>
                            </label>
                            <textarea id="resumen" name="resumen" rows="2" maxlength="500" class="form-control" placeholder="Descripción corta: se muestra en la tarjeta de la agenda y en la notificación."></textarea>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Contenido</label>
                            <textarea id="contenido" name="contenido" rows="5" class="form-control" placeholder="Detalle ampliado: se muestra al abrir la actividad en la agenda. Los saltos de línea se respetan."></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-modal-section activities-modal__section">
                    <h6 class="activities-modal__section-title">Programación y contexto</h6>
                    <div class="row g-3">
                        <div class="col-md-3 form-group form-required">
                            <label class="form-label">Fecha inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="form-label">Fecha fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="form-label">Hora inicio</label>
                            <input type="time" id="hora_inicio" name="hora_inicio" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="form-label">Hora fin</label>
                            <input type="time" id="hora_fin" name="hora_fin" class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Lugar</label>
                            <input type="text" id="lugar" name="lugar" class="form-control" placeholder="Biblioteca central - Sala de lectura">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Modalidad</label>
                            <select id="modalidad" name="modalidad" class="form-select">
                                <option value="">Seleccione</option>
                                <option value="Presencial">Presencial</option>
                                <option value="Virtual">Virtual</option>
                                <option value="Mixta">Mixta</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Referencia</label>
                            <input type="text" id="referencia" name="referencia" class="form-control" maxlength="255" placeholder="Ponente o https://enlace-de-inscripcion">
                            <small class="form-text">Si empieza con http(s) se muestra como enlace "Más información".</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Destacada</label>
                            <select id="destacado" name="destacado" class="form-select">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Estado</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Imagen</label>
                            <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*">
                            <small class="form-text">JPG o PNG de hasta 3 MB, de preferencia horizontal (16:9).</small>
                        </div>
                        <div class="col-12 d-none" id="imagenPreviewGrupo">
                            <div class="activities-preview">
                                <img id="imagenPreview" src="" alt="Vista previa de la imagen">
                                <div>
                                    <strong id="imagenPreviewTitulo">Imagen actual</strong>
                                    <label class="form-check mt-1 mb-0" id="quitarImagenGrupo">
                                        <input type="checkbox" class="form-check-input" id="quitar_imagen" name="quitar_imagen" value="1">
                                        <span class="form-check-label">Quitar la imagen</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer activities-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalCategoriaActividad" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formCategoriaActividad" class="modal-content shadow-sm activities-modal activities-category-modal">
            @csrf
            <input type="hidden" id="categoria_id" name="id">
            <div class="modal-header activities-modal__header">
                <div>
                    <span class="activities-modal__eyebrow">
                        <i class="bi bi-tags-fill"></i>
                        Clasificación editorial
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Categoría de actividad</h5>
                    <p class="activities-modal__copy mb-0">Crea categorías para organizar las actividades publicadas y facilitar la búsqueda en la agenda.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body activities-modal__body">
                <div class="activities-modal__intro">
                    <div class="activities-modal__intro-icon">
                        <i class="bi bi-collection-fill"></i>
                    </div>
                    <div>
                        <strong>Taxonomía de actividades</strong>
                        <p class="mb-0">Usa categorías claras y breves para que el personal identifique rápido el tipo de publicación y los usuarios encuentren mejor cada actividad.</p>
                    </div>
                </div>

                <div class="admin-modal-section activities-modal__section">
                    <h6 class="activities-modal__section-title">Datos de la categoría</h6>
                    <div class="row g-3">
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Abreviatura</label>
                            <input type="text" id="abreviatura" name="abreviatura" class="form-control" maxlength="20" placeholder="TALLER">
                        </div>
                        <div class="col-md-8 form-group form-required">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" maxlength="100" placeholder="Talleres y capacitaciones">
                        </div>
                        <div class="col-md-8 form-group">
                            <label class="form-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="3" class="form-control" placeholder="Describe el tipo de actividades que agrupará esta categoría."></textarea>
                        </div>
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Estado</label>
                            <select id="categoria_estado" name="estado" class="form-select">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer activities-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar categoría</button>
            </div>
        </form>
    </div>
</div>
@endsection
