$(document).ready(function () {
    // ================= EDITORIAL =================
    $('#editorial_id').select2({
        placeholder: "Seleccione editorial",
        width: '100%',
        ajax: {
            url: '/api/inventario/editoriales',
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

    // ================= AUTORES =================
    $('#autores').select2({
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
});

