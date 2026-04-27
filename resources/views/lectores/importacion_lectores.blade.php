@extends('layouts.admin')

@section('page-title', 'Importacion de lectores')

@section('css')
    <link href="{{ asset('css/lectores/importacion_lectores.css') }}?v={{ filemtime(public_path('css/lectores/importacion_lectores.css')) }}" rel="stylesheet" />
@endsection

@section('content')
<div class="admin-section reader-import">
    <div class="admin-breadcrumb">
        <span>Lectores</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Importacion</span>
    </div>

    <section class="admin-panel reader-import__panel">
        <div class="admin-panel__header reader-import__header">
            <div>
                <span class="reader-import__eyebrow"><i class="bi bi-file-earmark-spreadsheet"></i> Carga masiva</span>
                <h2 class="admin-panel__title">Importacion de nuevos lectores</h2>
                <p class="admin-panel__copy">Sube un archivo Excel, revisa la vista previa fila por fila y solo despues ejecuta la carga masiva de lectores al sistema.</p>
            </div>
            <div class="admin-actions">
                <a href="{{ route('lectores.importacion.plantilla') }}" class="admin-btn admin-btn--secondary">
                    <i class="bi bi-download"></i>
                    <span>Descargar plantilla</span>
                </a>
            </div>
        </div>

        <div class="reader-import__grid">
            <article class="reader-import__card">
                <h3>1. Cargar archivo</h3>
                <p>Se admiten archivos <strong>.xlsx</strong> y <strong>.csv</strong> con la plantilla oficial de lectores.</p>

                <form id="readerImportForm" class="reader-import__upload" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="readerImportFile" name="archivo" accept=".xlsx,.csv" class="form-control" required>
                    <button type="submit" class="admin-btn admin-btn--primary">
                        <i class="bi bi-eye"></i>
                        <span>Previsualizar archivo</span>
                    </button>
                </form>

                <div class="reader-import__tips">
                    <div><strong>Filas recomendadas:</strong> usa una fila por lector.</div>
                    <div><strong>Para estudiantes:</strong> completa codigo institucional; si la carrera no coincide con una registrada, se importara vacia.</div>
                    <div><strong>Sexo y estado academico:</strong> se ignoran durante esta importacion.</div>
                    <div><strong>Contrasena:</strong> puedes dejarla vacia y el sistema aplicara la predeterminada configurada.</div>
                    <div><strong>Correo:</strong> las filas sin correo valido se mostraran, pero no se importaran.</div>
                    <div><strong>Carreras disponibles:</strong> {{ $carreras->count() }} registradas.</div>
                </div>
            </article>

            <article class="reader-import__card">
                <h3>2. Plantilla esperada</h3>
                <p>Estas son las columnas que debe tener el Excel para que la revision y la carga funcionen correctamente.</p>

                <div class="reader-import__columns table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Obligatorio</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($columnasPlantilla as $columna)
                                <tr>
                                    <td><code>{{ $columna['campo'] }}</code></td>
                                    <td>{{ $columna['required'] }}</td>
                                    <td>{{ $columna['detalle'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <div id="readerImportSummary" class="reader-import__summary d-none">
            <div class="reader-import__summary-card">
                <span>Total filas</span>
                <strong data-summary="total">0</strong>
            </div>
            <div class="reader-import__summary-card is-success">
                <span>Validas</span>
                <strong data-summary="validos">0</strong>
            </div>
            <div class="reader-import__summary-card is-danger">
                <span>Observadas</span>
                <strong data-summary="invalidos">0</strong>
            </div>
            <div class="reader-import__summary-card is-info">
                <span>Estudiantes</span>
                <strong data-summary="estudiantes">0</strong>
            </div>
        </div>

        <section id="readerImportPreview" class="reader-import__preview d-none">
            <div class="reader-import__preview-head">
                <div>
                    <h3>3. Revisar vista previa</h3>
                    <p>Revisa cada fila, corrige los datos necesarios directamente en esta tabla y luego ejecuta la carga masiva.</p>
                </div>
                <button id="readerImportConfirm" type="button" class="admin-btn admin-btn--primary" disabled>
                    <i class="bi bi-cloud-upload"></i>
                    <span>Importar lectores</span>
                </button>
            </div>

            <div class="reader-import__table table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fila</th>
                            <th>Estado</th>
                            <th>Identidad</th>
                            <th>Datos personales</th>
                            <th>Contacto</th>
                            <th>Datos academicos</th>
                            <th>Acceso</th>
                            <th>Revision</th>
                        </tr>
                    </thead>
                    <tbody id="readerImportRows"></tbody>
                </table>
            </div>
        </section>
    </section>
</div>
@endsection

@section('js')
<script>
    window.readerImportConfig = {
        previewUrl: '{{ route('lectores.importacion.preview') }}',
        importUrl: '{{ route('lectores.importacion.cargar') }}',
        carreras: @json($carreras->map(fn($carrera) => ['id' => $carrera->id, 'nombre' => $carrera->nombre])->values()),
        tiposPersona: ['ESTUDIANTE', 'DOCENTE', 'ADMINISTRATIVO', 'EXTERNO']
    };
</script>
<script src="{{ asset('js/lectores/importacion_lectores.js') }}?v={{ filemtime(public_path('js/lectores/importacion_lectores.js')) }}"></script>
@endsection
