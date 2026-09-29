// "Mis prestamos" y "Mis reservas": filtros del historial y cancelacion de reservas.
// La cuenta regresiva (.lp-countdown) la maneja panel_lector.js.
document.addEventListener('DOMContentLoaded', function () {
    iniciarFiltrosHistorial();
    iniciarCancelarReserva();
});

function iniciarFiltrosHistorial() {
    document.querySelectorAll('[data-me-historial]').forEach(function (seccion) {
        const botones = seccion.querySelectorAll('[data-me-filtro]');
        const buscador = seccion.querySelector('[data-me-buscar]');
        const sinResultados = seccion.querySelector('[data-me-sin-resultados]');
        let filtro = 'todos';

        function normalizar(texto) {
            return (texto || '').toString().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
        }

        function aplicar() {
            const busqueda = normalizar(buscador ? buscador.value : '');
            let visibles = 0;

            seccion.querySelectorAll('[data-me-grupo]').forEach(function (grupo) {
                let enGrupo = 0;

                grupo.querySelectorAll('.me-item').forEach(function (item) {
                    const coincideFiltro = filtro === 'todos' || item.dataset.meEstado === filtro;
                    const coincideTexto = busqueda === '' || normalizar(item.dataset.meTexto).includes(busqueda);
                    const mostrar = coincideFiltro && coincideTexto;

                    item.hidden = !mostrar;
                    if (mostrar) {
                        enGrupo++;
                    }
                });

                grupo.hidden = enGrupo === 0;
                visibles += enGrupo;
            });

            if (sinResultados) {
                sinResultados.hidden = visibles > 0;
            }
        }

        botones.forEach(function (boton) {
            boton.addEventListener('click', function () {
                filtro = boton.dataset.meFiltro;
                botones.forEach(function (b) {
                    const activo = b === boton;
                    b.classList.toggle('is-active', activo);
                    b.setAttribute('aria-pressed', activo ? 'true' : 'false');
                });
                aplicar();
            });
        });

        if (buscador) {
            buscador.addEventListener('input', aplicar);
        }
    });
}

function iniciarCancelarReserva() {
    const modalEl = document.getElementById('modalCancelarReserva');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const tituloEl = modalEl.querySelector('[data-me-modal-titulo]');
    const confirmar = modalEl.querySelector('[data-me-confirmar]');
    const raiz = document.querySelector('[data-me-csrf]');
    let reservaId = null;

    document.querySelectorAll('[data-me-cancelar]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            reservaId = boton.dataset.meCancelar;
            if (tituloEl) {
                tituloEl.textContent = boton.dataset.meTitulo || '';
            }
            modal.show();
        });
    });

    confirmar.addEventListener('click', function () {
        if (!reservaId) {
            return;
        }

        confirmar.disabled = true;

        fetch('/pagina/reserva/' + encodeURIComponent(reservaId) + '/cancelar', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': raiz ? raiz.dataset.meCsrf : '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (datos) {
                    return { ok: res.ok, datos: datos };
                });
            })
            .then(function (r) {
                if (!r.ok || r.datos.error) {
                    throw new Error(r.datos.error || r.datos.message || 'No se pudo cancelar la reserva.');
                }

                modal.hide();
                notificar(r.datos.ok || 'Reserva cancelada correctamente.', true);
                setTimeout(function () { location.reload(); }, 800);
            })
            .catch(function (error) {
                notificar(error.message || 'No se pudo cancelar la reserva.', false);
            })
            .finally(function () {
                confirmar.disabled = false;
            });
    });
}

function notificar(mensaje, exito) {
    if (typeof alerta === 'function') {
        alerta(mensaje, exito);
    } else {
        window.alert(mensaje);
    }
}
