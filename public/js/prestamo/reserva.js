let tabla;
let modal;

$(document).ready(function () {
    tabla = $('#tabla-reservas').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        scrollX: false,
        autoWidth: false,
        ajax: {
            url: "/api/prestamos/reservas/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                d.tipo_usuario = $('#tipo_usuario').val();
            },
            error: default_error_handler
        },
        columns: [
            { data: 'fecha', name: 'fecha', className: 'reservation-col reservation-col--date' },
            { data: 'fecha_limite', name: 'fecha_limite', className: 'reservation-col reservation-col--date' },
            { data: 'libro', name: 'libro', className: 'reservation-col reservation-col--book' },
            { data: 'ejemplar', name: 'ejemplar', className: 'reservation-col reservation-col--code' },
            { data: 'lector', name: 'lector', className: 'reservation-col reservation-col--reader' },
            { data: 'estado', name: 'estado', className: 'reservation-col reservation-col--badge' },
            { data: 'prestamo', name: 'prestamo', className: 'reservation-col reservation-col--badge' },
            {
                data: 'acciones',
                name: 'acciones',
                orderable: false,
                searchable: false,
                className: 'reservation-col reservation-col--actions'
            }
        ],
        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-reservas');
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-reservas');
        }
    });

    $('#tabla-reservas').on('draw.dt', function () {
        $('.countdown').each(function () {
            let el = $(this);
            let seconds = parseInt(el.data('seconds'));

            if (seconds <= 0) {
                el.text('Vencido');
                return;
            }

            let interval = setInterval(function () {
                let d = Math.floor(seconds / 86400);
                let h = Math.floor((seconds % 86400) / 3600);
                let m = Math.floor((seconds % 3600) / 60);
                let s = seconds % 60;

                el.text(`${d}d ${h}h ${m}m ${s}s`);
                seconds--;

                if (seconds < 0) clearInterval(interval);
            }, 1000);
        });
    });

    modal = new bootstrap.Modal(document.getElementById('modalEntrega'));

    $(document).on('click', '.entregarReserva', function () {
        let id       = $(this).data('id');
        let libro    = $(this).data('libro')   || '—';
        let lector   = $(this).data('lector')  || '—';
        let tipo     = $(this).data('tipo')    || '—';
        let codigo   = $(this).data('codigo')  || '';
        let isbn     = $(this).data('isbn')    || '';
        let edicion  = $(this).data('edicion') || '';
        let autores  = $(this).data('autores') || '';

        $('#reserva_id').val(id);
        $('#dias').val('');
        $('#observaciones').val('');

        // Título
        $('#rsv-ctx-libro').text(libro);

        // Badges de metadatos
        if (codigo) {
            $('#rsv-ctx-codigo').text('Cód: ' + codigo).removeClass('d-none');
        } else {
            $('#rsv-ctx-codigo').addClass('d-none');
        }
        if (isbn) {
            $('#rsv-ctx-isbn').text('ISBN: ' + isbn).removeClass('d-none');
        } else {
            $('#rsv-ctx-isbn').addClass('d-none');
        }
        if (edicion) {
            $('#rsv-ctx-edicion').text(edicion).removeClass('d-none');
        } else {
            $('#rsv-ctx-edicion').addClass('d-none');
        }

        // Autores
        if (autores) {
            $('#rsv-ctx-autores')
                .html('<i class="bi bi-person-lines-fill"></i> ' + autores)
                .removeClass('d-none');
        } else {
            $('#rsv-ctx-autores').addClass('d-none');
        }

        // Lector y tipo
        $('#rsv-ctx-lector').text(lector);
        let tipoIcon  = tipo === 'A CASA' ? '<i class="bi bi-house-fill me-1"></i>' : '<i class="bi bi-building me-1"></i>';
        let tipoClass = tipo === 'A CASA' ? 'rsv-tipo-pill--casa' : 'rsv-tipo-pill--sala';
        $('#rsv-ctx-tipo').html(tipoIcon + tipo).attr('class', 'rsv-tipo-pill ' + tipoClass);

        modal.show();
    });

    $('#formEntrega').on('submit', function (e) {
        e.preventDefault();

        let id = $('#reserva_id').val();
        let dias = $('#dias').val();
        let observaciones = $('#observaciones').val();

        fetch(`/api/prestamos/reserva/${id}/entregar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                dias: dias,
                observaciones: observaciones
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alerta(data.success, true);
                    modal.hide();
                    $('#formEntrega')[0].reset();
                    tabla.ajax.reload(null, false);
                } else {
                    alerta(data.error || 'Ocurrio un error', false);
                }
            })
            .catch(() => {
                alerta('Error en la peticion', false);
            });
    });
});
