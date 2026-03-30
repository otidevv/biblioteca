let detalleCompra = [];
let libroSeleccionado = null;

$(document).ready(function () {
    $('#proveedor_id').select2({
        placeholder: 'Seleccione proveedor',
        allowClear: true,
        width: '100%',
        language: 'es'
    });

    $('#btnNuevoLibro').on('click', function () {
        resetLibroModal();
        $('#modalLibro').modal('show');
    });

    $('#libros').select2({
        dropdownParent: $('#modalLibro'),
        placeholder: 'Buscar libro...',
        width: '100%',
        language: 'es',
        allowClear: true,
        ajax: {
            url: '/api/inventario/libros',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data };
            }
        }
    });

    $('#libros').on('select2:select', function (e) {
        libroSeleccionado = e.params.data;
        mostrarDatosLibro(libroSeleccionado);
    });

    $('#libros').on('select2:clear', function () {
        limpiarCamposLibro();
    });

    $('#formLibro').on('submit', function (e) {
        e.preventDefault();

        if (!libroSeleccionado) {
            alerta('Seleccione un libro para agregarlo al detalle.', false);
            return;
        }

        const cantidad = parseInt($('#modal_cantidad').val(), 10);
        const precio = parseFloat($('#modal_precio').val());

        if (!cantidad || cantidad <= 0 || !precio || precio <= 0) {
            alerta('Cantidad y precio unitario son obligatorios.', false);
            return;
        }

        const subtotal = cantidad * precio;
        const autores = Array.isArray(libroSeleccionado.autores)
            ? libroSeleccionado.autores.map(autor => autor.nombre).join(', ')
            : '';

        detalleCompra.push({
            libro_id: libroSeleccionado.id,
            titulo: libroSeleccionado.text,
            autor: autores,
            cantidad: cantidad,
            precio: precio,
            subtotal: subtotal
        });

        renderDetalleCompra();
        calcularTotal();
        $('#modalLibro').modal('hide');
    });

    $(document).on('click', '.btnEliminarDetalle', function () {
        const index = Number($(this).data('index'));
        detalleCompra.splice(index, 1);
        renderDetalleCompra();
        calcularTotal();
    });

    $('#formCompra').on('submit', function (e) {
        e.preventDefault();

        if (!validar('#formCompra')) {
            return;
        }

        if (!detalleCompra.length) {
            alerta('Debe agregar al menos un libro al detalle de la compra.', false);
            return;
        }

        const btn = $('#btnGuardarCompra');
        btn.prop('disabled', true);

        $.ajax({
            url: '/api/inventario/compras/guardar',
            method: 'POST',
            data: JSON.stringify({
                siaf: $('#numero_siaf').val(),
                fecha_compra: $('#fecha_compra').val(),
                proveedor_id: $('#proveedor_id').val(),
                observaciones: $('#observaciones').val(),
                detalle: detalleCompra
            }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                alerta(response.mensaje || 'Compra guardada correctamente.', true);
                window.location.href = '/inventario/compras';
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const primerError = Object.values(xhr.responseJSON.errors)[0]?.[0] || 'No se pudo guardar la compra.';
                    alerta(primerError, false);
                    btn.prop('disabled', false);
                    return;
                }

                alerta(xhr.responseJSON?.error || xhr.responseJSON?.message || 'No se pudo guardar la compra.', false);
                btn.prop('disabled', false);
            }
        });
    });
});

function resetLibroModal() {
    $('#formLibro')[0].reset();
    $('#libros').val(null).trigger('change');
    limpiarCamposLibro();
    $('#modal_cantidad').val(1);
}

function mostrarDatosLibro(data) {
    $('#lbl_titulo').text(data.text || '');
    $('#lbl_autor').text(Array.isArray(data.autores) ? data.autores.map(a => a.nombre).join(', ') : '');
    $('#lbl_editorial').text(data.editorial?.nombre || '');

    if (data.imagen) {
        $('#preview_imagen').attr('src', data.imagen).show();
        $('#preview_empty').hide();
    } else {
        $('#preview_imagen').attr('src', '').hide();
        $('#preview_empty').show();
    }
}

function limpiarCamposLibro() {
    libroSeleccionado = null;
    $('#lbl_titulo').text('');
    $('#lbl_autor').text('');
    $('#lbl_editorial').text('');
    $('#preview_imagen').attr('src', '').hide();
    $('#preview_empty').show();
}

function renderDetalleCompra() {
    const $tbody = $('#tablaDetalles tbody');

    if (!detalleCompra.length) {
        $tbody.html(`
            <tr class="purchase-create__empty-row">
                <td colspan="6" class="text-center text-muted py-4">Todavia no has agregado libros a esta compra.</td>
            </tr>
        `);
        return;
    }

    $tbody.html(detalleCompra.map((item, index) => `
        <tr>
            <td>${safeHTML(item.titulo)}</td>
            <td>${safeHTML(item.autor || '-')}</td>
            <td>${item.cantidad}</td>
            <td>S/ ${Number(item.precio).toFixed(2)}</td>
            <td>S/ ${Number(item.subtotal).toFixed(2)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm btnEliminarDetalle" data-index="${index}">
                    Quitar
                </button>
            </td>
        </tr>
    `).join(''));
}

function calcularTotal() {
    const total = detalleCompra.reduce((acumulado, item) => acumulado + Number(item.subtotal || 0), 0);
    $('#monto_total').val(total.toFixed(2));
}
