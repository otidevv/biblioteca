@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
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
                    <label class="block text-sm font-semibold text-gray-700">Fecha de compra *</label>
                    <input type="date" name="fecha_compra"
                        class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                        required>
                </div>

                {{-- Proveedor --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700">Proveedor *</label>
                    <select name="proveedor_id"
                        class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
                        required>
                        <option value="">Seleccione un proveedor</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}">
                                {{ $proveedor->nombre }}
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
                        class="w-full mt-1 border-gray-300 rounded-lg bg-gray-100"
                        readonly required>
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
                    <button type="button" id="btnAgregarDetalle"
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

                <button type="submit"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                    Guardar Compra
                </button>
            </div>
        </form>
    </div>
</div>
 @endsection
 @section('modals')
    {{-- ================= MODAL AGREGAR LIBRO ================= --}}
    
<div class="modal fade" id="modalLibro" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div id="div_form">
            <form id="formLibro">
                <input type="hidden" id="id" name="id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">Título *</label>
                            <input type="text" id="modal_titulo"
                                class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700">Autor *</label>
                            <input type="text" id="modal_autor"
                                class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Cantidad *</label>
                            <input type="number" id="modal_cantidad" value="1" min="1"
                                class="w-full mt-1 border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Precio *</label>
                            <input type="number" step="0.01" id="modal_precio"
                                class="w-full mt-1 border-gray-300 rounded-lg">
                        </div>
                    </div>
            </form>

        </div>
    </div>
</div>
<div id="modalLibro"
    class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">

    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            ➕ Agregar Libro
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700">Título *</label>
                <input type="text" id="modal_titulo"
                    class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700">Autor *</label>
                <input type="text" id="modal_autor"
                    class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Cantidad *</label>
                <input type="number" id="modal_cantidad" value="1" min="1"
                    class="w-full mt-1 border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Precio *</label>
                <input type="number" step="0.01" id="modal_precio"
                    class="w-full mt-1 border-gray-300 rounded-lg">
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button type="button" id="btnCancelarModal"
                class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                Cancelar
            </button>

            <button type="button" id="btnGuardarLibro"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                Agregar
            </button>
        </div>
    </div>
</div>
@endsection