let tabla;

$(document).ready(function () {
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
            error: default_error_handler        
        },

        columns: [

            { data: 'codigo_dewey', name: 'codigo_dewey' },
            { data: 'codigo', name: 'codigo' },
            { data: 'isbn', name: 'isbn' },
            {
                data: 'tipo_registro',
                name: 'tipo_registro.nombre',
                render: function (data) {
                    return data ? data.nombre : '';
                }
            },
            { data: 'titulo', name: 'titulo' },
            { data: 'autores',name: 'autores',
                render: function(data){
                    if(!data) return '';
                    return data+'<br>';
                }
            },
            {
                data: 'ejemplares_count',
                name: 'count_ejemplares',
                render: function(data, type, row){
                    const total = Number(data || 0);
                    const propios = Number(row.ejemplares_usuario_count || 0);
                    const etiqueta = propios > 0
                        ? `<div><span class="badge bg-success-subtle text-success border border-success-subtle">En tu biblioteca: ${propios}</span></div>`
                        : `<div><span class="badge bg-light text-muted border">No esta en tu biblioteca</span></div>`;

                    return `
                        <div class="d-flex flex-column gap-1">
                            <strong>${total}</strong>
                            ${etiqueta}
                        </div>
                    `;
                }
            },
            { data: 'estado', name: 'estado' },
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
            $('#tabla-libros').css('width', '100%');
        }
    });
});
