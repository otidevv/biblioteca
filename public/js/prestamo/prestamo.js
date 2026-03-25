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

    //Abrir modal (compatible con DataTables)
    $(document).on('click', '.devolverPrestamo', function(){

      let tr = $(this).closest('tr');

    if (tr.hasClass('child')) {
        tr = tr.prev();
    }

    let data = tabla.row(tr).data();

    $('#prestamo_id').val(data.id);
    $('#libro_nombre').text(data.libro);
    $('#ejemplar_codigo').text(data.ejemplar);

    let ahora = new Date();
    let fechaLimite = new Date(data.fecha_limite_raw);

    let diffTime = ahora - fechaLimite;
    let diasRetraso = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    if (diasRetraso > 0) {

        // 🔴 mostrar alerta
        $('#alertaRetraso').removeClass('d-none');

        // texto dinámico
        $('#diasTexto').text(diasRetraso);

        // autocompletar campo
        $('#dias_retraso').val(diasRetraso);

    } else {

        $('#alertaRetraso').addClass('d-none');
        $('#dias_retraso').val(0);
    }

    let modal = new bootstrap.Modal(document.getElementById('modalPrestamo'));
    modal.show();
    });

    //Enviar formulario (SIN recargar página)
    $('#formEntrega').on('submit', function(e){
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