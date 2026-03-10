
$(document).ready(function () {

    $('#biblioteca_id').change(function(){
        tabla.ajax.reload();
    });
    tabla = $('#tabla-ejemplares').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url: "/api/administracion/libros/ejemplar/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                d.id=id;
                d.biblioteca_id=$('#biblioteca_id').val();
            },
            error: default_error_handler        
        },
        columns: [            
            { data: 'null', name: 'codigo_interno',
                render: function (data,type,row) {
                    return row.codigo_dewey+row.codigo_interno;
                }
            },
            { data: 'detalle_compra.compra', name: 'compra',
                render: function (data,type,row) {
                    return data ? data.nombre : row.siaf? row.siaf:'';
                }
            },
            { data: 'biblioteca', name: 'biblioteca',
                render: function (data) {
                    return data ? data.nombre : '';
                }
            },
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
    $('#btnAgregarEjemplar').on('click', function(){
        $('#modalEjemplar').modal('show');
    });
    $('#formEjemplar').on('submit', function(e){
        e.preventDefault();
        let formData = $(this).serialize();
        $.ajax({
            url: '/api/inventario/ejemplares/guardar',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response){
                if(response.success){
                    $('#modalEjemplar').modal('hide');
                    $('#formEjemplar')[0].reset();
                    tabla.ajax.reload(); // recargar datatable
                }
            },
            error: function(xhr){
                alert('Error al registrar ejemplares');
            }
        });
    });
});