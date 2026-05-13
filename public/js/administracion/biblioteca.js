let tabla;
$(document).ready(function () {
    tablaBibliotecas = $('#tabla-biblioteca').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        autoWidth: false,
        scrollX: true,
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
            {
                data: 'nombre',
                name: 'nombre',
                render: function (data, type, row) {
                    const imgHtml = row.imagen
                        ? `<img src="/${row.imagen.replace(/^\/+/,'')}" class="bib-table-thumb" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">`
                        + `<div class="bib-table-icon-fallback" style="display:none"><i class="bi bi-buildings-fill"></i></div>`
                        : `<div class="bib-table-icon-fallback"><i class="bi bi-buildings-fill"></i></div>`;

                    const desc = row.descripcion
                        ? `<span class="bib-table-desc">${row.descripcion}</span>`
                        : '';

                    return `
                        <div class="bib-table-identity">
                            <div class="bib-table-cover">${imgHtml}</div>
                            <div class="bib-table-info">
                                <span class="bib-table-code">${row.codigo || ''}</span>
                                <span class="bib-table-name">${data}</span>
                                ${desc}
                            </div>
                        </div>`;
                }
            },
            {
                data: 'direccion',
                name: 'direccion',
                render: function (data) {
                    if (!data) return '<span class="bib-table-no-data">Sin dirección</span>';
                    const truncated = data.length > 55 ? data.substring(0, 55) + '…' : data;
                    return `<span class="bib-table-location"><i class="bi bi-geo-alt-fill"></i>${truncated}</span>`;
                }
            },
            {
                data: 'estado',
                name: 'estado',
                render: function (data) {
                    return parseInt(data) === 1
                        ? '<span class="bib-status-pill bib-status-pill--active"><i class="bi bi-check-circle-fill"></i> Activa</span>'
                        : '<span class="bib-status-pill bib-status-pill--inactive"><i class="bi bi-x-circle-fill"></i> Inactiva</span>';
                }
            },
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
            $('#tabla-biblioteca').css('width', '100%');
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
