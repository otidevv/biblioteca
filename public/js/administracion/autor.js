let tabla;
$(document).ready(function () {
    $('.select2').select2({
        width: '100%'
    });

    $('#modalAutor').on('shown.bs.modal', function () {

        $('#pais').select2({
            dropdownParent: $('#modalAutor'),
            width: '100%'
        });

    });
    $('#modalEditorial').on('shown.bs.modal', function () {

        $('#pais').select2({
            dropdownParent: $('#modalEditorial'),
            width: '100%'
        });

    });
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
            { data: 'nombres', name: 'nombres' },
            { data: 'apellidos', name: 'apellidos' },
            { data: 'pais', name: 'pais' },
            { 
                data: 'acciones', 
                name: 'acciones', 
                className: 'admin-actions-cell',
                orderable: false, 
                searchable: false 
            }
        ],        
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
        }
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
        $('#formAutor')[0].reset();
        $('#id').val(data.id);
        $('#nombre').val(data.nombres);
        $('#apellidos').val(data.apellidos);
        $('#pais').val(data.pais_id ?? '0').trigger('change');
        $('#modalAutor').modal('show');
    });

    // ELIMINAR
    $('#tabla-autor').on('click', '.eliminarAutor', function () {
        let data = tabla.row($(this).closest('tr')).data();
        if (!confirm('¿Deseas eliminar al autor "' + data.nombres + ' ' + data.apellidos + '"?')) return;

        $.ajax({
            url: '/api/autores/' + data.id,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            success: function (response) {
                if (response.success) {
                    alerta('Autor eliminado correctamente', true);
                    tabla.ajax.reload();
                } else {
                    alerta(response.message ?? 'Error al eliminar el autor', false);
                }
            },
            error: function (xhr) {
                alerta(xhr.responseJSON?.message ?? 'Error al eliminar el autor', false);
            }
        });
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


