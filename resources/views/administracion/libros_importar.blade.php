@extends('layouts.admin')

@section('page-title', 'Importar libros')

@section('css')
    <link href="{{ asset('css/administracion/libros_importar.css') }}?v={{ filemtime(public_path('css/administracion/libros_importar.css')) }}" rel="stylesheet" />
@endsection

@section('content')
<div class="book-import">
    <div class="admin-breadcrumb">
        <span>Administracion</span>
        <span>/</span>
        <span>Libros</span>
        <span>/</span>
        <span class="admin-breadcrumb__current">Importar Excel</span>
    </div>

    <section class="admin-panel book-import__panel">
        <div class="admin-panel__header book-import__header">
            <div>
                <span class="book-import__eyebrow"><i class="bi bi-file-earmark-spreadsheet"></i> Modulo independiente</span>
                <h2 class="admin-panel__title">Importacion masiva de libros</h2>
                <p class="admin-panel__copy">Sube un archivo Excel con encabezados para registrar libros nuevos, calcular <code>numero</code> por <code>cod_materia</code> y relacionar autores y materias sin alterar el modulo actual.</p>
            </div>
            <a href="{{ url('/administracion/libros') }}" class="admin-btn admin-btn--secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Volver a libros</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="book-import__alert book-import__alert--error">
                <strong>No se pudo procesar la solicitud.</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('import_exception'))
            <div class="book-import__alert book-import__alert--error">
                <strong>Detalle tecnico de la importacion</strong>
                <div>{{ session('import_exception.message') }}</div>
                <div><small>{{ session('import_exception.file') }}:{{ session('import_exception.line') }}</small></div>
            </div>
        @endif

        @if (session('success'))
            <div class="book-import__alert book-import__alert--success">
                {{ session('success') }}
            </div>
        @endif

        <div class="book-import__grid">
            <article class="book-import__card">
                <h3>1. Biblioteca y archivo</h3>
                <p>Primero selecciona la biblioteca destino para los ejemplares. Luego sube el Excel con encabezados como <code>mat</code>, <code>codigo</code>, <code>titulo</code>, <code>anio</code> o <code>ano</code>, <code>idioma</code>, <code>edicion</code>, <code>ISBN</code>, <code>pais</code>, <code>paginas</code>, <code>editorial</code>, <code>ejemplar</code>, y opcionalmente <code>autores</code> y <code>materias</code>.</p>

                <form id="bookImportForm" action="{{ route('administracion.libros.importar') }}" method="POST" enctype="multipart/form-data" class="book-import__form">
                    @csrf
                    <div>
                        <label for="biblioteca_id" class="form-label">Biblioteca para los ejemplares</label>
                        <select name="biblioteca_id" id="biblioteca_id" class="form-select" required>
                            <option value="">Selecciona una biblioteca</option>
                            @foreach ($bibliotecas as $biblioteca)
                                <option value="{{ $biblioteca->id }}" @selected(old('biblioteca_id', session('biblioteca_importada_id')) == $biblioteca->id)>
                                    {{ $biblioteca->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <input type="file" name="archivo" accept=".xlsx,.xls" class="form-control" required>
                    <button id="bookImportSubmit" type="submit" class="admin-btn admin-btn--primary">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span id="bookImportSubmitLabel">Importar libros</span>
                    </button>
                </form>
            </article>

            <article class="book-import__card">
                <h3>2. Reglas de importacion</h3>
                <div class="book-import__rules">
                    <div><strong>Numero:</strong> se calcula automaticamente por <code>cod_materia</code>.</div>
                    <div><strong>Duplicados:</strong> se omiten por <code>codigo_ant</code>, <code>isbn</code> o <code>titulo</code>.</div>
                    <div><strong>Idioma:</strong> se busca por nombre y si no existe se guarda <code>null</code>.</div>
                    <div><strong>Editorial:</strong> se busca por nombre y, si no existe, se crea.</div>
                    <div><strong>Autores y materias:</strong> se vinculan sin duplicar registros en tablas pivote.</div>
                    <div><strong>Ejemplares:</strong> se crean automaticamente segun la columna <code>ejemplar</code> y se asignan a la biblioteca seleccionada.</div>
                </div>
            </article>
        </div>

        @php($summary = session('import_summary', []))
        @php($importErrors = session('import_errors', []))
        @php($insertedBooks = session('inserted_books', []))
        @php($omittedBooks = session('omitted_books', []))
            <section id="bookImportResults" class="book-import__results {{ empty($summary) ? 'd-none' : '' }}">
                <h3>Resumen de resultados</h3>
                <div class="book-import__summary">
                    <article class="book-import__summary-card">
                        <span>Total procesados</span>
                        <strong data-summary="procesados">{{ $summary['procesados'] ?? 0 }}</strong>
                    </article>
                    <article class="book-import__summary-card is-success">
                        <span>Libros insertados</span>
                        <strong data-summary="libros_insertados">{{ $summary['libros_insertados'] ?? 0 }}</strong>
                    </article>
                    <article class="book-import__summary-card is-warning">
                        <span>Omitidos</span>
                        <strong data-summary="omitidos">{{ $summary['omitidos'] ?? 0 }}</strong>
                    </article>
                    <article class="book-import__summary-card is-danger">
                        <span>Errores</span>
                        <strong data-summary="errores">{{ $summary['errores'] ?? 0 }}</strong>
                    </article>
                    <article class="book-import__summary-card is-info">
                        <span>Autores insertados</span>
                        <strong data-summary="autores_insertados">{{ $summary['autores_insertados'] ?? 0 }}</strong>
                    </article>
                    <article class="book-import__summary-card is-info">
                        <span>Materias insertadas</span>
                        <strong data-summary="materias_insertadas">{{ $summary['materias_insertadas'] ?? 0 }}</strong>
                    </article>
                    <article class="book-import__summary-card is-info">
                        <span>Ejemplares creados</span>
                        <strong data-summary="ejemplares_creados">{{ $summary['ejemplares_creados'] ?? 0 }}</strong>
                    </article>
                </div>

                    <div id="bookImportErrors" class="book-import__errors {{ empty($importErrors) ? 'd-none' : '' }}">
                        <h4>Detalle de errores</h4>
                        <ul id="bookImportErrorsList">
                            @foreach ($importErrors as $importError)
                                <li>{{ $importError }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div id="bookImportInserted" class="book-import__errors {{ empty($insertedBooks) ? 'd-none' : '' }}">
                        <h4>Libros registrados</h4>
                        <div class="book-import__table-wrap">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Titulo</th>
                                        <th>Codigo</th>
                                        <th>Cod. materia</th>
                                        <th>Numero</th>
                                        <th>Ejemplares</th>
                                    </tr>
                                </thead>
                                <tbody id="bookImportInsertedList">
                                    @foreach ($insertedBooks as $book)
                                        <tr>
                                            <td>{{ $book['titulo'] ?? '' }}</td>
                                            <td>{{ $book['codigo_ant'] ?? '-' }}</td>
                                            <td>{{ $book['cod_materia'] ?? '-' }}</td>
                                            <td>{{ $book['numero'] ?? '-' }}</td>
                                            <td>{{ $book['ejemplares'] ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="bookImportOmitted" class="book-import__errors {{ empty($omittedBooks) ? 'd-none' : '' }}">
                        <h4>Libros omitidos</h4>
                        <div class="book-import__table-wrap">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Titulo</th>
                                        <th>Codigo</th>
                                        <th>Cod. materia</th>
                                        <th>Numero</th>
                                        <th>Ejemplares</th>
                                        <th>Motivo</th>
                                    </tr>
                                </thead>
                                <tbody id="bookImportOmittedList">
                                    @foreach ($omittedBooks as $book)
                                        <tr>
                                            <td>{{ $book['titulo'] ?? '' }}</td>
                                            <td>{{ $book['codigo_ant'] ?? '-' }}</td>
                                            <td>{{ $book['cod_materia'] ?? '-' }}</td>
                                            <td>{{ $book['numero'] ?? '-' }}</td>
                                            <td>{{ $book['ejemplares'] ?? 0 }}</td>
                                            <td>{{ $book['motivo'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
            </section>
    </section>
</div>
@endsection

@section('js')
<script>
    (() => {
        const form = document.getElementById('bookImportForm');
        const submitButton = document.getElementById('bookImportSubmit');
        const submitLabel = document.getElementById('bookImportSubmitLabel');
        const results = document.getElementById('bookImportResults');
        const errorsBox = document.getElementById('bookImportErrors');
        const errorsList = document.getElementById('bookImportErrorsList');
        const insertedBox = document.getElementById('bookImportInserted');
        const insertedList = document.getElementById('bookImportInsertedList');
        const omittedBox = document.getElementById('bookImportOmitted');
        const omittedList = document.getElementById('bookImportOmittedList');

        if (!form) {
            return;
        }

        const resetFeedback = () => {
            document.querySelectorAll('[data-import-runtime]').forEach((node) => node.remove());
            errorsList.innerHTML = '';
            errorsBox.classList.add('d-none');
            insertedList.innerHTML = '';
            insertedBox.classList.add('d-none');
            omittedList.innerHTML = '';
            omittedBox.classList.add('d-none');
        };

        const showAlert = (message, type, extra = null) => {
            const box = document.createElement('div');
            box.className = `book-import__alert book-import__alert--${type}`;
            box.setAttribute('data-import-runtime', '1');

            let html = `<strong>${type === 'success' ? 'Proceso completado' : 'Importacion con problema'}</strong><div>${message}</div>`;

            if (extra?.message) {
                html += `<div><small>${extra.message}</small></div>`;
            }

            if (extra?.file && extra?.line) {
                html += `<div><small>${extra.file}:${extra.line}</small></div>`;
            }

            box.innerHTML = html;
            form.closest('.book-import__card').before(box);
        };

        const renderSummary = (summary, errors = [], insertedBooks = [], omittedBooks = []) => {
            Object.entries(summary || {}).forEach(([key, value]) => {
                const target = document.querySelector(`[data-summary="${key}"]`);
                if (target) {
                    target.textContent = value ?? 0;
                }
            });

            results.classList.remove('d-none');

            if (errors.length) {
                errorsList.innerHTML = errors.map((error) => `<li>${error}</li>`).join('');
                errorsBox.classList.remove('d-none');
            } else {
                errorsList.innerHTML = '';
                errorsBox.classList.add('d-none');
            }

            if (insertedBooks.length) {
                insertedList.innerHTML = insertedBooks.map((book) =>
                    `<tr><td>${book.titulo || ''}</td><td>${book.codigo_ant || '-'}</td><td>${book.cod_materia || '-'}</td><td>${book.numero || '-'}</td><td>${book.ejemplares || 0}</td></tr>`
                ).join('');
                insertedBox.classList.remove('d-none');
            } else {
                insertedList.innerHTML = '';
                insertedBox.classList.add('d-none');
            }

            if (omittedBooks.length) {
                omittedList.innerHTML = omittedBooks.map((book) =>
                    `<tr><td>${book.titulo || ''}</td><td>${book.codigo_ant || '-'}</td><td>${book.cod_materia || '-'}</td><td>${book.numero || '-'}</td><td>${book.ejemplares || 0}</td><td>${book.motivo || '-'}</td></tr>`
                ).join('');
                omittedBox.classList.remove('d-none');
            } else {
                omittedList.innerHTML = '';
                omittedBox.classList.add('d-none');
            }
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            resetFeedback();

            submitButton.disabled = true;
            submitLabel.textContent = 'Importando...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const rawText = await response.text();
                let data = null;

                try {
                    data = rawText ? JSON.parse(rawText) : null;
                } catch (parseError) {
                    showAlert('La respuesta del servidor no llego como JSON valido.', 'error', {
                        message: rawText ? rawText.slice(0, 500) : 'Respuesta vacia del servidor.'
                    });
                    return;
                }

                if (!data) {
                    showAlert('El servidor respondio sin contenido.', 'error', {
                        message: `HTTP ${response.status}`
                    });
                    return;
                }

                if (!response.ok || !data.success) {
                    const validationErrors = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : (data.message || 'No fue posible importar.');

                    showAlert(validationErrors, 'error', data.exception || null);
                    if (data.summary) {
                        renderSummary(data.summary, data.errors || [], data.inserted_books || [], data.omitted_books || []);
                    }
                    return;
                }

                showAlert(data.message || 'Importacion completada.', 'success');
                renderSummary(data.summary || {}, data.errors || [], data.inserted_books || [], data.omitted_books || []);
            } catch (error) {
                showAlert('No se pudo completar la solicitud desde el navegador.', 'error', {
                    message: error.message || 'Error inesperado.'
                });
            } finally {
                submitButton.disabled = false;
                submitLabel.textContent = 'Importar libros';
            }
        });
    })();
</script>
@endsection
