@extends('layouts.admin')

@php($libroActual = $libro ?? null)

@section('page-title', $libroActual ? 'Editar libro' : 'Nuevo libro')

@section('css')
    <link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/select2.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/administracion/libro_nuevo.css') }}?v={{ filemtime(public_path('css/administracion/libro_nuevo.css')) }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
    <script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
    <script src="{{ asset('/js/administracion/libro_nuevo.js') }}?v={{ filemtime(public_path('js/administracion/libro_nuevo.js')) }}"></script>
    <script>
        let libro = @json($libroActual);
        let codigoDewey = @json(optional($libroActual)->codigo_dewey);
        let textoDewey = @json(optional($libroActual)->codigo_dewey ? optional($libroActual)->codigo_dewey : '');
        let autores = @json(optional($libroActual)->autores ?? []);
    </script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span>Libros</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">{{ $libroActual ? 'Editar' : 'Nuevo' }}</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">{{ $libroActual ? 'Actualizar registro bibliografico' : 'Registrar nuevo libro' }}</h2>
                <p class="admin-panel__copy">Completa la informacion bibliografica, tecnica y documental del material antes de publicarlo en el catalogo.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('manual.codificacion') }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                    <i class="bi bi-book me-1"></i> Ver manual de codificacion
                </a>
            </div>
        </div>

        <form id="formLibro" class="book-form" enctype="multipart/form-data">
            <input type="hidden" name="id" id="id" value="{{ optional($libroActual)->id }}">

            <div class="book-form__hero">
                <div>
                    <span class="book-form__eyebrow">Catalogacion guiada</span>
                    <h3 class="book-form__headline">{{ $libroActual ? 'Ajusta la ficha del libro con una vista mas clara' : 'Registra un libro con todos sus datos clave en una sola vista' }}</h3>
                    <p class="book-form__summary">La pagina sugiere el Dewey por titulo, genera el codigo Cutter segun el autor principal y aprende de las correcciones guardadas para mejorar futuras clasificaciones.</p>
                </div>
                <div class="book-form__status">
                    <span class="book-form__status-label">Modo</span>
                    <strong>{{ $libroActual ? 'Edicion' : 'Nuevo registro' }}</strong>
                </div>
            </div>

            <div class="admin-modal-section book-form__section">
                <div class="book-form__section-head">
                    <span class="book-form__section-icon"><i class="bi bi-journal-text"></i></span>
                    <div>
                        <h5 class="admin-card__title mb-1">Informacion bibliografica</h5>
                        <p class="book-form__section-copy">Datos principales del libro, autoria y contexto editorial.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-12 form-group form-required">
                        <label>Titulo</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" value="{{ optional($libroActual)->titulo ?? '' }}" required>
                    </div>
                    <div class="col-md-6 form-group form-required">
                        <label>Autores</label>
                        <div class="d-flex gap-2">
                            <select id="autor_id" name="autor_id[]" class="form-select select2 flex-grow-1" multiple></select>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAutor">+</button>
                        </div>
                    </div>
                    <div class="col-md-6 form-group form-required">
                        <label>Editorial</label>
                        <div class="d-flex gap-2">
                            <select id="editorial_id" name="editorial_id" class="form-select select2 flex-grow-1">
                                <option value="">Seleccione</option>
                            </select>
                            <button type="button" class="btn btn-primary" id="btnNuevaEditorial">+</button>
                        </div>
                    </div>
                    <div class="col-md-3 form-group form-optional">
                        <label>Edicion</label>
                        <input type="text" name="edicion" class="form-control" value="{{ optional($libroActual)->edicion ?? '' }}">
                    </div>
                    <div class="col-md-3 form-group form-optional">
                        <label>Anio edicion</label>
                        <input type="number" name="anio_edicion" class="form-control" value="{{ optional($libroActual)->anio_edicion ?? '' }}">
                    </div>
                    <div class="col-md-3 form-group form-required">
                        <label>Idioma</label>
                        <select name="idioma" class="form-select select2">
                            <option value="">Seleccione...</option>
                            @foreach ($idiomas as $idioma)
                                <option value="{{ $idioma->id }}" {{ optional($libroActual)->idioma == $idioma->id ? 'selected' : '' }}>{{ $idioma->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group form-required">
                        <label>Paginas</label>
                        <input type="number" name="paginas" class="form-control" value="{{ optional($libroActual)->paginas ?? '' }}">
                    </div>
                    <div class="col-md-6 form-group form-optional">
                        <label>Materias</label>
                        <select name="materias[]" id="materias" class="form-select select2" multiple></select>
                    </div>
                    <div class="col-md-3 form-group form-optional">
                        <label>Fecha publicacion</label>
                        <input type="date" name="fecha_publicacion" class="form-control" value="{{ optional($libroActual)->fecha_publicacion ?? '' }}">
                    </div>
                    <div class="col-md-3 form-group form-optional">
                        <label>Lugar publicacion</label>
                        <input type="text" name="lugar_publicacion" class="form-control" value="{{ optional($libroActual)->lugar_publicacion ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="admin-modal-section book-form__section">
                <div class="book-form__section-head">
                    <span class="book-form__section-icon"><i class="bi bi-upc-scan"></i></span>
                    <div>
                        <h5 class="admin-card__title mb-1">Identificacion</h5>
                        <p class="book-form__section-copy">Clasificacion, tipo de registro y codigo interno.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4 form-group form-required">
                        <label>Tipo registro</label>
                        <select name="tipo_registro_id" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach ($tipo_registros as $tipoRegistro)
                                <option value="{{ $tipoRegistro->id }}" {{ optional($libroActual)->tipo_registro_id == $tipoRegistro->id ? 'selected' : '' }}>{{ $tipoRegistro->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group form-required">
                        <label>ISBN</label>
                        <input type="text" name="isbn" class="form-control" value="{{ optional($libroActual)->isbn ?? '' }}">
                    </div>
                    <div class="col-md-4 form-group form-required">
                        <label>Codigo Dewey</label>
                        <select id="codigo_dewey" name="codigo_dewey" class="form-select select2">
                            <option value="">Seleccione...</option>
                        </select>
                        <small id="deweySuggestionHint" class="text-muted d-block mt-2"></small>
                    </div>
                    <div class="col-md-2 form-group form-required">
                        <label>Codigo</label>
                        <input type="text" name="codigo" class="form-control" value="{{ optional($libroActual)->codigo ?? '' }}" required>
                        <small id="codigoLibroHint" class="text-muted d-block mt-2"></small>
                    </div>
                </div>
            </div>

            <div class="admin-modal-section book-form__section">
                <div class="book-form__section-head">
                    <span class="book-form__section-icon"><i class="bi bi-card-text"></i></span>
                    <div>
                        <h5 class="admin-card__title mb-1">Descripcion</h5>
                        <p class="book-form__section-copy">Resumen, notas internas y palabras clave para consulta.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-12 form-group form-optional">
                        <label>Resumen</label>
                        <textarea name="resumen" rows="4" class="form-control">{{ optional($libroActual)->resumen ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6 form-group form-optional">
                        <label>Anotaciones</label>
                        <textarea name="anotaciones" rows="4" class="form-control">{{ optional($libroActual)->anotaciones ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6 form-group form-optional">
                        <label>Palabras clave</label>
                        <textarea name="palabras_clave" rows="4" class="form-control">{{ optional($libroActual)->palabras_clave ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="admin-modal-section book-form__section">
                <div class="book-form__section-head">
                    <span class="book-form__section-icon"><i class="bi bi-images"></i></span>
                    <div>
                        <h5 class="admin-card__title mb-1">Archivos</h5>
                        <p class="book-form__section-copy">Portada e indice PDF para enriquecer la ficha digital.</p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6 form-group form-optional">
                        <label>Imagen portada</label>
                        <input type="file" name="imagen" id="imagen" class="form-control">
                        <div class="book-form__preview mt-2">
                            <img id="previewImagen" src="{{ optional($libroActual)->imagen ? '/'.optional($libroActual)->imagen : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22280%22 height=%22390%22 viewBox=%220 0 280 390%22%3E%3Crect width=%22280%22 height=%22390%22 rx=%2224%22 fill=%22%23f8fafc%22/%3E%3Cpath d=%22M84 118h112v154H84z%22 fill=%22none%22 stroke=%22%2394a3b8%22 stroke-width=%228%22 stroke-linejoin=%22round%22/%3E%3Ccircle cx=%22110%22 cy=%22150%22 r=%2218%22 fill=%22%23cbd5e1%22/%3E%3Cpath d=%22m94 238 32-34 26 24 34-40 26 50H94Z%22 fill=%22%23cbd5e1%22/%3E%3Ctext x=%22140%22 y=%22300%22 text-anchor=%22middle%22 fill=%22%2364758b%22 font-family=%22Arial%22 font-size=%2220%22%3ESin portada%3C/text%3E%3C/svg%3E' }}" alt="Vista previa de portada">
                        </div>
                    </div>
                    <div class="col-md-6 form-group form-optional">
                        <label>Archivo indice (PDF)</label>
                        <input type="file" name="archivo_indice" id="archivo_indice" class="form-control">
                        <small id="nombrePdf" class="text-muted">{{ basename(optional($libroActual)->archivo_indice ?? '') }}</small>
                    </div>
                </div>
            </div>

            <div class="book-form__footer">
                <a href="{{ url('/administracion/libros') }}" class="btn btn-outline-secondary">Volver al catalogo</a>
                <button type="submit" class="btn btn-success px-4">Guardar libro</button>
            </div>
        </form>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalEditorial" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formEditorial" class="w-100">
            <div class="modal-content shadow-sm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold">Registro de editorial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Tipo de documento</label>
                            <select id="ed_tipo_documento" name="tipo_documento" class="form-select validar_select">
                                <option value="0">Seleccione</option>
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Nro Documento</label>
                            <input type="text" id="ed_nro_documento" name="nro_documento" class="form-control">
                        </div>
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="ed_nombre" name="nombre" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Responsable</label>
                            <input type="text" id="ed_responsable" name="responsable" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Telefono</label>
                            <input type="text" id="ed_telefono" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Correo</label>
                            <input type="email" id="ed_correo" name="correo" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Web</label>
                            <input type="text" id="ed_web" name="web" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Pais</label>
                            <select id="ed_pais" name="pais" class="form-select select2">
                                <option value="0">Seleccione</option>
                                @foreach($paises as $pais)
                                    <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Direccion</label>
                            <input type="text" id="ed_direccion" name="direccion" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success px-4" type="submit">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalAutor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formAutor" class="w-100">
            <div class="modal-content shadow-sm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold">Registro de autor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="au_nombre" name="nombre" class="form-control">
                        </div>
                        <div class="col-md-6 form-group form-required">
                            <label class="form-label">Apellidos</label>
                            <input type="text" id="au_apellidos" name="apellidos" class="form-control">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Pais</label>
                            <select id="au_pais" name="pais" class="form-select select2">
                                <option value="0">Seleccione</option>
                                @foreach($paises as $pais)
                                    <option value="{{ $pais->id }}">{{ $pais->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success px-4" type="submit">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
