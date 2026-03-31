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
    const comentariosList = document.getElementById('bookCommentsList');

    inicializarComentariosPlegables();

    if (formComentario && comentarioUrl && comentariosList) {
        formComentario.addEventListener('submit', function (e) {
            e.preventDefault();

            const data = new FormData(formComentario);

            fetch(comentarioUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: data
            })
                .then(async (res) => {
                    const payload = await res.json().catch(() => null);

                    if (!res.ok || !payload) {
                        throw new Error(resolveErrorMessage(payload, 'No fue posible publicar el comentario.'));
                    }

                    return payload;
                })
                .then(payload => {
                    comentariosList.innerHTML = payload.comentariosHtml || '';
                    inicializarComentariosPlegables();
                    formComentario.reset();

                    const ratingSummary = document.getElementById('bookRatingSummary');
                    const mainRatingValue = document.getElementById('bookMainRatingValue');

                    if (ratingSummary && payload.ratingHtml) {
                        ratingSummary.innerHTML = payload.ratingHtml;
                    }

                    if (mainRatingValue && payload.mainRatingHtml) {
                        mainRatingValue.innerHTML = payload.mainRatingHtml;
                    }

                    if (typeof alerta === 'function') {
                        alerta('Comentario publicado correctamente.', true);
                    }
                })
                .catch((error) => {
                    if (typeof alerta === 'function') {
                        alerta(error?.message || 'No fue posible publicar el comentario.', false);
                    }
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
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: data
            })
                .then(async (res) => {
                    const payload = await res.json().catch(() => null);

                    if (!res.ok || !payload) {
                        throw new Error(resolveErrorMessage(payload, 'No fue posible registrar la reserva.'));
                    }

                    return payload;
                })
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
                })
                .catch((error) => {
                    alerta(error?.message || 'No fue posible registrar la reserva.', false);
                });
        });
    }
});

function resolveErrorMessage(payload, fallbackMessage) {
    if (!payload) {
        return fallbackMessage;
    }

    if (payload.errors && typeof payload.errors === 'object') {
        const firstGroup = Object.values(payload.errors)[0];
        const firstMessage = Array.isArray(firstGroup) ? firstGroup[0] : firstGroup;

        if (typeof firstMessage === 'string' && firstMessage.trim() !== '') {
            return firstMessage;
        }
    }

    if (typeof payload.error === 'string' && payload.error.trim() !== '') {
        return payload.error;
    }

    if (typeof payload.message === 'string' && payload.message.trim() !== '') {
        return payload.message;
    }

    return fallbackMessage;
}

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
            const ratingSummary = document.getElementById('bookRatingSummary');
            const mainRatingValue = document.getElementById('bookMainRatingValue');

            if (ratingSummary) {
                ratingSummary.innerHTML = html;
            }

            if (mainRatingValue) {
                mainRatingValue.innerHTML = html;
                const ratingNode = mainRatingValue.firstElementChild;
                if (ratingNode) {
                    ratingNode.classList.add('book-main-rating-stars');
                }
            }
        });
}

function inicializarComentariosPlegables() {
    const commentsList = document.querySelector('[data-comments-list]');
    const toggle = document.querySelector('[data-comments-toggle]');

    if (!commentsList || !toggle) {
        return;
    }

    const showText = toggle.dataset.showText || 'Ver más comentarios';
    const hideText = toggle.dataset.hideText || 'Ver menos comentarios';

    toggle.textContent = commentsList.classList.contains('is-collapsed') ? showText : hideText;

    toggle.onclick = function () {
        const collapsed = commentsList.classList.toggle('is-collapsed');
        toggle.textContent = collapsed ? showText : hideText;
    };
}
