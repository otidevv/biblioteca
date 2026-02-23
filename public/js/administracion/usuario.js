let tabla;
$(document).ready(function () {
    tabla = $('#tabla-usuarios').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url:  "/api/usuarios/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                // si necesitas enviar parámetros extra
                d.tipo_usuario=$('#tipo_usuario').val();
            },
            error: default_error_handler        
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            {  data: 'roles',
        name: 'rol',
        render: function (data, type, row) {

            if (!Array.isArray(data) || data.length === 0) {
                return '<span class="badge bg-secondary">Sin rol</span>';
            }

            return data.map(rol => {
                    let color = 'bg-primary';

                    switch (rol.nombre.toLowerCase()) {
                        case 'admin':
                            color = 'bg-danger';
                            break;
                        case 'programador':
                            color = 'bg-warning text-dark';
                            break;
                        case 'lector':
                            color = 'bg-info text-dark';
                            break;
                        case 'atencion a estudiantes':
                            color = 'bg-success';
                            break;
                    }

                    return `<span class="badge ${color} me-1" style="color: white;">${rol.nombre}</span>`;
                }).join('');
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
        initComplete: default_datatable_buttons
    });

    $('#tipo_usuario').on('change', function() {
        tabla.ajax.reload(); // suponiendo que tu DataTable se llama tablaUsuarios
    });
    // ABRE MODAL DE NUEVO USUARIO
    $('#btnNuevo').on('click', function () {
        $('#formUsuario')[0].reset();
        $('input[name="roles[]"]').prop('checked', false);
        $('#id').val('');
        modoNuevoUsuario();
        $('#div_credenciales').show();
        $('.password-group').show();
        $('#modalUsuario').modal('show');
    });

    // ABRE MODAL DE EDICIÓN
    $('#tabla-usuarios').on('click', '.editarUsuario', function () {
        let data = tabla.row($(this).closest('tr')).data();
        console.log(data);
        
        $('input[name="roles[]"]').prop('checked', false);
        $('#dni').val(data.persona.dni);
        $('#nombres').val(data.persona.nombres);
        $('#apellido_paterno').val(data.persona.apellido_paterno);
        $('#apellido_materno').val(data.persona.apellido_materno);
        $('#sexo').val(data.persona.sexo);
        $('#telefono').val(data.persona.telefono);
        $('#id').val(data.id);
        $('#biblioteca').val(data.roles[0]?.pivot.biblioteca_id ?? '');
        $('#sexo').val(data.persona.sexo ?? '');
        $('#direccion').val(data.persona.direccion ?? '');
        $('#email').val(data.email);
        // 🔥 MARCAR roles del usuario
            if (data.roles && Array.isArray(data.roles)) {
                data.roles.forEach(function (rol) {
                    $('#rol_' + rol.id).prop('checked', true);
                });
            }
        modoEdicionUsuario();

        $('#div_credenciales').hide();
        $('#modalUsuario').modal('show');
    });
    //ABRE MODAL DE CONTRASEÑA
    $('#tabla-usuarios').on('click', '.cambiarContrasena', function () {
        let data = tabla.row($(this).closest('tr')).data();
        console.log(data);
        
        $('#p_apodo').val(data.email);
        $('#id').val(data.id);
        $('#password').val("");
        $('#pchange_confirmed').val("");
        $('#modalContraseña').modal('show');
    });
    // EDITAR CONTRASEÑA
    $(document).on('click', '.toggle-password', function () {
        let target = $('#' + $(this).data('target'));
        let type = target.attr('type') === 'password' ? 'text' : 'password';
        target.attr('type', type);
    });
    // VERIFICAR FUERZA DE CONTRASEÑA
    $('#pchange').on('input', function () {
        let val = $(this).val();
        let strength = $('#password-strength');

        if (val.length < 8) {
            strength.text('Débil').removeClass().addClass('text-danger');
        } else if (val.match(/[A-Z]/) && val.match(/[0-9]/)) {
            strength.text('Fuerte').removeClass().addClass('text-success');
        } else {
            strength.text('Media').removeClass().addClass('text-warning');
        }
    });
    // VERIFICAR COINCIDENCIA DE CONTRASEÑAS
    $('#pchange_confirmed').on('input', function () {
        let match = $(this).val() === $('#pchange').val();
        let status = $('#password-match-status span');

        if (match) {
            status.text('Las contraseñas coinciden').removeClass().addClass('text-success');
        } else {
            status.text('No coinciden').removeClass().addClass('text-danger');
        }
    });
    //LIMPIAR Y RESETEAR MODAL DE CONTRASEÑA
    $('#modalContraseña').on('shown.bs.modal', function () {

        // Limpiar inputs
        $('#pchange').val('');
        $('#pchange_confirmed').val('');

        // Volver a ocultar contraseñas
        $('#pchange, #pchange_confirmed').attr('type', 'password');

        // Resetear mensajes
        $('#password-strength')
            .text('Usa al menos 8 caracteres')
            .removeClass()
            .addClass('text-muted small');

        $('#password-match-status span')
            .text('Las contraseñas deben coincidir')
            .removeClass()
            .addClass('text-muted small');

        // Quitar estados de error si usas validación visual
        $('#pchange, #pchange_confirmed').removeClass('is-invalid is-valid');
    });
    $('#modalContraseña').on('hidden.bs.modal', function () {
        $('#formContraseña')[0].reset();
    });
    // GUARDAR Y ACTUALIZAR USUARIO
    $('#formUsuario').on('submit', function (e) {
        e.preventDefault();
        if (!validar('#div_form')) return;

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({        
            url:$('#id').val()=='' ? '/api/usuarios/nuevo' : '/api/usuarios/edit',
            type:'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
                if (response.success) {
                    alerta("Usuario guardado correctamente", true);
                    // Reset form
                    form[0].reset();
                    // Cerrar modal
                    $('#modalUsuario').modal('hide');
                    // Recargar tabla (si usas DataTable)
                    tabla.ajax.reload();
                } else {
                    alerta(response.message??'Error al guardar el usuario', false);
                }
            },
            error: function (xhr) {
                // Limpiar errores previos
                $('.is-invalid').removeClass('is-invalid');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (field, messages) {
                        let input = $('[name="' + field + '"]');
                        // Campos array (roles[])
                        if (field.includes('.')) {
                            input = $('[name="' + field.split('.')[0] + '[]"]');
                        }
                        input.addClass('is-invalid');
                        alerta(messages[0], false);
                    });
                } else {
                    alerta(xhr.responseJSON.message??'Error al guardar el usuario', false);
                    //toastr.error('Error interno del servidor');
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });
    // GUARDAR NUEVA CONTRASEÑA
    $('#formContraseña').on('submit', function (e) {
        e.preventDefault();

        let password = $('#pchange').val();
        let confirm  = $('#pchange_confirmed').val();
        let userId   = $('#id').val(); // 👈 hidden input con el id del usuario

        // 🔒 Validaciones básicas
        if (password.length < 8) {
            alerta('La contraseña debe tener al menos 8 caracteres', false);
            return;
        }

        if (password !== confirm) {
            alerta('Las contraseñas no coinciden', false);
            return;
        }

        let btn = $(this).find('button[type="submit"]');
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
                    alerta('Contraseña actualizada correctamente', true);
                    $('#modalContraseña').modal('hide');
                } else {
                    alerta(response.message ?? 'Error al cambiar la contraseña', false);
                }
            },
            error: function (xhr) {
                alerta(
                    xhr.responseJSON?.message ?? 'Error interno del servidor',
                    false
                );
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });


});
function modoEdicionUsuario() {
    $('#div_credenciales').hide();

    $('#correo, #password, #re_password')
        .closest('.form-group')
        .removeClass('form-required');

    $('#password, #re_password').val('');
}

function modoNuevoUsuario() {
    $('#div_credenciales').show();

    $('#correo, #password, #re_password')
        .closest('.form-group')
        .addClass('form-required');

    $('#correo, #password, #re_password').val('');
}
