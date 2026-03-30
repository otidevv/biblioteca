let tablaSanciones;
let reglasActuales = [];

$(document).ready(function () {
    tablaSanciones = $('#tabla-sanciones').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url: '/api/sanciones/listar',
            type: 'GET',
            xhrFields: { withCredentials: true },
            error: default_error_handler
        },
        columns: [
            { data: 'codigo', name: 'codigo' },
            { data: 'nombre', name: 'nombre' },
            { data: 'origen_evento', name: 'origen_evento', defaultContent: '-' },
            { data: 'condicion', name: 'condicion', defaultContent: '-' },
            {
                data: 'dias_duracion',
                name: 'dias_duracion',
                render: function (data) {
                    return data !== null && data !== '' ? `${data} dia(s)` : '-';
                }
            },
            {
                data: 'monto',
                name: 'monto',
                render: function (data) {
                    return data !== null && data !== '' ? `S/ ${parseFloat(data).toFixed(2)}` : '-';
                }
            },
            { data: 'estado_badge', name: 'estado', orderable: false, searchable: false },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-sanciones');
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-sanciones');
        }
    });

    $('#btnNuevo').on('click', function () {
        $('#formSancionTipo')[0].reset();
        $('#id').val('');
        $('#estado').val('1');
        $('#requiere_pago').val('0');
        $('#bloquea_prestamos').val('1');
        $('#aplica_automaticamente').val('0');
        $('#modalSancionTipo').modal('show');
    });

    $('#tabla-sanciones').on('click', '.editarSancionTipo', function () {
        let data = tablaSanciones.row($(this).closest('tr')).data();

        $('#id').val(data.id);
        $('#codigo').val(data.codigo ?? '');
        $('#nombre').val(data.nombre ?? '');
        $('#descripcion').val(data.descripcion ?? '');
        $('#origen_evento').val(data.origen_evento ?? '');
        $('#condicion').val(data.condicion ?? '');
        $('#dias_duracion').val(data.dias_duracion ?? '');
        $('#monto').val(data.monto ?? '');
        $('#estado').val(Number(data.estado) === 1 ? '1' : '0');
        $('#requiere_pago').val(Number(data.requiere_pago) === 1 ? '1' : '0');
        $('#bloquea_prestamos').val(Number(data.bloquea_prestamos) === 1 ? '1' : '0');
        $('#aplica_automaticamente').val(Number(data.aplica_automaticamente) === 1 ? '1' : '0');

        $('#modalSancionTipo').modal('show');
    });

    $('#tabla-sanciones').on('click', '.reglasSancionTipo', function () {
        let data = tablaSanciones.row($(this).closest('tr')).data();
        abrirReglasSancion(data);
    });

    $('#formSancionTipo').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);
        let btn = form.find('button[type="submit"]');

        btn.prop('disabled', true).text('Guardando...');

        if (!validar('#formSancionTipo')) {
            btn.prop('disabled', false).text('Guardar');
            return;
        }

        $.ajax({
            url: $('#id').val() === '' ? '/api/sanciones/nuevo' : '/api/sanciones/edit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
                if (response.success) {
                    alerta(response.message ?? 'Tipo de sancion guardado correctamente', true);
                    form[0].reset();
                    $('#modalSancionTipo').modal('hide');
                    tablaSanciones.ajax.reload();
                } else {
                    alerta(response.message ?? 'Error al guardar el tipo de sancion', false);
                }
            },
            error: function (xhr) {
                $('.is-invalid').removeClass('is-invalid');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (field, messages) {
                        let input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        alerta(messages[0], false);
                    });
                } else {
                    alerta(xhr.responseJSON.message ?? 'Error al guardar el tipo de sancion', false);
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

    $('#btnNuevaRegla').on('click', function () {
        limpiarFormularioRegla();
    });

    $('#tablaReglasSancionBody').on('click', '.editarReglaSancion', function () {
        const reglaId = Number($(this).data('id'));
        const regla = reglasActuales.find(item => Number(item.id) === reglaId);

        if (!regla) {
            return;
        }

        $('#regla_id').val(regla.id);
        $('#evento').val(regla.evento ?? '');
        $('#dias_desde').val(regla.dias_desde ?? '');
        $('#dias_hasta').val(regla.dias_hasta ?? '');
        $('#cantidad_minima').val(regla.cantidad_minima ?? '');
        $('#cantidad_maxima').val(regla.cantidad_maxima ?? '');
        $('#duracion_dias').val(regla.duracion_dias ?? '');
        $('#regla_monto').val(regla.monto ?? '');
        $('#requiere_aprobacion').val(Number(regla.requiere_aprobacion) === 1 ? '1' : '0');
        $('#regla_estado').val(Number(regla.estado) === 1 ? '1' : '0');
    });

    $('#formReglaSancion').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);
        let btn = form.find('button[type="submit"]');

        btn.prop('disabled', true).text('Guardando...');

        if (!validar('#formReglaSancion')) {
            btn.prop('disabled', false).text('Guardar regla');
            return;
        }

        $.ajax({
            url: '/api/sanciones/reglas/guardar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
                if (response.success) {
                    alerta(response.message ?? 'Regla guardada correctamente', true);
                    const tipoId = $('#regla_tipo_sancion_id').val();
                    limpiarFormularioRegla(false);
                    cargarReglasSancion(tipoId);
                } else {
                    alerta(response.message ?? 'Error al guardar la regla', false);
                }
            },
            error: function (xhr) {
                $('.is-invalid').removeClass('is-invalid');

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (field, messages) {
                        let input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        alerta(messages[0], false);
                    });
                } else {
                    alerta(xhr.responseJSON.message ?? 'Error al guardar la regla', false);
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar regla');
            }
        });
    });
});

function abrirReglasSancion(data) {
    $('#reglasSancionTitulo').text(`Configuracion automatica para: ${data.nombre ?? 'Tipo de sancion'}`);
    $('#regla_tipo_sancion_id').val(data.id);
    limpiarFormularioRegla(false);
    cargarReglasSancion(data.id);
    $('#modalReglasSancion').modal('show');
}

function cargarReglasSancion(tipoId) {
    $.ajax({
        url: `/api/sanciones/${tipoId}/reglas`,
        type: 'GET',
        success: function (response) {
            reglasActuales = response.reglas ?? [];
            renderizarTablaReglas();
        },
        error: function (xhr) {
            alerta(xhr.responseJSON?.message ?? 'No se pudieron cargar las reglas de sancion.', false);
        }
    });
}

function renderizarTablaReglas() {
    const tbody = $('#tablaReglasSancionBody');

    if (!reglasActuales.length) {
        tbody.html(`
            <tr>
                <td colspan="7" class="text-center text-muted py-4">Aun no hay reglas registradas para este tipo de sancion.</td>
            </tr>
        `);
        return;
    }

    tbody.html(reglasActuales.map(regla => {
        const rangoDias = [regla.dias_desde ?? '-', regla.dias_hasta ?? '-'].join(' a ');
        const rangoCantidad = [regla.cantidad_minima ?? '-', regla.cantidad_maxima ?? '-'].join(' a ');
        const monto = regla.monto !== null && regla.monto !== '' ? `S/ ${parseFloat(regla.monto).toFixed(2)}` : '-';
        const estado = Number(regla.estado) === 1
            ? '<span class="badge bg-success">Activa</span>'
            : '<span class="badge bg-secondary">Inactiva</span>';

        return `
            <tr>
                <td>${regla.evento ?? '-'}</td>
                <td>${rangoDias}</td>
                <td>${rangoCantidad}</td>
                <td>${regla.duracion_dias ?? '-'} </td>
                <td>${monto}</td>
                <td>${estado}</td>
                <td><button type="button" class="btn btn-sm btn-primary editarReglaSancion" data-id="${regla.id}">Editar</button></td>
            </tr>
        `;
    }).join(''));
}

function limpiarFormularioRegla(resetTipo = false) {
    $('#formReglaSancion')[0].reset();
    $('#regla_id').val('');
    $('#requiere_aprobacion').val('0');
    $('#regla_estado').val('1');

    if (resetTipo) {
        $('#regla_tipo_sancion_id').val('');
    }
}
