$(document).ready(function () {
    $('.select2').select2({
        width: '100%'
    });
    $('#modalEditorial').on('shown.bs.modal', function () {

        $('#ed_pais').select2({
            dropdownParent: $('#modalEditorial'),
            width: '100%'
        });

    });
    $('#modalAutor').on('shown.bs.modal', function () {

        $('#au_pais').select2({
            dropdownParent: $('#modalAutor'),
            width: '100%'
        });

    });
    // Inicializamos Select2 con búsqueda manual
    $('#codigo_dewey').select2({
        placeholder: 'Seleccione código Dewey',
        allowClear: true,
        ajax: {
            url: '/api/inventario/dewey/buscar',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                let titulo = $('input[name="titulo"]').val().trim(); // Toma el título si existe
                return { q: params.term || titulo }; 
            },
            processResults: function(data) {
                return {
                    results: data.map(item => ({ id: item.id, text: item.codigo + ' - ' + item.nombre }))
                };
            },
            cache: true
        }
    });
    // Evento para buscar Dewey según el título
    $('input[name="titulo"]').on('blur', function() {
        let titulo = $(this).val().trim();
        if(titulo.length > 0){
            $.ajax({
                url: '/api/inventario/dewey/buscar',
                type: 'GET',
                data: { q: titulo },
                success: function(response){
                    // Limpiamos el select
                    $('#codigo_dewey').empty().trigger('change');

                    if(response.length > 0){
                        // Agregamos todas las coincidencias
                        response.forEach(function(item){
                            let newOption = new Option(item.codigo + ' - ' + item.nombre, item.id, false, false);
                            $('#codigo_dewey').append(newOption);
                        });
                        // Abrimos el dropdown para que el usuario vea las opciones
                        $('#codigo_dewey').select2('open');
                    }
                },
                error: function(err){
                    console.error('Error al buscar Dewey', err);
                }
            });
        }
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
        ajax: {
            url: '/api/inventario/autores',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.map(item => ({
                    id: item.id,
                    text: item.text,
                    apellido: item.apellidos, // importante: traer apellido desde backend
                    nombre: item.nombres    // importante: traer nombre desde backend
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
    // generar codigo    
    // Evento para generar código automáticamente al seleccionar autores
    $('#autor_id').on('change', function() {
        
        let selectedData = $(this).select2('data'); // array de autores seleccionados
        if (selectedData.length === 0) {
            $('input[name="codigo"]').val('');
            return;
        }

        // Tomamos el primer autor para generar el código
        let autor = selectedData[0];
        let apellido = autor.apellido;
        let nombre = autor.nombre;
        console.log(autor);

        if (!apellido || !nombre) return;

        let primeraLetra = apellido.charAt(0).toUpperCase();
        let tresLetras = apellido.substring(0,3).toUpperCase();

        // Consultamos la tabla de códigos Cutter
        $.ajax({
            url: '/api/inventario/codigo_cutter',
            type: 'GET',
            data: { letras: tresLetras },
            success: function(cutterData) {
                let codigoCutter = cutterData.codigo; // ejemplo: "LOP"

                // Verificamos si ya existe un código para este apellido
                $.ajax({
                    url: '/api/inventario/libros/check_codigo',
                    type: 'GET',
                    data: { apellido: apellido, cutter: codigoCutter },
                    success: function(res) {
                        let letraNombre = '';
                        if (res.existe) {
                            letraNombre = nombre.charAt(0).toUpperCase();
                        }

                        // Generamos código final
                        let codigoFinal = primeraLetra + codigoCutter + letraNombre;

                        // Actualizamos el input
                        $('input[name="codigo"]').val(codigoFinal);
                    }
                });
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
