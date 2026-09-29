document.addEventListener('DOMContentLoaded', function () {
    iniciarCuentasRegresivas();
    iniciarTooltipGrafico();
    iniciarExportarImagen();
});

// Exportar reporte > Imagen PNG: captura la tarjeta #lp-share con html2canvas.
function iniciarExportarImagen() {
    const plantilla = document.getElementById('lp-share');
    const estado = document.querySelector('[data-lp-export-estado]');

    document.querySelectorAll('[data-lp-exportar-imagen]').forEach(function (boton) {
        boton.addEventListener('click', async function () {
            if (!plantilla || boton.disabled) {
                return;
            }

            boton.disabled = true;
            avisar('Generando imagen del reporte…');

            try {
                await cargarLibreria(plantilla.dataset.libreria);
                if (document.fonts && document.fonts.ready) {
                    await document.fonts.ready;
                }

                const lienzo = await window.html2canvas(plantilla, {
                    scale: 2,
                    backgroundColor: '#fbf8f1',
                    logging: false,
                    // La tarjeta vive fuera de pantalla; en la copia se trae a la vista.
                    onclone: function (doc) {
                        const copia = doc.getElementById('lp-share');
                        copia.style.left = '0';
                        copia.style.top = '0';
                    },
                });

                const blob = await new Promise(function (resolver) { lienzo.toBlob(resolver, 'image/png'); });
                if (!blob) {
                    throw new Error('No se pudo crear la imagen');
                }

                descargar(blob, plantilla.dataset.archivo || 'reporte-lectura.png');
                avisar('Imagen descargada.');
            } catch (error) {
                console.error(error);
                avisar('No se pudo generar la imagen. Intenta de nuevo o descarga el PDF.');
            } finally {
                boton.disabled = false;
            }
        });
    });

    function avisar(texto) {
        if (estado) {
            estado.textContent = texto;
        }
    }
}

let promesaLibreria = null;

function cargarLibreria(url) {
    if (window.html2canvas) {
        return Promise.resolve();
    }

    if (!promesaLibreria) {
        promesaLibreria = new Promise(function (resolver, rechazar) {
            const script = document.createElement('script');
            script.src = url;
            script.onload = resolver;
            script.onerror = function () {
                promesaLibreria = null;
                rechazar(new Error('No se pudo cargar html2canvas'));
            };
            document.head.appendChild(script);
        });
    }

    return promesaLibreria;
}

function descargar(blob, nombre) {
    const url = URL.createObjectURL(blob);
    const enlace = document.createElement('a');
    enlace.href = url;
    enlace.download = nombre;
    document.body.appendChild(enlace);
    enlace.click();
    enlace.remove();
    setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
}

// Tiempo restante hasta la fecha limite (prestamos y reservas).
function iniciarCuentasRegresivas() {
    const elementos = document.querySelectorAll('.lp-countdown[data-fecha]');

    elementos.forEach(function (el) {
        const limite = new Date(el.dataset.fecha).getTime();
        // Horas antes del limite en que se marca como urgente (por defecto 24).
        const urgente = (parseFloat(el.dataset.urgenteHoras) || 24) * 3600000;

        function actualizar() {
            const restante = limite - Date.now();
            el.classList.remove('is-soon', 'is-late');

            if (isNaN(limite)) {
                el.textContent = '';
                return;
            }

            if (restante <= 0) {
                el.classList.add('is-late');
                el.replaceChildren(icono('bi-exclamation-octagon-fill'), document.createTextNode('Vencido'));
                return;
            }

            const dias = Math.floor(restante / 86400000);
            const horas = Math.floor((restante % 86400000) / 3600000);
            const minutos = Math.floor((restante % 3600000) / 60000);
            const texto = dias > 0 ? dias + 'd ' + horas + 'h' : horas + 'h ' + minutos + 'm';

            if (restante < urgente) {
                el.classList.add('is-soon');
            }

            el.replaceChildren(icono('bi-clock'), document.createTextNode(texto));
        }

        actualizar();
        setInterval(actualizar, 60000);
    });
}

function icono(clase) {
    const i = document.createElement('i');
    i.className = 'bi ' + clase;
    i.setAttribute('aria-hidden', 'true');
    return i;
}

// Grafico mensual: cada columna muestra su valor al pasar el mouse o con el foco del teclado.
function iniciarTooltipGrafico() {
    document.querySelectorAll('[data-lp-chart]').forEach(function (grafico) {
        const tooltip = grafico.querySelector('.lp-chart__tooltip');
        if (!tooltip) {
            return;
        }

        function mostrar(col) {
            const valor = parseInt(col.dataset.value, 10) || 0;
            const valorEl = document.createElement('strong');
            valorEl.textContent = valor + (valor === 1 ? ' préstamo' : ' préstamos');
            const etiquetaEl = document.createElement('span');
            etiquetaEl.textContent = col.dataset.label || '';
            tooltip.replaceChildren(valorEl, etiquetaEl);

            const caja = grafico.getBoundingClientRect();
            const barra = (col.querySelector('.lp-chart__bar') || col).getBoundingClientRect();
            const columna = col.getBoundingClientRect();
            const x = columna.left + columna.width / 2 - caja.left;
            const y = Math.min(barra.top, columna.bottom - 4) - caja.top - 10;

            tooltip.hidden = false;
            // Mantener el tooltip dentro del grafico.
            const mitad = tooltip.offsetWidth / 2;
            tooltip.style.left = Math.max(mitad, Math.min(caja.width - mitad, x)) + 'px';
            tooltip.style.top = Math.max(tooltip.offsetHeight, y) + 'px';
        }

        function ocultar() {
            tooltip.hidden = true;
        }

        grafico.querySelectorAll('.lp-chart__col').forEach(function (col) {
            col.addEventListener('pointerenter', function () { mostrar(col); });
            col.addEventListener('pointerleave', ocultar);
            col.addEventListener('focus', function () { mostrar(col); });
            col.addEventListener('blur', ocultar);
            col.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    ocultar();
                }
            });
        });
    });
}
