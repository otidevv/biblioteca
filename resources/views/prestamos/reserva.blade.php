@extends('layouts.admin')

@section('page-title', 'Reservas activas')

@section('css')
    <link href="{{ asset('css/prestamo/reserva.css') }}?v={{ filemtime(public_path('css/prestamo/reserva.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/prestamo/reserva.js') }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Prestamos</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Reservas</span>
    </div>

    <div class="reservation-register">
        <section class="reservation-register__hero">
            <div>
                <span class="reservation-register__eyebrow"><i class="bi bi-bookmark-check"></i> Gestion de reservas</span>
                <h2>Reservas activas</h2>
                <p>Visualiza solicitudes pendientes, controla el tiempo de recogida y convierte la reserva en prestamo desde una sola pantalla.</p>
            </div>
            <div class="reservation-register__hero-note">
                <span class="reservation-register__hero-note-label">Operacion sugerida</span>
                <strong>Entrega rapida y control del plazo de recojo</strong>
            </div>
        </section>

        <section class="reservation-register__stats">
            <article class="reservation-register__stat">
                <span>Vista principal</span>
                <strong>Reservas por atender</strong>
            </article>
            <article class="reservation-register__stat">
                <span>Gestion diaria</span>
                <strong>Seguimiento por lector y ejemplar</strong>
            </article>
            <article class="reservation-register__stat">
                <span>Accion central</span>
                <strong>Convertir reserva en prestamo</strong>
            </article>
        </section>

        <section class="admin-panel reservation-register__panel">
            <div class="admin-panel__header reservation-register__panel-head">
                <div>
                    <h2 class="admin-panel__title">Reservas en espera</h2>
                    <p class="admin-panel__copy">La tabla concentra las reservas activas y resalta el tiempo restante para entregar el material al lector.</p>
                </div>
            </div>

            <div class="reservation-register__table-wrap admin-table-shell table-responsive">
                <table id="tabla-reservas" class="table table-hover table-bordered align-middle reservation-register__table datatable w-100">
                    <thead>
                        <tr>
                            <th>Reservado</th>
                            <th>Tiempo restante</th>
                            <th>Libro</th>
                            <th>Ejemplar</th>
                            <th>Lector</th>
                            <th>Estado</th>
                            <th>Tipo</th>
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
<div class="modal fade" id="modalEntrega" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered reservation-register__modal-dialog">
    <div class="modal-content reservation-register__modal">
      <form id="formEntrega">

        <div class="modal-header reservation-register__modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="rsv-modal-hdr-icon">
              <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <div>
              <span class="reservation-register__modal-kicker">
                <i class="bi bi-arrow-left-right me-1"></i> Circulacion
              </span>
              <h5 class="modal-title mb-0">Registrar prestamo</h5>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body reservation-register__modal-body">
          <input type="hidden" id="reserva_id">

          {{-- Contexto de la reserva --}}
          <div class="rsv-modal-context">
            {{-- Libro ocupa toda la fila superior --}}
            <div class="rsv-modal-ctx-item rsv-modal-ctx-item--full">
              <div class="rsv-modal-ctx-icon rsv-modal-ctx-icon--book">
                <i class="bi bi-book-half"></i>
              </div>
              <div class="rsv-modal-ctx-body">
                <span class="rsv-modal-ctx-label">Libro</span>
                <strong id="rsv-ctx-libro" class="rsv-modal-ctx-value">—</strong>

                {{-- Badges: código, ISBN, edición --}}
                <div class="rsv-book-meta">
                  <span id="rsv-ctx-codigo"  class="rsv-book-meta-badge rsv-book-meta-badge--code d-none"></span>
                  <span id="rsv-ctx-isbn"    class="rsv-book-meta-badge rsv-book-meta-badge--isbn d-none"></span>
                  <span id="rsv-ctx-edicion" class="rsv-book-meta-badge rsv-book-meta-badge--edition d-none"></span>
                </div>

                {{-- Autores --}}
                <div id="rsv-ctx-autores" class="rsv-book-authors d-none"></div>
              </div>
            </div>
            {{-- Lector y Tipo en la fila inferior --}}
            <div class="rsv-modal-ctx-item">
              <div class="rsv-modal-ctx-icon rsv-modal-ctx-icon--reader">
                <i class="bi bi-person-fill"></i>
              </div>
              <div class="rsv-modal-ctx-body">
                <span class="rsv-modal-ctx-label">Lector</span>
                <strong id="rsv-ctx-lector" class="rsv-modal-ctx-value">—</strong>
              </div>
            </div>
            <div class="rsv-modal-ctx-item">
              <div class="rsv-modal-ctx-icon rsv-modal-ctx-icon--tipo">
                <i class="bi bi-tag-fill"></i>
              </div>
              <div class="rsv-modal-ctx-body">
                <span class="rsv-modal-ctx-label">Tipo de prestamo</span>
                <span id="rsv-ctx-tipo" class="rsv-tipo-pill rsv-tipo-pill--sala">—</span>
              </div>
            </div>
          </div>

          {{-- Formulario en 2 columnas --}}
          <div class="rsv-modal-form-section">
            <div class="rsv-modal-form-grid">
              <div class="rsv-modal-form-row">
                <div class="rsv-modal-form-icon">
                  <i class="bi bi-calendar-week"></i>
                </div>
                <div class="flex-grow-1">
                  <label class="form-label fw-semibold mb-1">Dias de prestamo</label>
                  <input type="number" id="dias" class="form-control" min="1" placeholder="Ej. 7" required>
                  <small class="text-muted d-block mt-1">Calcula la fecha limite automaticamente.</small>
                </div>
              </div>
              <div class="rsv-modal-form-row">
                <div class="rsv-modal-form-icon">
                  <i class="bi bi-chat-left-text"></i>
                </div>
                <div class="flex-grow-1">
                  <label class="form-label fw-semibold mb-1">
                    Observaciones <span class="fw-normal text-muted">(opcional)</span>
                  </label>
                  <textarea id="observaciones" class="form-control" rows="3"
                    placeholder="Indicaciones relevantes..."></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer reservation-register__modal-footer justify-content-between">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success px-4">
            <i class="bi bi-check-lg me-1"></i> Confirmar prestamo
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection
