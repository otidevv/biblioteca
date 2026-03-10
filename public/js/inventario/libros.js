
$(document).ready(function () {
    tabla = $('#tabla-libros').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url:  "/api/inventario/libros/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                // si necesitas enviar parámetros extra
            },
            error: default_error_handler        
        },
        columns: [
            { data: 'codigo_dewey', name: 'codigo_dewey' },
            { data: 'codigo', name: 'codigo' },
            { data: 'isbn', name: 'isbn' },
            { data: 'tipo_registro', name: 'tipo', 
                render: function (data, type, row) {
                return row.data?  data.nombre:'';
                }
            },
            { data: 'titulo', name: 'titulo' },
            { data: 'pais', name: 'pais' },
            { data: 'pais', name: 'pais' },
            { data: 'n', name: 'nombres', 
                render: function (data, type, row) {
                return row.nombres + ' ' + row.apellidos;
                }
            },
            { data: 'pais', name: 'pais' },
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