let tabla;
$(document).ready(function () {
    tabla = $('#tabla-tipo-registro').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url:  "/api/tipo_registros/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                // si necesitas enviar parámetros extra
            },
            error: default_error_handler        
        },
        columns: [
            { data: 'codigo', name: 'codigo' },
            { data: 'abreviatura', name: 'abreviatura' },
            { data: 'nombre', name: 'nombre' },
            {
                data: 'estado',
                name: 'estado',
                render: function (data) {

                    const activo = Number(data) === 1;

                    return `
                        <button type="button"
                            class="btn btn-sm ${activo ? 'btn-success' : 'btn-secondary'}"
                            title="${activo ? 'Activo' : 'Inactivo'}">
                            <i class="bi ${activo ? 'bi-eye-fill text-white' : 'bi-eye-slash-fill text-danger'}"></i>
                        </button>
                    `;
                }
            },
            { 
                data: 'acciones', 
                name: 'acciones', 
                orderable: false, 
                searchable: false 
            },
        ],        
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: default_datatable_buttons
    });

    // NUEVO
    $('#btnNuevo').on('click', function () {
        $('#formTipoRegistro')[0].reset();
        $('#id').val('');
        $('.password-group').show();
        $('#modalTipoRegistro').modal('show');
    });

    // EDITAR
    $('#tabla-tipo-registro').on('click', '.editarTipoRegistro', function () {
        let data = tabla.row($(this).closest('tr')).data();
        
        $('#id').val(data.id);
        $('#nombre').val(data.nombre);
        $('#codigo').val(data.codigo ?? '');
        $('#abreviatura').val(data.abreviatura ?? '');
        $('#descripcion').val(data.descripcion ?? '');
        // MARCAR roles del usuario
            if (data.roles && Array.isArray(data.roles)) {
                data.roles.forEach(function (rol) {
                    $('#rol_' + rol.id).prop('checked', true);
                });
            }
        $('#modalTipoRegistro').modal('show');
    });
    $('#formTipoRegistro').on('submit', function (e) {

        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');
        if(!validar('#formTipoRegistro')) {
            btn.prop('disabled', false).text('Guardar');
            return;
        }  
        $.ajax({
            url:$('#id').val()=='' ? '/api/tipo_registros/nuevo' : '/api/tipo_registros/edit',
            type:'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
                if (response.success) {
                    alerta("Tipo de registro guardado correctamente", true);
                    // Reset form
                    form[0].reset();
                    // Cerrar modal
                    $('#modalTipoRegistro').modal('hide');
                        tabla.ajax.reload();
                } else {
                    alerta(response.message??'Error al guardar el tipo de registro', false);
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
                    alerta(xhr.responseJSON.message??'Error al guardar el tipo de registro', false);
                    //toastr.error('Error interno del servidor');
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

});


