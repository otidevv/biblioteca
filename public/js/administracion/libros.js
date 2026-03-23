
$(document).ready(function () {
    tabla = $('#tabla-libros').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],

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
            { data: 'ejemplares_count', name: 'count_ejemplares' },
            { data: 'estado', name: 'estado' },
            {
                data: 'acciones',
                name: 'acciones',
                orderable: false,
                searchable: false
            }
        ],

        dom: default_datatable_dom,
        language: default_datatable_language,
        initComplete: default_datatable_buttons
    });
});