let tabla;
$(document).ready(function () {
    tabla = $('#tabla-editorial').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url:  "/api/editoriales/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                // si necesitas enviar parámetros extra
            },
            error: default_error_handler        
        },
        columns: [
            { data: 'tipo_documento', name: 'tipo_documento',
                render: function (data, type, row) {
                    return `<strong>${data}</strong><br><small class="text-muted">${row.nro_documento ?? ''}</small>`;
                }
             },
            { data: 'responsable', name: 'responsable',
                render: function (data, type, row) {
                    return `<strong>${data}</strong><br><small class="text-muted">${row.nombre ?? ''}</small>`;
                }
            },
            { data: 'telefono', name: 'telefono' },
            { data: 'correo', name: 'correo' },
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
        $('#formEditorial')[0].reset();
        $('#id').val('');
        $('.password-group').show();
        $('#modalEditorial').modal('show');
    });

    // EDITAR
    $('#tabla-editorial').on('click', '.editarEditorial', function () {
        let data = tabla.row($(this).closest('tr')).data();
        
        $('#id').val(data.id);
        $('#tipo_documento').val(data.tipo_documento);
        $('#nro_documento').val(data.nro_documento);
        $('#nombre').val(data.nombre);
        $('#responsable').val(data.responsable);
        $('#pais').val(data.pais);
        $('#correo').val(data.correo);
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
        $('#modalEditorial').modal('show');
    });
    $('#formEditorial').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');
        if(!validar('#formEditorial')) {
            btn.prop('disabled', false).text('Guardar');
            return;
        }

        $.ajax({
            url:$('#id').val()=='' ? '/api/editoriales/nuevo' : '/api/editoriales/edit',
            type:'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
                if (response.success) {
                    alerta("Editorial guardado correctamente", true);
                    // Reset form
                    form[0].reset();
                    // Cerrar modal
                    $('#modalEditorial').modal('hide');
                        tabla.ajax.reload();
                } else {
                    alerta(response.message??'Error al guardar el editorial', false);
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
                    alerta(xhr.responseJSON.message??'Error al guardar el editorial', false);
                    //toastr.error('Error interno del servidor');
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

});


