let tablaNotificaciones;
let recursosNotificaciones = { usuarios: [], actividades: [] };
let destinatariosSeleccionados = [];

$(document).ready(function () {
    cargarRecursosFormulario();
    inicializarSelectDestinatarios();

    tablaNotificaciones = $('#tabla-notificaciones').DataTable({
        processing: true,
        serverSide: true,
        ajax: '/api/notificaciones/listar',
        columns: [
            { data: 'titulo', name: 'titulo' },
            { data: 'tipo_badge', name: 'tipo', orderable: false, searchable: false },
            { data: 'audiencia_badge', name: 'audiencia', orderable: false, searchable: false },
            { data: 'destino', name: 'destino', orderable: false, searchable: false },
            { data: 'fecha_publicacion_texto', name: 'fecha_publicacion' },
            { data: 'fecha_expiracion_texto', name: 'fecha_expiracion' },
            { data: 'estado_badge', name: 'estado', orderable: false, searchable: false },
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

    $('#btnNuevaNotificacion').on('click', function () {
        resetFormNotificaciones();
        $('#modalNotificacion').modal('show');
    });

    $('#audiencia').on('change', actualizarVisibilidadNotificacion);
    $('#tipo').on('change', actualizarVisibilidadNotificacion);

    $('#user_ids').on('change', function () {
        destinatariosSeleccionados = ($(this).val() || []).map(String);
        $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
    });

    $('#modalNotificacion').on('shown.bs.modal', function () {
        inicializarSelectDestinatarios();
    });

    $(document).on('click', '.editarNotificacion', function () {
        const data = tablaNotificaciones.row($(this).closest('tr')).data();
        if (!data) return;

        resetFormNotificaciones();
        $('#id').val(data.id);
        $('#titulo').val(data.titulo);
        $('#contenido').val(data.contenido);
        $('#tipo').val(data.tipo);
        $('#audiencia').val(data.audiencia);
        $('#accion_url').val(data.accion_url || '');
        $('#estado').val(data.estado);

        if (data.fecha_publicacion) {
            $('#fecha_publicacion').val(normalizarFechaInput(data.fecha_publicacion));
        }

        if (data.fecha_expiracion) {
            $('#fecha_expiracion').val(normalizarFechaInput(data.fecha_expiracion));
        }

        if (Array.isArray(data.destinatarios)) {
            destinatariosSeleccionados = data.destinatarios.map(item => String(item.user_id));
        }

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
        const url = esEdicion ? '/api/notificaciones/edit' : '/api/notificaciones/nuevo';

        $.ajax({
            url: url,
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
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');
                $('#user_ids').next('.select2-container').find('.select2-selection').removeClass('is-invalid');

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    $.each(xhr.responseJSON.errors, function (campo, mensajes) {
                        let input = $('[name="' + campo + '"]');
                        if (campo.includes('.')) {
                            input = $('[name="' + campo.split('.')[0] + '[]"]');
                        }
                        input.addClass('is-invalid');
                        if (input.attr('id') === 'user_ids' && input.next('.select2-container').length) {
                            input.next('.select2-container').find('.select2-selection').addClass('is-invalid');
                            input.next('.select2-container').after('<div class="invalid-feedback d-block">' + mensajes[0] + '</div>');
                        } else {
                            input.after('<div class="invalid-feedback">' + mensajes[0] + '</div>');
                        }
                    });
                    return;
                }

                alerta(xhr.responseJSON?.message || 'No se pudo guardar la notificacion.', false);
            }
        });
    });
});

function cargarRecursosFormulario() {
    $.get('/api/notificaciones/recursos', function (response) {
        recursosNotificaciones = response;

        const actividades = response.actividades || [];
        const $actividades = $('#actividad_id');

        reconstruirOpcionesUsuarios();
        $actividades.find('option:not(:first)').remove();

        actividades.forEach(actividad => {
            $actividades.append(`<option value="${actividad.id}">${actividad.titulo}</option>`);
        });
    });
}

function actualizarVisibilidadNotificacion() {
    const audiencia = $('#audiencia').val();
    const tipo = $('#tipo').val();

    $('#grupoDestinatarios').toggleClass('d-none', audiencia !== 'personal');
    $('#grupoActividad').toggleClass('d-none', tipo !== 'actividad');

    if (audiencia === 'personal') {
        reconstruirOpcionesUsuarios();
        inicializarSelectDestinatarios();
    }
}

function resetFormNotificaciones() {
    $('#formNotificacion')[0].reset();
    $('#id').val('');
    destinatariosSeleccionados = [];
    $('#user_ids').val([]).trigger('change');
    $('#actividad_id').val('');
    $('.invalid-feedback').remove();
    $('.is-invalid').removeClass('is-invalid');
    $('#user_ids').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
    actualizarVisibilidadNotificacion();
}

function normalizarFechaInput(value) {
    return String(value).replace(' ', 'T').slice(0, 16);
}

function inicializarSelectDestinatarios() {
    const $usuarios = $('#user_ids');

    if (!$usuarios.length || typeof $usuarios.select2 !== 'function') {
        return;
    }

    if ($usuarios.hasClass('select2-hidden-accessible')) {
        $usuarios.select2('destroy');
    }

    $usuarios.select2({
        dropdownParent: $('#modalNotificacion'),
        width: '100%',
        language: 'es',
        closeOnSelect: false,
        allowClear: true,
        placeholder: $usuarios.data('placeholder') || 'Buscar usuarios por nombre, correo o rol'
    });
}

function reconstruirOpcionesUsuarios() {
    const usuarios = recursosNotificaciones.usuarios || [];
    const $usuarios = $('#user_ids');
    const seleccionados = destinatariosSeleccionados.length
        ? destinatariosSeleccionados
        : (($usuarios.val() || []).map(String));
    const grupos = {
        personal: [],
        lectores: [],
        otros: []
    };

    $usuarios.empty();

    usuarios.forEach(usuario => {
        const roles = obtenerRolesUsuario(usuario);
        const etiquetaRol = roles.length ? roles.join(', ') : (usuario.tipo_usuario || 'Sin rol definido');
        const texto = `${usuario.name} - ${usuario.email} | ${etiquetaRol}`;
        const opcion = `<option value="${usuario.id}">${escapeHtml(texto)}</option>`;

        if (roles.includes('LECTOR') || String(usuario.tipo_usuario || '').toLowerCase() === 'lector') {
            grupos.lectores.push(opcion);
        } else if (roles.some(rol => ['PROGRAMADOR', 'ADMINISTRADOR', 'ENCARGADO', 'ATENCION A ESTUDIANTES'].includes(rol))) {
            grupos.personal.push(opcion);
        } else {
            grupos.otros.push(opcion);
        }
    });

    agregarGrupoOpciones($usuarios, 'Personal interno', grupos.personal);
    agregarGrupoOpciones($usuarios, 'Lectores', grupos.lectores);
    agregarGrupoOpciones($usuarios, 'Otros usuarios', grupos.otros);

    $usuarios.val(seleccionados).trigger('change');
}

function agregarGrupoOpciones($select, etiqueta, opciones) {
    if (!opciones.length) return;

    $select.append(`<optgroup label="${escapeHtml(etiqueta)}">${opciones.join('')}</optgroup>`);
}

function obtenerRolesUsuario(usuario) {
    return Array.isArray(usuario.roles)
        ? usuario.roles
            .map(rol => String(rol.nombre || '').trim().toUpperCase())
            .filter(Boolean)
        : [];
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
