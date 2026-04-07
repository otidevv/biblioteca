let tablaInventarioFisico;

$(document).ready(function () {
    const config = window.inventoryPhysicalConfig || {};

    tablaInventarioFisico = $('#tabla-inventario-fisico').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [],
        scrollX: true,
        ajax: {
            url: '/api/inventario/fisico/listar',
            type: 'GET',
            xhrFields: { withCredentials: true },
            data: function (d) {
                const filtro = $('#filtro_biblioteca').val();
                d.biblioteca_id = config.canFilterLibrary ? filtro : (config.fixedLibraryId ?? '');
            },
            error: default_error_handler,
        },
        columns: [
            { data: 'imagen', name: 'imagen', orderable: false, searchable: false },
            { data: 'codigo_catalogo', name: 'libros.codigo_dewey', orderable: false, searchable: false },
            { data: 'titulo', name: 'libros.titulo' },
            { data: 'biblioteca', name: 'bibliotecas.nombre', orderable: false, searchable: false },
            { data: 'resumen_estado', name: 'resumen_estado', orderable: false, searchable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        autoWidth: false,
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-inventario-fisico');
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-inventario-fisico');
            $('#tabla-inventario-fisico').css('width', '100%');
        },
    });

    $('#filtro_biblioteca').on('change', function () {
        tablaInventarioFisico.ajax.reload();
    });

    $('#btnSolicitarExcelFisico').on('click', function () {
        solicitarReporteFisico('excel', config);
    });

    $('#btnSolicitarPdfFisico').on('click', function () {
        solicitarReporteFisico('pdf', config);
    });
});

function solicitarReporteFisico(formato, config) {
    const bibliotecaId = config.canFilterLibrary ? ($('#filtro_biblioteca').val() || '') : (config.fixedLibraryId ?? '');

    $.ajax({
        url: config.requestUrl,
        type: 'POST',
        data: {
            formato,
            biblioteca_id: bibliotecaId,
            _token: $('meta[name="csrf-token"]').attr('content'),
        },
        xhrFields: { withCredentials: true },
        success: function (response) {
            alerta(response.message || 'La solicitud fue registrada.', 1);
            setTimeout(function () {
                window.location.reload();
            }, 1200);
        },
        error: function (xhr) {
            default_error_handler(xhr);
        }
    });
}
