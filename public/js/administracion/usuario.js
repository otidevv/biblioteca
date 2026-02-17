let tabla;
$(document).ready(function () {
    alerta('Esto ya debería verse', true);
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
            },
            error: default_error_handler        
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'created_at', name: 'created_at' },
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
        tablaUsuarios.ajax.reload(); // suponiendo que tu DataTable se llama tablaUsuarios
    });
    // NUEVO
    $('#btnNuevo').on('click', function () {
        $('#formUsuario')[0].reset();
        $('input[name="roles[]"]').prop('checked', false);
        $('#id').val('');
        $('#div_credenciales').show();
        $('.password-group').show();
        $('#modalUsuario').modal('show');
    });

    // EDITAR
    $('#tabla-usuarios').on('click', '.editarUsuario', function () {
        if (!validar('#div_form')) return;
        let data = tabla.row($(this).closest('tr')).data();
        console.log(data);
        
        $('input[name="roles[]"]').prop('checked', false);
        $('#dni').val(data.id);
        $('#nombres').val(data.name);
        $('#apellido_paterno').val(data.email);
        $('#apellido_materno').val(data.id);
        $('#sexo').val(data.name);
        $('#telefono').val(data.email);
        $('#id').val(data.id);
        $('#sexo').val(data.persona.sexo ?? '');
        $('#direccion').val(data.name);
        $('#email').val(data.email);
        // 🔥 MARCAR roles del usuario
            if (data.roles && Array.isArray(data.roles)) {
                data.roles.forEach(function (rol) {
                    $('#rol_' + rol.id).prop('checked', true);
                });
            }

        $('#div_credenciales').hide();
        $('#modalUsuario').modal('show');
    });
    $('#formUsuario').on('submit', function (e) {
        e.preventDefault();
        if (!validar('#div_form')) return;

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({        
            url: '/api/usuarios/nuevo',
            type:$('#id').val()=='' ? 'POST' : 'PUT',
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
                    if (window.tablaUsuarios) {
                        tablaUsuarios.ajax.reload(null, false);
                    }
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

});


