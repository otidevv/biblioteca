let tabla;
let ejemplar_id=0;
$(document).ready(function () {
$('#barraSeleccion').hide();
    /* ===============================
       FILTRO DE BIBLIOTECA
    ===============================*/

    $('#biblioteca_filtro').change(function(){
        tabla.ajax.reload();
    });


    /* ===============================
       DATATABLE
    ===============================*/

    tabla = $('#tabla-ejemplares').DataTable({
        processing:true,
        serverSide:true,
        pageLength:50,
        order:[],
        ajax:{
            url:"/api/administracion/libros/ejemplar/listar",
            type:"GET",
            data:function(d){
                d.id = id;
                d.biblioteca_id = $('#biblioteca_filtro').val();
            }
        },
        columns:[
            {
                data:'id',
                orderable:false,
                searchable:false,
                render: function(data, type, row, meta){
                    let disabled = row.estado !== 'DISPONIBLE' ? 'disabled' : '';
                    return '<input type="checkbox" class="check-ejemplar" value="'+data+'" '+disabled+'>';
                }
            },
            {
                data:null,
                name:'codigo_interno',
                render:function(data,type,row){
                    return row.codigo_dewey+' '+row.tipo+row.codigo_interno;
                }
            },
            {
                data:'detalle_compra.compra',
                name:'compra',
                render:function(data,type,row){
                    return data ? data.nombre : (row.siaf ? row.siaf : '');
                }
            },
            {data:'biblioteca',name:'biblioteca'},
            {data:'estado',name:'estado'},

            {
                data:'acciones',
                name:'acciones',
                orderable:false,
                searchable:false
            }

        ],

        dom:default_datatable_dom,
        language:default_datatable_language

    });


    /* ===============================
       ABRIR MODAL
    ===============================*/

    $('#btnAgregarEjemplar').click(function(){
        ejemplar_id=0;
        requiredCampo('.validar-div', true);
        mostrarElemento('.validar-div', true);
        $('#modalEjemplar').modal('show');
    });


    /* ===============================
       GUARDAR EJEMPLAR
    ===============================*/

    $('#formEjemplar').submit(function(e){
        e.preventDefault();   // detener envío siempre
        if(!validar('#formEjemplar')) {
            return;
        }
        let datos = $(this).serializeArray();
        datos.push({name:'id', value:ejemplar_id});
        
        $.ajax({
            url: ejemplar_id==0 
                ? '/api/inventario/ejemplares/guardar'
                : '/api/inventario/ejemplares/actualizar',
            type:'POST',
            data:datos,
            headers:{
                'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
            },
            success:function(response){
                if(response.success){
                    $('#modalEjemplar').modal('hide');
                    $('#formEjemplar')[0].reset();
                    tabla.ajax.reload();
                    alerta(response.message,true);
                }
            },
            error: function (xhr) {
                // limpiar errores anteriores
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');                
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (campo, mensajes) {
                        let input = $('[name="'+campo+'"]');
                        // si es array ejemplo roles.0
                        if(campo.includes('.')){
                            campo = campo.split('.')[0];
                            input = $('[name="'+campo+'[]"]');
                        }
                        input.addClass('is-invalid');
                        input.after(
                            '<div class="invalid-feedback">'+mensajes[0]+'</div>'
                        );
                    });
                } else {
                    alerta(xhr.responseJSON.message, false);
                }

            }
        });

    });
    /* ===============================
       SELECCIONAR FILA
    ===============================*/
    $(document).on('click','#tabla-ejemplares tbody tr',function(e){
        if($(e.target).is('input,button,a')) return;
        let checkbox = $(this).find('.check-ejemplar');
        checkbox.prop('checked', !checkbox.prop('checked'));
        actualizarSeleccion();
    });
    /* ===============================
       CAMBIO CHECKBOX
    ===============================*/
    $(document).on('change','.check-ejemplar',function(){
        actualizarSeleccion();
    });
    /* ===============================
       CHECK TODOS
    ===============================*/
    $('#checkAll').change(function(){
        $('.check-ejemplar').prop('checked',this.checked);
        actualizarSeleccion();
    });

    /* ===============================
       MOVER EJEMPLARES
    ===============================*/
    $('#btnMoverBiblioteca').click(function(){
        let ejemplares = [];
        $('.check-ejemplar:checked').each(function(){
            ejemplares.push($(this).val());
        });
        let biblioteca = $('#biblioteca_destino').val();
        if(!biblioteca){
            alert('Seleccione una biblioteca');
            return;
        }
        $.ajax({

            url:'/api/inventario/ejemplares/enviar-biblioteca',
            type:'POST',

            data:{
                ejemplares:ejemplares,
                biblioteca_id:biblioteca
            },
            headers:{
                'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
            },
            success:function(respuesta){
                alerta(respuesta.message,true)
                ocultarBarra();
                tabla.ajax.reload();
            }
        });
    });
});
/* ===============================
   CONTROL DE SELECCION
===============================*/
function actualizarSeleccion(){
    let total = $('.check-ejemplar:checked').length;
    if(total > 0){
        $('#barraSeleccion').fadeIn(150);
        $('#contadorSeleccion').text(total);
    }else{
        ocultarBarra();
    }
}
function ocultarBarra(){
    $('#barraSeleccion').fadeOut(150);
    $('#barraSeleccion').hide();
    $('#contadorSeleccion').text(0);
    $('#checkAll').prop('checked',false);
}
function actualizarEjemplar(id) {
    ejemplar_id=id;
    requiredCampo('.validar-div', false);
    mostrarElemento('.validar-div', false);
    $('#modalEjemplar').modal('show');
    
}