let tabla;
$(document).ready(function () {
    tabla = $('#tabla-autor').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url:  "/api/autores/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                // si necesitas enviar parámetros extra
            },
            error: default_error_handler        
        },
        columns: [
            { data: 'nombres', name: 'nombres', 
                render: function (data, type, row) {
                return row.nombres + ' ' + row.apellidos;
                }
            },
            { data: 'pais', name: 'pais' },
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
        $('#formAutor')[0].reset();
        $('#id').val('');
        $('#modalAutor').modal('show');
    });

    // EDITAR
    $('#tabla-autor').on('click', '.editarAutor', function () {
        let data = tabla.row($(this).closest('tr')).data();        
        $('#id').val(data.id);
        $('#tipo_documento').val(data.tipo_documento);
        $('#nro_documento').val(data.nro_documento);
        $('#razon_social').val(data.razon_social);
        $('#responsable').val(data.responsable);
        $('#telefono').val(data.telefono);
        $('#direccion').val(data.direccion);
        $('#web').val(data.web);
        $('#estado').val(data.estado ?? '');
        // MARCAR roles del usuario
            if (data.roles && Array.isArray(data.roles)) {
                data.roles.forEach(function (rol) {
                    $('#rol_' + rol.id).prop('checked', true);
                });
            }

        $('#modalAutor').modal('show');
    });
    $('#formAutor').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');
        if(!validar('#formAutor')) {
            btn.prop('disabled', false).text('Guardar');
            return;
        }

        $.ajax({
            url:$('#id').val()=='' ? '/api/autores/nuevo' : '/api/autores/edit',
            type:'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
                if (response.success) {
                    alerta("Autor guardado correctamente", true);
                    // Reset form
                    form[0].reset();
                    // Cerrar modal
                    $('#modalAutor').modal('hide');
                        tabla.ajax.reload();
                } else {
                    alerta(response.message??'Error al guardar el autor', false);
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
                    alerta(xhr.responseJSON.message??'Error al guardar al autor', false);
                    //toastr.error('Error interno del servidor');
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

});


