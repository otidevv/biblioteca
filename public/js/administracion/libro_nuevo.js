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
                    text: item.nombre
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
    $('#autor_id').on('change', function () {

        let selectedData = $(this).select2('data');

        if (selectedData.length === 0) {
            $('input[name="codigo"]').val('');
            return;
        }

        let autor = selectedData[0];

        if(!autor) return;

        let apellido = (autor.apellido || '').toUpperCase();
        let nombre = (autor.nombre || '').toUpperCase();
        let titulo = ($('#titulo').val() || '').toUpperCase();

        if(!apellido || !nombre){
            console.warn('Autor sin apellido o nombre');
            return;
        }

        let primeraLetra = apellido.charAt(0);
        let letrasBusqueda = 3;

        generarCodigo();

        function generarCodigo(){

            let letrasApellido = apellido.substring(0, letrasBusqueda);

            $.ajax({
                url:'/api/inventario/codigo_cutter',
                type:'GET',
                data:{ letras: letrasApellido },
                success:function(cutterData){

                    let cutter = cutterData.codigo;
                    let baseCodigo = primeraLetra + cutter;

                    $.ajax({
                        url:'/api/inventario/libros/check_codigo',
                        type:'GET',
                        data:{ codigo: baseCodigo },
                        success:function(res){

                            if(!res.existe){
                                $('input[name="codigo"]').val(baseCodigo);
                                return;
                            }

                            // MISMO AUTOR
                            console.log(res.autor_id+" = "+autor.id);
                            
                            if(res.autor_id == autor.id){
                                let letraTitulo = titulo.charAt(0);
                                $('input[name="codigo"]').val(baseCodigo + letraTitulo);
                                return;
                            }

                            // MISMO APELLIDO DIFERENTE AUTOR
                            if(res.apellido === apellido){

                                let letraNombre = nombre.charAt(0);
                                $('input[name="codigo"]').val(baseCodigo + letraNombre);
                                return;
                            }

                            // MUCHOS AUTORES MISMAS 3 LETRAS
                            letrasBusqueda++;

                            if(letrasBusqueda <= apellido.length){
                                generarCodigo();
                            }else{
                                $('input[name="codigo"]').val(baseCodigo);
                            }

                        }
                    });

                }
            });

        }

    });
    // Vista previa imagen
    $('#imagen').on('change', function(e){

        let file = e.target.files[0];

        if(file){

            let reader = new FileReader();

            reader.onload = function(e){
                $('#previewImagen').attr('src', e.target.result);
            }

            reader.readAsDataURL(file);
        }

    });

    // mostrar nombre PDF
    $('#archivo_indice').on('change', function(){

        let file = this.files[0];

        if(file){
            $('#nombrePdf').text(file.name);
        }

    });
    //mandar datos del libro
    $('#formLibro').submit(function(e){

    e.preventDefault();

    let formData = new FormData(this);

    $.ajax({

        url: "/api/inventario/libros/guardar",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        success: function(resp){

            alert("Libro guardado correctamente");

            $('#formLibro')[0].reset();
            $('#previewImagen').attr('src','https://via.placeholder.com/150x200?text=Sin+imagen');

        },

        error: function(xhr){

            alert("Error al guardar");

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
