@extends('layouts.admin')

@section('page-title', 'Gestion de bibliotecas')

@section('css')
    <link href="{{ asset('css/administracion/biblioteca.css') }}" rel="stylesheet" />
@endsection

@section('js')
    <script src="{{ asset('lib/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/administracion/biblioteca.js') }}"></script>
@endsection

@section('content')
<div class="admin-section">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Bibliotecas</span>
    </div>

    <section class="admin-panel">
        <div class="admin-panel__header">
            <div>
                <h2 class="admin-panel__title">Sedes bibliotecarias</h2>
                <p class="admin-panel__copy">Configura las bibliotecas, su información institucional y la identidad visual de cada sede.</p>
            </div>
            <div class="admin-actions">
                <button id="btnNuevo" class="admin-btn admin-btn--primary">Agregar biblioteca</button>
            </div>
        </div>

        <div class="admin-table-shell table-responsive">
            <table id="tabla-biblioteca" class="table table-hover table-bordered align-middle text-nowrap datatable w-100">
                <thead>
                    <tr>
                        <th>Abrev.</th>
                        <th>Nombre</th>
                        <th>Direccion</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection

@section('modal')
<div class="modal fade" id="modalBiblioteca" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="formBiblioteca" enctype="multipart/form-data" class="modal-content shadow-sm library-admin-modal">
            @csrf
            <input type="hidden" id="id" name="id">
            <div class="modal-header library-admin-modal__header">
                <div>
                    <span class="library-admin-modal__eyebrow">
                        <i class="bi bi-buildings-fill"></i>
                        Gestion de sedes
                    </span>
                    <h5 class="modal-title fw-semibold mb-1">Registro de biblioteca</h5>
                    <p class="library-admin-modal__copy mb-0">Define la identidad de cada sede bibliotecaria con su nombre, información institucional y recurso visual.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body library-admin-modal__body">
                <div class="library-admin-modal__intro">
                    <div class="library-admin-modal__intro-icon">
                        <i class="bi bi-journal-richtext"></i>
                    </div>
                    <div>
                        <strong>Ficha de sede</strong>
                        <p class="mb-0">Completa datos claros para que usuarios y personal identifiquen rapidamente cada biblioteca dentro del sistema.</p>
                    </div>
                </div>

                <div class="admin-modal-section library-admin-modal__section">
                    <h6 class="library-admin-modal__section-title">Datos generales</h6>
                    <p class="library-admin-modal__section-copy">Usa una abreviatura corta y un nombre institucional consistente con la sede real.</p>
                    <div class="row g-3 mb-0">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Abrev.</label>
                            <input type="text" id="codigo" name="codigo" class="form-control" placeholder="Ejemplo: CENTRAL">
                            <div class="form-text">Código breve para identificar la sede.</div>
                        </div>
                        <div class="col-md-8 form-group">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre completo de la biblioteca">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Direccion</label>
                            <textarea id="direccion" name="direccion" class="form-control" rows="3" placeholder="Ubicación o dirección referencial de la sede"></textarea>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="form-label">Descripcion</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="4" placeholder="Describe el enfoque, servicios o caracteristicas destacadas de la biblioteca"></textarea>
                        </div>
                    </div>
                </div>

                <div class="admin-modal-section library-admin-modal__section library-admin-modal__section--media">
                    <h6 class="library-admin-modal__section-title">Imagen representativa</h6>
                    <p class="library-admin-modal__section-copy">Sube una imagen clara de la sede. La vista previa se actualizara automaticamente.</p>
                    <div class="row g-3 align-items-start">
                        <div class="col-md-7 form-group">
                            <label class="form-label">Imagen</label>
                            <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*">
                            <div class="form-text">Formatos recomendados: JPG, PNG o WEBP.</div>
                        </div>
                        <div class="col-md-5">
                            <div class="library-admin-modal__preview-shell">
                                <div class="library-admin-modal__preview-label">Vista previa</div>
                                <div class="library-admin-modal__preview-empty" id="previewPlaceholder">
                                    <i class="bi bi-image"></i>
                                    <span>Aún no se seleccionó una imagen</span>
                                </div>
                                <img id="previewImagen" src="" class="img-fluid rounded shadow-sm d-none library-admin-modal__preview-image">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer library-admin-modal__footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success px-4" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
