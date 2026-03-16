@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/inventario/compras.js') }}"></script>
@endsection
@section('content')
<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Compras</h1>
        <a href="{{ url('inventario/compra_nuevo') }}" id="btnNuevo"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            ➕ Agregar Compra
        </a>
    </div>

    {{-- Select para tipos de usuario --}}

    <div class="overflow-x-auto">
        <table id="tabla-compras" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
            <thead class="bg-gray-100">
                <tr>
                    <th>Nro SIAF</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection
@section('modal')
    <div class="modal fade" id="modalVerCompra">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Compra</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>SIAF</label>
                            <input class="form-control" id="ver_siaf" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Fecha</label>
                            <input class="form-control" id="ver_fecha" readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Proveedor</label>
                            <input class="form-control" id="ver_proveedor" readonly>
                        </div>
                        <div class="col-md-2">
                            <label>Total</label>
                            <input class="form-control" id="ver_total" readonly>
                        </div>
                    </div>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Libro</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th>Ejemplares</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDetalleCompra"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection