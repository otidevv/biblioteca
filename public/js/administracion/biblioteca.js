let tabla;
$(document).ready(function () {
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
// PREVIEW IMAGEN
$('#imagen').on('change', function (e) {
    let file = e.target.files[0];

    if (file) {
        let reader = new FileReader();

        reader.onload = function (e) {
            $('#previewImagen')
                .attr('src', e.target.result)
                .removeClass('d-none');
        }

        reader.readAsDataURL(file);
    }
});

// EDITAR
$('#tabla-biblioteca').on('click', '.editarBiblioteca', function () {

    let data = tablaBibliotecas.row($(this).closest('tr')).data();

    $('#formBiblioteca')[0].reset();
    $('#previewImagen').addClass('d-none');

    $('#id').val(data.id);
    $('#codigo').val(data.codigo);
    $('#nombre').val(data.nombre);
    $('#direccion').val(data.direccion);
    $('#descripcion').val(data.descripcion);

    // Mostrar imagen si existe
    if (data.imagen) {
        $('#previewImagen')
            .attr('src', '/storage/' + data.imagen)
            .removeClass('d-none');
    }

    $('#modalBiblioteca').modal('show');
});

// GUARDAR
$('#formBiblioteca').on('submit', function (e) {

    e.preventDefault();

    let form = $(this);
    let formData = new FormData(this);

    let btn = form.find('button[type="submit"]');
    btn.prop('disabled', true).text('Guardando...');

    $.ajax({
        url: $('#id').val() === '' 
            ? '/api/bibliotecas/nuevo' 
            : '/api/bibliotecas/edit',

        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,

        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },

        success: function (response) {

            if (response.success) {

                alerta("Biblioteca guardada correctamente", true);

                form[0].reset();
                $('#previewImagen').addClass('d-none');

                $('#modalBiblioteca').modal('hide');

                if (window.tablaBibliotecas) {
                    tablaBibliotecas.ajax.reload();
                }

            } else {
                alerta(response.message ?? 'Error', false);
            }
        },

        error: function (xhr) {

            $('.is-invalid').removeClass('is-invalid');

            if (xhr.status === 422) {

                let errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {
                    $('[name="' + field + '"]').addClass('is-invalid');
                    alerta(messages[0], false);
                });

            } else {
                alerta('Error del servidor', false);
            }
        },

        complete: function () {
            btn.prop('disabled', false).text('Guardar');
        }
    });
});

});


