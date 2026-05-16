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
                (extras.length ? '<div class="loan-register__sanction-meta">' + extras.join(' � ') + '</div>' : '') +
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
            { data: 'fechas',         name: 'fecha_prestamo',  className: 'loan-col loan-col--date',   searchable: false },
            { data: 'libro',          name: 'libro',           className: 'loan-col loan-col--book' },
            { data: 'ejemplar',       name: 'ejemplar',        className: 'loan-col loan-col--code' },
            { data: 'lector',         name: 'lector',          className: 'loan-col loan-col--reader' },
            { data: 'estado_prestamo',name: 'estado_prestamo', className: 'loan-col loan-col--status', searchable: false, orderable: false },
            { data: 'estado',         name: 'estado',          className: 'loan-col loan-col--badge',  searchable: false, orderable: false },
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

        let libroEl = $('<div>').html(data.libro);
        $('#libro_nombre').text(libroEl.find('.loan-table__book').text() || libroEl.text());

        let ejemplarEl = $('<div>').html(data.ejemplar);
        $('#ejemplar_codigo').text(ejemplarEl.find('.loan-table__code').text() || ejemplarEl.text());

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

// ── Préstamo directo ──────────────────────────────────────────────────────────

(function () {
    let pdStep = 1;
    let pdLectorId = null;
    let pdEjemplarId = null;
    let modalPD = null;

    function pdReset() {
        pdStep = 1;
        pdLectorId = null;
        pdEjemplarId = null;

        $('#pd-lector-q').val('');
        $('#pd-lector-results').empty();
        $('#pd-lector-sel').addClass('d-none');
        $('#pd-libro-q').val('');
        $('#pd-libro-results').empty();
        $('#pd-ejemplar-sel').addClass('d-none');
        $('#pd-filtro-bib').val('');
        $('#pd-dias').val('');
        $('#pd-tipo').val('0');
        $('#pd-obs').val('');
        $('#pd-fecha-est').val('');

        pdShowStep(1);
    }

    function pdShowStep(n) {
        pdStep = n;
        $('.pd-step-body').addClass('d-none');
        $('#pd-body-' + n).removeClass('d-none');

        $('.pd-step-tab').each(function () {
            const step = parseInt($(this).data('step'));
            const $bubble = $(this).find('.pd-step-bubble');
            $(this).removeClass('pd-step--active pd-step--done active fw-bold').css('border-bottom', '');

            if (step < n) {
                $(this).addClass('pd-step--done');
                $bubble.html('<i class="bi bi-check-lg"></i>');
            } else {
                $bubble.text(step);
                if (step === n) $(this).addClass('pd-step--active');
            }
        });

        $('#pd-btn-prev').prop('disabled', n === 1);
        $('#pd-btn-next').toggleClass('d-none', n === 3);
        $('#pd-btn-confirm').toggleClass('d-none', n !== 3);

        if (n === 2) pdCargarBibliotecas();
    }

    // ── Tarjeta lector ──
    function pdLectorCard(lector) {
        const partes  = lector.text.split(' - ');
        const nombre  = partes[0] || lector.text;
        const detalle = partes.slice(1).join(' · ');

        return $('<div>')
            .addClass('pd-result-item pd-lector-item')
            .attr('data-id', lector.id)
            .attr('data-text', lector.text)
            .html(
                '<div class="pd-result-avatar"><i class="bi bi-person-fill"></i></div>' +
                '<div class="pd-result-main">' +
                    '<strong class="pd-result-name">' + esc(nombre) + '</strong>' +
                    (detalle ? '<span class="pd-result-sub">' + esc(detalle) + '</span>' : '') +
                '</div>' +
                '<i class="bi bi-chevron-right pd-result-chevron"></i>'
            );
    }

    // ── Tarjeta ejemplar ──
    function pdEjemplarCard(e) {
        return $('<div>')
            .addClass('pd-result-item pd-ejemplar-item')
            .attr('data-id', e.id)
            .attr('data-libro', e.libro)
            .attr('data-codigo', e.codigo)
            .attr('data-bib', e.biblioteca)
            .html(
                '<div class="pd-result-avatar pd-result-avatar--book"><i class="bi bi-book-half"></i></div>' +
                '<div class="pd-result-main">' +
                    '<strong class="pd-result-name">' + esc(e.libro) + '</strong>' +
                    '<span class="pd-result-sub">' +
                        '<i class="bi bi-building" style="margin-right:3px;"></i>' + esc(e.biblioteca) +
                        '<span style="margin:0 5px;opacity:.4;">·</span>' +
                        '<code>Cód: ' + esc(e.codigo) + '</code>' +
                    '</span>' +
                '</div>' +
                '<span class="pd-result-badge"><i class="bi bi-check-circle"></i>Disponible</span>'
            );
    }

    function esc(str) {
        return $('<div>').text(str).html();
    }

    // ── Cargar bibliotecas accesibles ──
    let bibCargadas = false;
    function pdCargarBibliotecas() {
        if (bibCargadas) return;

        fetch('/api/prestamos/bibliotecas/accesibles', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (! Array.isArray(data)) return;
                const $sel = $('#pd-filtro-bib');
                data.forEach(b => {
                    $sel.append($('<option>').val(b.id).text(b.nombre));
                });
                bibCargadas = true;
            });
    }

    $(document).ready(function () {
        modalPD = new bootstrap.Modal(document.getElementById('modalPrestamoDirecto'));

        document.getElementById('modalPrestamoDirecto').addEventListener('hidden.bs.modal', pdReset);

        $('#btnNuevoPrestamoDirecto').on('click', function () {
            pdReset();
            bibCargadas = false;
            modalPD.show();
        });

        // ── Buscar lector ──
        function buscarLector() {
            const q = $('#pd-lector-q').val().trim();
            if (q.length < 2) { alerta('Escribe al menos 2 caracteres para buscar.', false); return; }

            $('#pd-lector-results').html(
                '<div class="text-muted small py-3 text-center"><i class="bi bi-hourglass-split me-1"></i>Buscando...</div>'
            );

            fetch('/api/prestamos/multas/lectores?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    const $wrap = $('<div class="pd-results-wrap">');
                    if (! data.results || data.results.length === 0) {
                        $wrap.append('<div class="p-3 text-muted small text-center">No se encontraron lectores.</div>');
                    } else {
                        data.results.forEach(l => $wrap.append(pdLectorCard(l)));
                    }
                    $('#pd-lector-results').html($wrap);
                })
                .catch(() => $('#pd-lector-results').html('<div class="text-danger small p-2">Error al buscar.</div>'));
        }

        $('#pd-lector-buscar').on('click', buscarLector);
        $('#pd-lector-q').on('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); buscarLector(); } });

        $(document).on('click', '.pd-lector-item', function () {
            pdLectorId = $(this).data('id');
            const texto  = $(this).data('text');
            const partes = texto.split(' - ');

            $('#pd-lector-sel-nombre').text(partes[0] || texto);
            $('#pd-lector-sel-info').text(partes.slice(1).join(' · '));
            $('#pd-lector-sel').removeClass('d-none');
            $('#pd-lector-results').empty();
        });

        $('#pd-lector-cambiar').on('click', function () {
            pdLectorId = null;
            $('#pd-lector-sel').addClass('d-none');
            $('#pd-lector-q').val('').focus();
        });

        // ── Buscar ejemplar ──
        function buscarEjemplar() {
            const q   = $('#pd-libro-q').val().trim();
            const bib = $('#pd-filtro-bib').val();
            if (q.length < 2 && ! bib) { alerta('Escribe al menos 2 caracteres para buscar.', false); return; }

            $('#pd-libro-results').html(
                '<div class="text-muted small py-3 text-center"><i class="bi bi-hourglass-split me-1"></i>Buscando ejemplares disponibles...</div>'
            );

            let url = '/api/prestamos/ejemplares/disponibles?q=' + encodeURIComponent(q);
            if (bib) url += '&biblioteca_id=' + encodeURIComponent(bib);

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    const $wrap = $('<div class="pd-results-wrap">');
                    if (! Array.isArray(data) || data.length === 0) {
                        $wrap.append(
                            '<div class="p-4 text-center">' +
                                '<i class="bi bi-journal-x text-muted d-block fs-3 mb-1"></i>' +
                                '<span class="text-muted small">No hay ejemplares disponibles con ese criterio.</span>' +
                            '</div>'
                        );
                    } else {
                        data.forEach(e => $wrap.append(pdEjemplarCard(e)));
                    }
                    $('#pd-libro-results').html($wrap);
                })
                .catch(() => $('#pd-libro-results').html('<div class="text-danger small p-2">Error al buscar.</div>'));
        }

        $('#pd-libro-buscar').on('click', buscarEjemplar);
        $('#pd-libro-q').on('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); buscarEjemplar(); } });
        $('#pd-filtro-bib').on('change', function () { if ($('#pd-libro-q').val().trim().length >= 2 || $(this).val()) buscarEjemplar(); });

        $(document).on('click', '.pd-ejemplar-item', function () {
            pdEjemplarId = $(this).data('id');
            $('#pd-ejemplar-sel-libro').text($(this).data('libro'));
            $('#pd-ejemplar-sel-bib').text($(this).data('bib'));
            $('#pd-ejemplar-sel-codigo').text('Cód: ' + $(this).data('codigo'));
            $('#pd-ejemplar-sel').removeClass('d-none');
            $('#pd-libro-results').empty();
            $('#pd-libro-q').val('');
        });

        $('#pd-ejemplar-cambiar').on('click', function () {
            pdEjemplarId = null;
            $('#pd-ejemplar-sel').addClass('d-none');
            $('#pd-libro-q').val('').focus();
        });

        // ── Calcular fecha límite estimada ──
        $('#pd-dias').on('input', function () {
            const dias = parseInt($(this).val());
            if (! isNaN(dias) && dias > 0) {
                const fecha = new Date();
                fecha.setDate(fecha.getDate() + dias);
                $('#pd-fecha-est').val(
                    fecha.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' a las 20:00'
                );
            } else {
                $('#pd-fecha-est').val('');
            }
        });

        // ── Navegación ──
        $('#pd-btn-next').on('click', function () {
            if (pdStep === 1) {
                if (! pdLectorId) { alerta('Debes seleccionar un lector antes de continuar.', false); return; }
                $('#pd-ctx-lector').text($('#pd-lector-sel-nombre').text());
                pdShowStep(2);
            } else if (pdStep === 2) {
                if (! pdEjemplarId) { alerta('Debes seleccionar un ejemplar antes de continuar.', false); return; }
                $('#pd-ctx-libro').text($('#pd-ejemplar-sel-libro').text());
                $('#pd-ctx-codigo').text($('#pd-ejemplar-sel-bib').text() + ' · ' + $('#pd-ejemplar-sel-codigo').text());
                pdShowStep(3);
            }
        });

        $('#pd-btn-prev').on('click', function () {
            if (pdStep > 1) pdShowStep(pdStep - 1);
        });

        // ── Confirmar ──
        $('#pd-btn-confirm').on('click', function () {
            const dias = parseInt($('#pd-dias').val());
            if (! dias || dias < 1) { alerta('Ingresa un número válido de días.', false); return; }

            const btn = $(this).prop('disabled', true);

            fetch('/api/prestamos/prestamo/directo', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    lector_id:      pdLectorId,
                    ejemplar_id:    pdEjemplarId,
                    prestamo_lugar: parseInt($('#pd-tipo').val()),
                    dias:           dias,
                    observaciones:  $('#pd-obs').val()
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alerta(data.success, true);
                        modalPD.hide();
                        tabla.ajax.reload(null, false);
                    } else {
                        alerta(data.error || 'Ocurrió un error', false);
                    }
                })
                .catch(() => alerta('Error en la petición', false))
                .finally(() => btn.prop('disabled', false));
        });
    });
})();

// ─────────────────────────────────────────────────────────────────────────────

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
