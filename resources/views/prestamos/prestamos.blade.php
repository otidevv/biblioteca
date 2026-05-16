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
                <button class="btn btn-primary" id="btnNuevoPrestamoDirecto" type="button">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo préstamo
                </button>
            </div>

            <div class="loan-register__table-wrap admin-table-shell table-responsive" role="region" aria-label="Tabla de préstamos activos">
                <div class="loan-register__scroll-hint" aria-hidden="true">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Desliza para ver más columnas</span>
                </div>
                <table id="tabla-prestamos" class="table table-hover table-bordered align-middle loan-register__table datatable w-100">
                    <thead>
                        <tr>
                            <th>Fechas</th>
                            <th>Libro</th>
                            <th>Ejemplar</th>
                            <th>Lector</th>
                            <th>Estado</th>
                            <th>Vigencia</th>
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

{{-- Modal: Nuevo préstamo directo --}}
<div class="modal fade" id="modalPrestamoDirecto" tabindex="-1" aria-labelledby="pdModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">

      <div class="modal-header pd-modal-header" style="background:var(--admin-accent,#2563eb);color:#fff;">
        <div>
          <span style="font-size:.75rem;opacity:.8;display:block;letter-spacing:.05em;">CIRCULACIÓN DIRECTA</span>
          <h5 class="modal-title mb-0" id="pdModalTitle" style="color:inherit;">Nuevo préstamo</h5>
        </div>
        <button type="button" class="pd-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      {{-- Indicador de pasos --}}
      <div class="pd-steps-bar" id="pd-steps-bar">
        <div class="pd-step-tab pd-step--active" data-step="1">
          <span class="pd-step-bubble">1</span>
          <span class="pd-step-info">
            <span class="pd-step-num">Paso 1</span>
            <strong class="pd-step-label">Lector</strong>
          </span>
        </div>
        <span class="pd-step-connector" aria-hidden="true"></span>
        <div class="pd-step-tab" data-step="2">
          <span class="pd-step-bubble">2</span>
          <span class="pd-step-info">
            <span class="pd-step-num">Paso 2</span>
            <strong class="pd-step-label">Ejemplar</strong>
          </span>
        </div>
        <span class="pd-step-connector" aria-hidden="true"></span>
        <div class="pd-step-tab" data-step="3">
          <span class="pd-step-bubble">3</span>
          <span class="pd-step-info">
            <span class="pd-step-num">Paso 3</span>
            <strong class="pd-step-label">Detalles</strong>
          </span>
        </div>
      </div>

      {{-- Paso 1: buscar lector --}}
      <div class="modal-body pd-step-body" id="pd-body-1">
        <div class="pd-body-intro">
          <i class="bi bi-person-lines-fill"></i>
          <span>Ingresa el nombre, DNI o correo del estudiante para buscarlo en el sistema.</span>
        </div>
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" id="pd-lector-q" class="form-control" placeholder="Nombre, DNI o correo...">
          <button class="btn btn-outline-secondary" id="pd-lector-buscar" type="button">Buscar</button>
        </div>
        <div id="pd-lector-results"></div>
        <div id="pd-lector-sel" class="d-none mt-2 pd-sel-card">
          <div class="d-flex align-items-center gap-3">
            <div class="pd-sel-card__avatar flex-shrink-0">
              <i class="bi bi-person-check-fill fs-5"></i>
            </div>
            <div class="flex-grow-1 min-width-0">
              <strong class="pd-sel-card__name" id="pd-lector-sel-nombre"></strong>
              <small class="pd-sel-card__sub d-block" id="pd-lector-sel-info"></small>
            </div>
            <button type="button" class="pd-sel-card__btn ms-auto flex-shrink-0" id="pd-lector-cambiar">
              <i class="bi bi-arrow-repeat"></i> Cambiar
            </button>
          </div>
        </div>
      </div>

      {{-- Paso 2: buscar ejemplar --}}
      <div class="modal-body pd-step-body d-none" id="pd-body-2">
        <div class="pd-body-intro">
          <i class="bi bi-book-fill"></i>
          <span>Escribe el título del libro o el código del ejemplar para ver los disponibles.</span>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-5">
            <label class="form-label fw-semibold small mb-1">Filtrar por biblioteca</label>
            <select id="pd-filtro-bib" class="form-select form-select-sm">
              <option value="">Todas las bibliotecas</option>
            </select>
          </div>
          <div class="col-md-7">
            <label class="form-label fw-semibold small mb-1">Buscar libro o código</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" id="pd-libro-q" class="form-control" placeholder="Título del libro o código...">
              <button class="btn btn-primary" id="pd-libro-buscar" type="button">Buscar</button>
            </div>
          </div>
        </div>

        <div id="pd-libro-results" style="max-height:340px;overflow-y:auto;"></div>

        <div id="pd-ejemplar-sel" class="d-none mt-3 pd-sel-card">
          <div class="d-flex align-items-center gap-3">
            <div class="pd-sel-card__avatar pd-sel-card__avatar--book flex-shrink-0">
              <i class="bi bi-book-half fs-5"></i>
            </div>
            <div class="flex-grow-1 min-width-0">
              <strong class="pd-sel-card__name d-block" id="pd-ejemplar-sel-libro"></strong>
              <div class="d-flex flex-wrap gap-2 mt-1">
                <span class="pd-sel-card__tag" id="pd-ejemplar-sel-bib"></span>
                <code class="pd-sel-card__sub" id="pd-ejemplar-sel-codigo"></code>
              </div>
            </div>
            <button type="button" class="pd-sel-card__btn flex-shrink-0" id="pd-ejemplar-cambiar">
              <i class="bi bi-arrow-repeat"></i> Cambiar
            </button>
          </div>
        </div>
      </div>

      {{-- Paso 3: detalles del préstamo --}}
      <div class="modal-body pd-step-body d-none" id="pd-body-3">
        <div class="p-3 rounded mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
          <div class="row g-2">
            <div class="col-sm-6">
              <small class="text-muted d-block">Lector</small>
              <strong id="pd-ctx-lector">—</strong>
            </div>
            <div class="col-sm-6">
              <small class="text-muted d-block">Libro / Ejemplar</small>
              <strong id="pd-ctx-libro">—</strong>
              <small class="text-muted d-block" id="pd-ctx-codigo"></small>
            </div>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Días de préstamo</label>
            <input type="number" id="pd-dias" class="form-control" min="1" placeholder="Ej. 7" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Tipo de préstamo</label>
            <select id="pd-tipo" class="form-select">
              <option value="0">En sala</option>
              <option value="1">A casa</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Fecha límite estimada</label>
            <input type="text" id="pd-fecha-est" class="form-control" readonly style="background:#f1f5f9;">
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label fw-semibold">Observaciones <span class="fw-normal text-muted">(opcional)</span></label>
          <textarea id="pd-obs" class="form-control" rows="2" placeholder="Indicaciones relevantes..."></textarea>
        </div>
      </div>

      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-secondary" id="pd-btn-prev" disabled>
          <i class="bi bi-arrow-left me-1"></i> Anterior
        </button>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="pd-btn-next">
            Siguiente <i class="bi bi-arrow-right ms-1"></i>
          </button>
          <button type="button" class="btn btn-success d-none" id="pd-btn-confirm">
            <i class="bi bi-check-lg me-1"></i> Confirmar préstamo
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

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
