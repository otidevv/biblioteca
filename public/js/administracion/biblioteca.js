let tabla;
$(document).ready(function () {
    alerta('Esto ya debería verse', true);
    tablaBibliotecas = $('#tabla-biblioteca').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url:  "/api/bibliotecas/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                // si necesitas enviar parámetros extra
            },
            error: default_error_handler        
        },
        columns: [
            { data: 'codigo', name: 'codigo' },
            { data: 'nombre', name: 'nombre' },
            { data: 'direccion', name: 'direccion' },
            { data: 'estado', name: 'estado' },
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
        $('#formBiblioteca')[0].reset();
        $('#id').val('');
        $('.password-group').show();
        $('#modalBiblioteca').modal('show');
    });

    // EDITAR
    $('#tabla-biblioteca').on('click', '.editarBiblioteca', function () {
        if (!validar('#div_form')) return;
        let data = tablaBibliotecas.row($(this).closest('tr')).data();
        console.log(data);
        
        $('input[name="roles[]"]').prop('checked', false);
        $('#id').val(data.id);
        $('#codigo').val(data.codigo);
        $('#nombre').val(data.nombre);
        $('#direccion').val(data.direccion);
        $('#descripcion').val(data.descripcion);
        $('#estado').val(data.estado ?? '');
        // MARCAR roles del usuario
            if (data.roles && Array.isArray(data.roles)) {
                data.roles.forEach(function (rol) {
                    $('#rol_' + rol.id).prop('checked', true);
                });
            }

        $('#div_credenciales').hide();
        $('#modalBiblioteca').modal('show');
    });
    $('#formBiblioteca').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url:$('#id').val()=='' ? '/api/bibliotecas/nuevo' : '/api/bibliotecas/edit',
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
                    $('#modalBiblioteca').modal('hide');
                    // Recargar tabla (si usas DataTable)
                    if (window.tablaBibliotecas) {
                        tablaBibliotecas.ajax.reload();
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


