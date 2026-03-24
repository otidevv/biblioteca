$(document).ready(function () {
    $('#select2-autor').select2({
        placeholder: 'Seleccione código Dewey',
        allowClear: true,
        ajax: {
            url: '/pagina/autores',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term }; 
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
    $('.select2-idiomas').select2({
        dropdownParent: $('.card'),
        placeholder: 'Buscar idioma...',
        allowClear: true,
        ajax: {
            url: '/pagina/idiomas',
            dataType: 'json',
            delay: 250,
            data: function(params){
                return { q: params.term };
            },
            processResults: function(data){
                return { results: data };
            }
        }
    });

    $('.select2-materias').select2({
        dropdownParent: $('.card'),
        placeholder: 'Buscar materia...',
        allowClear: true,
        ajax: {
            url: '/pagina/materias',
            dataType: 'json',
            delay: 250,
            data: function(params){
                return { q: params.term };
            },
            processResults: function(data){
                return { results: data };
            }
        }
    });

});