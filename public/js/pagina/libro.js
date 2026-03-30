document.addEventListener('DOMContentLoaded', function () {
    const page = window.libroPage || {};
    const libroId = page.id;
    const comentarioUrl = page.comentarioUrl;
    const reservarUrl = page.reservarUrl;

    if (!libroId) {
        return;
    }

    const formComentario = document.getElementById('formComentario');
    const bibliotecaSelect = document.getElementById('biblioteca_select');
    const formReserva = document.getElementById('formReserva');

    if (formComentario && comentarioUrl) {
        formComentario.addEventListener('submit', function (e) {
            e.preventDefault();

            const data = new FormData(formComentario);

            fetch(comentarioUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: data
            })
                .then(res => res.text())
                .then(html => {
                    document.getElementById('listaComentarios').innerHTML = html;
                    formComentario.reset();
                    refrescarCalificacion(libroId);
                });
        });
    }

    if (bibliotecaSelect) {
        bibliotecaSelect.addEventListener('change', function () {
            cargarEjemplares(libroId, this.value);
        });
    }

    if (formReserva && reservarUrl) {
        formReserva.addEventListener('submit', function (e) {
            e.preventDefault();

            const data = new FormData(formReserva);

            fetch(reservarUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: data
            })
                .then(res => res.json())
                .then(res => {
                    if (res.error) {
                        alerta(res.error, false);
                        return;
                    }

                    alerta(res.ok, true);

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalReserva'));
                    modal.hide();
                    formReserva.reset();

                    if (bibliotecaSelect.value) {
                        cargarEjemplares(libroId, bibliotecaSelect.value);
                    }

                    refrescarDisponibilidad(libroId);
                    refrescarEjemplares(libroId);
                });
        });
    }
});

function cargarEjemplares(libroId, bibliotecaId) {
    const ejemplarSelect = document.getElementById('ejemplar_select');

    if (!ejemplarSelect) {
        return;
    }

    if (!bibliotecaId) {
        ejemplarSelect.innerHTML = '<option value="">-- Seleccione una biblioteca primero --</option>';
        ejemplarSelect.setAttribute('disabled', 'disabled');
        return;
    }

    fetch(`/pagina/${bibliotecaId}/ejemplares/biblioteca?libro_id=${libroId}`)
        .then(res => res.json())
        .then(data => {
            let html = '<option value="">-- Seleccionar ejemplar --</option>';

            data.forEach(e => {
                html += `<option value="${e.id}">${e.codigo}</option>`;
            });

            ejemplarSelect.innerHTML = html;
            ejemplarSelect.removeAttribute('disabled');
        });
}

function refrescarDisponibilidad(libroId) {
    fetch(`/pagina/libro/${libroId}/disponibilidad`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('tablaDisponibilidad').innerHTML = html;
        });
}

function refrescarEjemplares(libroId) {
    fetch(`/pagina/libro/${libroId}/ejemplares`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('listaEjemplares').innerHTML = html;
        });
}

function refrescarCalificacion(libroId) {
    fetch(`/pagina/libro/${libroId}/rating`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('bookRatingSummary').innerHTML = html;
        });
}
