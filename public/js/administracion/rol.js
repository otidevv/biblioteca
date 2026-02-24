let tabla;
$(document).on('change', '.permiso-padre', function () {
    let padreId = $(this).val();
        $('.permiso-hijo-' + padreId).prop('checked', this.checked);
    });
// Si todos los hijos están marcados → marcar padre
$(document).on('change', '.permiso-hijo', function () {
    let clases = $(this).attr('class').split(' ');
    let padreClass = clases.find(c => c.startsWith('permiso-hijo-'));
    let padreId = padreClass.replace('permiso-hijo-', '');

    let total = $('.permiso-hijo-' + padreId).length;
    let checked = $('.permiso-hijo-' + padreId + ':checked').length;

    $('#permiso_' + padreId).prop('checked', total === checked);
});
$(document).ready(function () {
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
            { data: 'total_usuarios', name: 'total_usuarios' },
            { data: 'total_permisos', name: 'total_permisos' },
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
        $('#formRoles')[0].reset();
        $('#id').val('');
        $('.password-group').show();
        $('#modalRoles').modal('show');
    });

    // EDITAR
    $('#tabla-roles').on('click', '.editarRol', function () {
        let data = tabla.row($(this).closest('tr')).data();
        console.log(data);
        $('#id').val(data.id);        
        $('#nombre').val(data.nombre);
        $('#descripcion').val(data.descripcion);
        $('#modalRoles').modal('show');
    });
    // PERMISOS
    $('#tabla-roles').on('click', '.permisosRol', function () {
        let data = tabla.row($(this).closest('tr')).data();
        console.log(data);
        $('#id').val(data.id);        
        cargarPermisosRol(data.permisos);
        $('#modalPermisos').modal('show');
    });

    $('#formRoles').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url:$('#id').val()=='' ? '/api/roles/nuevo' : '/api/roles/edit',
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
                    $('#modalRoles').modal('hide');
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
    $('#formPermisos').on('submit', function (e) {
        e.preventDefault();

        let permisos = [];
        $('input[name="permisos[]"]:checked').each(function () {
            permisos.push($(this).val());
        });

        if (permisos.length === 0) {
            alerta('Debe seleccionar al menos un permiso', false);
            return;
        }

        $.ajax({
            url: '/api/roles/permisos/guardar',
            type: 'POST',
            data: {
                rol_id: $('#id').val(),
                permisos: permisos
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                alerta('Permisos asignados correctamente', true);
                tabla.ajax.reload();
                $('#modalPermisos').modal('hide');
            },
            error: function (xhr) {
                alerta(
                    xhr.responseJSON?.message ?? 'Error interno del servidor',
                    false
                );
            }
        });
    });



});


function cargarPermisosRol(permisosRol) {

    // 1️⃣ Desmarcar todo
    $('input[name="permisos[]"]').prop('checked', false);

    // 2️⃣ Marcar los que tiene el rol
    permisosRol.forEach(function (permiso) {
        $('#permiso_' + permiso.id).prop('checked', true);
    });
}