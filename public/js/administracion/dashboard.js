// Panel de operacion: graficos (ApexCharts local), filtro de biblioteca y modo oscuro.
document.addEventListener('DOMContentLoaded', function () {
    iniciarFiltroBiblioteca();

    const nodoDatos = document.getElementById('dsh-datos');
    if (!nodoDatos) {
        return;
    }

    let datos;
    try {
        datos = JSON.parse(nodoDatos.textContent);
    } catch (e) {
        return;
    }

    // Sin la libreria de graficos, se muestran las tablas equivalentes.
    if (typeof ApexCharts === 'undefined') {
        document.querySelectorAll('[data-dsh-tabla]').forEach(function (detalle) {
            detalle.open = true;
        });
        return;
    }

    const graficos = [];
    const circulacion = document.getElementById('dshChartCirculacion');
    const visitas = document.getElementById('dshChartVisitas');

    if (circulacion) {
        graficos.push(crearGrafico(circulacion, function (c) { return opcionesCirculacion(datos.circulacion, c, pasoEtiquetas(circulacion)); }));
    }
    if (visitas) {
        graficos.push(crearGrafico(visitas, function (c) { return opcionesVisitas(datos.visitas, c, pasoEtiquetas(visitas)); }));
    }

    // Al cambiar el ancho se recalcula cada cuantos dias se rotula el eje.
    let anchoPrevio = window.innerWidth;
    let espera = null;
    window.addEventListener('resize', function () {
        clearTimeout(espera);
        espera = setTimeout(function () {
            if (Math.abs(window.innerWidth - anchoPrevio) < 80) {
                return;
            }
            anchoPrevio = window.innerWidth;
            graficos.forEach(function (g) { g.refrescar(); });
        }, 250);
    });

    // El layout cambia .admin-dark en <body>: se vuelven a leer los colores.
    let modoOscuro = document.body.classList.contains('admin-dark');
    new MutationObserver(function () {
        const ahora = document.body.classList.contains('admin-dark');
        if (ahora === modoOscuro) {
            return;
        }
        modoOscuro = ahora;
        graficos.forEach(function (g) { g.refrescar(); });
    }).observe(document.body, { attributes: true, attributeFilter: ['class'] });
});

function iniciarFiltroBiblioteca() {
    const formulario = document.querySelector('[data-dsh-filtro]');
    if (!formulario) {
        return;
    }
    const select = formulario.querySelector('select');
    select.addEventListener('change', function () {
        if (!select.value) {
            window.location.href = formulario.getAttribute('action');
            return;
        }
        formulario.submit();
    });
}

function coloresPanel() {
    const raiz = document.querySelector('.dsh');
    const estilo = getComputedStyle(raiz);
    const leer = function (nombre) { return estilo.getPropertyValue(nombre).trim(); };

    return {
        oscuro: document.body.classList.contains('admin-dark'),
        superficie: leer('--dsh-surface'),
        texto: leer('--dsh-text'),
        tenue: leer('--dsh-muted'),
        borde: leer('--dsh-border'),
        sala: leer('--dsh-sala'),
        casa: leer('--dsh-casa'),
    };
}

function crearGrafico(elemento, construir) {
    let grafico = new ApexCharts(elemento, construir(coloresPanel()));
    grafico.render();

    return {
        refrescar: function () {
            grafico.destroy();
            grafico = new ApexCharts(elemento, construir(coloresPanel()));
            grafico.render();
        },
    };
}

// Un rotulo cada ~70px: en movil cada semana, en escritorio cada 3-4 dias.
function pasoEtiquetas(elemento) {
    const capacidad = Math.max(3, Math.floor(elemento.clientWidth / 70));
    return Math.max(1, Math.ceil(30 / capacidad));
}

function rotulos(etiquetas, paso) {
    const ultimo = etiquetas.length - 1;
    return etiquetas.map(function (etiqueta, i) {
        return (ultimo - i) % paso === 0 ? etiqueta : '';
    });
}

function sinAnimacion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function baseGrafico(c, alto) {
    return {
        chart: {
            height: alto,
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'inherit',
            foreColor: c.tenue,
            background: 'transparent',
            animations: { enabled: !sinAnimacion(), speed: 350 },
        },
        dataLabels: { enabled: false },
        legend: { show: false },
        grid: {
            borderColor: c.borde,
            strokeDashArray: 3,
            xaxis: { lines: { show: false } },
            padding: { left: 6, right: 10, top: -6 },
        },
        tooltip: {
            theme: c.oscuro ? 'dark' : 'light',
            shared: true,
            intersect: false,
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: {
                formatter: function (v) { return Math.round(v).toLocaleString('es-PE'); },
                style: { colors: c.tenue, fontSize: '11px' },
            },
        },
    };
}

function ejeX(etiquetas, c, paso) {
    return {
        categories: rotulos(etiquetas, paso),
        axisBorder: { show: false },
        axisTicks: { show: false },
        tooltip: { enabled: false },
        labels: {
            rotate: 0,
            hideOverlappingLabels: true,
            trim: false,
            style: { colors: c.tenue, fontSize: '11px' },
        },
    };
}

function opcionesCirculacion(d, c, paso) {
    const base = baseGrafico(c, 290);

    return Object.assign(base, {
        chart: Object.assign(base.chart, { type: 'bar', stacked: true }),
        series: [
            { name: 'En sala', data: d.sala },
            { name: 'A casa', data: d.casa },
        ],
        colors: [c.sala, c.casa],
        plotOptions: {
            bar: {
                columnWidth: '72%',
                borderRadius: 4,
                borderRadiusApplication: 'end',
                borderRadiusWhenStacked: 'last',
            },
        },
        // 1px de superficie por lado = 2px de separacion entre segmentos apilados.
        stroke: { show: true, width: 1, colors: [c.superficie] },
        states: { hover: { filter: { type: 'darken' } } },
        xaxis: ejeX(d.etiquetas, c, paso),
        tooltip: Object.assign(base.tooltip, {
            x: { formatter: function (v, o) { return d.largas[o.dataPointIndex] || v; } },
            y: { formatter: function (v) { return v + (v === 1 ? ' préstamo' : ' préstamos'); } },
        }),
    });
}

function opcionesVisitas(d, c, paso) {
    const base = baseGrafico(c, 230);

    return Object.assign(base, {
        chart: Object.assign(base.chart, { type: 'area' }),
        series: [{ name: 'Visitas', data: d.total }],
        colors: [c.sala],
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 0, opacityFrom: c.oscuro ? 0.28 : 0.2, opacityTo: 0.02, stops: [0, 100] },
        },
        markers: { size: 0, strokeColors: c.superficie, strokeWidth: 2, hover: { size: 5 } },
        xaxis: Object.assign(ejeX(d.etiquetas, c, paso), { crosshairs: { show: true, stroke: { color: c.borde, width: 1, dashArray: 0 } } }),
        tooltip: Object.assign(base.tooltip, {
            x: { formatter: function (v, o) { return d.largas[o.dataPointIndex] || v; } },
            y: { formatter: function (v) { return Number(v).toLocaleString('es-PE') + (v === 1 ? ' visita' : ' visitas'); } },
        }),
    });
}
