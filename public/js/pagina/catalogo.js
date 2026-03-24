$(document).ready(function () {
    $('#autor_id').select2({
        placeholder: 'Seleccione autores',
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
    $('#idioma_id').select2({
        placeholder: 'Seleccione idioma',
        allowClear: true,
        ajax: {
            url: '/pagina/idiomas',
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

    $('#materia_id').select2({
        placeholder: 'Seleccione materia',
        allowClear: true,
        ajax: {
            url: '/pagina/materias',
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
});