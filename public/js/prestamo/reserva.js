let tabla;
let modal;

$(document).ready(function () {

    // 🔹 Inicializar DataTable
    tabla = $('#tabla-reservas').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
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
            { data: 'fecha', name: 'fecha' },
            { data: 'fecha_limite', name: 'fecha_limite' },
            { data: 'libro', name: 'libro' },
            { data: 'ejemplar', name: 'ejemplar' },
            { data: 'lector', name: 'lector' },
            { data: 'estado', name: 'estado' },
            { data: 'prestamo', name: 'prestamo' },
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

    // 🔹 Countdown
    $('#tabla-reservas').on('draw.dt', function () {
        $('.countdown').each(function() {
            let el = $(this);
            let seconds = parseInt(el.data('seconds'));

            if (seconds <= 0) { 
                el.text('Vencido'); 
                return; 
            }

            let interval = setInterval(function() {
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

    // 🔹 Inicializar modal
    modal = new bootstrap.Modal(document.getElementById('modalEntrega'));

    // 🔥 Abrir modal (compatible con DataTables)
    $(document).on('click', '.entregarReserva', function () {
        let id = $(this).data('id');

        $('#reserva_id').val(id);
        $('#dias').val(1);
        $('#observaciones').val('');

        modal.show();
    });

    // 🔥 Enviar formulario (SIN recargar página)
    $('#formEntrega').on('submit', function(e){
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

                // limpiar formulario
                $('#formEntrega')[0].reset();

                // 🔥 recargar SOLO la tabla
                tabla.ajax.reload(null, false);

            } else {
                alerta(data.error || 'Ocurrió un error', false);
            }
        })
        .catch(() => {
            alerta('Error en la petición', false);
        });
    });

});