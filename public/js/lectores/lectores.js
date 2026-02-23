let tabla;
let dniConsultado = null;
$(document).ready(function () {
    tabla = $('#tabla-lectores').DataTable({        
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url:  "/api/usuarios/lectores/listar",
            type: "GET",
            xhrFields: { withCredentials: true },
            data: function (d) {
                // si necesitas enviar parámetros extra
            },
            error: default_error_handler        
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'persona.dni', name: 'persona.dni' },
            { data: 'email', name: 'email' },
            { data: 'persona.tipo_persona', name: 'persona.tipo_persona'
                    , render: function (data, type, row) {
                        if (data === 'ESTUDIANTE') return 'ESTUDIANTE';
                        if (data === 'DOCENTE') return 'DOCENTE';
                        if (data === 'ADMINISTRATIVO') return 'ADMINISTRATIVO';
                        if (data === 'EXTERNO') return 'EXTERNO';
                        return 'Otro';
                    }
             },
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

    // ABRE MODAL DE NUEVO LECTOR
    $('#btnNuevo').on('click', function () {
        $('#formLector')[0].reset();
        $('input[name="roles[]"]').prop('checked', false);
        $('#id').val('');
        $('#div_credenciales').show();
        $('.password-group').show();
        $('#modalLector').modal('show');
    });

    // ABRE MODAL DE EDICIÓN
    $('#tabla-lectores').on('click', '.editarLector', function () {
        let data = tabla.row($(this).closest('tr')).data();
        console.log(data);
        
        $('input[name="roles[]"]').prop('checked', false);
        $('#dni').val(data.persona.dni);
        $('#nombres').val(data.persona.nombres);
        $('#apellido_paterno').val(data.persona.apellido_paterno);
        $('#apellido_materno').val(data.persona.apellido_materno);
        $('#sexo').val(data.persona.sexo);
        $('#telefono').val(data.persona.telefono);
        $('#id').val(data.id);
        $('#biblioteca').val(data.id);
        $('#sexo').val(data.persona.sexo ?? '');
        $('#direccion').val(data.persona.direccion ?? '');
        $('#email').val(data.email);
        // 🔥 MARCAR roles del usuario
            if (data.roles && Array.isArray(data.roles)) {
                data.roles.forEach(function (rol) {
                    $('#rol_' + rol.id).prop('checked', true);
                });
            }

        $('#div_credenciales').hide();
        $('#modalLector').modal('show');
    });
    // GUARDAR Y ACTUALIZAR LECTOR
    $('#formLector').on('submit', function (e) {
        e.preventDefault();
        if (!validar('#div_form')) return;
        console.log("entro");
        

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({        
            url:$('#id').val()=='' ? '/api/usuarios/lectores/nuevo' : '/api/usuarios/lectores/edit',
            type:'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
                if (response.success) {
                    alerta("Usuario guardado correctamente", true);
                    // Reset form
                    form[0].reset();
                    // Cerrar modal
                    $('#modalUsuario').modal('hide');
                    // Recargar tabla (si usas DataTable)
                    tabla.ajax.reload();
                } else {
                    alerta(response.message??'Error al guardar el usuario', false);
                }
            },
            error: function (xhr) {
                // Limpiar errores previos
                $('.is-invalid').removeClass('is-invalid');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (field, messages) {
                        let input = $('[name="' + field + '"]');
                        // Campos array (roles[])
                        if (field.includes('.')) {
                            input = $('[name="' + field.split('.')[0] + '[]"]');
                        }
                        input.addClass('is-invalid');
                        alerta(messages[0], false);
                    });
                } else {
                    alerta(xhr.responseJSON.message??'Error al guardar el usuario', false);
                    //toastr.error('Error interno del servidor');
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });
    //BUSCAR POR DNI AL REGISTRAR NUEVO LECTOR SEGUN TIPO DE USUARIO/LECTOR
    $('#dni').on('keypress', function (e) {
        if (e.which === 13) {
            $('#btnBuscarDni').click();
        }
    });
    $('#dni').on('blur', function () {
        const dni = $(this).val().trim();
        const btn = $('#btnBuscarDni');

        //  Si no tiene 8 dígitos, no busca
        if (dni.length !== 8) return;

        //  Si ya fue consultado, no repetir
        if (dni === dniConsultado) return;

        dniConsultado = dni;

        //  Spinner ON
        btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm"></span>'
        );

        buscarDNIapi(dni, btn);
    });
    $('#dni').on('input', function () {
        limpiarCamposPersona();
        const dni = $(this).val().trim();
        const btn = $('#btnBuscarDni');

        // Solo números
        if (!/^\d*$/.test(dni)) {
            $(this).val(dni.replace(/\D/g, ''));
            return;
        }

        //  Si borra el DNI → resetear estado
        if (dni.length < 8) {
            dniConsultado = null;
            bloquearCamposPersona(false);
            btn.prop('disabled', false).html('<i class="bi bi-search"></i>');
            return;
        }

        //  Buscar SOLO si:
        // - tiene 8 dígitos
        // - no fue consultado antes
        // - botón no está deshabilitado
        if (dni.length === 8 && dni !== dniConsultado && !btn.prop('disabled')) {

            dniConsultado = dni;

            btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span>'
            );

            buscarDNIapi(dni, btn);
        }
    });


    $('#btnBuscarDni').on('click', function () {
        limpiarCamposPersona();
        const btn = $(this);
        const dni = $('#dni').val().trim();

        if (dni.length !== 8) {
            alerta('Ingrese un DNI válido', false);
            return;
        }

        // 🔄 Spinner ON
        btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm"></span>'
        );

        buscarDNIapi(dni, btn);
    });
    //DESBLOQUEAR CAMPOS NOMBRE, APELLIDO PATERNO Y APELLIDO MATERNO SI SE CAMBIA EL TIPO DE USUARIO O EL DNI
    $('#tipo_persona, #dni').on('change', function () {
        bloquearCamposPersona(false);
    });
    // TOGGLE CAMPOS DE ESTUDIANTE SI SE SELECCIONA TIPO DE USUARIO ESTUDIANTE
    $('#tipo_persona').on('change', function () {
        console.log("actualiza");
        
        toggleCamposEstudiante();   
    });
    $('#modalLector').on('shown.bs.modal', function () {
        toggleCamposEstudiante();
    });
    //ACTUALIZA EL USUARIO SEGUN EL CORREO INGRESADO
    $('#email_personal').on('input blur', function () {
        $('#email').val($(this).val().trim());
    });


});
function buscarDNIapi(dni, btn) {
    if(!validar_select_id('tipo_persona')) {
        btn.prop('disabled', false).html('<i class="bi bi-search"></i>');
        return;
    }
    $.ajax({
        url: '/api/externo/buscar-dni',
        type: 'GET',
        data: { nro_documento: dni, tipo_usuario: $('#tipo_persona').val() },

        success: function (res) {
            console.log(res.respuesta.codigo+ ' -- '+res.respuesta.correo);
            
            $('#nombres').val(res.respuesta.nombre ?? '');
            $('#apellido_paterno').val(res.respuesta.apaterno ?? '');
            $('#apellido_materno').val(res.respuesta.amaterno ?? '');
            $('#email_personal').val(res.respuesta.correo ?? '');
            $('#email').val(res.respuesta.correo ?? '');
            $('#codigo_institucional').val(res.respuesta.codigo ?? '');
            // 🔒 Bloquear campos
            bloquearCamposPersona(true);

            alerta('Datos encontrados', true);
        },

        error: function () {
            limpiarCamposPersona();
            alerta('No se encontró el DNI', false);
        },

        complete: function () {
            //  Spinner OFF (SIEMPRE)
            btn.prop('disabled', false).html(
                '<i class="bi bi-search"></i>'
            );
        }
    });
}

function bloquearCamposPersona(bloquear = true) {
    $('#nombres').prop('readonly', bloquear);
    $('#apellido_paterno').prop('readonly', bloquear);
    $('#apellido_materno').prop('readonly', bloquear);
}
function limpiarCamposPersona() {
    $('#nombres').val('');
    $('#apellido_paterno').val('');
    $('#apellido_materno').val('');
}
function toggleCamposEstudiante() {

    const tipo = $('#tipo_persona').val();

    if (tipo === 'ESTUDIANTE') {
        $('#bloqueEstudiante').slideDown(); 
    } else {
        // Ocultar
        $('#bloqueEstudiante').slideUp();

        // Limpiar valores
        $('#codigo_institucional').val('');
        $('#carrera_id').val('');
        $('#estado_academico').val('');
    }
}

