@extends('layouts.admin')

@section('page-title', 'Prestamos activos')

@section('css')
    <link href="{{ asset('css/prestamo/prestamos.css') }}?v={{ filemtime(public_path('css/prestamo/prestamos.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/prestamo/prestamo.js') }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Prestamos</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Registro</span>
    </div>

    <div class="loan-register">
        <section class="loan-register__hero">
            <div>
                <span class="loan-register__eyebrow"><i class="bi bi-journal-check"></i> Circulacion activa</span>
                <h2>Control de prestamos</h2>
                <p>Supervisa los ejemplares entregados, revisa vencimientos y registra devoluciones con una vista mas clara para el trabajo diario.</p>
            </div>
            <div class="loan-register__hero-note">
                <span class="loan-register__hero-note-label">Flujo rapido</span>
                <strong>Filtra, revisa y devuelve desde una sola tabla</strong>
            </div>
        </section>

        <section class="loan-register__stats">
            <article class="loan-register__stat">
                <span>Vista principal</span>
                <strong>Prestamos activos</strong>
            </article>
            <article class="loan-register__stat">
                <span>Accion recomendada</span>
                <strong>Registrar devoluciones a tiempo</strong>
            </article>
            <article class="loan-register__stat">
                <span>Seguimiento</span>
                <strong>Control por libro, lector y plazo</strong>
            </article>
        </section>

        <section class="admin-panel loan-register__panel">
            <div class="admin-panel__header loan-register__panel-head">
                <div>
                    <h2 class="admin-panel__title">Prestamos en curso</h2>
                    <p class="admin-panel__copy">La tabla permite buscar rapidamente por fecha, lector, libro o estado y abrir el flujo de devolucion sin salir del modulo.</p>
                </div>
            </div>

            <div class="loan-register__table-wrap admin-table-shell table-responsive">
                <table id="tabla-prestamos" class="table table-hover table-bordered align-middle loan-register__table datatable w-100">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Plazo</th>
                            <th>Libro</th>
                            <th>Ejemplar</th>
                            <th>Lector</th>
                            <th>Prestamo</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalPrestamo" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered loan-register__modal-dialog">
    <div class="modal-content shadow-lg border-0 loan-register__modal">
      <form id="formEntrega" class="loan-register__modal-form">
        <div class="modal-header loan-register__modal-header">
          <div>
            <span class="loan-register__modal-kicker">Actualizacion de circulacion</span>
            <h5 class="modal-title">Registrar devolucion</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body loan-register__modal-body">
          <input type="hidden" id="prestamo_id">

          <div class="loan-register__summary">
            <div class="loan-register__summary-item">
              <span>Libro</span>
              <strong id="libro_nombre"></strong>
            </div>

            <div class="loan-register__summary-item">
              <span>Ejemplar</span>
              <strong id="ejemplar_codigo"></strong>
            </div>
          </div>

          <div id="mensajeRetraso" class="loan-register__delay-note d-none"></div>

          <div id="alertaRetraso" class="alert loan-register__alert d-none">
            Este prestamo esta fuera de plazo.
          </div>

          <div id="sancionPreview" class="loan-register__sanction loan-register__sanction--neutral">
            <div class="loan-register__sanction-title">Sin sancion configurada</div>
            <div class="loan-register__sanction-copy">La devolucion se revisara con la configuracion actual antes de guardar.</div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Estado del libro</label>
              <select id="estado_libro" class="form-select">
                <option value="1">Buen estado</option>
                <option value="2">Deterioro</option>
                <option value="3">Perdida</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Dias de retraso</label>
              <input type="number" id="dias_retraso" class="form-control" min="0" value="0">
              <small class="text-muted d-block mt-2">Ajusta este valor solo si necesitas corregir el calculo automatico. La previsualizacion de sancion se actualiza con este cambio.</small>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">Observaciones</label>
            <textarea id="observaciones" class="form-control" rows="3" placeholder="Detalle adicional..."></textarea>
          </div>
        </div>

        <div class="modal-footer loan-register__modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success px-4">Guardar devolucion</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
