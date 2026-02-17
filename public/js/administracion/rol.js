let tabla;
$(document).ready(function () {
    alerta('Esto ya debería verse', true);
    tabla = $('#tabla-roles').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url:  "/api/roles/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                // si necesitas enviar parámetros extra
            },
            error: default_error_handler        
        },
        columns: [
            { data: 'nombre', name: 'nombre' },
            { data: 'count_users', name: 'count_users' },
            { data: 'count_permisos', name: 'count_permisos' },
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

    // NUEVO
    $('#btnNuevo').on('click', function () {
        $('#formUsuario')[0].reset();
        $('#id').val('');
        $('.password-group').show();
        $('#modalUsuario').modal('show');
    });

    // EDITAR
    $('#tabla-roles').on('click', '.editarUsuario', function () {
        let id = $(this).data('id');

        $.get(window.routes.edit.replace(':id', id), function (u) {
            $('#id').val(u.id);
            $('#name').val(u.name);
            $('#email').val(u.email);
            $('.password-group').hide();
            $('#modalUsuario').modal('show');
        });
    });
    $('#formUsuario').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: '/api/usuarios/nuevo',
            type: 'POST',
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


