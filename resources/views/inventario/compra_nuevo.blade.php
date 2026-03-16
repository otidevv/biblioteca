@extends('layouts.admin')
@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
@endsection
@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/inventario/compra_nueva.js') }}"></script>
@endsection
@section('content')
    <nav class="mb-4 text-sm text-gray-600">
        <ol class="flex items-center space-x-2">
            <li class="font-semibold text-gray-800">
                Administración
            </li>
            <li class="text-gray-400">›</li>
            <li class="text-emerald-700 font-semibold">
                Compras
            </li>
        </ol>
    </nav>

    <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="overflow-x-auto">
            <form class="space-y-6">

                {{-- ================= DATOS DE LA COMPRA ================= --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    {{-- Número SIAF --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Número SIAF</label>
                        <input type="text" name="numero_siaf"
                            class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    {{-- Fecha --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Fecha de compra</label>
                        <input type="date" name="fecha_compra" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    {{-- Proveedor --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700">Proveedor</label>
                        <select name="proveedor_id" class="form-select select2">
                            <option value="">Seleccione un proveedor</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}">
                                    {{ $proveedor->razon_social . ($proveedor->responsable ? ' : '.$proveedor->responsable : '') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Monto total --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Monto total</label>
                        <input type="number" step="0.01" name="monto_total"
                            class="w-full mt-1 border-gray-300 rounded-lg bg-gray-100" readonly>
                    </div>

                    {{-- Observaciones --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Observaciones</label>
                        <textarea name="observaciones" rows="2"
                            class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Notas adicionales de la compra"></textarea>
                    </div>
                </div>

                {{-- ================= DETALLE DE LIBROS ================= --}}
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-bold text-gray-800">Detalle de Libros</h2>
                        <button type="button" id="btnNuevoLibro"
                            class="px-3 py-1 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                            ➕ Agregar Libro
                        </button>
                    </div>

                    <table class="min-w-full border border-gray-300 rounded-lg" id="tablaDetalles">
                        <thead class="bg-gray-100 text-sm text-gray-700">
                            <tr>
                                <th class="px-3 py-2 border">Título</th>
                                <th class="px-3 py-2 border">Autor</th>
                                <th class="px-3 py-2 border">Cantidad</th>
                                <th class="px-3 py-2 border">Precio</th>
                                <th class="px-3 py-2 border">Subtotal</th>
                                <th class="px-3 py-2 border">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Filas agregadas por JS --}}
                        </tbody>
                    </table>
                </div>

                {{-- ================= BOTONES ================= --}}
                <div class="flex justify-end gap-3 pt-4">
                    <a href="#"
                        class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                        Cancelar
                    </a>

                    <button type="submit" id="btnGuardarCompra" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                        Guardar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>
 @endsection
 @section('modal')
    {{-- ================= MODAL AGREGAR LIBRO ================= --}}
    
    <div class="modal fade" id="modalLibro" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div id="div_form">
                <form id="formLibro">
                    <input type="hidden" id="id" name="id">
                    <div class="modal-content shadow-sm">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title fw-semibold">Agregar libro</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    <div class="modal-body">
                            <div class="row">
                                <!-- ===================== -->
                                <!-- DETALLE COMPRA -->
                                <!-- ===================== -->
                                <div class="col-md-6">
                                    <h6 class="text-primary">Datos detalle</h6>
                                    <div class="col-md-12 form-group form-required validar-div">
                                        <label class="form-label">Título</label>
                                        <select id="libros" class="form-select"></select>
                                    </div>
                                    <div class="col-md-6 form-group form-required validar-div">
                                        <label class="form-label">Cantidad</label>
                                        <input type="number" id="modal_cantidad" value="1" min="1" class="form-control">
                                    </div>
                                    <div class="col-md-6 form-group form-required validar-div">
                                        <label class="form-label">Precio</label>
                                        <input type="number" step="0.01" id="modal_precio" class="form-control">
                                    </div>
                                </div>
                                <!-- ===================== -->
                                <!-- DATOS DEL LIBRO -->
                                <!-- ===================== -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-2">DATOS DEL LIBRO</h6>
                                    <div class="row g-2">
                                        <div class="col-md-12 text-center">
                                            <img id="preview_imagen" class="img-fluid rounded shadow mb-2" style="max-height:150px; display:none;">
                                            <div id="btnRegistrarLibro" style="display:none;">
                                                <a id="linkRegistrarLibro" class="btn btn-warning btn-sm">
                                                    Registrar libro
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Título</label>
                                            <div id="lbl_titulo" class="form-control bg-light"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Autor</label>
                                            <div id="lbl_autor" class="form-control bg-light"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Editorial</label>
                                            <div id="lbl_editorial" class="form-control bg-light"></div>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button class="btn btn-success px-4" type="submit">
                                Agregar
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection