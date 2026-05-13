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

    inicializarSelect2();

    $('#titulo').on('input', function () {
        clearTimeout(debounceTimer);
        const valor = $(this).val().trim();

        if (valor.length === 0 || valor.length >= 2) {
            debounceTimer = setTimeout(function () {
                const url = $form.attr('action') + '?' + $form.serialize();
                cargarLibros(url);
            }, 500);
        }
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        const url = $(this).attr('action') + '?' + $(this).serialize();
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
