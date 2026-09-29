let tablaNotificaciones;

const ICONOS_TIPO = {
    aviso: 'bi-bell-fill',
    actividad: 'bi-calendar-event-fill',
    recordatorio: 'bi-clock-history',
    personal: 'bi-person-badge-fill',
    critico: 'bi-exclamation-triangle-fill',
    disponibilidad_libro: 'bi-book-half'
};

const AYUDA_AUDIENCIA = {
    admins: 'Todo el personal con un rol activo distinto de lector (incluye bibliotecas especializadas).',
    lectores: 'Todos los usuarios con rol de lector activo.',
    personal: 'Solo las personas que elijas abajo. Verás cuántas la leyeron.',
    global: 'Todas las personas que inicien sesión.'
};

$(document).ready(function () {
    cargarActividades();
    inicializarSelectDestinatarios();

    tablaNotificaciones = $('#tabla-notificaciones').DataTable({
        processing: true,
        serverSide: true,
        order: [],
        ajax: {
            url: '/api/notificaciones/listar',
            error: typeof default_error_handler === 'function' ? default_error_handler : undefined
        },
        columns: [
            { data: 'titulo', name: 'titulo' },
            { data: 'tipo_badge', name: 'tipo', orderable: false, searchable: false },
            { data: 'audiencia_badge', name: 'audiencia', orderable: false, searchable: false },
            { data: 'destino', name: 'destino', orderable: false, searchable: false },
            { data: 'fecha_publicacion_texto', name: 'fecha_publicacion' },
            { data: 'fecha_expiracion_texto', name: 'fecha_expiracion' },
            { data: 'vigencia', name: 'estado', orderable: false, searchable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-notificaciones');
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-notificaciones');
        }
    });

    // Indicadores del encabezado: vienen en la misma respuesta de la tabla.
    $('#tabla-notificaciones').on('xhr.dt', function (e, settings, json) {
        if (!json || !json.resumen) return;
        Object.entries(json.resumen).forEach(function ([clave, valor]) {
            $('[data-resumen="' + clave + '"]').text(valor);
        });
    });

    $('#btnNuevaNotificacion').on('click', function () {
        resetFormNotificaciones();
        $('#modalNotificacion').modal('show');
    });

    $('#audiencia, #tipo').on('change', actualizarVisibilidadNotificacion);
    $('#titulo, #contenido, #accion_url, #tipo').on('input change', actualizarVistaPrevia);

    $('#user_ids').on('change', function () {
        $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
    });

    $(document).on('click', '.editarNotificacion', function () {
        const data = tablaNotificaciones.row($(this).closest('tr')).data();
        if (!data) return;

        resetFormNotificaciones();
        $('#id').val(data.id);
        $('#titulo').val(decodificarHtml(data.titulo));
        $('#contenido').val(decodificarHtml(data.contenido));
        $('#tipo').val(data.tipo);
        $('#audiencia').val(data.audiencia);
        $('#accion_url').val(decodificarHtml(data.accion_url || ''));
        $('#estado').val(String(Number(data.estado)));
        // Hora local (el JSON del modelo viene en UTC).
        $('#fecha_publicacion').val(data.fecha_publicacion_input || '');
        $('#fecha_expiracion').val(data.fecha_expiracion_input || '');

        // Destinatarios actuales: se agregan como opciones seleccionadas.
        const $usuarios = $('#user_ids');
        (data.destinatarios_opciones || []).forEach(function (opcion) {
            $usuarios.append(new Option(decodificarHtml(opcion.text), opcion.id, true, true));
        });
        $usuarios.trigger('change');

        if (data.entidad_tipo === 'actividad' && data.entidad_id) {
            $('#actividad_id').val(String(data.entidad_id));
        }

        actualizarVisibilidadNotificacion();
        $('#modalNotificacion').modal('show');
    });

    $('#formNotificacion').on('submit', function (e) {
        e.preventDefault();

        if (!validar('#formNotificacion')) {
            return;
        }

        const esEdicion = $('#id').val() !== '';
        const $boton = $(this).find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: esEdicion ? '/api/notificaciones/edit' : '/api/notificaciones/nuevo',
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#modalNotificacion').modal('hide');
                tablaNotificaciones.ajax.reload(null, false);
                alerta(response.message, true);
            },
            error: function (xhr) {
                limpiarErrores();

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    $.each(xhr.responseJSON.errors, function (campo, mensajes) {
                        let input = $('[name="' + campo + '"]');
                        if (campo.includes('.') || !input.length) {
                            input = $('[name="' + campo.split('.')[0] + '[]"]');
                        }
                        const $error = $('<div class="invalid-feedback d-block">').text(mensajes[0]);
                        input.addClass('is-invalid');

                        if (input.attr('id') === 'user_ids' && input.next('.select2-container').length) {
                            input.next('.select2-container').find('.select2-selection').addClass('is-invalid');
                            input.next('.select2-container').after($error);
                        } else {
                            input.after($error);
                        }
                    });
                    return;
                }

                alerta(xhr.responseJSON?.message || 'No se pudo guardar la notificación.', false);
            },
            complete: function () {
                $boton.prop('disabled', false);
            }
        });
    });
});

function cargarActividades() {
    $.get('/api/notificaciones/recursos', function (response) {
        const $actividades = $('#actividad_id');
        $actividades.find('option:not(:first)').remove();

        (response.actividades || []).forEach(function (actividad) {
            // Texto, nunca HTML: el titulo lo escribe un usuario.
            $actividades.append(new Option(actividad.titulo, actividad.id));
        });
    });
}

// Destinatarios: busqueda en el servidor (nombre, correo o DNI), agrupada por tipo de usuario.
function inicializarSelectDestinatarios() {
    const $usuarios = $('#user_ids');

    if (!$usuarios.length || typeof $usuarios.select2 !== 'function' || $usuarios.hasClass('select2-hidden-accessible')) {
        return;
    }

    $usuarios.select2({
        dropdownParent: $('#modalNotificacion'),
        width: '100%',
        language: 'es',
        closeOnSelect: false,
        allowClear: true,
        minimumInputLength: 2,
        placeholder: $usuarios.data('placeholder') || 'Buscar usuarios por nombre, correo o DNI',
        ajax: {
            url: '/api/notificaciones/usuarios',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term || '' };
            },
            processResults: function (data) {
                return { results: data.results || [] };
            }
        }
    });
}

function actualizarVisibilidadNotificacion() {
    const audiencia = $('#audiencia').val();
    const tipo = $('#tipo').val();

    $('#grupoDestinatarios').toggleClass('d-none', audiencia !== 'personal');
    $('#grupoActividad').toggleClass('d-none', tipo !== 'actividad');
    $('#ayudaAudiencia').text(AYUDA_AUDIENCIA[audiencia] || '');
    actualizarVistaPrevia();
}

// Vista previa de como se vera en la campana.
function actualizarVistaPrevia() {
    const tipo = $('#tipo').val();
    const titulo = ($('#titulo').val() || '').trim();
    const contenido = ($('#contenido').val() || '').trim();
    const enlace = ($('#accion_url').val() || '').trim();

    $('#previewIcono').attr('class', 'bi ' + (ICONOS_TIPO[tipo] || 'bi-bell-fill'));
    $('#previewTarjeta').toggleClass('is-critical', tipo === 'critico');
    $('#previewTitulo').text(titulo || 'Título de la notificación');
    $('#previewContenido').text(contenido || 'Aquí aparecerá el mensaje que verá el usuario.');
    $('#previewEnlace').toggleClass('d-none', enlace === '').text(enlace ? 'Abrir: ' + enlace : '');
}

function resetFormNotificaciones() {
    $('#formNotificacion')[0].reset();
    $('#id').val('');
    $('#user_ids').empty().val(null).trigger('change');
    $('#actividad_id').val('');
    limpiarErrores();
    actualizarVisibilidadNotificacion();
}

function limpiarErrores() {
    $('#formNotificacion .invalid-feedback').remove();
    $('#formNotificacion .is-invalid').removeClass('is-invalid');
    $('#user_ids').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
}

// DataTables envia los textos escapados; se decodifican para editarlos como texto.
function decodificarHtml(valor) {
    return new DOMParser().parseFromString(String(valor || ''), 'text/html').documentElement.textContent;
}
