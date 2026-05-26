let tabla;

$(document).ready(function () {
    tabla = $('#tabla-usuarios').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        pageLength: 50,
        order: [],
        ajax: {
            url: '/api/usuarios/listar',
            type: 'GET',
            xhrFields: { withCredentials: true },
            data: function (d) {
                d.tipo_usuario = $('#tipo_usuario').val();
            },
            error: default_error_handler
        },
        columns: [
            {
                data: 'name',
                name: 'name',
                render: function (data, type, row) {
                    const words = (data || '').trim().split(/\s+/);
                    const initials = words.slice(0, 2).map(w => w[0] || '').join('').toUpperCase();
                    const palette = ['#0f766e','#2563eb','#7c3aed','#ea580c','#16a34a','#b45309','#0369a1'];
                    const color = palette[(data.charCodeAt(0) || 0) % palette.length];
                    return `
                        <div class="user-table-identity">
                            <div class="user-table-avatar" style="background:${color}">${initials}</div>
                            <div class="user-table-info">
                                <span class="user-table-name">${data}</span>
                                <span class="user-table-email">${row.email || ''}</span>
                            </div>
                        </div>`;
                }
            },
            {
                data: 'roles',
                name: 'rol',
                render: function (data) {
                    if (!Array.isArray(data) || data.length === 0) {
                        return '<span class="user-role-pill user-role-pill--none">Sin rol</span>';
                    }
                    const roleStyles = {
                        'programador':          'violet',
                        'administrador':        'red',
                        'encargado':            'blue',
                        'atencion a estudiantes':'green',
                        'lector':               'cyan',
                    };
                    return data.map(rol => {
                        const key = (rol.nombre || '').toLowerCase();
                        const variant = roleStyles[key] || 'gray';
                        return `<span class="user-role-pill user-role-pill--${variant}">${rol.nombre}</span>`;
                    }).join('');
                }
            },
            {
                data: 'estado',
                name: 'estado',
                orderable: false,
                searchable: false,
                render: function (data) {
                    return parseInt(data) === 1
                        ? '<span class="user-status-pill user-status-pill--active"><i class="bi bi-check-circle-fill"></i> Activo</span>'
                        : '<span class="user-status-pill user-status-pill--inactive"><i class="bi bi-x-circle-fill"></i> Inactivo</span>';
                }
            },
            {
                data: 'acciones',
                name: 'acciones',
                orderable: false,
                searchable: false
            }
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
            aplicarIconosAccionesUsuario();
        },
        drawCallback: function () {
            aplicarIconosAccionesUsuario();
        }
    });

    $('#tipo_usuario').on('change', function () {
        tabla.ajax.reload();
    });

    $('#btnNuevo').on('click', function () {
        $('#formUsuario')[0].reset();
        $('input[name="roles[]"]').prop('checked', false);
        $('#id').val('');
        modoNuevoUsuario();
        $('#div_credenciales').show();
        $('.password-group').show();
        $('#modalUsuario').modal('show');
    });

    $('#tabla-usuarios').on('click', '.editarUsuario', function () {
        let data = tabla.row($(this).closest('tr')).data();

        $('input[name="roles[]"]').prop('checked', false);
        $('#dni').val(data.persona?.dni ?? '');
        $('#nombres').val(data.persona?.nombres ?? '');
        $('#apellido_paterno').val(data.persona?.apellido_paterno ?? '');
        $('#apellido_materno').val(data.persona?.apellido_materno ?? '');
        $('#sexo').val(data.persona?.sexo ?? '');
        $('#telefono').val(data.persona?.telefono ?? '');
        $('#id').val(data.id);
        $('#biblioteca').val(data.roles?.[0]?.pivot?.biblioteca_id ?? '');
        $('#direccion').val(data.persona?.direccion ?? '');
        $('#correo').val(data.email ?? '');

        if (data.roles && Array.isArray(data.roles)) {
            data.roles.forEach(function (rol) {
                $('#rol_' + rol.id).prop('checked', true);
            });
        }

        modoEdicionUsuario();
        $('#div_credenciales').hide();
        $('#modalUsuario').modal('show');
    });

    $('#tabla-usuarios').on('click', '.cambiarContrasena', function () {
        let data = tabla.row($(this).closest('tr')).data();

        $('#p_apodo').val(data.email ?? '');
        $('#password_user_id').val(data.id ?? '');
        $('#modalContrasena').modal('show');
    });

    $(document).on('click', '.toggle-password', function () {
        let target = $('#' + $(this).data('target'));
        let type = target.attr('type') === 'password' ? 'text' : 'password';
        target.attr('type', type);
    });

    $('#pchange').on('input', function () {
        let val = $(this).val();
        let strength = $('#password-strength');

        if (val.length < 8) {
            strength.text('Debil').removeClass().addClass('text-danger');
        } else if (val.match(/[A-Z]/) && val.match(/[0-9]/)) {
            strength.text('Fuerte').removeClass().addClass('text-success');
        } else {
            strength.text('Media').removeClass().addClass('text-warning');
        }
    });

    $('#pchange_confirmed').on('input', function () {
        let match = $(this).val() === $('#pchange').val();
        let status = $('#password-match-status span');

        if (match) {
            status.text('Las contrasenas coinciden').removeClass().addClass('text-success');
        } else {
            status.text('No coinciden').removeClass().addClass('text-danger');
        }
    });

    $('#modalContrasena').on('shown.bs.modal', function () {
        $('#pchange').val('');
        $('#pchange_confirmed').val('');
        $('#pchange, #pchange_confirmed').attr('type', 'password');
        $('#password-strength')
            .text('Usa al menos 8 caracteres')
            .removeClass()
            .addClass('text-muted small');
        $('#password-match-status span')
            .text('Las contrasenas deben coincidir')
            .removeClass()
            .addClass('text-muted small');
        $('#pchange, #pchange_confirmed').removeClass('is-invalid is-valid');
    });

    $('#modalContrasena').on('hidden.bs.modal', function () {
        $('#formContrasena')[0].reset();
        $('#password_user_id').val('');
    });

    $('#formUsuario').on('submit', function (e) {
        e.preventDefault();

        if (!validarCamposObligatoriosUsuario()) {
            return;
        }

        if (!validar('#formUsuario')) {
            return;
        }

        let form = $(this);
        let formData = new FormData(this);
        let btn = form.find('button[type="submit"]');

        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: $('#id').val() === '' ? '/api/usuarios/nuevo' : '/api/usuarios/edit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    alerta('Usuario guardado correctamente', true);
                    form[0].reset();
                    $('#modalUsuario').modal('hide');
                    tabla.ajax.reload();
                } else {
                    alerta(response.message ?? 'Error al guardar el usuario', false);
                }
            },
            error: function (xhr) {
                $('.is-invalid').removeClass('is-invalid');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    $.each(errors, function (field, messages) {
                        let input = $('[name="' + field + '"]');

                        if (field.includes('.')) {
                            input = $('[name="' + field.split('.')[0] + '[]"]');
                        }

                        input.addClass('is-invalid');
                        alerta(messages[0], false);
                    });
                } else {
                    alerta(xhr.responseJSON?.message ?? 'Error al guardar el usuario', false);
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

    $('#formContrasena').on('submit', function (e) {
        e.preventDefault();

        let password = $('#pchange').val();
        let confirm = $('#pchange_confirmed').val();
        let userId = $('#password_user_id').val();
        let btn = $(this).find('button[type="submit"]');

        if (!userId) {
            alerta('No se encontro el usuario para actualizar la contrasena', false);
            return;
        }

        if (password.length < 8) {
            alerta('La contrasena debe tener al menos 8 caracteres', false);
            return;
        }

        if (password !== confirm) {
            alerta('Las contrasenas no coinciden', false);
            return;
        }

        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: '/api/usuarios/contrasena',
            type: 'POST',
            data: {
                id: userId,
                password: password,
                password_confirmation: confirm
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    alerta('Contrasena actualizada correctamente', true);
                    $('#modalContrasena').modal('hide');
                } else {
                    alerta(response.message ?? 'Error al cambiar la contrasena', false);
                }
            },
            error: function (xhr) {
                alerta(xhr.responseJSON?.message ?? 'Error interno del servidor', false);
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar cambios');
            }
        });
    });
});

function aplicarIconosAccionesUsuario() {
    $('#tabla-usuarios tbody tr').each(function () {
        const $cell = $(this).find('td').last();
        const $buttons = $cell.find('.editarUsuario, .cambiarContrasena, .eliminarUsuario');

        if ($buttons.length) {
            $cell.addClass('user-actions-cell');
            let $menu = $cell.find('.user-action-menu__dropdown');

            if (!$menu.length) {
                $cell.empty().append(
                    '<div class="dropdown user-action-menu">' +
                        '<button class="btn user-action-menu__trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir acciones">' +
                            '<i class="bi bi-three-dots"></i>' +
                        '</button>' +
                        '<div class="dropdown-menu dropdown-menu-end user-action-menu__dropdown"></div>' +
                    '</div>'
                );
                $menu = $cell.find('.user-action-menu__dropdown');
            }

            $buttons.appendTo($menu);
        }
    });

    $('#tabla-usuarios .editarUsuario').each(function () {
        $(this)
            .removeClass('btn-sm btn-primary btn-warning btn-danger btn-info')
            .attr('type', 'button')
            .addClass('dropdown-item user-action-link user-action-link--edit')
            .attr('title', 'Editar usuario')
            .attr('aria-label', 'Editar usuario')
            .html('<i class="bi bi-pencil-square"></i><span>Editar</span>');
    });

    $('#tabla-usuarios .cambiarContrasena').each(function () {
        $(this)
            .removeClass('btn-sm btn-primary btn-warning btn-danger btn-info')
            .attr('type', 'button')
            .addClass('dropdown-item user-action-link user-action-link--password')
            .attr('title', 'Cambiar contrasena')
            .attr('aria-label', 'Cambiar contrasena')
            .html('<i class="bi bi-shield-lock"></i><span>Contrasena</span>');
    });

    $('#tabla-usuarios .eliminarUsuario').each(function () {
        $(this)
            .removeClass('btn-sm btn-primary btn-warning btn-danger btn-info')
            .attr('type', 'button')
            .addClass('dropdown-item user-action-link user-action-link--delete')
            .attr('title', 'Eliminar usuario')
            .attr('aria-label', 'Eliminar usuario')
            .html('<i class="bi bi-trash3"></i><span>Eliminar</span>');
    });
}

function validarCamposObligatoriosUsuario() {
    const requiredFields = ['#telefono', '#direccion'];
    let isValid = true;

    requiredFields.forEach(function (selector) {
        const $field = $(selector);
        const $group = $field.closest('.form-group');

        $field.removeClass('is-invalid');
        $group.find('.invalid-feedback').remove();

        if (($field.val() || '').trim() === '') {
            $field.addClass('is-invalid');
            $group.append('<div class="invalid-feedback">Este campo es obligatorio</div>');
            isValid = false;
        }
    });

    return isValid;
}

function modoEdicionUsuario() {
    $('#div_credenciales').hide();

    $('#password, #re_password')
        .closest('.form-group')
        .removeClass('form-required');

    $('#password, #re_password').val('');
}

function modoNuevoUsuario() {
    $('#div_credenciales').show();

    $('#password, #re_password')
        .closest('.form-group')
        .addClass('form-required');

    $('#correo, #password, #re_password').val('');
}
