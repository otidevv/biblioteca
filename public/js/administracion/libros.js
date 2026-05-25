let tabla;

function toTitleCase(str) {
    if (!str) return '';
    return str.toLowerCase().replace(/\b[a-záéíóúüñ]/gi, c => c.toUpperCase());
}

function formatAutores(raw) {
    if (!raw) return '<span class="books-cell__empty">—</span>';
    // El API devuelve formato "APELLIDO:NOMBRE" separados por coma
    const lista = raw.split(/[,\n]+/).map(a => a.trim()).filter(Boolean);
    const items = lista.map(function(a) {
        const partes = a.split(':');
        if (partes.length === 2) {
            const apellido = toTitleCase(partes[0].trim());
            const nombre   = toTitleCase(partes[1].trim());
            return `<span class="books-autor-name">${nombre} ${apellido}</span>`;
        }
        return `<span class="books-autor-name">${toTitleCase(a)}</span>`;
    });
    return `<div class="books-autor-cell">${items.join('')}</div>`;
}

function formatTipo(data) {
    if (!data || !data.nombre) return '<span class="books-cell__empty">—</span>';
    const nombre = toTitleCase(data.nombre.replace(/-/g, ' ').replace(/_/g, ' '));
    return `<span class="books-tipo-badge">${nombre}</span>`;
}

function formatEstado(data) {
    const activo = data == 1 || String(data).toLowerCase() === 'activo';
    return activo
        ? '<span class="books-estado-badge books-estado-badge--active"><i class="bi bi-check-circle-fill"></i> Activo</span>'
        : '<span class="books-estado-badge books-estado-badge--inactive"><i class="bi bi-dash-circle-fill"></i> Inactivo</span>';
}

function cargarOpcionesFiltros() {
    $.get('/api/bibliotecas/listar?draw=1&start=0&length=500', function(res) {
        const sel = $('#filtro-biblioteca');
        (res.data || [])
            .filter(function(b) { return b.estado == 1 || b.estado == null; })
            .sort(function(a, b) { return (a.nombre || '').localeCompare(b.nombre || ''); })
            .forEach(function(bib) {
                sel.append(new Option(bib.nombre, bib.id));
            });
    });

    $.get('/api/tipo_registros/listar?draw=1&start=0&length=200', function(res) {
        const sel = $('#filtro-tipo');
        (res.data || [])
            .sort(function(a, b) { return (a.nombre || '').localeCompare(b.nombre || ''); })
            .forEach(function(tipo) {
                const nombre = tipo.nombre
                    ? tipo.nombre.replace(/-/g, ' ').replace(/_/g, ' ')
                    : tipo.nombre;
                sel.append(new Option(toTitleCase(nombre), tipo.id));
            });
    });
}

function hayFiltrosActivos() {
    return $('#filtro-biblioteca').val() || $('#filtro-tipo').val() ||
           $('#filtro-estado').val()     || $('#filtro-con-ejemplares').val();
}

$(document).ready(function () {
    cargarOpcionesFiltros();

    tabla = $('#tabla-libros').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        autoWidth: false,
        scrollX: true,

        ajax: {
            url: "/api/inventario/libros/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            error: default_error_handler,
            data: function(d) {
                d.biblioteca_id    = $('#filtro-biblioteca').val();
                d.tipo_registro_id = $('#filtro-tipo').val();
                d.estado_filtro    = $('#filtro-estado').val();
                d.con_ejemplares   = $('#filtro-con-ejemplares').val();
            }
        },

        columns: [
            {
                data: 'codigo_dewey',
                name: 'codigo_dewey',
                render: function(data, type, row) {
                    const dewey  = data || '—';
                    const codigo = row.codigo || '';
                    return `<div class="books-code-cell">
                        <span class="books-code-cell__dewey">${dewey}</span>
                        ${codigo ? `<span class="books-code-cell__codigo">${codigo}</span>` : ''}
                    </div>`;
                }
            },
            {
                data: 'codigo',
                name: 'codigo',
                visible: false
            },
            {
                data: 'isbn',
                name: 'isbn',
                render: function(data) {
                    return data
                        ? `<span class="books-cell__mono">${data}</span>`
                        : '<span class="books-cell__empty">—</span>';
                }
            },
            {
                data: 'tipo_registro',
                name: 'tipo_registro.nombre',
                render: function(data) {
                    return formatTipo(data);
                }
            },
            {
                data: 'titulo',
                name: 'titulo',
                render: function(data, type) {
                    if (!data) return '';
                    const titulo = toTitleCase(data);
                    if (type === 'display' && titulo.length > 60) {
                        return `<span class="books-titulo-cell" title="${data}">${titulo.substring(0, 60)}<span class="books-titulo-cell__ellipsis">…</span></span>`;
                    }
                    return `<span class="books-titulo-cell">${titulo}</span>`;
                }
            },
            {
                data: 'autores',
                name: 'autores',
                render: function(data) {
                    return formatAutores(data);
                }
            },
            {
                data: 'ejemplares_count',
                name: 'ejemplares_count',
                orderable: false,
                render: function(data, type, row) {
                    const total   = Number(data || 0);
                    const propios = Number(row.ejemplares_usuario_count || 0);
                    const otras   = total - propios;
                    const resumen = Array.isArray(row.bibliotecas_resumen) ? row.bibliotecas_resumen : [];

                    if (total === 0) {
                        return '<span class="books-cell__empty">Sin ejemplares</span>';
                    }

                    // Desglose por biblioteca
                    let bibsHtml = '';
                    if (resumen.length > 0) {
                        bibsHtml = resumen.map(function(bib) {
                            const cls  = bib.es_mia ? 'books-bib-item--mine' : 'books-bib-item--other';
                            const icon = bib.es_mia ? 'bi-building-check' : 'bi-building';
                            return `<span class="books-bib-item ${cls}"><i class="bi ${icon}"></i>${bib.nombre}: <b>${bib.count}</b></span>`;
                        }).join('');
                    } else {
                        // Fallback con conteos si resumen no disponible
                        if (propios > 0) bibsHtml += `<span class="books-bib-item books-bib-item--mine"><i class="bi bi-building-check"></i>Tu biblioteca: <b>${propios}</b></span>`;
                        if (otras   > 0) bibsHtml += `<span class="books-bib-item books-bib-item--other"><i class="bi bi-building"></i>Otras: <b>${otras}</b></span>`;
                    }

                    return `<div class="books-exemplars-cell">
                        <strong class="books-exemplars-cell__total">${total} <span class="books-exemplars-cell__label">ejemplar${total !== 1 ? 'es' : ''}</span></strong>
                        <div class="books-bib-list">${bibsHtml}</div>
                    </div>`;
                }
            },
            {
                data: 'estado',
                name: 'estado',
                render: function(data) {
                    return formatEstado(data);
                }
            },
            {
                data: 'acciones',
                name: 'acciones',
                className: 'admin-actions-cell',
                orderable: false,
                searchable: false
            }
        ],

        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-libros');
            $('#tabla-libros').css('width', '100%');
        }
    });

    $('#filtro-biblioteca, #filtro-tipo, #filtro-estado, #filtro-con-ejemplares').on('change', function() {
        $('#btn-limpiar-filtros').toggle(!!hayFiltrosActivos());
        tabla.ajax.reload();
    });

    $('#btn-limpiar-filtros').on('click', function() {
        $('#filtro-biblioteca, #filtro-tipo, #filtro-estado, #filtro-con-ejemplares').val('');
        $(this).hide();
        tabla.ajax.reload();
    });
});
