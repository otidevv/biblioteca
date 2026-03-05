@extends('layouts.admin')

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/editorial.js') }}"></script>
@endsection
@section('content')
<nav class="mb-4 text-sm text-gray-600">
    <ol class="flex items-center space-x-2">
        <li class="font-semibold text-gray-800">
            Administración
        </li>
        <li class="text-gray-400">›</li>
        <li class="text-emerald-700 font-semibold">
            Aditoriales
        </li>
    </ol>
</nav>

<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de editoriales</h1>
        <button id="btnNuevo"
            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            ➕ Agregar Editorial
        </button>
    </div>
    <div class="overflow-x-auto">
        <table id="tabla-editorial" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
            <thead class="bg-gray-100">
                <tr>
                    <th>RUC/DNI</th>
                    <th>Nombre</th>
                    <th>Telefono</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>


<!-- MODAL -->

@endsection
@section('modal')
<div class="modal fade" id="modalEditorial" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-sm">

            <!-- HEADER -->
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-semibold">Registro de Editorial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEditorial">
                <input type="hidden" id="id" name="id">

                <!-- BODY -->
                <div class="modal-body">

                    <div class="row g-3">

                        <!-- Documento -->
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Tipo de documento</label>
                            <select id="tipo_documento" name="tipo_documento"
                                    class="form-select validar_select">
                                <option value="0">Seleccione</option>
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                            </select>
                        </div>

                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">N° de documento</label>
                            <input type="text" id="nro_documento" name="nro_documento" class="form-control validar_numero">
                        </div>

                        <!-- Razón social -->
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control">
                        </div>

                        <!-- Responsable -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">Responsable</label>
                            <input type="text" id="responsable" name="responsable" class="form-control">
                        </div>

                        <!-- Contacto -->
                        <div class="col-md-6 form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control validar_numero">
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label">Correo</label>
                            <input type="email" id="correo" name="correo" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Web</label>
                            <input type="text" id="web" name="web" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">País</label>
                            <select id="pais" name="pais" class="form-select">
                                <option value="0">Seleccione</option>
                                <option value="Perú">Perú</option>
                                <option value="Chile">Chile</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Ecuador">Ecuador</option>
                                <option value="Bolivia">Bolivia</option>
                                <option value="Uruguay">Uruguay</option>    
                                <option value="Venezuela">Venezuela</option>
                                <option value="Paraguay">Paraguay</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>

                        <!-- Dirección -->
                        <div class="col-md-12 form-group">
                            <label class="form-label">Dirección</label>
                            <input type="text" id="direccion" name="direccion" class="form-control">
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer bg-light">
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success px-4">
                        Guardar
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


 @endsection