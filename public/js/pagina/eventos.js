// Agenda publica: filtros, detalle en modal y enlace directo (#evento-{id}).
document.addEventListener('DOMContentLoaded', function () {
    iniciarFiltrosAgenda();
    iniciarDetalleEventos();
});

function normalizarTexto(texto) {
    return (texto || '').toString().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
}

function iniciarFiltrosAgenda() {
    const agenda = document.querySelector('[data-ev-agenda]');
    if (!agenda) {
        return;
    }

    const botones = agenda.querySelectorAll('[data-ev-categoria]');
    const modalidad = agenda.querySelector('[data-ev-modalidad]');
    const buscador = agenda.querySelector('[data-ev-buscar]');
    const sinResultados = agenda.querySelector('[data-ev-sin-resultados]');
    let categoria = '';

    function aplicar() {
        const mod = modalidad ? modalidad.value : '';
        const busqueda = normalizarTexto(buscador ? buscador.value : '');
        let visibles = 0;

        agenda.querySelectorAll('[data-ev-grupo]').forEach(function (grupo) {
            let enGrupo = 0;

            grupo.querySelectorAll('.ev-card').forEach(function (tarjeta) {
                const mostrar = (categoria === '' || tarjeta.dataset.evCat === categoria)
                    && (mod === '' || tarjeta.dataset.evMod === mod)
                    && (busqueda === '' || normalizarTexto(tarjeta.dataset.evTexto).includes(busqueda));

                tarjeta.hidden = !mostrar;
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
            categoria = boton.dataset.evCategoria;
            botones.forEach(function (b) {
                const activo = b === boton;
                b.classList.toggle('is-active', activo);
                b.setAttribute('aria-pressed', activo ? 'true' : 'false');
            });
            aplicar();
        });
    });

    if (modalidad) {
        modalidad.addEventListener('change', aplicar);
    }

    if (buscador) {
        buscador.addEventListener('input', aplicar);
    }
}

function iniciarDetalleEventos() {
    const modalEl = document.getElementById('evModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const cuerpo = modalEl.querySelector('[data-ev-modal-cuerpo]');

    function abrir(id) {
        const plantilla = document.getElementById('ev-detalle-' + id);
        if (!plantilla) {
            return false;
        }

        cuerpo.replaceChildren(plantilla.content.cloneNode(true));
        const titulo = cuerpo.querySelector('.ev-modal__title');
        modalEl.setAttribute('aria-label', titulo ? titulo.textContent : 'Detalle de la actividad');
        modal.show();
        cambiarHash('#evento-' + id);

        return true;
    }

    document.addEventListener('click', function (e) {
        const disparador = e.target.closest('[data-ev-detalle]');
        if (disparador) {
            e.preventDefault();
            abrir(disparador.dataset.evDetalle);
        }
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        if (location.hash.indexOf('#evento-') === 0) {
            cambiarHash(location.pathname + location.search);
        }
    });

    // Enlace directo desde una notificacion: /evento#evento-12
    const coincidencia = location.hash.match(/^#evento-(\d+)$/);
    if (coincidencia) {
        abrir(coincidencia[1]);
    }
}

// replaceState puede fallar en contextos restringidos; el detalle igual se abre.
function cambiarHash(url) {
    try {
        history.replaceState(null, '', url);
    } catch (e) {
        /* sin cambio de URL */
    }
}
