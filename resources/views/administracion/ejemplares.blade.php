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

    <div class="ex-page-header">
        <div class="ex-page-header__main">
            <div class="admin-breadcrumb">
                <span>Administracion</span>
                <span>/</span>
                <span>Libros</span>
                <span>/</span>
                <span class="admin-breadcrumb__current">Ejemplares</span>
            </div>
            <h2 class="ex-page-header__title">Ejemplares del libro</h2>
            <p class="ex-page-header__copy">Gestiona disponibilidad, ubicacion y trazabilidad de cada copia fisica desde una vista unificada.</p>
        </div>
        <a href="{{ url('/administracion/libros') }}" class="admin-btn admin-btn--ghost ex-page-header__back">
            <i class="bi bi-arrow-left-circle"></i>
            Volver al catalogo
        </a>
    </div>

    <div class="ex-stats-row">
        <div class="ex-stat-card">
            <span class="ex-stat-card__label">
                <i class="bi bi-upc-scan"></i> Codigo topografico
            </span>
            <strong class="ex-stat-card__value" style="font-size:1.1rem;">{{ $libro->codigo_dewey.$libro->codigo }}</strong>
        </div>
        <div class="ex-stat-card">
            <span class="ex-stat-card__label">
                <i class="bi bi-collection"></i> Total de ejemplares
            </span>
            <strong class="ex-stat-card__value ex-stat-card__value--total">{{ $libro->ejemplares_count }}</strong>
            <span class="ex-stat-card__sub">registrados</span>
        </div>
        <div class="ex-stat-card ex-stat-card--avail">
            <span class="ex-stat-card__label">
                <i class="bi bi-check-circle"></i> Disponibles
            </span>
            <strong class="ex-stat-card__value ex-stat-card__value--avail">{{ $disponiblesCount }}</strong>
            <span class="ex-stat-card__sub">ejemplares</span>
        </div>
        <div class="ex-stat-card ex-stat-card--pending">
            <span class="ex-stat-card__label">
                <i class="bi bi-arrow-left-right"></i> Pendientes
            </span>
            <strong class="ex-stat-card__value ex-stat-card__value--pending">{{ $pendientesCount }}</strong>
            <span class="ex-stat-card__sub">pendiente aceptacion</span>
        </div>
    </div>

    <section class="admin-panel">
        <div class="row g-4">
            <div class="col-12 col-xl-4">
                <div class="admin-modal-section exemplars-book-card h-100">
                    <div class="exemplars-book-card__header">
                        <div>
                            <span class="exemplars-book-card__eyebrow">
                                <i class="bi bi-bookmark-check"></i>
                                Ficha bibliografica
                            </span>
                            <h3 class="admin-card__title mb-0 mt-2">Informacion del libro</h3>
                        </div>
                    </div>
                    <div class="text-center mb-3">
                        <img class="exemplars-book-card__cover" src="{{ asset($libro->imagen ?: 'img/libro-placeholder.png') }}" alt="Portada del libro">
                    </div>
                    <div class="exemplars-book-meta">
                        <div class="exemplars-book-meta__item">
                            <span class="exemplars-book-meta__label"><i class="bi bi-upc-scan"></i>Codigo</span>
                            <strong class="exemplars-book-meta__value">{{ $libro->codigo_dewey.$libro->codigo }}</strong>
                        </div>
                        <div class="exemplars-book-meta__item">
                            <span class="exemplars-book-meta__label"><i class="bi bi-book"></i>Titulo</span>
                            <strong class="exemplars-book-meta__value">{{ $libro->titulo }}</strong>
                        </div>
                        <div class="exemplars-book-meta__item">
                            <span class="exemplars-book-meta__label"><i class="bi bi-barcode"></i>ISBN</span>
                            <strong class="exemplars-book-meta__value">{{ $libro->isbn ?: 'Sin ISBN' }}</strong>
                        </div>
                        <div class="exemplars-book-meta__item">
                            <span class="exemplars-book-meta__label"><i class="bi bi-person-lines-fill"></i>Autor(es)</span>
                            <strong class="exemplars-book-meta__value">{{ $libro->autores->map(fn($a) => $a->nombres.' '.$a->apellidos)->join(', ') }}</strong>
                        </div>
                        <div class="exemplars-book-meta__item">
                            <span class="exemplars-book-meta__label"><i class="bi bi-building"></i>Editorial</span>
                            <strong class="exemplars-book-meta__value">{{ $libro->editorial ? $libro->editorial->nombre : 'Sin editorial' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="admin-panel exemplars-table-panel">
                    <div class="ex-panel-header">
                        <div class="ex-panel-header__top">
                            <div class="ex-panel-header__text">
                                <h3 class="admin-card__title mb-0">Ejemplares registrados</h3>
                                <p class="admin-panel__copy mb-0">Administra ubicacion, estado y movimiento interno de cada ejemplar.</p>
                            </div>
                            <div class="ex-panel-header__actions">
                                <label class="exemplars-filter">
                                    <span class="exemplars-filter__label">
                                        <i class="bi bi-building"></i>
                                        Filtrar por biblioteca
                                    </span>
                                    <select id="biblioteca_filtro" class="admin-select exemplars-filter__select">
                                        <option value="-1" {{ request()->query('biblioteca') ? '' : 'selected' }}>Todos los ejemplares</option>
                                        <option value="">Sin biblioteca</option>
                                        @foreach($bibliotecas as $b)
                                            <option value="{{$b->id}}" {{ (string) request()->query('biblioteca') === (string) $b->id ? 'selected' : '' }}>{{$b->nombre}}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <button class="admin-btn admin-btn--primary" id="btnAgregarEjemplar">
                                    <i class="bi bi-plus-circle"></i>
                                    <span>Agregar ejemplar</span>
                                </button>
                            </div>
                        </div>
                        @if(!$accesoGlobalBibliotecas && !empty($bibliotecasUsuarioIds))
                        <div class="ex-panel-header__notice">
                            <i class="bi bi-info-circle-fill ex-panel-header__notice-icon"></i>
                            <span>Solo podras mover ejemplares de tu biblioteca. Los traslados quedan pendientes hasta que el destino los acepte.</span>
                        </div>
                        @endif
                    </div>

                    <div id="barraSeleccion" class="ex-transfer-bar" style="display:none;">
                        <div class="ex-transfer-bar__left">
                            <span class="ex-transfer-bar__title">
                                <i class="bi bi-arrow-left-right"></i>
                                Trasladar ejemplares
                            </span>
                            <select id="biblioteca_destino" class="form-select form-select-sm ex-transfer-bar__select">
                                <option value="">Seleccionar biblioteca destino...</option>
                                @foreach($bibliotecasDestino as $b)
                                    <option value="{{$b->id}}">{{$b->nombre}}</option>
                                @endforeach
                            </select>
                            <button class="admin-btn admin-btn--primary ex-transfer-bar__btn" id="btnMoverBiblioteca">
                                Trasladar
                            </button>
                        </div>
                        <div class="ex-transfer-bar__right">
                            <span class="ex-transfer-badge">
                                Seleccionados: <b id="contadorSeleccion">0</b>
                            </span>
                            <button class="admin-btn admin-btn--danger ex-transfer-bar__btn" id="btnEliminarEjemplares">
                                <i class="bi bi-trash3"></i>
                                Eliminar
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
                            <div class="col-md-6 form-group">
                                <label class="form-label">Codigo antiguo o interno</label>
                                <input type="text" id="codigo_ant_ejemplar" name="codigo_ant" class="form-control" placeholder="Codigo anterior del sistema">
                            </div>
                            <div class="col-md-6 form-group js-edit-only-group">
                                <label class="form-label">Numero de inventario</label>
                                <input type="number" id="codigo_interno_ejemplar" name="codigo_interno" class="form-control" placeholder="Codigo interno del ejemplar">
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
