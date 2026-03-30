let tabla;
let modal;

function renderizarPreviewSancion(data) {
    const $box = $('#sancionPreview');

    if (!data || data.aplica === false) {
        $box
            .removeClass('loan-register__sanction--warning loan-register__sanction--danger')
            .addClass('loan-register__sanction--neutral')
            .html(
                '<div class="loan-register__sanction-title">No se aplicara sancion</div>' +
                '<div class="loan-register__sanction-copy">' + (data?.motivo || 'La devolucion actual no genera una sancion automatica.') + '</div>'
            );
        return;
    }

    const sanciones = Array.isArray(data.sanciones) && data.sanciones.length ? data.sanciones : [data];
    const tieneDeterioro = sanciones.some(item => item.evento === 'devolucion_deterioro');
    const title = sanciones.length > 1
        ? `Se aplicaran ${sanciones.length} sanciones`
        : 'Se aplicara sancion: ' + (sanciones[0].nombre || sanciones[0].codigo);

    const details = sanciones.map(item => {
        const extras = [];
        if (item.duracion_dias) extras.push(`Duracion: ${item.duracion_dias} dia(s)`);
        if (item.monto) extras.push(`Monto: S/ ${item.monto}`);
        if (item.requiere_pago) extras.push('Requiere pago');
        if (item.bloquea_prestamos) extras.push('Bloquea prestamos');

        return (
            '<div class="loan-register__sanction-entry">' +
                '<div class="loan-register__sanction-entry-name">' + (item.nombre || item.codigo) + '</div>' +
                '<div class="loan-register__sanction-copy">' + (item.descripcion || item.motivo || 'La devolucion activa una sancion automatica.') + '</div>' +
                (extras.length ? '<div class="loan-register__sanction-meta">' + extras.join(' · ') + '</div>' : '') +
            '</div>'
        );
    }).join('');

    $box
        .removeClass('loan-register__sanction--neutral')
        .addClass(tieneDeterioro ? 'loan-register__sanction--danger' : 'loan-register__sanction--warning')
        .html('<div class="loan-register__sanction-title">' + title + '</div>' + details);
}

function cargarPreviewSancion() {
    const id = $('#prestamo_id').val();
    if (!id) return;

    fetch(`/api/prestamos/${id}/preview-sancion?estado_libro=${encodeURIComponent($('#estado_libro').val())}&dias_retraso=${encodeURIComponent($('#dias_retraso').val())}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.json())
        .then(renderizarPreviewSancion)
        .catch(() => {
            renderizarPreviewSancion({
                aplica: false,
                motivo: 'No se pudo obtener la previsualizacion de sancion en este momento.'
            });
        });
}

$(document).ready(function () {
    tabla = $('#tabla-prestamos').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        scrollX: false,
        autoWidth: false,

        ajax: {
            url: "/api/prestamos/prestamos/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                d.tipo_usuario = $('#tipo_usuario').val();
            },
            error: default_error_handler
        },

        columns: [
            { data: 'fecha_prestamo', name: 'fecha_prestamo', className: 'loan-col loan-col--date' },
            { data: 'fecha_limite', name: 'fecha_limite', className: 'loan-col loan-col--date' },
            { data: 'libro', name: 'libro', className: 'loan-col loan-col--book' },
            { data: 'ejemplar', name: 'ejemplar', className: 'loan-col loan-col--code' },
            { data: 'lector', name: 'lector', className: 'loan-col loan-col--reader' },
            { data: 'estado_prestamo', name: 'estado_prestamo', className: 'loan-col loan-col--badge' },
            { data: 'estado', name: 'estado', className: 'loan-col loan-col--badge' },
            {
                data: 'acciones',
                name: 'acciones',
                orderable: false,
                searchable: false,
                className: 'loan-col loan-col--actions'
            }
        ],

        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-prestamos');
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-prestamos');
        }
    });

    $('#tabla-prestamos').on('draw.dt', function () {
        iniciarCountdown();
    });

    modal = new bootstrap.Modal(document.getElementById('modalPrestamo'));

    $(document).on('click', '.devolverPrestamo', function () {
        let tr = $(this).closest('tr');
        if (tr.hasClass('child')) {
            tr = tr.prev();
        }

        let data = tabla.row(tr).data();
        $('#prestamo_id').val(data.id);
        $('#libro_nombre').text($('<div>').html(data.libro).text());
        $('#ejemplar_codigo').text($('<div>').html(data.ejemplar).text());

        let ahora = new Date();
        let fechaLimite = new Date(data.fecha_limite_raw);
        let diffTime = ahora - fechaLimite;
        let diasRetraso = Math.floor(diffTime / (1000 * 60 * 60 * 24));

        if (diasRetraso > 0) {
            let mensaje = diasRetraso === 1
                ? 'Tiene 1 dia de retraso. Se aplicara penalizacion.'
                : `Tiene ${diasRetraso} dias de retraso. Se aplicara penalizacion.`;

            $('#mensajeRetraso')
                .removeClass('d-none')
                .text(mensaje);

            $('#dias_retraso').val(diasRetraso);
        } else {
            $('#mensajeRetraso')
                .addClass('d-none')
                .text('');

            $('#dias_retraso').val(0);
        }

        cargarPreviewSancion();
        modal.show();
    });

    $('#estado_libro, #dias_retraso').on('change input', function () {
        cargarPreviewSancion();
    });

    $('#formEntrega').on('submit', function (e) {
        e.preventDefault();

        let id = $('#prestamo_id').val();

        fetch(`/api/prestamos/${id}/devolver`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                estado_libro: $('#estado_libro').val(),
                dias_retraso: $('#dias_retraso').val(),
                observaciones: $('#observaciones').val()
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    alerta(data.ok, true);
                    bootstrap.Modal.getInstance(document.getElementById('modalPrestamo')).hide();
                    $('#formEntrega')[0].reset();
                    tabla.ajax.reload(null, false);
                }
            });
    });
});

function iniciarCountdown() {
    $('.countdown').each(function () {
        let el = $(this);

        if (el.data('iniciado')) return;

        let seconds = parseInt(el.data('seconds'));

        if (isNaN(seconds) || seconds <= 0) {
            el.text('Vencido');
            return;
        }

        el.data('iniciado', true);

        let interval = setInterval(function () {
            let d = Math.floor(seconds / 86400);
            let h = Math.floor((seconds % 86400) / 3600);
            let m = Math.floor((seconds % 3600) / 60);
            let s = seconds % 60;

            el.text(`${d}d ${h}h ${m}m ${s}s`);

            seconds--;

            if (seconds < 0) {
                el.text('Vencido');
                clearInterval(interval);
            }
        }, 1000);
    });
}
