$(document).ready(function () {
    $('.select2-autor').select2({
        placeholder: 'Buscar autor...',
        allowClear: true,
        ajax: {
            url: '/pagina/autores',
            dataType: 'json',
            delay: 250,
            data: function(params){
                return {
                    q: params.term
                };
            },
            processResults: function(data){
                return {
                    results: data
                };
            }
        }
    });

    $('.select2-idiomas').select2({
        placeholder: 'Buscar idima...',
        allowClear: true,
        ajax: {
            url: '/pagina/idiomas',
            dataType: 'json',
            delay: 250,
            data: function(params){
                return {
                    q: params.term
                };
            },
            processResults: function(data){
                return {
                    results: data
                };
            }
        }
    });
    $('.select2-materias').select2({
        placeholder: 'Buscar materia...',
        allowClear: true,
        ajax: {
            url: '/pagina/materias',
            dataType: 'json',
            delay: 250,
            data: function(params){
                return {
                    q: params.term
                };
            },
            processResults: function(data){
                return {
                    results: data
                };
            }
        }
    });
document.addEventListener("DOMContentLoaded", function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});

});
$(document).ready(function(){

    // 🔍 BUSCAR SIN RECARGAR
    $('form').on('submit', function(e){
        e.preventDefault();

        let url = $(this).attr('action') + '?' + $(this).serialize();

        cargarLibros(url);
    });

    // 📄 PAGINACIÓN SIN RECARGAR
    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();

        let url = $(this).attr('href');

        cargarLibros(url);
    });

    function cargarLibros(url){
        $('#contenedor-libros').html('<div class="text-center p-4">Cargando...</div>');

        $.get(url, function(data){
            $('#contenedor-libros').html(data);
        });
    }

});