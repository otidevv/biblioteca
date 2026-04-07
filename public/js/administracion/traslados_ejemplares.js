let tablaTrasladosPendientes;
let tablaTrasladosEnviados;

$(document).ready(function () {
    tablaTrasladosPendientes = $('#tabla-traslados-pendientes').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [],
        autoWidth: false,
        scrollX: true,
        ajax: {
            url: '/api/inventario/ejemplares/traslados/pendientes',
            type: 'GET',
            error: default_error_handler,
        },
        columns: [
            { data: 'seleccion', orderable: false, searchable: false },
            { data: 'libro', name: 'libro.titulo' },
            { data: 'ejemplar_codigo', name: 'ejemplar_id', orderable: false, searchable: false },
            { data: 'origen', name: 'bibliotecaOrigen.nombre', orderable: false, searchable: false },
            { data: 'solicitado_por', name: 'solicitadoPor.name', orderable: false, searchable: false },
            { data: 'acciones', orderable: false, searchable: false },
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
        },
        drawCallback: function () {
            $('#checkAllPendientes').prop('checked', false);
            actualizarSeleccionTraslados('pendientes');
        }
    });

    tablaTrasladosEnviados = $('#tabla-traslados-enviados').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [],
        autoWidth: false,
        scrollX: true,
        ajax: {
            url: '/api/inventario/ejemplares/traslados/enviados',
            type: 'GET',
            error: default_error_handler,
        },
        columns: [
            { data: 'seleccion', orderable: false, searchable: false },
            { data: 'libro', name: 'libro.titulo' },
            { data: 'ejemplar_codigo', name: 'ejemplar_id', orderable: false, searchable: false },
            { data: 'destino', name: 'bibliotecaDestino.nombre', orderable: false, searchable: false },
            { data: 'solicitado_por', name: 'solicitadoPor.name', orderable: false, searchable: false },
            { data: 'acciones', orderable: false, searchable: false },
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
        },
        drawCallback: function () {
            $('#checkAllEnviados').prop('checked', false);
            actualizarSeleccionTraslados('enviados');
        }
    });

    $(document).on('change', '.check-traslado-pendiente', function () {
        actualizarSeleccionTraslados('pendientes');
    });

    $(document).on('change', '.check-traslado-enviado', function () {
        actualizarSeleccionTraslados('enviados');
    });

    $('#checkAllPendientes').on('change', function () {
        $('.check-traslado-pendiente:not(:disabled)').prop('checked', this.checked);
        actualizarSeleccionTraslados('pendientes');
    });

    $('#checkAllEnviados').on('change', function () {
        $('.check-traslado-enviado:not(:disabled)').prop('checked', this.checked);
        actualizarSeleccionTraslados('enviados');
    });
});

function procesarTrasladosSeleccionados(tipo, accion) {
    const selector = tipo === 'pendientes' ? '.check-traslado-pendiente:checked' : '.check-traslado-enviado:checked';
    const ids = $(selector).map(function () {
        return Number($(this).val());
    }).get();

    procesarTraslados(accion, ids);
}

function procesarTraslados(accion, ids) {
    if (!ids.length) {
        alerta('Selecciona al menos un movimiento.', false);
        return;
    }

    const mensaje = {
        aceptar: '¿Aceptar los traslados seleccionados?',
        rechazar: '¿Rechazar los traslados seleccionados?',
        cancelar: '¿Cancelar los traslados seleccionados?'
    }[accion] || '¿Confirmar esta acción?';

    if (!confirm(mensaje)) {
        return;
    }

    $.ajax({
        url: '/api/inventario/ejemplares/traslados/accion',
        type: 'POST',
        data: {
            movimiento_ids: ids,
            accion: accion,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            alerta(response.message, true);
            recargarTablasTraslados();
        },
        error: function (xhr) {
            const message = xhr.responseJSON?.message || 'No se pudo procesar la acción sobre los traslados.';
            alerta(message, false);
        }
    });
}

function recargarTablasTraslados() {
    if (tablaTrasladosPendientes) {
        tablaTrasladosPendientes.ajax.reload();
    }

    if (tablaTrasladosEnviados) {
        tablaTrasladosEnviados.ajax.reload();
    }
}

function actualizarSeleccionTraslados(tipo) {
    if (tipo === 'pendientes') {
        const total = $('.check-traslado-pendiente:checked').length;
        $('#pendingBulkCount').text(total);
        $('#pendingBulkBar').toggle(total > 0);
        return;
    }

    const total = $('.check-traslado-enviado:checked').length;
    $('#sentBulkCount').text(total);
    $('#sentBulkBar').toggle(total > 0);
}
