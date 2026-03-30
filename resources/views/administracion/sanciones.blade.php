@extends('layouts.admin')

@section('page-title', 'Gestion de sanciones')

@section('css')
    <link href="{{ asset('css/administracion/sanciones.css') }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/sanciones.js') }}?v={{ filemtime(public_path('js/administracion/sanciones.js')) }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Sanciones</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Tipos de sancion</h2>
                <p class="admin-panel__copy">Administra reglas base de sancion para tardanza, deterioro, no recojo y otros eventos del sistema.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevo" class="admin-btn admin-btn--primary">Agregar tipo de sancion</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-sanciones" class="table table-hover table-bordered align-middle datatable w-100">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Origen</th>
                        <th>Condicion</th>
                        <th>Duracion</th>
                        <th>Monto</th>
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
<div class="modal fade" id="modalSancionTipo" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form id="formSancionTipo" class="modal-content shadow-sm sanction-modal">
            <input type="hidden" id="id" name="id">
            <div class="modal-header sanction-modal__header">
                <div>
                    <span class="sanction-modal__eyebrow">
                        <i class="bi bi-exclamation-diamond-fill"></i>
                        Politica disciplinaria
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de tipo de sancion</h5>
                    <p class="sanction-modal__copy mb-0">Configura la ficha base de una sancion para automatizar tardanza, deterioro, no recojo y otros eventos del sistema.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body sanction-modal__body">
                <div class="sanction-modal__intro">
                    <div class="sanction-modal__intro-icon">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    <div>
                        <strong>Configuracion base</strong>
                        <p class="mb-0">Define el comportamiento general de la sancion y luego complementa la automatizacion con reglas mas especificas.</p>
                    </div>
                </div>

                <div class="admin-modal-section sanction-modal__section">
                    <h6 class="sanction-modal__section-title">Identidad de la sancion</h6>
                    <p class="sanction-modal__section-copy">Usa un codigo estable y un nombre claro para que el equipo identifique de inmediato cuando debe aplicarse.</p>
                    <div class="row g-3">
                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">Codigo</label>
                                <input type="text" id="codigo" name="codigo" class="form-control mayuscula" placeholder="PRESTAMO_TARDANZA">
                            </div>
                            <div class="col-md-8 form-group form-required">
                                <label class="form-label">Nombre</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Prestamo con tardanza">
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="form-label">Descripcion</label>
                                <textarea id="descripcion" name="descripcion" rows="3" class="form-control" placeholder="Describe cuando y como debe aplicarse esta sancion."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="admin-modal-section sanction-modal__section sanction-modal__section--rules">
                        <h6 class="sanction-modal__section-title">Comportamiento y efecto</h6>
                        <p class="sanction-modal__section-copy">Ajusta origen, condicion, duracion, monto y si debe restringir prestamos o generar cobro.</p>
                        <div class="row g-3">
                            <div class="col-md-3 form-group">
                                <label class="form-label">Origen del evento</label>
                                <select id="origen_evento" name="origen_evento" class="form-select">
                                    <option value="">Seleccione</option>
                                    <option value="prestamo">Prestamo</option>
                                    <option value="reservacion">Reservacion</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="form-label">Condicion</label>
                                <select id="condicion" name="condicion" class="form-select">
                                    <option value="">Seleccione</option>
                                    <option value="tardanza">Tardanza</option>
                                    <option value="deterioro">Deterioro</option>
                                    <option value="perdida">Perdida</option>
                                    <option value="no_recojo">No recojo</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="form-label">Dias de duracion</label>
                                <input type="number" id="dias_duracion" name="dias_duracion" class="form-control" min="0">
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="form-label">Monto</label>
                                <input type="number" step="0.01" id="monto" name="monto" class="form-control" min="0">
                            </div>
                            <div class="col-md-2 form-group form-required">
                                <label class="form-label">Estado</label>
                                <select id="estado" name="estado" class="form-select">
                                    <option value="1">Activa</option>
                                    <option value="0">Inactiva</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label">Requiere pago</label>
                                <select id="requiere_pago" name="requiere_pago" class="form-select">
                                    <option value="0">No</option>
                                    <option value="1">Si</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label">Bloquea prestamos</label>
                                <select id="bloquea_prestamos" name="bloquea_prestamos" class="form-select">
                                    <option value="1">Si</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label">Aplicacion automatica</label>
                                <select id="aplica_automaticamente" name="aplica_automaticamente" class="form-select">
                                    <option value="1">Si</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="modal-footer sanction-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success px-4">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalReglasSancion" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-sm sanction-modal sanction-modal--rules-panel">
            <div class="modal-header sanction-modal__header">
                <div>
                    <span class="sanction-modal__eyebrow">
                        <i class="bi bi-sliders2"></i>
                        Automatizacion avanzada
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Reglas automaticas de sancion</h5>
                    <p class="sanction-modal__copy mb-0" id="reglasSancionTitulo">Configura cuando debe aplicarse esta sancion.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body sanction-modal__body">
                <div class="admin-modal-section sanction-modal__section">
                    <div class="admin-panel__header mb-3">
                        <div>
                            <h6 class="admin-card__title mb-1">Reglas registradas</h6>
                            <p class="admin-panel__copy mb-0">Cada regla define evento, rangos y duracion aplicada.</p>
                        </div>
                    </div>

                    <div class="table-responsive admin-table-shell">
                        <table class="table table-hover table-bordered align-middle w-100" id="tabla-reglas-sancion">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Rango dias</th>
                                    <th>Cantidad</th>
                                    <th>Duracion</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaReglasSancionBody"></tbody>
                        </table>
                    </div>
                </div>

                <form id="formReglaSancion" class="mt-3">
                    <input type="hidden" id="regla_id" name="id">
                    <input type="hidden" id="regla_tipo_sancion_id" name="tipo_sancion_id">

                    <div class="admin-modal-section sanction-modal__section sanction-modal__section--rules">
                        <div class="admin-panel__header mb-3">
                            <div>
                                <h6 class="admin-card__title mb-1">Registrar o actualizar regla</h6>
                                <p class="admin-panel__copy mb-0">Puedes definir umbrales por dias, cantidades y si requiere aprobacion.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4 form-group form-required">
                                <label class="form-label">Evento</label>
                                <select id="evento" name="evento" class="form-select">
                                    <option value="">Seleccione</option>
                                    <option value="prestamo_tardio">Prestamo tardio</option>
                                    <option value="devolucion_deterioro">Devolucion con deterioro</option>
                                    <option value="reserva_no_recogida">Reserva no recogida</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="form-label">Dias desde</label>
                                <input type="number" id="dias_desde" name="dias_desde" class="form-control" min="0">
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="form-label">Dias hasta</label>
                                <input type="number" id="dias_hasta" name="dias_hasta" class="form-control" min="0">
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="form-label">Cantidad minima</label>
                                <input type="number" id="cantidad_minima" name="cantidad_minima" class="form-control" min="0">
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="form-label">Cantidad maxima</label>
                                <input type="number" id="cantidad_maxima" name="cantidad_maxima" class="form-control" min="0">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="form-label">Duracion en dias</label>
                                <input type="number" id="duracion_dias" name="duracion_dias" class="form-control" min="0">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="form-label">Monto</label>
                                <input type="number" step="0.01" id="regla_monto" name="monto" class="form-control" min="0">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="form-label">Requiere aprobacion</label>
                                <select id="requiere_aprobacion" name="requiere_aprobacion" class="form-select">
                                    <option value="0">No</option>
                                    <option value="1">Si</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group form-required">
                                <label class="form-label">Estado</label>
                                <select id="regla_estado" name="estado" class="form-select">
                                    <option value="1">Activa</option>
                                    <option value="0">Inactiva</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <button type="button" class="btn btn-outline-secondary" id="btnNuevaRegla">Nueva regla</button>
                            <button type="submit" class="btn btn-success px-4">Guardar regla</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
