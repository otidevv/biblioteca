$(document).ready(function () {

    // ===============================
    // ABRIR MODAL NUEVO
    // ===============================
    $('#btnNuevoLibro').on('click', function () {

        $('#formLibro').trigger('reset');
        $('#id').val('');

        $('#libros').val(null).trigger('change');

        limpiarCamposLibro();

        $('#modalLibro').modal('show');
    });


    // ===============================
    // SELECT2 CON AJAX
    // ===============================
    $('#libros').select2({
        dropdownParent: $('#modalLibro'),
        placeholder: "Buscar libro...",
        width: '100%',
        language: 'es',
        allowClear: true,
        tags: true,

        createTag: function (params) {

            let term = $.trim(params.term);

            if (term === '') {
                return null;
            }
            return {
                id: 'nuevo:' + term,
                text: '➕ Agregar "' + term + '"',
                nuevo: true
            };
        },

        ajax: {
            url: '/api/inventario/libros',
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


    // ===============================
    // CUANDO SE SELECCIONA
    // ===============================
    $('#libros').on('select2:select', function (e) {

        let data = e.params.data;

        // 👉 SI ES NUEVO LIBRO
        if (data.nuevo) {

            let tituloNuevo = data.id.replace('nuevo:', '');

            habilitarModoNuevoLibro(tituloNuevo);
            return;
        }
        /*
            // 👉 SI ES LIBRO EXISTENTE
            $('#input_titulo')
                .val(data.text)
                .prop('disabled', true);

            $('#input_autor')
                .val(data.autor || '')
                .prop('disabled', true);

            $('#input_editorial')
                .val(data.editorial || '')
                .prop('disabled', true);

            if (data.imagen) {
                $('#preview_imagen')
                    .attr('src', data.imagen)
                    .show();
            } else {
                $('#preview_imagen')
                    .hide()
                    .attr('src','');
            }

            $('#input_imagen').addClass('d-none');
        */
        modoLibroExistente(data);

    });


    // ===============================
    // CUANDO LIMPIA SELECCIÓN
    // ===============================
    $('#libros').on('select2:clear', function () {
        limpiarCamposLibro();
    });

});


// ===================================
// FUNCIONES AUXILIARES
// ===================================
function modoLibroExistente(data) {

    // TÍTULO
    $('#input_titulo')
        .val(data.text)
        .prop('disabled', true);

    // ===============================
    // AUTORES (select2 múltiple)
    // ===============================
    if (data.autores && data.autores.length > 0) {

        let autoresSeleccionados = data.autores.map(a => ({
            id: a.id,
            text: a.nombre
        }));

        // cargar valores manualmente
        $('#input_autor')
            .empty()
            .select2({
                data: autoresSeleccionados
            })
            .val(autoresSeleccionados.map(a => a.id))
            .trigger('change')
            .prop('disabled', true);
    }

    // ===============================
    // EDITORIAL
    // ===============================
    if (data.editorial) {

        let editorialOption = new Option(
            data.editorial.nombre,
            data.editorial.id,
            true,
            true
        );

        $('#input_editorial')
            .append(editorialOption)
            .trigger('change')
            .prop('disabled', true);
    }

    // ===============================
    // IMAGEN
    // ===============================
    if (data.imagen) {
        $('#preview_imagen')
            .attr('src', data.imagen)
            .show();
    } else {
        $('#preview_imagen')
            .hide()
            .attr('src','');
    }

    $('#input_imagen').addClass('d-none');
}
function habilitarModoNuevoLibro(titulo) {

    $('#input_titulo')
        .val(titulo)
        .prop('disabled', false);

    $('#input_autor')
        .val(null)
        .prop('disabled', false)
        .trigger('change');

    $('#input_editorial')
        .val(null)
        .prop('disabled', false)
        .trigger('change');

    $('#preview_imagen')
        .hide()
        .attr('src','');

    $('#input_imagen')
        .removeClass('d-none');
}

function limpiarCamposLibro() {

    // TÍTULO
    $('#input_titulo')
        .val('')
        .prop('disabled', true);

    // =========================
    // AUTORES (select2 múltiple)
    // =========================
    $('#input_autor')
        .val(null)              // limpiar selección
        .trigger('change')      // actualizar select2
        .prop('disabled', true);

    // =========================
    // EDITORIAL (select2 simple)
    // =========================
    $('#input_editorial')
        .val(null)
        .trigger('change')
        .prop('disabled', true);

    // =========================
    // IMAGEN
    // =========================
    $('#preview_imagen')
        .hide()
        .attr('src', '');

    $('#input_imagen')
        .val('')               // limpiar archivo seleccionado
        .addClass('d-none');
}