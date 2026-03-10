@extends('layouts.admin')
@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
@endsection
@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/administracion/ejemplar.js') }}"></script>
    <script>
        let id = @json($id);
        console.log(id);
        
    </script>
@endsection
@section('content')
<nav class="mb-4 text-sm text-gray-600">
    <ol class="flex items-center space-x-2">
        <li class="font-semibold text-gray-800">
            Administración
        </li>
        <li class="text-gray-400">›</li>
        <li class="text-emerald-700 font-semibold">
            Libros-Ejempares
        </li>
    </ol>
</nav>

<div class="bg-white p-6 rounded-xl shadow-lg">
    <div class="overflow-x-auto">
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <div class="row">

                <!-- DATOS DEL LIBRO -->
                <div class="col-md-6">

                    <div class="card">
                        <div class="card-header">
                            <b>Información del Libro</b>
                        </div>

                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-4 text-center">
                                    <img id="portadaLibro"
                                        src="{{asset($libro->imagen)}}"style="width:100%; height:auto;">
                                </div>

                                <div class="col-md-8">

                                    <p><b>Código:{{$libro->codigo_dewey.$libro->codigo}}</b> <span id="libro_codigo"></span></p>
                                    <p><b>Título:{{$libro->titulo}}</b> <span id="libro_titulo"></span></p>
                                    <p><b>ISBN:{{$libro->isbn}}</b> <span id="libro_isbn"></span></p>
                                    <p><b>Autor(es):@foreach($libro->autores as $autores)
                                            {{$autores->nombres.' '.$autores->apellidos}}</b> <span name="libro_autores"></span>
                                            @endforeach
                                    </p>
                                    <p><b>Editorial:{{$libro->editorial? $libro->editorial->nombre:''}}</b> <span id="libro_editorial"></span></p>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>


                <!-- TABLA EJEMPLARES -->
                <div class="col-md-6">

                    <div class="card">

                        <div class="card-header d-flex justify-content-between align-items-center">

                            <b>Ejemplares</b>

                            <div class="d-flex gap-2">

                                <select id="biblioteca_id" class="form-select form-select-sm" style="width:200px;">
                                    <option value="-1">TODOS LOS EJEMPLARES</option>
                                    <option value="">SIN BIBLIOTECA</option>
                                    @foreach($bibliotecas as $b)
                                        <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                                    @endforeach
                                </select>

                                <button class="btn btn-success btn-sm" id="btnAgregarEjemplar">
                                    + Agregar ejemplar
                                </button>

                            </div>

                        </div>

                        <div class="card-body">

                            <div class="table-responsive">

                                <table id="tabla-ejemplares"
                                    class="table table-hover table-bordered align-middle text-nowrap datatable w-100">

                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Siaf</th>
                                            <th>Ubicación</th>
                                            <th>Estado</th>
                                            <th width="120">Acciones</th>
                                        </tr>
                                    </thead>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
@endsection
@section('modal')
<div class="modal fade" id="modalEjemplar" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <form id="formEjemplar">

            <div class="modal-content shadow-sm">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold">Registro de Ejemplar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <!-- Cantidad -->
                        <div class="col-md-6 form-required">
                            <label class="form-label">Cantidad</label>
                            <input type="number" id="cantidad" name="cantidad" class="form-control" min="1">
                        </div>

                        <!-- Biblioteca -->
                        <div class="col-md-6">
                            <label class="form-label">Biblioteca</label>
                            <select id="biblioteca_id" name="biblioteca_id" class="form-select select2">
                                <option value="">Seleccione</option>
                                @foreach($bibliotecas as $biblioteca)
                                    <option value="{{ $biblioteca->id }}">
                                        {{ $biblioteca->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- SIAF -->
                        <div class="col-md-12">
                            <label class="form-label">SIAF</label>
                            <input type="text" id="siaf" name="siaf" class="form-control">
                        </div>

                        <!-- libro_id oculto -->
                        <input type="hidden" name="libro_id" value="{{ $libro->id }}">

                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
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
@endsection