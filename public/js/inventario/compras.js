let tablaCompras;

$(document).ready(function () {
    tablaCompras = $('#tabla-compras').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [],
        ajax: {
            url: '/api/inventario/compras/listar',
            type: 'GET',
            xhrFields: { withCredentials: true },
            error: default_error_handler
        },
        columns: [
            { data: 'numero_siaf', name: 'numero_siaf' },
            {
                data: 'proveedor',
                name: 'proveedor.razon_social',
                render: function (data) {
                    if (!data) {
                        return '<span class="text-muted">Sin proveedor</span>';
                    }

                    if (data.responsable) {
                        return `<strong>${safeHTML(data.responsable)}</strong><br><small class="text-muted">${safeHTML(data.razon_social ?? '')}</small>`;
                    }

                    return `<strong>${safeHTML(data.razon_social ?? '')}</strong>`;
                }
            },
            {
                data: 'fecha_compra',
                name: 'fecha_compra',
                render: function (data) {
                    return data ? `<span class="purchase-date">${formatearFechaCompra(data)}</span>` : '-';
                }
            },
            {
                data: 'monto_total',
                name: 'monto_total',
                render: function (data) {
                    return `<span class="purchase-amount">S/ ${Number(data || 0).toFixed(2)}</span>`;
                }
            },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-compras');
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-compras');
        }
    });

    $('#tabla-compras').on('click', '.verCompra', function () {
        const id = $(this).data('id');
        if (!id) {
            return;
        }

        cargarCompra(id);
    });

    $('#modalVerCompra').on('hidden.bs.modal', function () {
        resetearModalCompra();
    });
});

function cargarCompra(id) {
    resetearModalCompra();
    $('#ver_proveedor').text('Cargando informacion...');
    $('#tablaDetalleCompra').html(`
        <tr>
            <td colspan="5" class="text-center text-muted py-4">Cargando detalle de compra...</td>
        </tr>
    `);

    $('#modalVerCompra').modal('show');

    $.ajax({
        url: `/api/inventario/compras/${id}`,
        type: 'GET',
        xhrFields: { withCredentials: true },
        success: function (response) {
            const data = response?.data;
            if (!data) {
                alerta('No se pudo cargar el detalle de la compra.', 0);
                return;
            }

            $('#ver_siaf').text(data.numero_siaf ?? '-');
            $('#ver_fecha').text(formatearFechaCompra(data.fecha_compra));
            $('#ver_proveedor').text(data.proveedor?.razon_social ?? 'Sin proveedor');
            $('#ver_proveedor_responsable').text(data.proveedor?.responsable ?? 'Sin responsable registrado');
            $('#ver_total').text(`S/ ${Number(data.monto_total || 0).toFixed(2)}`);
            $('#ver_observaciones').text((data.observaciones || '').trim() || 'Sin observaciones registradas.');
            $('#tablaDetalleCompra').html(renderDetalleCompra(data.compra_detalles || []));
        },
        error: function () {
            $('#tablaDetalleCompra').html(`
                <tr>
                    <td colspan="5" class="text-center text-danger py-4">No se pudo cargar el detalle de la compra.</td>
                </tr>
            `);
            $('#ver_proveedor').text('Error al cargar');
            alerta('No se pudo obtener el detalle de la compra.', 0);
        }
    });
}

function renderDetalleCompra(detalles) {
    if (!Array.isArray(detalles) || !detalles.length) {
        return `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">No hay detalle asociado a esta compra.</td>
            </tr>
        `;
    }

    return detalles.map(function (det) {
        let ejemplares = '<span class="text-muted">Sin ejemplares generados</span>';

        if (Array.isArray(det.ejemplares) && det.ejemplares.length) {
            ejemplares = `
                <div class="purchase-badges">
                    ${det.ejemplares.map(function (e) {
                        const codigo = `${e.codigo_dewey ?? ''}${e.tipo ?? ''}${e.codigo_interno ?? ''}`.trim() || 'Sin codigo';
                        const biblioteca = e.biblioteca?.nombre ? `<small>${safeHTML(e.biblioteca.nombre)}</small>` : '<small>Sin biblioteca</small>';
                        return `
                            <div class="purchase-badge-card">
                                <span class="badge bg-primary">${safeHTML(codigo)}</span>
                                ${biblioteca}
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        const codigoLibro = det.libro?.codigo_dewey
            ? `${det.libro.codigo_dewey}${det.libro.codigo ?? ''}`
            : 'Sin codigo catalografico';

        return `
            <tr>
                <td>
                    <div class="purchase-view-modal__book">
                        <strong>${safeHTML(det.libro?.titulo ?? 'Libro no disponible')}</strong>
                        <small>${safeHTML(codigoLibro)}</small>
                    </div>
                </td>
                <td><span class="purchase-view-modal__value">${det.cantidad ?? 0}</span></td>
                <td><span class="purchase-view-modal__value">S/ ${Number(det.precio_unitario || 0).toFixed(2)}</span></td>
                <td><span class="purchase-view-modal__value">S/ ${Number(det.monto_total || 0).toFixed(2)}</span></td>
                <td>${ejemplares}</td>
            </tr>
        `;
    }).join('');
}

function resetearModalCompra() {
    $('#ver_siaf').text('-');
    $('#ver_fecha').text('-');
    $('#ver_proveedor').text('-');
    $('#ver_proveedor_responsable').text('Sin responsable registrado');
    $('#ver_total').text('S/ 0.00');
    $('#ver_observaciones').text('Sin observaciones registradas.');
    $('#tablaDetalleCompra').html('');
}

function formatearFechaCompra(value) {
    const texto = String(value || '');
    if (/^\d{4}-\d{2}-\d{2}/.test(texto)) {
        const [anio, mes, dia] = texto.slice(0, 10).split('-');
        return `${dia}/${mes}/${anio}`;
    }

    return texto || '-';
}
