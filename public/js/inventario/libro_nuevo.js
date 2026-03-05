$(document).ready(function () {
    $('.select2').select2({
        width: '100%'
    });
    // ================= EDITORIAL =================
    $('#editorial_id').select2({
        placeholder: "Buscar editorial",
        allowClear: true,
        ajax: {
            url: '/api/inventario/editoriales',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.nombre
                    }))
                };
            }
        }
    });

    // ================= AUTORES =================
    $('#autor_id').select2({
        placeholder: "Seleccione autor(es)",
        width: '100%',
        multiple: true,
        ajax: {
            url: '/api/inventario/autores',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.map(item => ({
                    id: item.id,
                    text: item.text
                }))
            })
        }
    });

    // ================= MATERIAS =================
    $('#materias').select2({
        placeholder: "Seleccione materia(s)",
        width: '100%',
        multiple: true,
        ajax: {
            url: '/api/inventario/materias',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.map(item => ({
                    id: item.id,
                    text: item.text
                }))
            })
        }
    });
    
    $('#formEditorial').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(this);

        // Botón loading
        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Guardando...');
        if(!validar('#formEditorial')) {
            btn.prop('disabled', false).text('Guardar');
            return;
        }

        $.ajax({
            url:'/api/editoriales/nuevo',
            type:'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
                if (response.success) {
                    alerta("Editorial guardado correctamente", true);
                    // Reset form
                    form[0].reset();
                    // Cerrar modal
                    $('#modalEditorial').modal('hide');
                    btn.prop('disabled', false).text('Guardar');

                } else {
                    alerta(response.message??'Error al guardar el editorial', false);
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
                    alerta(xhr.responseJSON.message??'Error al guardar el editorial', false);
                    //toastr.error('Error interno del servidor');
                }
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });
});

$('#btnNuevaEditorial').click(function(){
    $('#modalEditorial').modal('show');
});
$('#btnNuevaEditoria2').click(function(){
    console.log("dd");
    
    $('#modalEditorial').modal('show');
});
