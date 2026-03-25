@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/prestamo/prestamo.js') }}"></script>
@endsection
@section('content')
<nav class="mb-4 text-sm text-gray-600">
    <ol class="flex items-center space-x-2">
        <li class="font-semibold text-gray-800">
            Administración
        </li>
        <li class="text-gray-400">›</li>
        <li class="font-semibold text-gray-800">
            Prestamos
        </li>
    </ol>
</nav>

<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="overflow-x-auto">
        <table id="tabla-prestamos" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
            <thead class="bg-gray-100">
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
</div>


<!-- MODAL -->
@endsection
@section('modal')
<div class="modal fade" id="modalPrestamo" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">

      <form id="formEntrega">

        <!-- HEADER -->
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            <i class="fas fa-book-reader me-2"></i> Registrar devolución
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <!-- BODY -->
        <div class="modal-body">

          <input type="hidden" id="prestamo_id">

          <!-- INFO LIBRO -->
          <div class="p-3 mb-3 rounded bg-light border">
            
            <div class="mb-2">
              <small class="text-muted">Libro</small><br>
              <strong id="libro_nombre" class="text-primary"></strong>
            </div>

            <div>
              <small class="text-muted">Ejemplar</small><br>
              <span id="ejemplar_codigo" class="badge bg-secondary" style="color:white"></span>
            </div>

          </div>

          <!-- ALERTA DINÁMICA -->
          <div id="alertaRetraso" class="alert alert-danger d-none">
            <i class="fas fa-exclamation-triangle"></i>
            Este préstamo está fuera de plazo
          </div>

          <!-- FORMULARIO -->
          <div class="row">

            <!-- ESTADO -->
            <div class="col-md-6 mb-3">
              <label class="form-label">
                <i class="fas fa-info-circle"></i> Estado del libro
              </label>
              <select id="estado_libro" class="form-select">
                <option value="1">✅ Buen estado</option>
                <option value="2">⚠️ Deterioro</option>
                <option value="3">❌ Pérdida</option>
              </select>
            </div>

            <!-- DIAS -->
            <div class="col-md-6 mb-3">
              <label class="form-label">
                <i class="fas fa-clock"></i> Días de retraso
              </label>
              <input type="number" id="dias_retraso" class="form-control" min="0" value="0">
            </div>

          </div>

          <!-- OBSERVACIONES -->
          <div class="mb-3">
            <label class="form-label">
              <i class="fas fa-comment-dots"></i> Observaciones
            </label>
            <textarea id="observaciones" class="form-control" rows="3"
              placeholder="Detalle adicional..."></textarea>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Guardar
          </button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancelar
          </button>
        </div>

      </form>
      <div id="alertaRetraso" class="alert alert-danger d-none">
          <i class="fas fa-exclamation-triangle"></i>
          <strong>Atención:</strong>
          Este préstamo tiene <span id="diasTexto"></span> día(s) de retraso.
          <br>Se aplicará penalización correspondiente.
      </div>
    </div>
  </div>
</div>
@endsection