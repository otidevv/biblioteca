let tabla;
let modal;

$(document).ready(function () {
    tabla = $('#tabla-prestamos').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        scrollX: true,      // 👈 scroll horizontal

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
            { data: 'fecha_prestamo', name: 'fecha_prestamo' },
            { data: 'fecha_limite', name: 'fecha_limite' },
            { data: 'libro', name: 'libro' },
            { data: 'ejemplar', name: 'ejemplar' },
            { data: 'lector', name: 'lector' },
            { data: 'estado_prestamo', name: 'estado_prestamo' }, 
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
        initComplete: default_datatable_buttons
    });

    // 🔹 Countdown
    $('#tabla-prestamos').on('draw.dt', function () {
        iniciarCountdown();
    });

    // 🔹 Inicializar modal
    modal = new bootstrap.Modal(document.getElementById('modalPrestamo'));

    // 🔥 Abrir modal (compatible con DataTables)
    $(document).on('click', '.devolverPrestamo', function () {
        let id = $(this).data('id');

        $('#prestamo_id').val(id);
        $('#dias').val(1);
        $('#observaciones').val('');

        modal.show();
    });

    // 🔥 Enviar formulario (SIN recargar página)
    $('#formEntrega').on('submit', function(e){
        e.preventDefault();

        let id = $('#prestamo_id').val();
        let dias = $('#dias').val();
        let observaciones = $('#observaciones').val();
        let estado = $('#estado_prestamo').val();

        fetch(`/api/prestamos/${id}/devolver`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                dias: dias,
                observaciones: observaciones,
                estado:estado
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
function iniciarCountdown() {

    $('.countdown').each(function() {

        let el = $(this);

        // evitar duplicar intervalos
        if (el.data('iniciado')) return;

        let seconds = parseInt(el.data('seconds'));

        if (isNaN(seconds) || seconds <= 0) {
            el.text('Vencido');
            return;
        }

        el.data('iniciado', true);

        let interval = setInterval(function() {

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