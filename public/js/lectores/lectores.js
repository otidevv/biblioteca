let tabla;
let dniConsultado = null;

$(document).ready(function () {
    const $form = $('#formLector');
    const $modal = $('#modalLector');

    tabla = $('#tabla-lectores').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        order: [],
        ajax: {
            url: '/api/usuarios/lectores/listar',
            type: 'GET',
            xhrFields: { withCredentials: true },
            error: default_error_handler
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'persona.dni', name: 'persona.dni' },
            { data: 'email', name: 'email' },
            {
                data: 'persona.tipo_persona',
                name: 'persona.tipo_persona',
                render: function (data) {
                    return data || 'Sin tipo';
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
        }
    });

    $('#btnNuevo').on('click', function () {
        resetFormularioLector();
        habilitarModoNuevo();
        $modal.modal('show');
    });

    $('#tabla-lectores').on('click', '.editarLector', function () {
        const data = tabla.row($(this).closest('tr')).data();

        resetFormularioLector();

        $('#id').val(data.id || '');
        $('#persona_id').val(data.persona?.id || '');
        $('#tipo_persona').val(data.persona?.tipo_persona || '0');
        $('#dni').val(data.persona?.dni || '');
        $('#sexo').val(data.persona?.sexo || '0');
        $('#nombres').val(data.persona?.nombres || '');
        $('#apellido_paterno').val(data.persona?.apellido_paterno || '');
        $('#apellido_materno').val(data.persona?.apellido_materno || '');
        $('#codigo_institucional').val(data.persona?.codigo_institucional || '');
        $('#carrera_id').val(data.persona?.carrera_id || '0');
        $('#estado_academico').val(data.persona?.estado_academico || '0');
        $('#telefono').val(data.persona?.telefono || '');
        $('#email_personal').val(data.persona?.email_personal || data.email || '');
        $('#direccion').val(data.persona?.direccion || '');
        $('#email').val(data.email || '');

        dniConsultado = data.persona?.dni || null;
        bloquearCamposPersona(true);
        habilitarModoEdicion();
        toggleCamposEstudiante();
        $modal.modal('show');
    });

    $form.on('submit', function (e) {
        e.preventDefault();

        if (!validar('#formLector')) {
            return;
        }

        const formData = new FormData(this);
        const btn = $form.find('button[type="submit"]');

        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: $('#id').val() === '' ? '/api/usuarios/lectores/nuevo' : '/api/usuarios/lectores/edit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (!response.success) {
                    alerta(response.message || 'Error al guardar el lector', false);
                    return;
                }

                alerta('Lector guardado correctamente', true);
                $modal.modal('hide');
                tabla.ajax.reload();
            },
            error: function (xhr) {
                $('.is-invalid').removeClass('is-invalid');

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    Object.entries(xhr.responseJSON.errors).forEach(function ([field, messages]) {
                        const input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        alerta(messages[0], false);
                    });
                    return;
                }

                alerta(xhr.responseJSON?.message || 'Error al guardar el lector', false);
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i><span>Guardar</span>');
            }
        });
    });

    $('#dni').on('keypress', function (e) {
        if (e.which === 13) {
            $('#btnBuscarDni').click();
        }
    });

    $('#dni').on('blur', function () {
        const dni = $(this).val().trim();
        const btn = $('#btnBuscarDni');

        if (dni.length !== 8 || dni === dniConsultado) {
            return;
        }

        dniConsultado = dni;
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        buscarDNIapi(dni, btn);
    });

    $('#dni').on('input', function () {
        limpiarCamposPersona();
        const dni = $(this).val().trim();
        const btn = $('#btnBuscarDni');

        if (!/^\d*$/.test(dni)) {
            $(this).val(dni.replace(/\D/g, ''));
            return;
        }

        if (dni.length < 8) {
            dniConsultado = null;
            bloquearCamposPersona(false);
            btn.prop('disabled', false).html('<i class="bi bi-search"></i>');
        }
    });

    $('#btnBuscarDni').on('click', function () {
        limpiarCamposPersona();
        const btn = $(this);
        const dni = $('#dni').val().trim();

        if (dni.length !== 8) {
            alerta('Ingrese un DNI valido', false);
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        buscarDNIapi(dni, btn);
    });

    $('#tipo_persona, #dni').on('change', function () {
        bloquearCamposPersona(false);
    });

    $('#tipo_persona').on('change', function () {
        toggleCamposEstudiante();
    });

    $modal.on('shown.bs.modal', function () {
        toggleCamposEstudiante();
    });

    $modal.on('hidden.bs.modal', function () {
        resetFormularioLector();
    });

    $('#email_personal').on('input blur', function () {
        $('#email').val($(this).val().trim());
    });
});

function buscarDNIapi(dni, btn) {
    if (!validar_select_id('tipo_persona')) {
        btn.prop('disabled', false).html('<i class="bi bi-search"></i>');
        return;
    }

    $.ajax({
        url: '/api/externo/buscar-dni',
        type: 'GET',
        data: { nro_documento: dni, tipo_usuario: $('#tipo_persona').val() },
        success: function (res) {
            $('#nombres').val(res.respuesta.nombre ?? '');
            $('#apellido_paterno').val(res.respuesta.apaterno ?? '');
            $('#apellido_materno').val(res.respuesta.amaterno ?? '');
            $('#email_personal').val(res.respuesta.correo ?? '');
            $('#email').val(res.respuesta.correo ?? '');
            $('#codigo_institucional').val(res.respuesta.codigo ?? '');
            bloquearCamposPersona(true);
            alerta('Datos encontrados', true);
        },
        error: function () {
            limpiarCamposPersona();
            alerta('No se encontro el DNI', false);
        },
        complete: function () {
            btn.prop('disabled', false).html('<i class="bi bi-search"></i>');
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
    const $grupoCodigo = $('#grupoCodigoInstitucional');
    const $grupoCarrera = $('#grupoCarrera');
    const $grupoEstado = $('#grupoEstadoAcademico');

    if (tipo === 'ESTUDIANTE') {
        $grupoCodigo.addClass('form-required');
        $grupoCarrera.addClass('form-required');
        $grupoEstado.addClass('form-required');
        $('#codigo_institucional, #carrera_id, #estado_academico').prop('disabled', false);
        $('#bloqueEstudiante').slideDown();
        return;
    }

    $grupoCodigo.removeClass('form-required');
    $grupoCarrera.removeClass('form-required');
    $grupoEstado.removeClass('form-required');
    $('#codigo_institucional, #carrera_id, #estado_academico').prop('disabled', true);
    $('#bloqueEstudiante').slideUp();
    $('#codigo_institucional').val('');
    $('#carrera_id').val('0');
    $('#estado_academico').val('0');
}

function resetFormularioLector() {
    const form = $('#formLector')[0];

    if (form) {
        form.reset();
    }

    $('#id').val('');
    $('#persona_id').val('');
    $('#email').val('');
    $('#carrera_id').val('0');
    $('#estado_academico').val('0');
    $('#tipo_persona').val('0');
    $('#sexo').val('0');
    dniConsultado = null;
    bloquearCamposPersona(false);
    $('.is-invalid').removeClass('is-invalid');
    $('#formLector .invalid-feedback').remove();
}

function habilitarModoNuevo() {
    $('.password-group').show().addClass('form-required');
    $('#password, #password_confirmation').val('');
}

function habilitarModoEdicion() {
    $('.password-group').hide().removeClass('form-required');
    $('#password, #password_confirmation').val('');
}
