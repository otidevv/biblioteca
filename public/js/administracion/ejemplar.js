let tabla;
let tablaMovimientos;
let ejemplar_id=0;
let ejemplaresCache = {};
const ejemplarContexto = window.ejemplarContexto || {
    bibliotecaFijaId: null,
    puedeFiltrarBiblioteca: true,
    accesoGlobal: false,
    bibliotecasUsuarioIds: [],
};
$(document).ready(function () {
    $('#barraSeleccion').hide();

    $('#modalEjemplar').on('hidden.bs.modal', function () {
        ejemplar_id = 0;
        resetEjemplarForm();
    });

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
        autoWidth:false,
        scrollX:true,
        responsive:false,
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
                    let disabled = row.can_move ? '' : 'disabled';
                    return '<input type="checkbox" class="check-ejemplar" value="'+data+'" '+disabled+'>';
                }
            },
            {
                data:null,
                name:'codigo_interno',
                render:function(data,type,row){
                    let codigoPrincipal = [row.codigo_dewey || row.codigo_ant || 'Sin codigo', row.tipo ? (row.tipo + (row.codigo_interno ?? '')) : ''].join(' ').trim();
                    let codigoInterno = row.codigo_interno ? 'Interno #' + row.codigo_interno : 'Sin numeracion';

                    return `
                        <div class="exemplars-table__code">
                            <span class="exemplars-table__code-main">${codigoPrincipal}</span>
                            <span class="exemplars-table__code-meta">${codigoInterno}</span>
                        </div>
                    `;
                }
            },
            {
                data:'detalle_compra.compra',
                name:'compra',
                render:function(data,type,row){
                    const compra = data ? data.nombre : 'Sin compra asociada';
                    const siaf = row.siaf ? row.siaf : 'Sin referencia SIAF';
                    return `
                        <div class="exemplars-table__purchase">
                            <span class="exemplars-table__purchase-main">${compra}</span>
                            <span class="exemplars-table__purchase-meta">${siaf}</span>
                        </div>
                    `;
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
        columnDefs: [
            { targets: 0, width: '44px', className: 'exemplars-col exemplars-col--check' },
            { targets: 1, width: '240px', className: 'exemplars-col exemplars-col--code' },
            { targets: 2, width: '220px', className: 'exemplars-col exemplars-col--purchase' },
            { targets: 3, width: '180px', className: 'exemplars-col exemplars-col--library' },
            { targets: 4, width: '130px', className: 'exemplars-col exemplars-col--status' },
            { targets: 5, width: '88px', className: 'admin-actions-cell exemplars-col exemplars-col--actions' }
        ],

        dom:default_datatable_dom,
        language:default_datatable_language,
        initComplete: function () {
            default_datatable_buttons.call(this);
            decorateTableActionButtons('#tabla-ejemplares');
        },
        preDrawCallback: function () {
            ejemplaresCache = {};
        },
        rowCallback: function(row, data) {
            ejemplaresCache[data.id] = data;
        },
        drawCallback: function () {
            decorateTableActionButtons('#tabla-ejemplares');
            $('#checkAll').prop('checked', false);
            actualizarSeleccion();
        }

    });

    tablaMovimientos = $('#tabla-movimientos-ejemplares').DataTable({
        processing:true,
        serverSide:true,
        pageLength:25,
        order:[[3, 'desc']],
        autoWidth:false,
        scrollX:true,
        responsive:false,
        ajax:{
            url:'/api/inventario/ejemplares/movimientos/listar',
            type:'GET',
            data:function(d){
                d.id = id;
            }
        },
        columns:[
            { data:'codigo', name:'ejemplar_id', orderable:false, searchable:false },
            { data:'origen', name:'bibliotecaOrigen.nombre', orderable:false, searchable:false },
            { data:'destino', name:'bibliotecaDestino.nombre', orderable:false, searchable:false },
            { data:'solicitado_por', name:'solicitadoPor.name', orderable:false, searchable:false },
            { data:'resuelto_por', name:'resueltoPor.name', orderable:false, searchable:false },
            { data:'estado_label', name:'estado' }
        ],
        dom:default_datatable_dom,
        language:default_datatable_language,
        initComplete:function(){
            default_datatable_buttons.call(this);
        }
    });


    /* ===============================
       ABRIR MODAL
    ===============================*/

    $('#btnAgregarEjemplar').click(function(){
        ejemplar_id = 0;
        prepareCreateMode();
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
                    ejemplar_id = 0;
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
        if (
            $(e.target).is('input,button,a') ||
            $(e.target).closest('.admin-action-menu').length ||
            $(e.target).closest('.dropdown-menu').length ||
            $(e.target).closest('.admin-action-link').length
        ) {
            return;
        }
        let checkbox = $(this).find('.check-ejemplar');
        if (!checkbox.length || checkbox.is(':disabled')) {
            return;
        }
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
        $('.check-ejemplar:not(:disabled)').prop('checked',this.checked);
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
        if(!ejemplares.length){
            alerta('Selecciona al menos un ejemplar disponible.', false);
            return;
        }
        let biblioteca = $('#biblioteca_destino').val();
        if(!biblioteca){
            alerta('Seleccione una biblioteca de destino.', false);
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
                alerta(respuesta.message,true);
                ocultarBarra();
                tabla.ajax.reload();
                tablaMovimientos.ajax.reload();
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.error || xhr.responseJSON?.message || 'No se pudo mover los ejemplares seleccionados.';
                alerta(message, false);
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

function resolverTraslado(id, accion) {
    const etiqueta = accion === 'aceptar' ? 'aceptar' : 'rechazar';
    const mensaje = accion === 'aceptar'
        ? '¿Aceptar el traslado del ejemplar a tu biblioteca?'
        : '¿Rechazar el traslado del ejemplar y devolverlo a su biblioteca de origen?';

    if (!confirm(mensaje)) {
        return;
    }

    $.ajax({
        url:'/api/inventario/ejemplares/resolver-traslado',
        type:'POST',
        data:{
            id:id,
            accion:etiqueta
        },
        headers:{
            'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
        },
        success:function(response){
            alerta(response.message, true);
            tabla.ajax.reload();
            tablaMovimientos.ajax.reload();
        },
        error:function(xhr){
            const message = xhr.responseJSON?.message || 'No se pudo responder el traslado.';
            alerta(message, false);
        }
    });
}
function actualizarEjemplar(id) {
    ejemplar_id=id;
    prepareEditMode();
    $('#modalEjemplarTitulo').text('Actualizar ejemplar');
    $('#btnGuardarEjemplar').text('Actualizar');

    const ejemplar = ejemplaresCache[id];
    if (ejemplar) {
        $('#siaf').val(ejemplar.siaf || '');
        $('#biblioteca_modal').val(ejemplar.biblioteca_id || 0);
    }

    $('#modalEjemplar').modal('show');
}

function prepareCreateMode() {
    resetEjemplarForm();
    $('.js-quantity-group').removeClass('oculto');
    $('.js-quantity-group').find('input').prop('disabled', false);
    requiredCampo('.js-quantity-group', true);
    $('#modalEjemplarTitulo').text('Registro de ejemplar');
    $('#btnGuardarEjemplar').text('Guardar');
}

function prepareEditMode() {
    resetEjemplarForm();
    $('.js-quantity-group').addClass('oculto');
    $('.js-quantity-group').find('input').prop('disabled', true);
    requiredCampo('.js-quantity-group', false);
    $('#modalEjemplarTitulo').text('Actualizar ejemplar');
    $('#btnGuardarEjemplar').text('Actualizar');
}

function resetEjemplarForm() {
    $('#formEjemplar')[0].reset();
    $('.invalid-feedback').remove();
    $('.is-invalid').removeClass('is-invalid');
    $('#biblioteca_modal').val(0);
    $('#cantidad').val(1);
    $('#siaf').val('');
    $('.js-quantity-group').removeClass('oculto');
    $('.js-quantity-group').find('input').prop('disabled', false);
}
