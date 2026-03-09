@extends('layouts.admin')
@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
@endsection
@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/inventario/libro_nuevo.js') }}"></script>
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
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <div  id="div_form_libro">
                <form id="formLibro" enctype="multipart/form-data">

                    <!-- ================= IDENTIFICACIÓN ================= -->

                    <h5 class="border-bottom pb-2 mb-3">Identificación</h5>

                    <div class="row g-3 mb-3">               

                        <div class="col-md-2 form-group form-required">
                            <label>ISBN</label>
                            <input type="text" name="isbn" class="form-control">
                        </div>
                        <div class="col-md-4 form-group form-required">
                            <label>Tipo Registro</label>
                            <select name="tipo_registro_id" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach ($tipo_registros as $tr)
                            <option value="{{ $tr->id }}">{{ $tr->nombre }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group form-required">
                            <label>Código Dewey</label>
                            <select id="codigo_dewey" name="codigo_dewey" class="form-select select2">
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group form-required">
                            <label>Código</label>
                            <input type="text" name="codigo" class="form-control" required>
                        </div>
                    </div>


                    <!-- ================= INFORMACIÓN BIBLIOGRÁFICA ================= -->

                    <h5 class="border-bottom pb-2 mt-4 mb-3">Información Bibliográfica</h5>

                    <div class="row g-3">

                        <div class="col-md-12 form-group form-required">
                            <label>Título</label>
                            <input type="text" name="titulo" class="form-control" required>
                        </div>

                        <div class="col-md-4 form-group form-required">
                            <label>Editorial</label>

                            <div class="d-flex">

                                <select id="editorial_id" name="editorial_id" class="form-select select2 flex-grow-1">
                                    <option value="">Seleccione</option>
                                </select>

                                <button type="button" class="btn btn-primary" id="btnNuevaEditorial">
                                    +
                                </button>

                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Edición</label>
                            <input type="text" name="edicion" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Año edición</label>
                            <input type="number" name="anio_edicion" class="form-control">
                        </div>


                        <div class="col-md-3 form-group form-required">
                            <label>Idioma</label>
                            <select name="idioma" class="form-select select2">
                                <option value="">Seleccione...</option>
                                @foreach ($idiomas as $tr)
                                    <option value="{{ $tr->id }}">{{ $tr->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group form-required">
                            <label>Páginas</label>
                            <input type="number" name="paginas" class="form-control">
                        </div>

                        <div class="col-md-3 form-group form-required">
                            <label>Fecha publicación</label>
                            <input type="date" name="fecha_publicacion" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Lugar publicación</label>
                            <input type="text" name="lugar_publicacion" class="form-control">
                        </div>
                    </div>
                    <!-- ================= RELACIONES ================= -->
                    <h5 class="border-bottom pb-2 mt-4 mb-3">Relaciones</h5>
                    <div class="row g-3">
                        <div class="col-md-6 form-group form-required">
                            <label>Autores</label>
                            <div class="d-flex">
                                <select id="autor_id" name="autor_id[]" class="form-select select2 flex-grow-1" multiple>
                                <option value="">Seleccione autor(es)</option>
                                </select>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAutor">
                                +
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Materias</label>
                            <select name="materias[]" id="materias" class="form-select select2" multiple></select>
                        </div>
                    </div>


                    <!-- ================= DESCRIPCIÓN ================= -->

                    <h5 class="border-bottom pb-2 mt-4 mb-3">Descripción</h5>

                    <div class="row g-3">

                        <div class="col-md-12">
                            <label>Resumen</label>
                            <textarea name="resumen" rows="3" class="form-control"></textarea>
                        </div>

                        <div class="col-md-12">
                            <label>Anotaciones</label>
                            <textarea name="anotaciones" rows="3" class="form-control"></textarea>
                        </div>

                    </div>


                    <!-- ================= ARCHIVOS ================= -->

                    <h5 class="border-bottom pb-2 mt-4 mb-3">Archivos</h5>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label>Imagen portada</label>

                            <input type="file" name="imagen" class="form-control">

                        </div>

                        <div class="col-md-6">

                            <label>Archivo índice (PDF)</label>

                            <input type="file" name="archivo_indice" class="form-control">

                        </div>

                    </div>


                    <!-- ================= BOTÓN ================= -->

                    <div class="mt-4 text-end">

                        <button type="submit" class="btn btn-success px-4">

                        Guardar Libro

                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>
</div>
@endsection
@section('modal')
<div class="modal fade" id="modalEditorial" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div id="div_form">
            <form id="formEditorial">
                <div class="modal-content shadow-sm">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-semibold">Registro de editorial</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group mb-3 form-required">
                                <label class="form-label">Tipo de documento</label>
                                <select id="ed_tipo_documento"  name="tipo_documento"
                                        class="form-select validar_select">
                                    <option value="0">Seleccione</option>
                                    <option value="DNI">DNI</option>
                                    <option value="RUC">RUC</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3 form-required">
                            <label class="form-label">Nro Documento</label>
                            <input type="text" id="ed_nro_documento" name="nro_documento" class="form-control">
                            </div>

                            <div class="col-md-6 form-group mb-3 form-required">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="ed_nombre" name="nombre" class="form-control">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Responsable</label>
                            <input type="text" id="ed_responsable" name="responsable" class="form-control">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" id="ed_telefono" name="telefono" class="form-control">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" id="ed_correo" name="correo" class="form-control">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Web</label>
                            <input type="text" id="ed_web" name="web" class="form-control">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">País</label>
                                <select id="ed_pais" name="pais" class="form-select select2">
                                    <option value="0">Seleccione</option>
                                    @foreach($paises as $pais)
                                        <option value="{{$pais->id}}">{{$pais->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 form-group mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" id="ed_direccion" class="form-control">
                            </div>


                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn btn-success px-4" type="submit">
                            Guardar
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
<div class="modal fade" id="modalAutor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div id="div_form_autor">
            <form id="formAutor">
                <div class="modal-content shadow-sm">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-semibold">Registro de autor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-6 form-group mb-3 form-required">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="au_nombre" name="nombre" class="form-control">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="form-label">Responsable</label>
                                <input type="text" id="au_responsable" name="responsable" class="form-control">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" id="au_telefono" name="telefono" class="form-control">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="form-label">Correo</label>
                                <input type="email" id="au_correo" name="correo" class="form-control">
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label class="form-label">Web</label>
                                <input type="text" id="au_web" name="web" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="form-label">País</label>
                                <select id="au_pais" name="pais" class="form-select select2">
                                    <option value="0">Seleccione</option>
                                    @foreach($paises as $pais)
                                        <option value="{{$pais->id}}">{{$pais->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 form-group mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" id="au_direccion" class="form-control">
                            </div>


                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="btn btn-success px-4" type="submit">
                            Guardar
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection