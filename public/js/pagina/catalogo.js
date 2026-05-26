$(document).ready(function () {
    const $form = $('#catalogoFiltrosForm');
    const $contenedor = $('#contenedor-libros');
    let requestActiva = null;
    let debounceTimer = null;

    function contextoActual() {
        return {
            titulo:     $('#titulo').val().trim(),
            codigo_ant: $('#codigo_ant').val().trim(),
        };
    }

    function inicializarSelect2() {
        $('#autor_id').select2({
            placeholder: 'Seleccione autores',
            allowClear: true,
            ajax: {
                url: '/pagina/autores',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return Object.assign({ q: params.term }, contextoActual());
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: false
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
                    return Object.assign({ q: params.term }, contextoActual());
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: false
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
                    return Object.assign({ q: params.term }, contextoActual());
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: false
            }
        });
    }

    // ── Tabs ──
    $('.catalog-tab-btn').on('click', function () {
        const tab = $(this).data('tab');
        $('.catalog-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.catalog-tab-panel').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });

    // ── Badge: autor activo en Tab 2 ──
    function actualizarAutorBadge() {
        const val = $('#autor_id').val();
        if (val) {
            $('#autorActiveName').text($('#autor_id option:selected').text());
            $('#autorActiveBadge').addClass('visible');
        } else {
            $('#autorActiveBadge').removeClass('visible');
        }
    }

    function limpiarSelects() {
        ['#autor_id', '#idioma_id', '#materia_id'].forEach(function (sel) {
            $(sel).val(null).trigger('change');
        });
        $('#autorAlphaBtns .autor-alpha-btn').removeClass('active');
        $('#autorAlphaResults').empty().removeClass('visible');
        $('#autorActiveBadge').removeClass('visible');
    }

    // ── Letter picker ──
    $('#autorAlphaBtns').on('click', '.autor-alpha-btn', function () {
        const $btn  = $(this);
        const letra = $btn.data('letra');

        if ($btn.hasClass('active')) {
            $btn.removeClass('active');
            $('#autorAlphaResults').empty().removeClass('visible');
            return;
        }

        $('#autorAlphaBtns .autor-alpha-btn').removeClass('active');
        $btn.addClass('active');

        const params = Object.assign({ letra: letra }, contextoActual());

        $.get('/pagina/autores', params, function (data) {
            const $results = $('#autorAlphaResults');
            $results.empty();

            if (!data.length) {
                $results.append('<span class="autor-alpha-empty">Sin autores para esta letra</span>');
            } else {
                data.forEach(function (autor) {
                    $('<button type="button" class="autor-alpha-chip"></button>')
                        .text(autor.text)
                        .data('autor-id', autor.id)
                        .appendTo($results);
                });
            }

            $results.addClass('visible');
        });
    });

    $('#autorAlphaResults').on('click', '.autor-alpha-chip', function () {
        const id   = $(this).data('autor-id');
        const text = $(this).text();

        $('#autorAlphaResults .autor-alpha-chip').removeClass('selected');
        $(this).addClass('selected');

        const option = new Option(text, id, true, true);
        $('#autor_id').append(option).trigger('change');

        actualizarAutorBadge();

        clearTimeout(debounceTimer);
        const url = $form.attr('action') + '?' + buildParams();
        cargarLibros(url);
    });

    $('#autorActiveClear').on('click', function () {
        $('#autor_id').val(null).trigger('change');
        $('#autorAlphaBtns .autor-alpha-btn').removeClass('active');
        $('#autorAlphaResults').empty().removeClass('visible');
        $('#autorActiveBadge').removeClass('visible');

        clearTimeout(debounceTimer);
        const url = $form.attr('action') + '?' + buildParams();
        cargarLibros(url);
    });

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

                // Actualizar contador de resultados
                const total = $contenedor.find('#libros-total-meta').data('total');
                if (total !== undefined) {
                    $('#catalogResultsCount').text(total);
                    $('#catalogResultsSuffix').text(' resultado' + (total !== 1 ? 's' : ''));
                }
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

        const codigoAnt = $('#codigo_ant').val().trim();
        if (codigoAnt) params.set('codigo_ant', codigoAnt);

        const perPage = $('#perPage').val();
        if (perPage && perPage !== '8') params.set('per_page', perPage);

        return params.toString();
    }

    inicializarSelect2();
    actualizarAutorBadge();

    $('#titulo').on('input', function () {
        clearTimeout(debounceTimer);
        const valor = $(this).val().trim();

        if (valor.length === 0 || valor.length >= 2) {
            debounceTimer = setTimeout(function () {
                limpiarSelects();
                const url = $form.attr('action') + '?' + buildParams();
                cargarLibros(url);
            }, 500);
        }
    });

    $('#codigo_ant').on('input', function () {
        clearTimeout(debounceTimer);
        const valor = $(this).val().trim();

        if (valor.length === 0 || valor.length >= 2) {
            debounceTimer = setTimeout(function () {
                limpiarSelects();
                const url = $form.attr('action') + '?' + buildParams();
                cargarLibros(url);
            }, 500);
        }
    });

    $('#perPage').on('change', function () {
        clearTimeout(debounceTimer);
        const url = $form.attr('action') + '?' + buildParams();
        cargarLibros(url);
    });

    $('#autor_id').on('select2:select select2:clear', function () {
        clearTimeout(debounceTimer);
        actualizarAutorBadge();
        const url = $form.attr('action') + '?' + buildParams();
        cargarLibros(url);
    });

    $('#idioma_id, #materia_id').on('select2:select select2:clear', function () {
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
