$(document).ready(function () {
    const libroActual = libro || null;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const $formLibro = $('#formLibro');
    const $submitLibro = $formLibro.find('button[type="submit"]');
    const $deweyHint = $('#deweySuggestionHint');
    const $codigoHint = $('#codigoLibroHint');

    $('.select2').select2({
        width: '100%'
    });

    $('#modalEditorial').on('shown.bs.modal', function () {
        $('#ed_pais').select2({
            dropdownParent: $('#modalEditorial'),
            width: '100%'
        });
    });

    $('#modalAutor').on('shown.bs.modal', function () {
        $('#au_pais').select2({
            dropdownParent: $('#modalAutor'),
            width: '100%'
        });
    });

    $('#codigo_dewey').select2({
        placeholder: 'Seleccione codigo Dewey',
        allowClear: true,
        ajax: {
            url: '/api/inventario/dewey/buscar',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                const titulo = ($('#titulo').val() || '').trim();
                return { q: params.term || titulo };
            },
            processResults: function (data) {
                return {
                    results: (data || []).map(function (item) {
                        return {
                            id: item.codigo,
                            text: item.codigo + ' - ' + item.nombre
                        };
                    })
                };
            },
            cache: true
        }
    });

    if (codigoDewey) {
        const option = new Option(textoDewey || codigoDewey, codigoDewey, true, true);
        $('#codigo_dewey').append(option).trigger('change');
    }

    $('#titulo').on('blur', function () {
        const titulo = ($(this).val() || '').trim();

        if (!titulo) {
            return;
        }

        $.ajax({
            url: '/api/inventario/libros/sugerir-dewey',
            type: 'GET',
            data: { titulo: titulo },
            success: function (response) {
                $('#codigo_dewey').empty().trigger('change');
                const option = new Option(response.codigo + ' - ' + response.nombre, response.codigo, true, true);
                $('#codigo_dewey').append(option);
                $deweyHint.text('Sugerencia automatica basada en el titulo. Coincidencias: ' + (response.coincidencias || []).join(', '));
                actualizarCodigoLibro();

                $('#codigo_dewey').trigger('change');
            },
            error: function () {
                $deweyHint.text('No se encontro una sugerencia automatica confiable. Puedes elegir la clasificacion manualmente.');
            }
        });
    });

    $('#codigo_dewey').on('change', function () {
        const data = $(this).select2('data');
        const item = Array.isArray(data) && data.length ? data[0] : null;
        $deweyHint.text(item && item.text ? 'Clasificacion seleccionada: ' + item.text : '');
        actualizarCodigoLibro();
    });

    $('#editorial_id').select2({
        placeholder: 'Buscar editorial',
        allowClear: true,
        ajax: {
            url: '/api/inventario/editoriales',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: (data || []).map(function (item) {
                        return {
                            id: item.id,
                            text: item.nombre
                        };
                    })
                };
            }
        }
    });

    if (libroActual && libroActual.editorial) {
        const editorial = libroActual.editorial;
        const option = new Option(editorial.nombre, editorial.id, true, true);
        $('#editorial_id').append(option).trigger('change');
    }

    $('#autor_id').select2({
        placeholder: 'Seleccione autor(es)',
        width: '100%',
        ajax: {
            url: '/api/inventario/autores',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: (data || []).map(item => ({
                    id: item.id,
                    text: item.text,
                    apellido: item.apellidos,
                    nombre: item.nombres
                }))
            })
        }
    });

    if (Array.isArray(autores) && autores.length > 0) {
        const ids = [];

        autores.forEach(function (item) {
            const texto = (item.nombres || '').trim() + ' ' + (item.apellidos || '').trim();
            const option = new Option(texto.trim(), item.id, true, true);
            $('#autor_id').append(option);
            ids.push(item.id);
        });

        $('#autor_id').val(ids).trigger('change');
    }

    $('#materias').select2({
        placeholder: 'Seleccione materia(s)',
        width: '100%',
        multiple: true,
        ajax: {
            url: '/api/inventario/materias',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: (data || []).map(item => ({
                    id: item.id,
                    text: item.nombre
                }))
            })
        }
    });

    if (libroActual && Array.isArray(libroActual.materias) && libroActual.materias.length > 0) {
        const valores = [];

        libroActual.materias.forEach(function (item) {
            const option = new Option(item.nombre, item.id, true, true);
            $('#materias').append(option);
            valores.push(item.id);
        });

        $('#materias').val(valores).trigger('change');
    }

    $('#autor_id').on('change', function () {
        actualizarCodigoLibro();
    });

    $('#titulo, input[name="edicion"]').on('blur', function () {
        actualizarCodigoLibro();
    });

    $('#formEditorial').on('submit', function (e) {
        e.preventDefault();

        if (!validar('#formEditorial')) {
            return;
        }

        const form = $(this);
        const formData = new FormData(this);
        const btn = form.find('button[type="submit"]');

        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: '/api/editoriales/nuevo',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                if (!response.success) {
                    alerta(response.message || 'Error al guardar la editorial', false);
                    return;
                }

                if (response.data) {
                    const option = new Option(response.data.nombre, response.data.id, true, true);
                    $('#editorial_id').append(option).trigger('change');
                }

                alerta('Editorial guardada correctamente', true);
                form[0].reset();
                $('#modalEditorial').modal('hide');
            },
            error: function (xhr) {
                alerta(xhr.responseJSON?.message || 'Error al guardar la editorial', false);
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

    $('#formAutor').on('submit', function (e) {
        e.preventDefault();

        if (!validar('#formAutor')) {
            return;
        }

        const form = $(this);
        const formData = new FormData(this);
        const btn = form.find('button[type="submit"]');

        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: '/api/autores/nuevo',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                if (!response.success) {
                    alerta(response.message || 'Error al guardar el autor', false);
                    return;
                }

                if (response.data) {
                    const texto = ((response.data.nombres || '') + ' ' + (response.data.apellidos || '')).trim();
                    const option = new Option(texto, response.data.id, true, true);
                    $('#autor_id').append(option).trigger('change');
                }

                alerta('Autor guardado correctamente', true);
                form[0].reset();
                $('#modalAutor').modal('hide');
            },
            error: function (xhr) {
                alerta(xhr.responseJSON?.message || 'Error al guardar el autor', false);
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

    $('#imagen').on('change', function (e) {
        const file = e.target.files[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            $('#previewImagen').attr('src', event.target.result);
        };
        reader.readAsDataURL(file);
    });

    $('#archivo_indice').on('change', function () {
        const file = this.files[0];
        $('#nombrePdf').text(file ? file.name : '');
    });

    $formLibro.on('submit', function (e) {
        e.preventDefault();

        const $selectGroups = $formLibro.find('.form-group').has('select');
        $selectGroups.removeClass('form-required');

        const formularioBaseValido = validar('#formLibro');

        $selectGroups.addClass('form-required');

        if (!formularioBaseValido || !validarSelectsLibro()) {
            return;
        }

        const formData = new FormData(this);

        $submitLibro.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: libroActual && libroActual.id ? '/api/inventario/libros/actualizar' : '/api/inventario/libros/guardar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () {
                alerta('Libro guardado correctamente', true);

                if (!libroActual) {
                    $formLibro[0].reset();
                    $('#autor_id, #editorial_id, #materias, #codigo_dewey').val(null).trigger('change');
                    $('#nombrePdf').text('');
                }
            },
            error: function (xhr) {
                alerta(xhr.responseJSON?.message || 'Error al guardar el libro', false);
            },
            complete: function () {
                $submitLibro.prop('disabled', false).text('Guardar libro');
            }
        });
    });

    $('#btnNuevaEditorial').on('click', function () {
        $('#modalEditorial').modal('show');
    });

    function actualizarCodigoLibro() {
        const titulo = ($('#titulo').val() || '').trim();
        const codigoDeweySeleccionado = $('#codigo_dewey').val();
        const autoresSeleccionados = $('#autor_id').val() || [];

        if (!titulo || !codigoDeweySeleccionado || !autoresSeleccionados.length) {
            return;
        }

        $.ajax({
            url: '/api/inventario/libros/generar-codigo',
            type: 'GET',
            data: {
                id: $('#id').val() || '',
                titulo: titulo,
                codigo_dewey: codigoDeweySeleccionado,
                autor_id: autoresSeleccionados,
                edicion: $('input[name="edicion"]').val() || ''
            },
            success: function (response) {
                $('input[name="codigo"]').val(response.codigo);

                const detalle = response.detalle || {};
                const partes = [
                    detalle.cutter ? 'Cutter: ' + detalle.cutter : null,
                    detalle.prefijo_autor ? 'Autor: ' + detalle.prefijo_autor : null,
                    detalle.prefijo_edicion ? 'Edicion: ' + detalle.prefijo_edicion : null,
                    detalle.prefijo_titulo ? 'Titulo: ' + detalle.prefijo_titulo : null
                ].filter(Boolean);

                $codigoHint.text(partes.length ? 'Codigo generado automaticamente. ' + partes.join(' | ') : 'Codigo generado automaticamente.');
            },
            error: function () {
                $codigoHint.text('No fue posible generar el codigo automaticamente con los datos actuales.');
            }
        });
    }

    function validarSelectsLibro() {
        let valido = true;

        [
            '#autor_id',
            '#editorial_id',
            'select[name="idioma"]',
            'select[name="tipo_registro_id"]',
            '#codigo_dewey'
        ].forEach(function (selector) {
            const $select = $(selector);
            const $group = $select.closest('.form-group');
            const valor = $select.val();
            const estaVacio = Array.isArray(valor)
                ? valor.length === 0
                : !valor || valor === '0';

            $select.removeClass('is-invalid');
            $group.find('.invalid-feedback').remove();
            $group.find('.select2-selection').removeClass('is-invalid');

            if (estaVacio) {
                valido = false;
                $select.addClass('is-invalid');
                $group.find('.select2-selection').addClass('is-invalid');
                $group.append('<div class="invalid-feedback">Este campo es obligatorio</div>');
            }
        });

        return valido;
    }
});
