$(document).ready(function () {

    // ================= AUTORES =================
    $('#autor_id').select2({
        placeholder: "Seleccione autor(es)",
        width: '100%',
        ajax: {
            url: '/pagina/autores',
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
    $('#materia_id').select2({
        placeholder: "Seleccione materia(s)",
        width: '100%',
        ajax: {
            url: '/pagina/materias',
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
    $('#registro_id').select2({
        placeholder: "Seleccione materia(s)",
        width: '100%',
        ajax: {
            url: '/pagina/registros',
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
    $('#idioma_id').select2({
        placeholder: "Seleccione materia",
        width: '100%',
        ajax: {
            url: '/pagina/idiomas',
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
// Función para cargar libros con filtros
function cargarLibros(url) {
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            document.querySelector('#libros-container').innerHTML = html;
        });
}

// Construir URL con filtros
function buildUrl(baseUrl = "/pagina") {
    let params = new URLSearchParams();
    params.append('search', document.getElementById('search').value);
    params.append('registro_id', $('#registro_id').val() || '');
    params.append('idioma_id', $('#idioma_id').val() || '');
    params.append('autor_id', $('#autor_id').val() || '');
    params.append('materia_id', $('#materia_id').val() || '');
    return baseUrl + "?" + params.toString();
}

// Botón aplicar
document.getElementById('apply').addEventListener('click', function() {
    let url = buildUrl("/");
    cargarLibros(url);
});

// Botón reset
document.getElementById('reset').addEventListener('click', function() {
    // Limpiar input de búsqueda
    document.getElementById('search').value = '';

    // Resetear Select2 correctamente
    $('#registro_id').val(null).trigger('change');
    $('#idioma_id').val(null).trigger('change');
    $('#autor_id').val(null).trigger('change');
    $('#materia_id').val(null).trigger('change');

    // Recargar resultados sin filtros
    cargarLibros("/pagina");
});
