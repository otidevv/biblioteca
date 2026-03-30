document.addEventListener('DOMContentLoaded', function () {
    iniciarCountdownPrestamos();
});

function iniciarCountdownPrestamos() {
    const elementos = document.querySelectorAll('.prestamos-countdown');

    elementos.forEach(el => {
        const fechaLimite = new Date(el.dataset.fecha).getTime();

        function actualizar() {
            const ahora = new Date().getTime();
            const diferencia = fechaLimite - ahora;

            el.classList.remove('is-success', 'is-warning', 'is-danger');

            if (diferencia <= 0) {
                el.innerHTML = '<i class="bi bi-exclamation-octagon-fill"></i><span>Fuera de plazo</span>';
                el.classList.add('is-danger');
                return;
            }

            const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
            const horas = Math.floor((diferencia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));

            el.innerHTML = `<i class="bi bi-clock-history"></i><span>${dias}d ${horas}h ${minutos}m</span>`;

            if (dias < 1) {
                el.classList.add('is-danger');
            } else if (dias < 3) {
                el.classList.add('is-warning');
            } else {
                el.classList.add('is-success');
            }
        }

        actualizar();
        setInterval(actualizar, 60000);
    });
}
