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
            { data: 'nombres', name: 'nombres',
                render: function(data, type, row) {
                    if (type !== 'display') return data;
                    const iniciales = ((row.nombres?.[0] ?? '') + (row.apellidos?.[0] ?? '')).toUpperCase();
                    return `<div class="autor-cell">
                                <div class="autor-avatar">${iniciales}</div>
                                <span class="autor-cell__name">${row.nombres ?? ''}</span>
                            </div>`;
                }
            },
            { data: 'apellidos', name: 'apellidos',
                render: data => `<span class="autor-cell__apellido">${data ?? ''}</span>`
            },
            { data: 'pais', name: 'pais',
                render: function(data) {
                    if (!data) return '<span class="autor-pais--vacio">—</span>';
                    return `<span class="autor-pais"><i class="bi bi-geo-alt-fill"></i>${data}</span>`;
                }
            },
            { data: 'libros_count', name: 'libros_count', className: 'text-center', searchable: false,
                render: function(data) {
                    const n = data ?? 0;
                    const cls = n === 0 ? 'autor-badge--empty' : n >= 10 ? 'autor-badge--high' : 'autor-badge--normal';
                    return `<span class="autor-badge ${cls}"><i class="bi bi-book"></i>${n}</span>`;
                }
            },
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
                $('.is-invalid').removeClass('is-invalid');
                let json = xhr.responseJSON;
                if (xhr.status === 422 && json.errors) {
                    $.each(json.errors, function (field, messages) {
                        let input = $('[name="' + field + '"]');
                        if (field.includes('.')) {
                            input = $('[name="' + field.split('.')[0] + '[]"]');
                        }
                        input.addClass('is-invalid');
                        alerta(messages[0], false);
                    });
                } else {
                    alerta(json?.message ?? 'Error al guardar el autor', false);
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

});


