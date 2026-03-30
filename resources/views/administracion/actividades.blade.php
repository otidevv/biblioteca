@extends('layouts.admin')

@section('page-title', 'Gestion de actividades')

@section('css')
    <link href="{{ asset('css/administracion/actividades.css') }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/actividades.js') }}?v={{ filemtime(public_path('js/administracion/actividades.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Actividades</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Agenda de actividades</h2>
                <p class="admin-panel__copy">Publica talleres, encuentros y avisos de biblioteca. Las actividades activas generan notificacion automaticamente para la comunidad.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevaCategoriaActividad" class="admin-btn admin-btn--ghost">
                    <i class="bi bi-tags"></i>
                    Gestionar categorias
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
                        <th>Titulo</th>
                        <th>Categoria</th>
                        <th>Fecha</th>
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
                <h2 class="admin-panel__title">Categorias de actividad</h2>
                <p class="admin-panel__copy">Define los grupos que se usaran al publicar talleres, eventos y avisos dentro del sistema.</p>
            </div>
            <div class="activities-categories-panel__hint">
                <i class="bi bi-info-circle"></i>
                <span>La creacion y edicion se realizan desde <strong>Gestionar categorias</strong>.</span>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-categorias-actividad" class="table table-hover table-bordered align-middle datatable w-100">
                <thead>
                    <tr>
                        <th>Abreviatura</th>
                        <th>Nombre</th>
                        <th>Descripcion</th>
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
                        Programacion cultural
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de actividad</h5>
                    <p class="activities-modal__copy mb-0">Configura informacion, fechas y visibilidad de una actividad. Si queda activa, tambien se notificara a los usuarios.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body activities-modal__body">
                <div class="activities-modal__intro">
                    <div class="activities-modal__intro-icon">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <strong>Publicacion con notificacion</strong>
                        <p class="mb-0">Este modulo alimenta la pagina de eventos y el centro de notificaciones. Solo las actividades activas se publican y notifican.</p>
                    </div>
                </div>

                <div class="admin-modal-section activities-modal__section">
                    <h6 class="activities-modal__section-title">Contenido principal</h6>
                    <div class="row g-3">
                        <div class="col-md-8 form-group form-required">
                            <label class="form-label">Titulo</label>
                            <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Taller de alfabetizacion informacional">
                        </div>
                        <div class="col-md-4 form-group form-required">
                            <label class="form-label">Categoria</label>
                            <select id="actividad_categoria_id" name="actividad_categoria_id" class="form-select">
                                <option value="">Seleccione</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Resumen</label>
                            <textarea id="resumen" name="resumen" rows="2" class="form-control" placeholder="Descripcion corta para cards y campana de notificaciones."></textarea>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Contenido</label>
                            <textarea id="contenido" name="contenido" rows="5" class="form-control" placeholder="Detalle ampliado de la actividad."></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-modal-section activities-modal__section">
                    <h6 class="activities-modal__section-title">Programacion y contexto</h6>
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
                            <input type="text" id="referencia" name="referencia" class="form-control" placeholder="Ponente, enlace o referencia adicional">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Destacada</label>
                            <select id="destacado" name="destacado" class="form-select">
                                <option value="0">No</option>
                                <option value="1">Si</option>
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
                        Clasificacion editorial
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Categoria de actividad</h5>
                    <p class="activities-modal__copy mb-0">Crea categorias para organizar mejor las actividades publicadas y facilitar la busqueda en eventos y notificaciones.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body activities-modal__body">
                <div class="activities-modal__intro">
                    <div class="activities-modal__intro-icon">
                        <i class="bi bi-collection-fill"></i>
                    </div>
                    <div>
                        <strong>Taxonomia de actividades</strong>
                        <p class="mb-0">Usa categorias claras y breves para que el personal identifique rapido el tipo de publicacion y los usuarios encuentren mejor cada actividad.</p>
                    </div>
                </div>

                <div class="admin-modal-section activities-modal__section">
                    <h6 class="activities-modal__section-title">Datos de la categoria</h6>
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
                            <label class="form-label">Descripcion</label>
                            <textarea id="descripcion" name="descripcion" rows="3" class="form-control" placeholder="Describe el tipo de actividades que agrupara esta categoria."></textarea>
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
                <button type="submit" class="btn btn-success px-4">Guardar categoria</button>
            </div>
        </form>
    </div>
</div>
@endsection
