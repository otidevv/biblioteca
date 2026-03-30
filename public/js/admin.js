const decimal_places = 2;
const size_maximo = 26214400;//15 MB
var default_server = "sistema1";
var default_contanier = "#mensaje_container";
const sistema1 =  'sistema1';
const sistema2= 'sistema2';
var default_datatable_dom=
"<'card-body border-bottom py-3 datatables_header'<'d-flex'<'text-muted'l><'ms-auto text-muted'f>>>" +
"<'table-responsive'tr>" +
"<'card-footer d-flex align-items-center'<'m-0 text-muted'i><'m-0 ms-auto'p>>";

var default_datatable_language={
    "lengthMenu": "_MENU_",
    "info": "_START_ a _END_ de _TOTAL_",
    "infoEmpty": "Sin registros",
    "infoFiltered": "(Filtrado de _MAX_ registros)",
    "loadingRecords": "Cargando...",
    "processing": '<div class="pt-4 d-flex justify-content-center"><div class="spinner-border text-blue" role="status"></div><div class="ms-2"><b>Cargando...</b></div></div>',
    "search":         "",
    "searchPlaceholder": "Buscar",
    "zeroRecords":    "No se encontraron registros",
    "paginate": {
        "first":      "Primero",
        "last":       "Ultimo",
        "next":       ">",
        "previous":   "<"
    }
};

function default_error_handler(jqXHR, ajaxOptions, thrownError) {
    alerta(response_helper(jqXHR), false);     
    //console.log(thrownError + "\r\n" + jqXHR.statusText + "\r\n" + jqXHR.responseText + "\r\n" + ajaxOptions.responseText);
    return true;
}


function default_datatable_buttons() {
    var self = this.api(),
    $container = $(self.table().container()).closest('.dataTables_wrapper'),
    $filter = $container.find('.dataTables_filter').first(),
    $length = $container.find('.dataTables_length').first(),
    input = $filter.find('input').off('keypress'),
    $searchButton = $('<button>')
                .html('<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>')
                .addClass('btn btn-secondary align-top btn-icon ms-1')
                .click(function() {
                    self.search(input.val()).draw();
                }),
    $clearButton = $('<button>')
                .html('<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path></svg>')
                .addClass('btn btn-white align-top btn-icon ms-1 escritorio')
                .click(function() {
                    input.val('');
                    $searchButton.click(); 
                });

    input.removeClass('form-control-sm').addClass('form-control');
    input.keypress(function (e) {
        if (e.which == 13) {
            $searchButton.click(); 
            return false;
        }
    });

    $filter.find('.btn-icon').remove();
    $filter.append($searchButton, $clearButton);

    var select = $length.find('select');
    select.removeClass('form-control-sm').removeClass('form-control').removeClass('form-select-sm');
    select.addClass('form-select'); 
    //custom-select custom-select-sm form-control form-control-sm
} 

function decorateTableActionButtons(tableSelector) {
    const actionMap = [
        { match: ['editar'], icon: 'bi-pencil-square', label: 'Editar', variant: 'edit' },
        { match: ['eliminar'], icon: 'bi-trash3', label: 'Eliminar', variant: 'delete' },
        { match: ['contrasena', 'password'], icon: 'bi-shield-lock', label: 'Clave', variant: 'password' },
        { match: ['permisos'], icon: 'bi-shield-check', label: 'Permisos', variant: 'permissions' },
        { match: ['ver'], icon: 'bi-eye', label: 'Ver', variant: 'view' },
        { match: ['mover'], icon: 'bi-arrow-left-right', label: 'Mover', variant: 'move' }
    ];

    $(tableSelector + ' tbody tr').each(function () {
        const $row = $(this);
        const $cell = $row.find('td').last();
        const $existingMenu = $cell.find('.admin-action-menu, .user-action-menu').first();
        const $actions = $cell.find('a, button').filter(function () {
            return !$(this).hasClass('dropdown-toggle')
                && !$(this).is('[data-bs-dismiss]')
                && !$(this).closest('.admin-action-menu__dropdown, .user-action-menu__dropdown').length
                && !$(this).hasClass('admin-action-menu__trigger')
                && !$(this).hasClass('user-action-menu__trigger');
        });

        if (!$actions.length && !$existingMenu.length) {
            return;
        }

        $cell.addClass('admin-actions-cell');

        if (!$existingMenu.length && $actions.length) {
            const $menu = $(
                '<div class="dropdown admin-action-menu">' +
                    '<button class="btn admin-action-menu__trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir acciones">' +
                        '<i class="bi bi-three-dots"></i>' +
                    '</button>' +
                    '<div class="dropdown-menu dropdown-menu-end admin-action-menu__dropdown"></div>' +
                '</div>'
            );

            const $dropdown = $menu.find('.admin-action-menu__dropdown');
            $actions.appendTo($dropdown);
            $cell.empty().append($menu);
        }

        const $finalActions = $cell.find('a, button').filter(function () {
            return !$(this).hasClass('dropdown-toggle')
                && !$(this).is('[data-bs-dismiss]')
                && !$(this).hasClass('admin-action-menu__trigger')
                && !$(this).hasClass('user-action-menu__trigger');
        });

        $finalActions.each(function () {
            const $action = $(this);
            const classText = ($action.attr('class') || '').toLowerCase();
            const action = actionMap.find(item => item.match.some(key => classText.includes(key)));

            $action.removeClass('btn-sm btn-primary btn-secondary btn-success btn-warning btn-danger btn-info btn-light btn-dark');
            $action.addClass('dropdown-item admin-action-link');

            if (action) {
                $action
                    .removeClass('admin-action-link--edit admin-action-link--delete admin-action-link--password admin-action-link--permissions admin-action-link--view admin-action-link--move')
                    .addClass('admin-action-link--' + action.variant)
                    .attr('title', action.label)
                    .attr('aria-label', action.label)
                    .html('<i class="bi ' + action.icon + '"></i><span>' + action.label + '</span>');
            } else {
                const text = ($action.text() || '').trim();

                if (!$action.find('span').length && text !== '') {
                    $action.html('<span>' + text + '</span>');
                }
            }
        });
    });
}


//validar formulario (boostrap 4)
function validar(ident_form) //validar_fecha, validar_entero, validar_decimal
{    
    var resultado = true;
    limpiar(ident_form);
    //recorremos los items del formulario
    $(ident_form+" .form-group").each(function() {
        var grupo = this;

        if($(grupo).hasClass("form-required"))//es obligatorio
        {
            var textbox = $(this).find('input[type=text],input[type=number],input[type=password],input[type=email],input[type=date]')//es un textbox
            if(textbox.length)
            {
                if(textbox.val().trim()=="")//esta vacio
                {
                    resultado=false;
                    $(textbox).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Este campo es obligatorio</div>');                   
                }
            }
            
            var checkbox = $(this).find('input[type=checkbox]')//es un checkbox
            if(checkbox.length)
            {
                if(!checkbox.is(':checked'))//no esta check
                {
                    resultado=false;
                    $(checkbox).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Este campo es obligatorio</div>');                   
                }
            }  

            var textarea = $(this).find('textarea')//es una textarea
            if(textarea.length)
            {
                if(textarea.val().trim()=="")//esta vacio
                {
                    resultado=false;
                    $(textarea).addClass('is-invalid')
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Este campo es obligatorio</div>');
                }
            }

            var file = $(this).find('input[type=file]')//es un file
            if(file.length)
            {
                if(file[0].files.length == 0)//esta vacio
                {
                    resultado=false;
                    $(file).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Este campo es obligatorio</div>');                   
                }
            }  
        }

        //debe ser fecha
        var fecha = $(this).find('.validar_fecha')
        if(fecha.length)
        {
            if(fecha.val().length>0)
            {
                if(!esFecha(fecha.val()))
                {
                    resultado=false;
                    $(fecha).addClass('is-invalid')
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese una fecha válida</div>');
                }
            }
        }

        //debe ser fecha alt
        var fecha_alt = $(this).find('.validar_fecha_alt')
        if(fecha_alt.length)
        {
            if(fecha_alt.val().length>0)
            {
                if(!esFechaAlt(fecha_alt.val()))
                {
                    resultado=false;
                    $(fecha_alt).addClass('is-invalid')
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese una fecha válida</div>');
                }
            }
        }

        //debe ser fecha hora
        var fecha_hora = $(this).find('.validar_fecha_hora')
        if(fecha_hora.length)
        {
            if(fecha_hora.val().length>0)
            {
                if(!esFechaHora(fecha_hora.val()))
                {
                    resultado=false;
                    $(fecha_hora).addClass('is-invalid')
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese una fecha válida</div>');
                }
            }
        }

        //debe ser hora
        var hora = $(this).find('.validar_hora')
        if(hora.length)
        {
            if(hora.val().length>0)
            {
                if(!esHora(hora.val()))
                {
                    resultado=false;
                    $(hora).addClass('is-invalid')
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese una hora válida</div>');
                }
            }
        }

        
        //debe fecha mayor
        var fecha_mayor = $(this).find('[class*=validar_fecha_mayor]')
        if(fecha_mayor.length)
        { 
            if(fecha_mayor.val().length>0)
            {
                var className = fecha_mayor.attr('class');
                var dos_index_mas = className.indexOf("validar_fecha_mayor:");

                if(dos_index_mas!=-1)
                {
                    var dos_index = dos_index_mas + 19;
                    var space_index = className.indexOf(" ", dos_index);

                    if(space_index!=-1)
                        var id_otro = className.substring(dos_index + 1, space_index);
                    else
                        var id_otro = className.substring(dos_index + 1);
                   
                    if(esFecha(fecha_mayor.val()) && esFecha($("#"+id_otro).val()))
                    {
                        var mayor = new Date(db_fecha(fecha_mayor.val()));
                        var menor = new Date(db_fecha($("#"+id_otro).val()));

                        if(mayor <= menor)
                        {
                            resultado=false;
                            $(fecha_mayor).addClass('is-invalid');
                            $(grupo).find('.invalid-feedback').remove();
                            $(grupo).append('<div class="invalid-feedback">La fecha debe ser mayor</div>');
                        }                    
                    }
                }                
            }
        }

        //debe fecha mayor igual
        var fecha_mayor_igual = $(this).find('[class*=validar_fecha_mayor_igual]')
        if(fecha_mayor_igual.length)
        { 
            if(fecha_mayor_igual.val().length>0)
            {
                var className = fecha_mayor_igual.attr('class');
                var dos_index_mas = className.indexOf("validar_fecha_mayor_igual:");

                if(dos_index_mas!=-1)
                {
                    var dos_index = dos_index_mas + 25;
                    var space_index = className.indexOf(" ", dos_index);

                    if(space_index!=-1)
                        var id_otro = className.substring(dos_index + 1, space_index);
                    else
                        var id_otro = className.substring(dos_index + 1);
                   
                    if(esFecha(fecha_mayor_igual.val()) && esFecha($("#"+id_otro).val()))
                    {
                        var mayor = new Date(db_fecha(fecha_mayor_igual.val()));
                        var menor = new Date(db_fecha($("#"+id_otro).val()));

                        if(mayor < menor)
                        {
                            resultado=false;
                            $(fecha_mayor_igual).addClass('is-invalid');
                            $(grupo).find('.invalid-feedback').remove();
                            $(grupo).append('<div class="invalid-feedback">La fecha debe ser mayor o igual</div>');
                        }                    
                    }
                }                
            }
        }

        //debe ser numero
        var numero = $(this).find('.validar_numero')
        if(numero.length)
        {
            if(numero.val().length>0)
            {
                var numero_temp = numero.val();
                if(!esNumero(numero_temp))
                {
                    resultado=false;
                    $(numero).addClass('is-invalid')
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese un número válido</div>');
                }
            }
        }

        //debe ser entero
        var entero = $(this).find('.validar_entero')
        if(entero.length)
        {
            if(entero.val().length>0)
            {
                var entero_temp = entero.val();
                if(!esNumero(entero_temp))
                {
                    resultado=false;
                    $(entero).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese un número entero válido</div>');
                }
                else
                {
                    entero.val(parseInt(entero_temp));
                }
            }
        }

        //debe ser entero > 0
        var entero_cero = $(this).find('.validar_entero_cero')
        if(entero_cero.length)
        {
            if(entero_cero.val().length > 0)
            {
                var entero_temp_cero = entero_cero.val();
                if(!esNumero(entero_temp_cero))
                {
                    resultado=false;
                    $(entero_cero).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese un número entero válido</div>');
                }
                else
                {
                    var numero_cero = parseInt(entero_temp_cero);

                    if(numero_cero == 0)
                    {
                        resultado=false;
                        $(entero_cero).addClass('is-invalid');
                        $(grupo).find('.invalid-feedback').remove();
                        $(grupo).append('<div class="invalid-feedback">El número debe ser mayor que 0</div>');
                    }
                    else
                        entero_cero.val(parseInt(entero_temp_cero));
                }
            }
        }
       
        //debe ser decimal
        var decimal = $(this).find('.validar_decimal')
        if(decimal.length)
        {
            if(decimal.val().length>0)
            {
                var decimal_temp = decimal.val();
                if(!esNumero(decimal_temp))
                {
                    resultado=false;
                    $(decimal).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese un número decimal válido</div>');
                }
                else
                {
                    decimal.val(parseFloat(decimal_temp).toFixed(decimal_places));
                }
            }
        }

        //debe ser decimal > 0
        var decimal_cero = $(this).find('.validar_decimal_cero')
        if(decimal_cero.length)
        {
            if(decimal_cero.val().length > 0)
            {
                var decimal_temp_cero = decimal_cero.val();
                if(!esNumero(decimal_temp_cero))
                {
                    resultado=false;
                    $(decimal_cero).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese un número decimal válido</div>');
                }
                else
                {
                    var numero_d_cero = parseFloat(decimal_temp_cero);
                    if(numero_d_cero == 0)
                    {
                        resultado=false;
                        $(decimal_cero).addClass('is-invalid');
                        $(grupo).find('.invalid-feedback').remove();
                        $(grupo).append('<div class="invalid-feedback">El número debe ser mayor que 0</div>');
                    }
                    else
                        decimal_cero.val(parseFloat(decimal_temp_cero).toFixed(decimal_places));
                }
            }
        }

        //debe ser numeros separado por comas
        var numeros_coma = $(this).find('.validar_numeros_coma')
        if(numeros_coma.length)
        {
            if(numeros_coma.val().length > 0)
            {            
                var coma_valido = true;   
                let valores = numeros_coma.val().split(',');
                for (let i = 0; i < valores.length; i++) {
                    if(!esNumero(valores[i])){
                        coma_valido = false
                    }
                }

                if(!coma_valido)
                {
                    resultado=false;
                    $(numeros_coma).addClass('is-invalid')
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese números válidos</div>');
                }
            }
        }

        //debe ser correo
        var correo = $(this).find('.validar_correo')
        if(correo.length)
        {
            if(correo.val().length>0)
            {                
                if(!esCorreo(correo.val()))
                {
                    resultado=false;
                    $(correo).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese un correo válido</div>');
                } 
            }
        }

        //debe ser igual
        var igual = $(this).find('[class*=validar_igual]')
        if(igual.length)
        {
            if(igual.val().length>0)
            {
                var className = igual.attr('class');
                var dos_index_mas = className.indexOf("validar_igual:");

                if(dos_index_mas!=-1)
                {
                    var dos_index = dos_index_mas + 13;
                    var space_index = className.indexOf(" ", dos_index);

                    if(space_index!=-1)
                        var id_otro = className.substring(dos_index + 1, space_index);
                    else
                        var id_otro = className.substring(dos_index + 1);

                    if(igual.val()!=$("#"+id_otro).val())
                    {
                        resultado=false;
                        $(igual).addClass('is-invalid');
                        $(grupo).find('.invalid-feedback').remove();
                        $(grupo).append('<div class="invalid-feedback">Los valores deben ser iguales</div>');
                    }
                }                
            }
        }

        //debe ser minimo
        var minimo = $(this).find('[class*=validar_minimo]')
        if(minimo.length)
        {
            if(minimo.val().length>0)
            {
                var className = minimo.attr('class');
                var dos_index_mas = className.indexOf("validar_minimo:");

                if(dos_index_mas!=-1)
                {
                    var dos_index = dos_index_mas + 14;
                    var space_index = className.indexOf(" ", dos_index);

                    if(space_index!=-1)
                        var tamaño = className.substring(dos_index + 1, space_index);
                    else
                        var tamaño = className.substring(dos_index + 1);

                    if(minimo.val().length < parseInt(tamaño))
                    {
                        resultado=false;
                        $(minimo).addClass('is-invalid');
                        $(grupo).find('.invalid-feedback').remove();
                        $(grupo).append('<div class="invalid-feedback">Debe contener al menos '+tamaño+' caracteres</div>');
                    }
                }                
            }
        }

        //debe ser maximo
        var maximo = $(this).find('[class*=validar_maximo]')
        if(maximo.length)
        {
            if(maximo.val().length>0)
            {
                var className = maximo.attr('class');
                var dos_index_mas = className.indexOf("validar_maximo:");

                if(dos_index_mas!=-1)
                {
                    var dos_index = dos_index_mas + 14;
                    var space_index = className.indexOf(" ", dos_index);

                    if(space_index!=-1)
                        var tamaño = className.substring(dos_index + 1, space_index);
                    else
                        var tamaño = className.substring(dos_index + 1);

                    if(maximo.val().length > parseInt(tamaño))
                    {
                        resultado=false;
                        $(maximo).addClass('is-invalid');
                        $(grupo).find('.invalid-feedback').remove();
                        $(grupo).append('<div class="invalid-feedback">El tamaño del texto no debe ser mayor a '+tamaño+' caracteres</div>');
                    }
                }                
            }
        }


        //debe ser diferente de 0
        var select  = $(this).find('.validar_select')
        if(select.length)
        {
            if(select.val().length>0)//esta vacio ignorar
            {
                var select_val = select.val(); 
                if(select_val=="0")
                {
                    resultado=false;
                    $(select).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Seleccione una opción</div>');
                }
            }
        }

        //debe ser diferente de -1
        var select_1  = $(this).find('.validar_select_1')
        if(select_1.length)
        {
            if(select_1.val().length > 0)//esta vacio ignorar
            {
                var select_1_val = select_1.val(); 
                if(select_1_val == "-1")
                {
                    resultado=false;
                    $(select_1).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Seleccione una opción</div>');
                }
            }
        }

        //no debe contener caracteres  especiales
        var no_especial = $(this).find('.validar_no_especial')
        if(no_especial.length)
        {
            if(no_especial.val().length>0)
            { 
                if(esEspecial(no_especial.val()))
                {
                    resultado=false;
                    $(no_especial).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">No debe contener carácteres especiales</div>');
                } 
            }
        }

        //debe ser tamaño exacto
        var exacto = $(this).find('[class*=validar_exacto]')
        if(exacto.length)
        {
            if(exacto.val().length>0)
            {
                var className = exacto.attr('class');
                var dos_index_mas = className.indexOf("validar_exacto:");

                if(dos_index_mas!=-1)
                {
                    var dos_index = dos_index_mas + 14;
                    var space_index = className.indexOf(" ", dos_index);

                    if(space_index!=-1)
                        var tamaño = className.substring(dos_index + 1, space_index);
                    else
                        var tamaño = className.substring(dos_index + 1);

                    if(exacto.val().length != parseInt(tamaño))
                    {
                        resultado=false;
                        $(exacto).addClass('is-invalid');
                        $(grupo).find('.invalid-feedback').remove();
                        $(grupo).append('<div class="invalid-feedback">Debe contener '+tamaño+' caracteres</div>');
                    }
                }                
            }
        }


        //debe ser latitud
        var latitud = $(this).find('.validar_latitud')
        if(latitud.length)
        {
            if(latitud.val().length>0)
            {
                if(!esLatitud(latitud.val()))
                {
                    resultado=false;
                    $(latitud).addClass('is-invalid')
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese una latitud válida</div>');
                }
            }
        }


        //debe ser longitud
        var longitud = $(this).find('.validar_longitud')
        if(longitud.length)
        {
            if(longitud.val().length>0)
            {
                if(!esLongitud(longitud.val()))
                {
                    resultado=false;
                    $(longitud).addClass('is-invalid')
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">Ingrese una longitud válida</div>');
                }
            }
        }

        //validar mask
        var input_mask = $(this).find('.validar_mask');        
        if(input_mask.length)
        {
            var mask_raw = input_mask.val();
            var mask_value = input_mask.val().replace(/\D/g,'');
            var mask_data = input_mask.data("mask");

            if(mask_value.length > 0)//hay datos
            {
                if(!cumple_formato(mask_raw, mask_data))
                {
                    resultado=false;
                    $(input_mask).addClass('is-invalid');
                    $(grupo).find('.invalid-feedback').remove();
                    $(grupo).append('<div class="invalid-feedback">No cumple con el formato requerido</div>');
                } 
            } else {
                resultado=false;
                $(input_mask).addClass('is-invalid');
                $(grupo).find('.invalid-feedback').remove();
                $(grupo).append('<div class="invalid-feedback">Este campo es obligatorio</div>');       
            }
        }

        

    });

    return resultado;
}
// validar input  debe ser diferente de 0 por id
function validar_select_id(selectId) {

    const select = $('#' + selectId);

    // Si no existe el select → no validar
    if (!select.length) return true;

    // Limpiar errores previos
    select.removeClass('is-invalid');
    select.closest('.form-group').find('.invalid-feedback').remove();

    // Validar valor
    if (select.val() === "0" || select.val() === "") {

        select.addClass('is-invalid');

        select.closest('.form-group').append(
            '<div class="invalid-feedback">Seleccione una opción</div>'
        );

        return false;
    }

    return true;
}
//limpiar validaciones de formulario
function limpiar(ident_form) {
    $(ident_form+" .form-group").each(function() {
        var l_textbox = $(this).find('input[type=text], input[type=number], input[type=email], input[type=password], input[type=checkbox], input[type=file], input[type=date]');//es un textbox
        l_textbox.removeClass('is-invalid');

        var l_textarea = $(this).find('textarea');//es una textarea
        l_textarea.removeClass('is-invalid');

        var l_select = $(this).find('select')//es un select
        l_select.removeClass('is-invalid'); 

        $(this).find('.invalid-feedback').remove();
    });
}

//vaciar valores de formulario
function vaciar(ident_form) {
    $(ident_form+" .form-group").each(function() {        
        var textbox = $(this).find('input[type=text], input[type=number], input[type=email], input[type=password], input[type=date]')//es un textbox
        if(textbox.length)
            textbox.val('');

        var textarea = $(this).find('textarea')//es una textarea
        if(textarea.length)
            textarea.val('');
    });
}

//Verificar si es fecha válida
function esFecha(dateString)
{
    // First check for the pattern
    if(!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateString))
        return false;      
    // Parse the date parts to integers
    var parts = dateString.split("/");
    var day = parseInt(parts[0], 10);
    var month  = parseInt(parts[1], 10);    
    var year = parseInt(parts[2], 10);

    // Check the ranges of month and year
    if(year < 1000 || year > 3000 || month == 0 || month > 12)
        return false;

    var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

    // Adjust for leap years
    if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
        monthLength[1] = 29;

    // Check the range of the day
    return day > 0 && day <= monthLength[month - 1];
};

function esFechaAlt(dateString)
{
    // First check for the pattern
    if(!/^\d{4}[-]\d{1,2}[-]\d{1,2}$/.test(dateString))
        return false;      
    
    // Parse the date parts to integers
    var parts = dateString.split("-");
    var year = parseInt(parts[0], 10);
    var month  = parseInt(parts[1], 10);    
    var day = parseInt(parts[2], 10); 

    // Check the ranges of month and year
    if(year < 1000 || year > 3000 || month == 0 || month > 12)
        return false;

    var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

    // Adjust for leap years
    if(year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
        monthLength[1] = 29;

    // Check the range of the day
    return day > 0 && day <= monthLength[month - 1];
};


//Verificar si es fecha y hora válida
function esFechaHora(dateString)
{    
    var pattern = new RegExp("^(3[01]|[12][0-9]|0[1-9])/(1[0-2]|0[1-9])/[0-9]{4} (2[0-3]|[01]?[0-9]):([0-5]?[0-9])$");

    if (dateString.search(pattern)===0) 
        return true;
    else 
        return false;
};

function esHora(horaString) {
    var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(horaString);
    if(isValid)
        return true;
    else
        return false;
}


function esEspecial(texto) {
    var resultado = false;
    var splChars = "*|,\":<>[]{}`\';()@&$#%/\\";
    for (var i = 0; i < texto.length; i++) 
    {
        if(splChars.indexOf(texto.charAt(i)) != -1)
        {
            resultado = true;
            break;
        }
    }
    return resultado;
}

function esLatitud(lat) {
    var res = true;
    var patron = new RegExp('^-?([1-8]?[1-9]|[1-9]0)\\.{1}\\d{1,6}');

    if(!(isFinite(lat) && Math.abs(lat) <= 90))
        res = false;

    if(!patron.test(lat))
        res = false;

    return res;   
}
  
function esLongitud(lng) {
    var res = true;
    var patron = new RegExp('^-?([1-8]?[1-9]|[1-9]0)\\.{1}\\d{1,6}');
    if(!(isFinite(lng) && Math.abs(lng) <= 180))
        res = false;

    if(!patron.test(lng))
        res = false;

    return res;
}

//convertir de dd/mm/yyy a yyyy-mm-dd
function db_fecha(dateString) {
    var fecha = dateString+"";    
    if(dateString!= null && fecha!=""){
        var parts = dateString.split("/");
        return parts[2]+'-'+parts[1]+'-'+parts[0];
    }
    else
        return "";    
}

function db_fecha_hora(dateString) {
    var fecha = dateString+"";    
    if(dateString!= null && fecha!=""){
        var s_fecha = dateString.substring(0,10);
        var s_hora = dateString.substring(11);
        var parts = s_fecha.split("/");
        return parts[2]+'-'+parts[1]+'-'+parts[0]+' '+s_hora+':00';
    }
    else
        return "";    
}

//convertir de yyyy-mm-dd hh:mm:ss a dd/mm/yyy
function dis_fecha(dateString) {
    var fecha = dateString+"";  
    if(dateString!= null && fecha!=""){
        var ofecha = new Date(fecha);
        return ('0' + ofecha.getDate()).slice(-2)+'/'+('0' + (ofecha.getMonth()+1)).slice(-2)+'/'+ofecha.getFullYear();
    }
    else
        return "";    
}

function dis_fecha_hora(dateString) {
    var fecha = dateString+"";  
    if(dateString!= null && fecha!=""){
        var ofecha = new Date(fecha);
        return ('0' + ofecha.getDate()).slice(-2)+'/'+('0' + (ofecha.getMonth()+1)).slice(-2)+'/'+ofecha.getFullYear()+' '+('0' + ofecha.getHours()).slice(-2)+':'+('0' + ofecha.getMinutes()).slice(-2);
    }
    else
        return "";            
}

function dis_solo_hora(dateString) {
    var fecha = dateString+"";  
    if(dateString!= null && fecha!=""){
        var ofecha = new Date(fecha);
        return ('0' + ofecha.getHours()).slice(-2)+':'+('0' + ofecha.getMinutes()).slice(-2);
    }
    else
        return "";            
}

function dis_hora(timeString) {
    var hora = timeString+"";  
    if(timeString!= null && hora!=""){
        var parts = hora.split(":");
        return parts[0]+':'+parts[1];
    }
    else
        return "";    
}

function db_hora(timeString) {
    var hora = timeString+"";    
    if(timeString != null && hora != ""){
        var s_hora = hora.substring(0, 5);      
        return s_hora+':00';
    }
    else
        return "";    
}

function obj_fecha(obj) {    
    if(typeof obj.getMonth === 'function')
        return ('0' + obj.getDate()).slice(-2)+'/'+('0' + (obj.getMonth()+1)).slice(-2)+'/'+obj.getFullYear();
    else
        return "";    
}

function obj_fecha_hora(obj) {    
    if(typeof obj.getMonth === 'function')
        return ('0' + obj.getDate()).slice(-2)+'/'+('0' + (obj.getMonth()+1)).slice(-2)+'/'+obj.getFullYear()+' '+('0' + +obj.getHours()).slice(-2)+':'+('0' + obj.getMinutes()).slice(-2);
    else
        return ""; 
}

function safeDecimal(texto) {
    if(isNaN(texto) | texto == null)    
        return 0
    else 
        return parseFloat(texto)
}

function safeMoney(texto) {
    if(isNaN(texto) | texto == null)    
        return (0).toFixed(decimal_places); 
    else 
    {
        if(texto!="")
        {
            var n_decimal = parseFloat(texto);
            return n_decimal.toFixed(decimal_places).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");       
        }
        else
            return (0).toFixed(decimal_places); 
    }
}

//prevenir mostrar texto null
function safeText(texto) {
    if(texto!=null)
    {
        var aux = texto+"";
        return aux.replace("&#039;", "'");   
    }    
    else
        return "";
}

function safeCode(texto) {
    if(texto!=null)
    {
        var aux = texto+"";
        return aux.replaceAll("&#039;", "'")
        .replaceAll("&quot;", '"')
        .replaceAll("&gt;", '>')
        .replaceAll("&lt;", '<')
        .replaceAll("&amp;", '&');  
        
    }    
    else
        return "";
}

function safeHTML(texto) {
    if(texto!=null)    
        return sds = $("<div/>").html(texto).text();      
    else
        return "";
}

function safeListCount(lista) {
    if(lista != null)   
        return lista.length;    
    else
        return 0;
}

function safeSelect(valor) {
    if(valor == "" || valor == "0" || valor == "-1" || valor == 0 || valor == -1)   
        return null;
    else
        return valor;   
}

//comprobar si es numero
function esNumero(numberString) 
{
    return !isNaN(numberString)    
}

function esCorreo(dateEmail)
{
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(dateEmail);
};

function cumple_formato(texto, formato) {//____ ____ ____ ____ 0000 0000 0000 0000
    var resultado = true;
    var texto_value = texto.replace(/\D/g,'');
    var formato_value = formato.replace(/\D/g,'');

    if(texto_value.length != formato_value.length)
        resultado = false;

    return resultado;
}

function textoMax(texto, max) 
{
    if(texto != null)
    {
        if(texto.length > max)
            return texto.substring(0, max)+"...";   
        else
            return texto;  
    }
    else
        return "";
}

function ceros(num, zero) {
    var str = "" + num;
    var pad = "0".repeat(zero);
    return pad.substring(0, pad.length - str.length) + str;
}

//obtener el elemento de una lista por su id
function elementId(idb, datos) {
    var res = null;
    for (let i = 0; i < datos.length; i++) {
        if(datos[i].id==idb)
        {
            res = datos[i];
            break;
        }        
    }
    return res;
}


function alerta(mensaje, estado) 
{
   if(estado)
   {
       $('<div class="notificacion alert alert-important alert-success alert-dismissible shadow" role="alert">'+
           '<div class="d-flex">'+
               '<div>'+
                   '<svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l5 5l10 -10"></path></svg>'+
               '</div>'+
               '<div>'+mensaje+'</div>'+
           '</div>'+
           '<a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close" onclick="alerta_cerrar(this);"></a>'+
       '</div>').appendTo(default_contanier).delay(8000).queue(function() { $(this).remove(); });
   }
   else
   {
       $('<div class="notificacion alert alert-important alert-danger alert-dismissible shadow" role="alert">'+
           '<div class="d-flex">'+
               '<div>'+
                   '<svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>'+
               '</div>'+
               '<div>'+mensaje+'</div>'+
           '</div>'+
           '<a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close" onclick="alerta_cerrar(this);"></a>'+
       '</div>').appendTo(default_contanier).delay(8000).queue(function() { $(this).remove(); });
   }
}

function alerta_cerrar(notif) 
{
   //e.preventDefault();
   var parent = $(notif).parent('.notificacion');
   parent.remove();
   //parent.fadeOut("slow", function() { $(this).remove(); } );
}


function response_helper(response) {    
   
   var res_text = JSON.stringify(response);
   if(res_text.indexOf("CSRF token mismatch.") !== -1) {
       setTimeout(function () { location.reload(); }, 3000);
       return "La sesión ha expirado"; 
   }        
   
   if(response.hasOwnProperty('responseJSON'))   
   {
       console.log('Tiene responseJSON');
       return JSON.stringify(response.responseJSON.message); 
   } 
       
   
   else if(response.hasOwnProperty('responseText'))        
   {
       console.log('Tiene responseText');
       var converted = JSON.parse(response.responseText);
       if(converted.hasOwnProperty('message'))
       {
           console.log('Tiene responseText message');
           return converted.message;
       }
       else
       {
           console.log('Tiene responseText sin message');
           return textoMax(JSON.stringify(converted), 150);
       }
   }
       
   var converted = JSON.parse(response);
   if(converted.hasOwnProperty('message'))
   {
       console.log('Tiene  message');
       return JSON.stringify(converted.message); 
   }   
   
   console.log('No tiene nada ');
   return textoMax(JSON.stringify(response), 150);          
   
}

function url_time(url) {
   var d = new Date();
   return url+'?t='+d.getTime();
}

function format_file(filename) {
    if(filename.indexOf('.') != -1)
        return filename.split('.').pop().toUpperCase();
    else
        return 'UNDEFINED';
}

function format_getSize(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}

function get_mesText(month) {
    var meses = ['','ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SETIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
    return meses[month];
}

function get_stars(rate) {
    var html_stars =
    '<div class="stars">'+
        '<div class="stars-rate '+(rate > 0 ? 'stars-rate-'+rate : '')+'"></div>'+
        '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-star-filled text-yellow" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z" stroke-width="0" fill="currentColor" /></svg>'+
        '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-star-filled text-yellow" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z" stroke-width="0" fill="currentColor" /></svg>'+
        '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-star-filled text-yellow" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z" stroke-width="0" fill="currentColor" /></svg>'+
        '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-star-filled text-yellow" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z" stroke-width="0" fill="currentColor" /></svg>'+
        '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-star-filled text-yellow" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.243 7.34l-6.38 .925l-.113 .023a1 1 0 0 0 -.44 1.684l4.622 4.499l-1.09 6.355l-.013 .11a1 1 0 0 0 1.464 .944l5.706 -3l5.693 3l.1 .046a1 1 0 0 0 1.352 -1.1l-1.091 -6.355l4.624 -4.5l.078 -.085a1 1 0 0 0 -.633 -1.62l-6.38 -.926l-2.852 -5.78a1 1 0 0 0 -1.794 0l-2.853 5.78z" stroke-width="0" fill="currentColor" /></svg>'+
    '</div>';
    return html_stars;
}

function get_ubicacion(departamento, provincia, distrito) {
    if(departamento) {
        if(provincia) {
            if(distrito) {
                return '<small class="lh-1 d-block text-muted"><span title="'+departamento.nombre+'">'+departamento.abreviatura+'</span> / <span title="'+provincia.nombre+'">'+provincia.abreviatura+'</span></small><div>'+distrito.nombre+'</div>';
            } else {
                return '<small class="lh-1 d-block text-muted" title="'+departamento.nombre+'">'+departamento.abreviatura+'</small><div>'+provincia.nombre+'</div>';
            }
        } else {
            return departamento.nombre;
        }
    } else {
        return '';
    }    
}

function get_idiomas(proveedor_idiomas) {
    var result = '';
    for (let i = 0; i < proveedor_idiomas.length; i++) {
        if(proveedor_idiomas[i].idioma) {
            result += (result == '' ? '':' / ')+'<span title="'+proveedor_idiomas[i].idioma.nombre+'">'+proveedor_idiomas[i].idioma.abreviatura+'</span>';
        }
    }
    return result;
}


function get_color_background(background)
{
    if(computeLuminence(background) > 0.179) {
        return '#000000';
    } else {
        return '#ffffff';
    }
}

function computeLuminence(backgroundcolor) {
    var colors = hexToRgb(backgroundcolor);
    
    var components = ['r', 'g', 'b'];
    for (var i in components) {
        var c = components[i];
        
        colors[c] = colors[c] / 255.0;

        if (colors[c] <= 0.03928) { 
            colors[c] = colors[c]/12.92;
        } else { 
            colors[c] = Math.pow (((colors[c] + 0.055) / 1.055), 2.4);
        }
    }
    
    var luminence = 0.2126 * colors.r + 0.7152 * colors.g + 0.0722 * colors.b;

    return luminence;
}

function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

//=============Esportar============//
function exportar(tipo_archivo) {
    if (tipo_archivo === 'excel') {
        tabla.button('.buttons-excel').trigger();
    } else if (tipo_archivo === 'pdf') {
        tabla.button('.buttons-pdf').trigger();
    }
}

/*----- */


$( document ).ready(function() {

   //forzar mayuscula
   $('.mayuscula').keyup(function(event){
       var start = this.selectionStart;
       var end = this.selectionEnd;
       this.value = this.value.toUpperCase();
       this.setSelectionRange(start, end);
   });


});
//=======================
// activa y descativa requiered
function requiredCampo(selector, activar = true){

    $(selector).each(function(){

        let grupo = $(this).closest('.form-group');

        if(activar){
            grupo.addClass('form-required');
        }else{
            grupo.removeClass('form-required');
        }

        // limpiar errores solo de ese campo
        $(this).removeClass('is-invalid');
        grupo.find('.invalid-feedback').remove();

    });
}
//mostrar elemento o ocultar
function mostrarElemento(selector, mostrar = true){

    $(selector).each(function(){

        if(mostrar){
            $(this).show();
        }else{
            $(this).hide();
        }

    });

}
