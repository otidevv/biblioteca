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
        <table id="tabla-reservas" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
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
<div class="modal fade" id="modalEntrega" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEntrega">
        <div class="modal-header">
          <h5 class="modal-title">Registrar Préstamo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" id="reserva_id">

          <div class="mb-3">
            <label>Días de préstamo</label>
            <input type="number" id="dias" class="form-control" min="1" required>
          </div>

          <div class="mb-3">
            <label>Observaciones</label>
            <textarea id="observaciones" class="form-control"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection