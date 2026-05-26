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

    if (comentariosList) {
        comentariosList.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-comment-action]');
            if (!btn) return;

            const article = btn.closest('[data-comment-id]');
            if (!article) return;

            const action    = btn.dataset.commentAction;
            const commentId = article.dataset.commentId;

            if (action === 'edit') {
                mostrarFormEdit(article);
            } else if (action === 'cancel-edit') {
                ocultarFormEdit(article);
            } else if (action === 'edit-star') {
                setEditStars(article, parseInt(btn.dataset.value, 10));
            } else if (action === 'save-edit') {
                guardarEdicion(article, commentId);
            } else if (action === 'delete') {
                eliminarComentario(article, commentId);
            }
        });
    }

    if (formComentario && comentarioUrl && comentariosList) {
        formComentario.addEventListener('submit', function (e) {
            e.preventDefault();

            const btn = document.getElementById('btnPublicarComentario');
            const btnOriginal = btn ? btn.innerHTML : null;

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Publicando...';
            }

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

                    // Reiniciar contador de caracteres
                    const charCount = document.getElementById('comentarioCharCount');
                    if (charCount) {
                        charCount.textContent = '0';
                        charCount.closest('.book-char-counter')?.classList.remove('book-char-counter--warn', 'book-char-counter--max');
                    }

                    const ratingSummary = document.getElementById('bookRatingSummary');
                    const mainRatingValue = document.getElementById('bookMainRatingValue');

                    if (ratingSummary && payload.ratingHtml) {
                        ratingSummary.innerHTML = payload.ratingHtml;
                    }

                    if (mainRatingValue && payload.mainRatingHtml) {
                        mainRatingValue.innerHTML = payload.mainRatingHtml;
                    }

                    if (typeof alerta === 'function') {
                        alerta('Reseña publicada correctamente.', true);
                    }
                })
                .catch((error) => {
                    if (typeof alerta === 'function') {
                        alerta(error?.message || 'No fue posible publicar el comentario.', false);
                    }
                })
                .finally(() => {
                    if (btn && btnOriginal) {
                        btn.disabled = false;
                        btn.innerHTML = btnOriginal;
                    }
                });
        });
    }

    if (bibliotecaSelect) {
        bibliotecaSelect.addEventListener('change', function () {
            const step2 = document.getElementById('step2');
            if (step2) {
                step2.classList.toggle('book-modal-step--done', !!this.value);
            }
            cargarEjemplares(libroId, this.value);
        });
    }

    if (formReserva && reservarUrl) {
        formReserva.addEventListener('submit', function (e) {
            e.preventDefault();

            const btn = document.getElementById('btnConfirmarReserva');
            const btnOriginal = btn ? btn.innerHTML : null;

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Procesando...';
            }

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

                    const step2 = document.getElementById('step2');
                    if (step2) step2.classList.remove('book-modal-step--done');

                    if (bibliotecaSelect && bibliotecaSelect.value) {
                        cargarEjemplares(libroId, bibliotecaSelect.value);
                    }

                    refrescarDisponibilidad(libroId);
                    refrescarEjemplares(libroId);
                })
                .catch((error) => {
                    alerta(error?.message || 'No fue posible registrar la reserva.', false);
                })
                .finally(() => {
                    if (btn && btnOriginal) {
                        btn.disabled = false;
                        btn.innerHTML = btnOriginal;
                    }
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
        ejemplarSelect.innerHTML = '<option value="">Primero selecciona una sede</option>';
        ejemplarSelect.setAttribute('disabled', 'disabled');
        return;
    }

    ejemplarSelect.innerHTML = '<option value="">Cargando ejemplares...</option>';
    ejemplarSelect.setAttribute('disabled', 'disabled');

    fetch(`/pagina/${bibliotecaId}/ejemplares/biblioteca?libro_id=${libroId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.length) {
                ejemplarSelect.innerHTML = '<option value="">Sin ejemplares disponibles en esta sede</option>';
                return;
            }

            let html = '<option value="">Seleccionar ejemplar...</option>';

            data.forEach(e => {
                const codigo = e.codigo_dewey && e.codigo_interno
                    ? `${e.codigo_dewey}/${e.codigo_interno}`
                    : (e.codigo || e.codigo_ant || `Ejemplar #${e.id}`);
                const tipo = e.tipo ? ` · ${e.tipo}` : '';
                const siaf = e.siaf ? ` · SIAF: ${e.siaf}` : '';
                html += `<option value="${e.id}">${codigo}${tipo}${siaf}</option>`;
            });

            ejemplarSelect.innerHTML = html;
            ejemplarSelect.removeAttribute('disabled');
        })
        .catch(() => {
            ejemplarSelect.innerHTML = '<option value="">Error al cargar ejemplares</option>';
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

function mostrarFormEdit(article) {
    const view = article.querySelector('.book-comment-view');
    const form = article.querySelector('.book-comment-edit-form');
    if (view) view.hidden = true;
    if (form) form.hidden = false;
}

function ocultarFormEdit(article) {
    const view = article.querySelector('.book-comment-view');
    const form = article.querySelector('.book-comment-edit-form');
    if (view) view.hidden = false;
    if (form) form.hidden = true;
}

function setEditStars(article, value) {
    const stars = article.querySelectorAll('.book-edit-star');
    const input = article.querySelector('.book-edit-rating-val');

    stars.forEach((star, idx) => {
        const icon = star.querySelector('i');
        const active = idx < value;
        star.classList.toggle('is-active', active);
        if (icon) {
            icon.className = active ? 'bi bi-star-fill' : 'bi bi-star';
        }
    });

    if (input) input.value = value;
}

function guardarEdicion(article, commentId) {
    const form    = article.querySelector('.book-comment-edit-form');
    const textarea = form ? form.querySelector('.book-comment-edit-textarea') : null;
    const ratingInput = form ? form.querySelector('.book-edit-rating-val') : null;
    const saveBtn = form ? form.querySelector('[data-comment-action="save-edit"]') : null;

    if (!textarea || !ratingInput) return;

    const comentarioText = textarea.value.trim();
    const rating = parseInt(ratingInput.value, 10);

    if (!comentarioText) {
        if (typeof alerta === 'function') alerta('El comentario no puede estar vacío.', false);
        return;
    }
    if (!rating || rating < 1 || rating > 5) {
        if (typeof alerta === 'function') alerta('Selecciona una calificación.', false);
        return;
    }

    const originalBtnHtml = saveBtn ? saveBtn.innerHTML : null;
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Guardando...';
    }

    const csrfToken = document.querySelector('input[name="_token"]');

    fetch(`/pagina/comentario/${commentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken ? csrfToken.value : '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ comentario: comentarioText, rating }),
    })
        .then(async (res) => {
            const payload = await res.json().catch(() => null);
            if (!res.ok || !payload) throw new Error(resolveErrorMessage(payload, 'No fue posible actualizar el comentario.'));
            return payload;
        })
        .then(payload => {
            const lista = document.getElementById('bookCommentsList');
            if (lista) {
                lista.innerHTML = payload.comentariosHtml || '';
                inicializarComentariosPlegables();
            }
            actualizarRating(payload);
            if (typeof alerta === 'function') alerta('Comentario actualizado correctamente.', true);
        })
        .catch(err => {
            if (typeof alerta === 'function') alerta(err?.message || 'No fue posible actualizar el comentario.', false);
        })
        .finally(() => {
            if (saveBtn && originalBtnHtml) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnHtml;
            }
        });
}

function eliminarComentario(article, commentId) {
    if (!confirm('¿Seguro que quieres eliminar este comentario? Esta acción no se puede deshacer.')) return;

    const csrfToken = document.querySelector('input[name="_token"]');

    fetch(`/pagina/comentario/${commentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken ? csrfToken.value : '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(async (res) => {
            const payload = await res.json().catch(() => null);
            if (!res.ok || !payload) throw new Error(resolveErrorMessage(payload, 'No fue posible eliminar el comentario.'));
            return payload;
        })
        .then(payload => {
            const lista = document.getElementById('bookCommentsList');
            if (lista) {
                lista.innerHTML = payload.comentariosHtml || '';
                inicializarComentariosPlegables();
            }
            actualizarRating(payload);
            if (typeof alerta === 'function') alerta('Comentario eliminado.', true);
        })
        .catch(err => {
            if (typeof alerta === 'function') alerta(err?.message || 'No fue posible eliminar el comentario.', false);
        });
}

function actualizarRating(payload) {
    const ratingSummary   = document.getElementById('bookRatingSummary');
    const mainRatingValue = document.getElementById('bookMainRatingValue');
    if (ratingSummary && payload.ratingHtml)     ratingSummary.innerHTML = payload.ratingHtml;
    if (mainRatingValue && payload.mainRatingHtml) mainRatingValue.innerHTML = payload.mainRatingHtml;
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
