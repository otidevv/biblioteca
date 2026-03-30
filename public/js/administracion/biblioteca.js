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
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-biblioteca');
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-biblioteca');
        }
    });

    // NUEVO
    $('#btnNuevo').on('click', function () {
        $('#formBiblioteca')[0].reset();
        $('#id').val('');
        $('#previewImagen').attr('src', '').addClass('d-none');
        $('#previewPlaceholder').removeClass('d-none');
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
            $('#previewPlaceholder').addClass('d-none');
        }

        reader.readAsDataURL(file);
    } else {
        $('#previewImagen').attr('src', '').addClass('d-none');
        $('#previewPlaceholder').removeClass('d-none');
    }
});

// EDITAR
$('#tabla-biblioteca').on('click', '.editarBiblioteca', function () {

    let data = tablaBibliotecas.row($(this).closest('tr')).data();

    $('#formBiblioteca')[0].reset();
    $('#previewImagen').attr('src', '').addClass('d-none');
    $('#previewPlaceholder').removeClass('d-none');

    $('#id').val(data.id);
    $('#codigo').val(data.codigo);
    $('#nombre').val(data.nombre);
    $('#direccion').val(data.direccion);
    $('#descripcion').val(data.descripcion);

    // Mostrar imagen si existe
    if (data.imagen) {
        let imagenPath = String(data.imagen).trim();

        if (!/^https?:\/\//i.test(imagenPath)) {
            imagenPath = imagenPath.replace(/^\/+/, '');

            if (imagenPath.startsWith('storage/')) {
                imagenPath = '/' + imagenPath;
            } else {
                imagenPath = '/storage/' + imagenPath;
            }
        }

        $('#previewImagen')
            .attr('src', imagenPath)
            .removeClass('d-none');
        $('#previewPlaceholder').addClass('d-none');
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
                $('#previewImagen').attr('src', '').addClass('d-none');
                $('#previewPlaceholder').removeClass('d-none');

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
