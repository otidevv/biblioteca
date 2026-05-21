@extends('layouts.admin')

@section('page-title', 'Ejemplares del libro')

@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/administracion/ejemplares.css') }}?v={{ filemtime(public_path('css/administracion/ejemplares.css')) }}" rel="stylesheet" />
@endsection
@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/administracion/ejemplar.js') }}?v={{ filemtime(public_path('js/administracion/ejemplar.js')) }}"></script>
    <script>let id = @json($id);</script>
    <script>
        window.ejemplarContexto = {
            bibliotecaFijaId: @json($bibliotecaFijaId),
            puedeFiltrarBiblioteca: @json($puedeFiltrarBiblioteca),
            accesoGlobal: @json($accesoGlobalBibliotecas),
            bibliotecasUsuarioIds: @json($bibliotecasUsuarioIds),
        };
    </script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span>Libros</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Ejemplares</span>
    </div>

    <section class="admin-panel">
        <div class="exemplars-hero">
            <div class="exemplars-hero__body">
                <span class="exemplars-hero__eyebrow">
                    <i class="bi bi-layers"></i>
                    Control de coleccion
                </span>
                <h2 class="exemplars-hero__title">Ejemplares del libro</h2>
                <p class="exemplars-hero__copy">Gestiona disponibilidad, ubicacion y trazabilidad de cada copia fisica desde una vista mas clara.</p>
            </div>
            <div class="exemplars-hero__stats">
                <a href="{{ url('/administracion/libros') }}" class="exemplars-hero__back">
                    <i class="bi bi-arrow-left-circle"></i>
                    <span>Volver al catalogo</span>
                </a>
                <div class="exemplars-stat">
                    <span class="exemplars-stat__label">
                        <i class="bi bi-upc-scan"></i>
                        Codigo topografico
                    </span>
                    <strong class="exemplars-stat__value">{{ $libro->codigo_dewey.$libro->codigo }}</strong>
                </div>
                <div class="exemplars-stat exemplars-stat--accent">
                    <span class="exemplars-stat__label">
                        <i class="bi bi-collection"></i>
                        Ejemplares actuales
                    </span>
                    <strong class="exemplars-stat__value exemplars-stat__value--big">{{ $libro->ejemplares_count }}</strong>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-5">
                <div class="admin-modal-section exemplars-book-card h-100">
                    <div class="exemplars-book-card__header">
                        <div>
                            <span class="exemplars-book-card__eyebrow">
                                <i class="bi bi-bookmark-check"></i>
                                Ficha bibliografica
                            </span>
                            <h3 class="admin-card__title mb-0">Informacion del libro</h3>
                        </div>
                    </div>
                    <div class="row g-3 align-items-start">
                        <div class="col-md-4 text-center">
                            <img id="portadaLibro" class="exemplars-book-card__cover" src="{{ asset($libro->imagen) }}" alt="Portada del libro">
                        </div>
                        <div class="col-md-8">
                            <div class="exemplars-book-meta">
                                <div class="exemplars-book-meta__item">
                                    <span class="exemplars-book-meta__label">
                                        <i class="bi bi-upc-scan"></i>Codigo
                                    </span>
                                    <strong class="exemplars-book-meta__value">{{ $libro->codigo_dewey.$libro->codigo }}</strong>
                                </div>
                                <div class="exemplars-book-meta__item">
                                    <span class="exemplars-book-meta__label">
                                        <i class="bi bi-book"></i>Titulo
                                    </span>
                                    <strong class="exemplars-book-meta__value">{{ $libro->titulo }}</strong>
                                </div>
                                <div class="exemplars-book-meta__item">
                                    <span class="exemplars-book-meta__label">
                                        <i class="bi bi-barcode"></i>ISBN
                                    </span>
                                    <strong class="exemplars-book-meta__value">{{ $libro->isbn ?: 'Sin ISBN' }}</strong>
                                </div>
                                <div class="exemplars-book-meta__item">
                                    <span class="exemplars-book-meta__label">
                                        <i class="bi bi-person-lines-fill"></i>Autor(es)
                                    </span>
                                    <strong class="exemplars-book-meta__value">{{ $libro->autores->map(fn($autor) => $autor->nombres.' '.$autor->apellidos)->join(', ') }}</strong>
                                </div>
                                <div class="exemplars-book-meta__item">
                                    <span class="exemplars-book-meta__label">
                                        <i class="bi bi-building"></i>Editorial
                                    </span>
                                    <strong class="exemplars-book-meta__value">{{ $libro->editorial? $libro->editorial->nombre:'Sin editorial' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="admin-panel exemplars-table-panel">
                    <div class="admin-panel__header">
                        <div>
                            <h3 class="admin-card__title mb-0">Ejemplares registrados</h3>
                            <p class="admin-panel__copy">Administra ubicacion, estado y movimiento interno de cada ejemplar.</p>
                            @if(!$accesoGlobalBibliotecas && !empty($bibliotecasUsuarioIds))
                                <p class="admin-panel__copy mb-0">
                                    <i class="bi bi-info-circle text-warning me-1"></i>
                                    Solo podras mover ejemplares que pertenezcan a tu biblioteca asignada. Los traslados quedan pendientes hasta que la biblioteca destino los acepte o rechace.
                                </p>
                            @endif
                        </div>
                        <div class="admin-actions exemplars-toolbar">
                            <label class="exemplars-filter">
                                <span class="exemplars-filter__label">
                                    <i class="bi bi-building"></i>
                                    Filtrar por biblioteca
                                </span>
                                <select id="biblioteca_filtro" class="admin-select exemplars-filter__select">
                                    <option value="-1">Todos los ejemplares</option>
                                    <option value="">Sin biblioteca</option>
                                    @foreach($bibliotecas as $b)
                                        <option value="{{$b->id}}">{{$b->nombre}}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button class="admin-btn admin-btn--primary" id="btnAgregarEjemplar">
                                <i class="bi bi-plus-circle"></i>
                                Agregar ejemplar
                            </button>
                        </div>
                    </div>

                    <div id="barraSeleccion" class="exemplars-bulk-bar" style="display:none !important;">
                        <div class="exemplars-bulk-bar__summary">
                            <i class="bi bi-check2-square exemplars-bulk-bar__icon"></i>
                            <span><b id="contadorSeleccion">0</b> ejemplares seleccionados</span>
                        </div>
                        <div class="exemplars-bulk-bar__actions">
                            <select id="biblioteca_destino" class="form-select form-select-sm exemplars-bulk-bar__select">
                                <option value="">Seleccionar biblioteca destino...</option>
                                @foreach($bibliotecasDestino as $b)
                                    <option value="{{$b->id}}">{{$b->nombre}}</option>
                                @endforeach
                            </select>
                            <button class="admin-btn admin-btn--primary exemplars-bulk-bar__btn" id="btnMoverBiblioteca">
                                <i class="bi bi-arrow-left-right"></i>
                                Mover
                            </button>
                        </div>
                    </div>

                    <div class="admin-table-shell table-responsive exemplars-table-shell">
                        <table id="tabla-ejemplares" class="table table-hover table-bordered align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="40" title="Seleccionar todos"><input type="checkbox" id="checkAll" title="Seleccionar todos"></th>
                                    <th><i class="bi bi-upc-scan text-muted me-1"></i>Codigo</th>
                                    <th><i class="bi bi-receipt text-muted me-1"></i>SIAF / Compra</th>
                                    <th><i class="bi bi-building text-muted me-1"></i>Biblioteca</th>
                                    <th><i class="bi bi-circle-half text-muted me-1"></i>Estado</th>
                                    <th width="120" class="text-center"><i class="bi bi-sliders text-muted me-1"></i>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="admin-panel exemplars-table-panel">
                <div class="admin-panel__header">
                    <div>
                        <span class="exemplars-hero__eyebrow exemplars-moves__eyebrow">
                            <i class="bi bi-arrow-left-right"></i>
                            Trazabilidad
                        </span>
                        <h3 class="admin-card__title mb-0 mt-2">Historial de movimientos</h3>
                        <p class="admin-panel__copy">Consulta quien solicito el traslado de un ejemplar, que biblioteca estuvo involucrada y que usuario lo acepto o rechazo.</p>
                    </div>
                </div>

                <div class="admin-table-shell table-responsive exemplars-table-shell">
                    <table id="tabla-movimientos-ejemplares" class="table table-hover table-bordered align-middle w-100">
                        <thead>
                            <tr>
                                <th><i class="bi bi-bookmark text-muted me-1"></i>Ejemplar</th>
                                <th><i class="bi bi-box-arrow-right text-muted me-1"></i>Origen</th>
                                <th><i class="bi bi-box-arrow-in-right text-muted me-1"></i>Destino</th>
                                <th><i class="bi bi-person text-muted me-1"></i>Solicitado por</th>
                                <th><i class="bi bi-person-check text-muted me-1"></i>Resuelto por</th>
                                <th><i class="bi bi-circle-half text-muted me-1"></i>Estado</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalEjemplar" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formEjemplar">
            <div class="modal-content shadow-sm">
                <div class="modal-header exemplars-modal__header">
                    <div>
                        <span class="exemplars-modal__eyebrow">
                            <i class="bi bi-boxes"></i>
                            Inventario fisico
                        </span>
                        <h5 class="modal-title fw-semibold mb-1" id="modalEjemplarTitulo">Registro de ejemplar</h5>
                        <p class="exemplars-modal__copy mb-0">Agrega nuevos ejemplares o actualiza la biblioteca y referencia administrativa de una copia existente.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-modal-section exemplars-modal__section">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="exemplars-modal__book-context">
                                    <span class="exemplars-modal__book-label">Libro seleccionado</span>
                                    <strong class="exemplars-modal__book-title">{{ $libro->titulo }}</strong>
                                    <span class="exemplars-modal__book-code">{{ $libro->codigo_dewey.$libro->codigo }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 form-group form-required js-quantity-group">
                                <label class="form-label">Cantidad</label>
                                <input type="number" id="cantidad" name="cantidad" class="form-control" min="1" placeholder="Cantidad de ejemplares a registrar">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">SIAF o referencia</label>
                                <input type="text" id="siaf" name="siaf" class="form-control" placeholder="Codigo SIAF, compra o referencia interna">
                            </div>
                            <div class="col-md-12 form-group form-required">
                                <label class="form-label">Biblioteca</label>
                                <select id="biblioteca_modal" name="biblioteca_id" class="form-select validar_select">
                                    <option value="0">Seleccione</option>
                                    @foreach($bibliotecas as $biblioteca)
                                        <option value="{{$biblioteca->id}}">{{$biblioteca->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="libro_id" value="{{$libro->id}}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer exemplars-modal__footer">
                    <button type="button" class="admin-btn admin-btn--ghost" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>
                    <button class="admin-btn admin-btn--primary" type="submit" id="btnGuardarEjemplar">
                        <i class="bi bi-check-circle"></i>
                        Guardar ejemplar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
