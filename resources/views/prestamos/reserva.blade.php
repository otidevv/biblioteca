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
                            <th>Fecha</th>
                            <th>Plazo</th>
                            <th>Libro</th>
                            <th>Ejemplar</th>
                            <th>Lector</th>
                            <th>Estado</th>
                            <th>Prestamo</th>
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
  <div class="modal-dialog modal-dialog-centered reservation-register__modal-dialog">
    <div class="modal-content reservation-register__modal">
      <form id="formEntrega">
        <div class="modal-header reservation-register__modal-header">
          <div>
            <span class="reservation-register__modal-kicker">Circulacion</span>
            <h5 class="modal-title">Registrar prestamo</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body reservation-register__modal-body">
          <input type="hidden" id="reserva_id">

          <div class="reservation-register__summary">
            <div class="reservation-register__summary-item">
              <span>Accion</span>
              <strong>Convertir reserva en prestamo activo</strong>
            </div>
            <div class="reservation-register__summary-item">
              <span>Recomendacion</span>
              <strong>Define el plazo segun el tipo de prestamo</strong>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Dias de prestamo</label>
            <input type="number" id="dias" class="form-control" min="1" required>
            <small class="text-muted d-block mt-2">Indica cuantos dias durara el prestamo antes de calcular la fecha limite.</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea id="observaciones" class="form-control" rows="3" placeholder="Anota alguna indicacion relevante para la entrega..."></textarea>
          </div>
        </div>

        <div class="modal-footer reservation-register__modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success px-4">Guardar prestamo</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
