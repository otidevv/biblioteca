let tablaActividades;
let tablaCategoriasActividad;

$(document).ready(function () {
    tablaActividades = $('#tabla-actividades').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [],
        ajax: {
            url: '/api/actividades/listar',
            type: 'GET',
            xhrFields: { withCredentials: true },
            error: default_error_handler
        },
        columns: [
            { data: 'imagen_preview', name: 'imagen', orderable: false, searchable: false },
            { data: 'titulo', name: 'titulo' },
            { data: 'categoria_nombre', name: 'categoria.nombre', defaultContent: '-' },
            {
                data: null,
                name: 'fecha_inicio',
                render: function (data, type, row) {
                    const inicio = formatearFechaActividad(row.fecha_inicio);
                    const fin = formatearFechaActividad(row.fecha_fin ?? row.fecha_inicio);
                    return inicio === fin ? inicio : `${inicio} al ${fin}`;
                }
            },
            { data: 'modalidad', name: 'modalidad', defaultContent: '-' },
            { data: 'destacado_badge', name: 'destacado', orderable: false, searchable: false },
            { data: 'estado_badge', name: 'estado', orderable: false, searchable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-actividades');
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-actividades');
        }
    });

    tablaCategoriasActividad = $('#tabla-categorias-actividad').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        order: [],
        ajax: {
            url: '/api/actividades/categorias/listar',
            type: 'GET',
            xhrFields: { withCredentials: true },
            error: default_error_handler
        },
        columns: [
            { data: 'abreviatura', name: 'abreviatura' },
            { data: 'nombre', name: 'nombre' },
            { data: 'descripcion', name: 'descripcion', defaultContent: '-' },
            { data: 'actividades_count', name: 'actividades_count', searchable: false },
            { data: 'estado_badge', name: 'estado', orderable: false, searchable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-categorias-actividad');
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-categorias-actividad');
        }
    });

    $('#btnNuevaActividad').on('click', function () {
        resetActividadForm();
        $('#modalActividad').modal('show');
    });

    $('#btnNuevaCategoriaActividad').on('click', function () {
        resetCategoriaActividadForm();
        $('#modalCategoriaActividad').modal('show');
    });

    $('#tabla-actividades').on('click', '.editarActividad', function () {
        let data = tablaActividades.row($(this).closest('tr')).data();
        if (!data) return;

        resetActividadForm();
        $('#id').val(data.id);
        $('#actividad_categoria_id').val(data.actividad_categoria_id ?? '');
        $('#fecha_inicio').val(normalizarFecha(data.fecha_inicio));
        $('#fecha_fin').val(normalizarFecha(data.fecha_fin));
        $('#hora_inicio').val(normalizarHora(data.hora_inicio));
        $('#hora_fin').val(normalizarHora(data.hora_fin));
        $('#titulo').val(data.titulo ?? '');
        $('#resumen').val(data.resumen ?? '');
        $('#contenido').val(data.contenido ?? '');
        $('#referencia').val(data.referencia ?? '');
        $('#lugar').val(data.lugar ?? '');
        $('#modalidad').val(data.modalidad ?? '');
        $('#destacado').val(Number(data.destacado) === 1 ? '1' : '0');
        $('#estado').val(Number(data.estado) === 1 ? '1' : '0');

        $('#modalActividad').modal('show');
    });

    $('#tabla-categorias-actividad').on('click', '.editarCategoriaActividad', function () {
        let data = tablaCategoriasActividad.row($(this).closest('tr')).data();
        if (!data) return;

        resetCategoriaActividadForm();
        $('#categoria_id').val(data.id);
        $('#abreviatura').val(data.abreviatura ?? '');
        $('#nombre').val(data.nombre ?? '');
        $('#descripcion').val(data.descripcion ?? '');
        $('#categoria_estado').val(Number(data.estado) === 1 ? '1' : '0');
        $('#modalCategoriaActividad').modal('show');
    });

    $('#formActividad').on('submit', function (e) {
        e.preventDefault();

        if (!validar('#formActividad')) {
            return;
        }

        let formData = new FormData(this);
        let url = $('#id').val() === '' ? '/api/actividades/nuevo' : '/api/actividades/edit';

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#modalActividad').modal('hide');
                tablaActividades.ajax.reload(null, false);
                alerta(response.message, true);
            },
            error: function (xhr) {
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    $.each(xhr.responseJSON.errors, function (campo, mensajes) {
                        let input = $('[name="' + campo + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + mensajes[0] + '</div>');
                    });
                    return;
                }

                alerta(xhr.responseJSON?.message || 'No se pudo guardar la actividad.', false);
            }
        });
    });

    $('#formCategoriaActividad').on('submit', function (e) {
        e.preventDefault();

        if (!validar('#formCategoriaActividad')) {
            return;
        }

        let formData = new FormData(this);
        let url = $('#categoria_id').val() === '' ? '/api/actividades/categorias/nuevo' : '/api/actividades/categorias/edit';

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#modalCategoriaActividad').modal('hide');
                tablaCategoriasActividad.ajax.reload(null, false);
                recargarOpcionesCategorias();
                alerta(response.message, true);
            },
            error: function (xhr) {
                $('#formCategoriaActividad .invalid-feedback').remove();
                $('#formCategoriaActividad .is-invalid').removeClass('is-invalid');

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    $.each(xhr.responseJSON.errors, function (campo, mensajes) {
                        let input = $('#formCategoriaActividad').find('[name="' + campo + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + mensajes[0] + '</div>');
                    });
                    return;
                }

                alerta(xhr.responseJSON?.message || 'No se pudo guardar la categoria.', false);
            }
        });
    });
});

function resetActividadForm() {
    $('#formActividad')[0].reset();
    $('#id').val('');
    $('#destacado').val('0');
    $('#estado').val('1');
    $('.invalid-feedback').remove();
    $('.is-invalid').removeClass('is-invalid');
}

function resetCategoriaActividadForm() {
    $('#formCategoriaActividad')[0].reset();
    $('#categoria_id').val('');
    $('#categoria_estado').val('1');
    $('#formCategoriaActividad .invalid-feedback').remove();
    $('#formCategoriaActividad .is-invalid').removeClass('is-invalid');
}

function recargarOpcionesCategorias() {
    $.ajax({
        url: '/api/actividades/categorias/listar',
        type: 'GET',
        data: { start: 0, length: 200 },
        success: function (response) {
            const opciones = response.data || [];
            const valorActual = $('#actividad_categoria_id').val();
            const $select = $('#actividad_categoria_id');

            $select.find('option:not(:first)').remove();

            opciones
                .filter(categoria => Number(categoria.estado) === 1)
                .forEach(categoria => {
                    $select.append(`<option value="${categoria.id}">${categoria.nombre}</option>`);
                });

            if (valorActual) {
                $select.val(valorActual);
            }
        }
    });
}

function normalizarFecha(value) {
    return value ? String(value).slice(0, 10) : '';
}

function normalizarHora(value) {
    return value ? String(value).slice(0, 5) : '';
}

function formatearFechaActividad(value) {
    if (!value) {
        return '-';
    }

    const texto = String(value);
    if (/^\d{4}-\d{2}-\d{2}/.test(texto)) {
        const [anio, mes, dia] = texto.slice(0, 10).split('-');
        return `${dia}/${mes}/${anio}`;
    }

    return texto;
}
