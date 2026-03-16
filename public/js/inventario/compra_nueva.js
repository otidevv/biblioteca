let detalleCompra = [];
let libroSeleccionado = null;

$(document).ready(function () {

    // ===============================
    // SELECT2
    // ===============================
    $('#proveedor_id').select2({
        placeholder: "Seleccione proveedor",
        allowClear: true,
        width: '100%',
        language: "es"
    });

    // ===============================
    // ABRIR MODAL
    // ===============================
    $('#btnNuevoLibro').click(function () {

        $('#formLibro')[0].reset();
        $('#libros').val(null).trigger('change');

        limpiarCamposLibro();

        $('#modalLibro').modal('show');

    });


    // ===============================
    // SELECT2 LIBROS
    // ===============================
    $('#libros').select2({

        dropdownParent: $('#modalLibro'),
        placeholder: "Buscar libro...",
        width: '100%',
        language: 'es',
        allowClear: true,

        ajax: {

            url: '/api/inventario/libros',
            dataType: 'json',
            delay: 250,

            data: function (params) {
                return { q: params.term };
            },

            processResults: function (data) {
                return { results: data };
            }

        }

    });


    // ===============================
    // SELECCIONAR LIBRO
    // ===============================
    $('#libros').on('select2:select', function (e) {

        let data = e.params.data;

        libroSeleccionado = data;

        modoLibroExistente(data);

    });


    // ===============================
    // LIMPIAR SELECT
    // ===============================
    $('#libros').on('select2:clear', function () {

        limpiarCamposLibro();

    });


    // ===============================
    // AGREGAR LIBRO AL DETALLE
    // ===============================
    $('#formLibro').submit(function(e){

        e.preventDefault();

        if(!libroSeleccionado){
            alert("Seleccione un libro");
            return;
        }

        let cantidad = parseInt($('#modal_cantidad').val());
        let precio = parseFloat($('#modal_precio').val());

        if(cantidad <= 0 || precio <= 0){
            alert("Cantidad y precio son obligatorios");
            return;
        }

        let subtotal = cantidad * precio;

        let autores = libroSeleccionado.autores
            ? libroSeleccionado.autores.map(a=>a.nombre).join(', ')
            : '';

        let item = {

            libro_id: libroSeleccionado.id,
            titulo: libroSeleccionado.text,
            autor: autores,
            cantidad: cantidad,
            precio: precio,
            subtotal: subtotal

        };

        detalleCompra.push(item);

        agregarFilaTabla(item);

        calcularTotal();

        $('#modalLibro').modal('hide');

    });


    // ===============================
    // ELIMINAR FILA
    // ===============================
    $(document).on('click','.btnEliminarDetalle',function(){

        let fila = $(this).closest('tr');
        let index = fila.index();

        detalleCompra.splice(index,1);

        fila.remove();

        calcularTotal();

    });


    // ===============================
    // GUARDAR COMPRA
    // ===============================
    $('#btnGuardarCompra').click(function(e){

        e.preventDefault();

        if(detalleCompra.length == 0){
            alert("Debe agregar al menos un libro");
            return;
        }

        let datosCompra = {

            siaf: $('input[name="numero_siaf"]').val(),
            fecha_compra: $('input[name="fecha_compra"]').val(),
            proveedor_id: $('select[name="proveedor_id"]').val(),
            observaciones: $('textarea[name="observaciones"]').val(),
            detalle: detalleCompra

        };

        $.ajax({

            url:'/api/inventario/compras/guardar',
            method:'POST',
            data:JSON.stringify(datosCompra),
            contentType:'application/json',

            headers:{
                'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
            },

            success:function(){

                alert("Compra guardada correctamente");

                location.reload();

            }

        });

    });

});


// ===============================
// MOSTRAR DATOS DEL LIBRO
// ===============================
function modoLibroExistente(data){

    $('#lbl_titulo').text(data.text);

    if(data.autores && data.autores.length){
        let autores = data.autores.map(a => a.nombre).join(', ');
        $('#lbl_autor').text(autores);
    }else{
        $('#lbl_autor').text('');
    }

    if(data.editorial){
        $('#lbl_editorial').text(data.editorial.nombre);
    }else{
        $('#lbl_editorial').text('');
    }

    if(data.imagen){

        $('#preview_imagen')
            .attr('src',data.imagen)
            .show();

    }else{

        $('#preview_imagen')
            .hide()
            .attr('src','');

    }

}


// ===============================
// LIMPIAR CAMPOS
// ===============================
function limpiarCamposLibro(){

    libroSeleccionado = null;

    $('#lbl_titulo').text('');
    $('#lbl_autor').text('');
    $('#lbl_editorial').text('');

    $('#preview_imagen')
        .hide()
        .attr('src','');

}


// ===============================
// AGREGAR FILA TABLA
// ===============================
function agregarFilaTabla(item){

    let fila = `
        <tr>

        <td>${item.titulo}</td>

        <td>${item.autor}</td>

        <td>${item.cantidad}</td>

        <td>${item.precio}</td>

        <td>${item.subtotal.toFixed(2)}</td>

        <td>
        <button class="btn btn-danger btn-sm btnEliminarDetalle">
        Eliminar
        </button>
        </td>

        </tr>
        `;

    $('#tablaDetalles tbody').append(fila);

}


// ===============================
// CALCULAR TOTAL
// ===============================
function calcularTotal(){

    let total = 0;

    detalleCompra.forEach(function(item){

        total += parseFloat(item.subtotal);

    });

    $('input[name="monto_total"]').val(total.toFixed(2));

}