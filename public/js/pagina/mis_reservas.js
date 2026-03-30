let reservaId = null;

document.addEventListener('click', function (e) {
    const cancelarBtn = e.target.closest('.btn-cancelar');

    if (cancelarBtn) {
        reservaId = cancelarBtn.dataset.id;

        const modal = new bootstrap.Modal(document.getElementById('modalCancelar'));
        modal.show();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    iniciarCountdown();

    const confirmar = document.getElementById('confirmarCancelacion');

    if (confirmar) {
        confirmar.addEventListener('click', function () {
            if (!reservaId) return;

            fetch(`/pagina/reserva/${reservaId}/cancelar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.error) {
                    alerta(res.error, false);
                    return;
                }

                alerta(res.ok, true);

                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCancelar'));
                modal.hide();

                setTimeout(() => {
                    location.reload();
                }, 800);
            });
        });
    }
});

function iniciarCountdown() {
    const elementos = document.querySelectorAll('.countdown');

    elementos.forEach(el => {
        const fechaLimite = new Date(el.dataset.fecha).getTime();

        function actualizar() {
            const ahora = new Date().getTime();
            const diferencia = fechaLimite - ahora;

            el.classList.remove('is-success', 'is-warning', 'is-danger');

            if (diferencia <= 0) {
                el.innerHTML = '<i class="bi bi-x-octagon-fill"></i><span>Vencido</span>';
                el.classList.add('is-danger');
                return;
            }

            const horas = Math.floor(diferencia / (1000 * 60 * 60));
            const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

            el.innerHTML = `<i class="bi bi-clock-history"></i><span>${horas}h ${minutos}m ${segundos}s</span>`;

            if (horas < 1) {
                el.classList.add('is-danger');
            } else if (horas < 3) {
                el.classList.add('is-warning');
            } else {
                el.classList.add('is-success');
            }
        }

        actualizar();
        setInterval(actualizar, 1000);
    });
}
