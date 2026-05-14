$(document).ready(function () {
    const $form = $('#catalogoFiltrosForm');
    const $contenedor = $('#contenedor-libros');
    let requestActiva = null;
    let debounceTimer = null;

    function inicializarSelect2() {
        $('#autor_id').select2({
            placeholder: 'Seleccione autores',
            allowClear: true,
            ajax: {
                url: '/pagina/autores',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
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
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
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
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });
    }

    function cargarLibros(url) {
        if (!$contenedor.length) {
            return;
        }

        if (requestActiva && requestActiva.readyState !== 4) {
            requestActiva.abort();
        }

        $contenedor.css('opacity', '0.5');
        $('#catalog-search-icon').removeClass('bi-search').addClass('bi-arrow-repeat spin-icon');

        requestActiva = $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .done(function (html) {
                $contenedor.html(html);
                window.history.replaceState({}, '', url);
            })
            .fail(function () {
                if (typeof alerta === 'function') {
                    alerta('No se pudo actualizar el catalogo en este momento.', false);
                }
            })
            .always(function () {
                $contenedor.css('opacity', '1');
                $('#catalog-search-icon').removeClass('bi-arrow-repeat spin-icon').addClass('bi-search');
                requestActiva = null;
            });
    }

    // Construye los parámetros manualmente para evitar problemas con
    // el <select> oculto que Select2 reemplaza al serializar el form.
    function buildParams() {
        const params = new URLSearchParams();
        const titulo = $('#titulo').val().trim();
        if (titulo) params.set('titulo', titulo);

        const autorId = $('#autor_id').val();
        if (autorId) params.set('autor_id', autorId);

        const idiomaId = $('#idioma_id').val();
        if (idiomaId) params.set('idioma_id', idiomaId);

        const materiaId = $('#materia_id').val();
        if (materiaId) params.set('materia_id', materiaId);

        return params.toString();
    }

    inicializarSelect2();

    $('#titulo').on('input', function () {
        clearTimeout(debounceTimer);
        const valor = $(this).val().trim();

        if (valor.length === 0 || valor.length >= 2) {
            debounceTimer = setTimeout(function () {
                const url = $form.attr('action') + '?' + buildParams();
                cargarLibros(url);
            }, 500);
        }
    });

    // select2:select fires after the value is set; select2:clear fires after clearing with X
    $('#autor_id, #idioma_id, #materia_id').on('select2:select select2:clear', function () {
        clearTimeout(debounceTimer);
        const url = $form.attr('action') + '?' + buildParams();
        cargarLibros(url);
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        const url = $(this).attr('action') + '?' + buildParams();
        cargarLibros(url);
    });

    $(document).on('click', '#contenedor-libros .pagination a', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');

        if (url) {
            cargarLibros(url);
        }
    });
});
