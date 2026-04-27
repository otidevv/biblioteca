document.addEventListener('DOMContentLoaded', function () {
    const config = window.readerImportConfig || {};
    const form = document.getElementById('readerImportForm');
    const previewSection = document.getElementById('readerImportPreview');
    const summarySection = document.getElementById('readerImportSummary');
    const rowsContainer = document.getElementById('readerImportRows');
    const confirmButton = document.getElementById('readerImportConfirm');
    const confirmLabel = confirmButton ? confirmButton.querySelector('span') : null;
    const csrfInput = form ? form.querySelector('input[name="_token"]') : null;
    let currentToken = null;

    if (!form || !config.previewUrl || !config.importUrl || !rowsContainer || !confirmButton || !csrfInput) {
        return;
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const data = new FormData(form);
        currentToken = null;
        confirmButton.disabled = true;

        try {
            const response = await fetch(config.previewUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: data
            });

            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'No se pudo procesar el archivo.');
            }

            renderPreview(payload.data);

            if (typeof alerta === 'function') {
                alerta(
                    payload.data.can_import
                        ? 'Vista previa lista. Ya puedes importar los lectores.'
                        : 'Se detectaron observaciones. Solo se importaran las filas validas.',
                    payload.data.can_import
                );
            }
        } catch (error) {
            rowsContainer.innerHTML = '';
            previewSection.classList.add('d-none');
            summarySection.classList.add('d-none');
            syncConfirmButton({ total: 0, can_import: false });

            if (typeof alerta === 'function') {
                alerta(error.message || 'No se pudo procesar el archivo.', false);
            }
        }
    });

    confirmButton.addEventListener('click', async function () {
        if (!currentToken) {
            return;
        }

        const payloadData = {
            token: currentToken,
            rows: collectRows(rowsContainer)
        };

        try {
            confirmButton.disabled = true;

            const response = await fetch(config.importUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfInput.value,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payloadData)
            });

            const payload = await response.json();

            if (!response.ok || !payload.success) {
                if (payload.data) {
                    renderPreview(payload.data);
                } else {
                    syncConfirmButton({ total: collectRows(rowsContainer).length, can_import: false });
                }
                throw new Error(payload.message || 'No se pudo completar la importacion.');
            }

            if (typeof alerta === 'function') {
                alerta(payload.message || 'Importacion completada.', true);
            }

            form.reset();
            currentToken = null;
            setTimeout(function () {
                window.location.reload();
            }, 1000);
        } catch (error) {
            confirmButton.disabled = false;

            if (typeof alerta === 'function') {
                alerta(error.message || 'No se pudo completar la importacion.', false);
            }
        }
    });

    rowsContainer.addEventListener('input', function () {
        syncConfirmButton({ total: collectRows(rowsContainer).length, can_import: false, dirty: true });
    });

    rowsContainer.addEventListener('change', function () {
        syncConfirmButton({ total: collectRows(rowsContainer).length, can_import: false, dirty: true });
    });

    function renderPreview(preview) {
        currentToken = preview.token;
        renderSummary(preview.summary || {}, summarySection);
        renderRows(preview.rows || [], rowsContainer, config);
        summarySection.classList.remove('d-none');
        previewSection.classList.remove('d-none');
        syncConfirmButton({
            total: preview.summary ? preview.summary.total : 0,
            can_import: preview.can_import,
            dirty: !preview.can_import,
        });
    }

    function syncConfirmButton(state) {
        const total = state.total || 0;
        confirmButton.disabled = total === 0 || !currentToken;

        if (!confirmLabel) {
            return;
        }

        if (state.can_import) {
            confirmLabel.textContent = 'Importar lectores';
            return;
        }

        confirmLabel.textContent = state.dirty ? 'Validar cambios e importar' : 'Importar lectores';
    }
});

function renderSummary(summary, container) {
    container.querySelector('[data-summary="total"]').textContent = summary.total || 0;
    container.querySelector('[data-summary="validos"]').textContent = summary.validos || 0;
    container.querySelector('[data-summary="invalidos"]').textContent = summary.invalidos || 0;
    container.querySelector('[data-summary="estudiantes"]').textContent = summary.estudiantes || 0;
}

function renderRows(rows, container, config) {
    container.innerHTML = rows.map(function (row) {
        const statusClass = row.is_valid ? 'is-valid' : 'is-invalid';
        const statusText = row.is_valid ? 'Lista' : 'Observar';
        const errorsHtml = row.is_valid
            ? '<div class="reader-import__review is-valid">Sin observaciones</div>'
            : '<div class="reader-import__review"><ul>' + row.errors.map(function (error) {
                return '<li>' + escapeHtml(error) + '</li>';
            }).join('') + '</ul></div>';

        return `
            <tr data-excel-row="${escapeHtml(row.excel_row)}">
                <td>${escapeHtml(row.excel_row)}</td>
                <td><span class="reader-import__status ${statusClass}">${statusText}</span></td>
                <td>
                    ${renderField('tipo_persona', 'Tipo', row.data.tipo_persona || '', 'select', config.tiposPersona || [], 'reader-import__field--narrow')}
                    ${renderField('dni', 'DNI', row.data.dni || '', 'text', [], 'reader-import__field')}
                </td>
                <td>
                    ${renderField('nombres', 'Nombres', row.data.nombres || '', 'text', [], 'reader-import__field--wide')}
                    ${renderField('apellido_paterno', 'Apellido paterno', row.data.apellido_paterno || '', 'text', [], 'reader-import__field--wide')}
                    ${renderField('apellido_materno', 'Apellido materno', row.data.apellido_materno || '', 'text', [], 'reader-import__field--wide')}
                </td>
                <td>
                    ${renderField('email_personal', 'Correo', row.data.email_personal || '', 'email', [], 'reader-import__field--wide')}
                    ${renderField('telefono', 'Telefono', row.data.telefono || '', 'text', [], 'reader-import__field')}
                    ${renderField('direccion', 'Direccion', row.data.direccion || '', 'text', [], 'reader-import__field--wide')}
                </td>
                <td>
                    ${renderField('codigo_institucional', 'Codigo', row.data.codigo_institucional || '', 'text', [], 'reader-import__field')}
                    ${renderField('carrera', 'Carrera', row.data.carrera || '', 'select', config.carreras || [], 'reader-import__field--wide')}
                </td>
                <td>
                    ${renderField('password', 'Contrasena (opcional)', row.data.password || '', 'text', [], 'reader-import__field')}
                </td>
                <td>${errorsHtml}</td>
            </tr>
        `;
    }).join('');
}

function collectRows(container) {
    return Array.from(container.querySelectorAll('tr[data-excel-row]')).map(function (row) {
        const values = {
            excel_row: row.dataset.excelRow || '',
        };

        row.querySelectorAll('[data-field]').forEach(function (input) {
            values[input.dataset.field] = input.value || '';
        });

        return values;
    });
}

function renderField(name, label, value, type, options, extraClass) {
    const classes = ['reader-import__field'];
    if (extraClass) {
        classes.push(extraClass);
    }

    if (type === 'select') {
        return `
            <div class="${classes.join(' ')}">
                <label>${escapeHtml(label)}</label>
                <select class="form-select form-select-sm" data-field="${escapeHtml(name)}">
                    <option value=""></option>
                    ${renderOptions(options, value)}
                </select>
            </div>
        `;
    }

    return `
        <div class="${classes.join(' ')}">
            <label>${escapeHtml(label)}</label>
            <input type="${escapeHtml(type)}" class="form-control form-control-sm" data-field="${escapeHtml(name)}" value="${escapeHtml(value)}">
        </div>
    `;
}

function renderOptions(options, selectedValue) {
    return options.map(function (option) {
        const value = typeof option === 'string' ? option : option.value || option.nombre || '';
        const label = typeof option === 'string' ? option : option.label || option.nombre || option.value || '';
        const normalizedSelected = String(selectedValue || '').trim().toLowerCase();
        const selected = (
            String(value).trim().toLowerCase() === normalizedSelected
            || String(label).trim().toLowerCase() === normalizedSelected
        ) ? ' selected' : '';

        return `<option value="${escapeHtml(value)}"${selected}>${escapeHtml(label)}</option>`;
    }).join('');
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
