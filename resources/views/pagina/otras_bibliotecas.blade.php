@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Otras bibliotecas')
@section('meta_description', 'Directorio de enlaces oficiales a otras bibliotecas para ampliar la consulta académica y bibliográfica.')

@section('content')
<style>
.external-libraries-page {
    display: grid;
    gap: 1.5rem;
}

.external-libraries-hero {
    position: relative;
    overflow: hidden;
    padding: 1.75rem;
    border-radius: 1.5rem;
    border: 1px solid rgba(24, 77, 59, 0.1);
    background:
        radial-gradient(circle at top right, rgba(216, 177, 92, 0.2), transparent 28%),
        linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(240, 247, 244, 0.92));
}

.external-libraries-hero::after {
    content: "";
    position: absolute;
    inset: auto -40px -40px auto;
    width: 180px;
    height: 180px;
    background: radial-gradient(circle, rgba(47, 122, 93, 0.14), transparent 68%);
    pointer-events: none;
}

.external-libraries-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.9rem;
    padding: 0.35rem 0.8rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: #1d5a46;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.external-libraries-hero h2 {
    margin: 0 0 0.7rem;
    font-size: clamp(1.8rem, 2.4vw, 2.5rem);
    font-weight: 800;
    color: #143529;
}

.external-libraries-hero p {
    max-width: 760px;
    margin: 0;
    color: #526761;
    line-height: 1.7;
}

.external-libraries-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.external-library-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    height: 100%;
    padding: 1.5rem;
    border-radius: 1.35rem;
    border: 1px solid rgba(24, 77, 59, 0.09);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(243, 248, 245, 0.88));
    box-shadow: 0 14px 34px rgba(24, 77, 59, 0.08);
}

.external-library-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.8rem;
}

.external-library-card__icon {
    width: 52px;
    height: 52px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(24, 77, 59, 0.12), rgba(216, 177, 92, 0.22));
    color: #175a45;
    font-size: 1.35rem;
}

.external-library-card__badge {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: #1f5f4b;
    font-size: 0.74rem;
    font-weight: 700;
}

.external-library-card h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #173d2f;
}

.external-library-card small {
    color: #6c8078;
    font-weight: 600;
}

.external-library-card p {
    margin: 0;
    color: #5b7069;
    line-height: 1.65;
}

.external-library-card__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: fit-content;
    margin-top: auto;
    padding: 0.8rem 1rem;
    border-radius: 0.95rem;
    text-decoration: none;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #1a7357, #0f4e3a);
}

.external-library-card__link:hover {
    color: #fff;
    background: linear-gradient(135deg, #23815f, #12553f);
}

body.library-dark .external-libraries-hero {
    border-color: rgba(255, 255, 255, 0.08);
    background:
        radial-gradient(circle at top right, rgba(216, 177, 92, 0.12), transparent 28%),
        linear-gradient(135deg, rgba(16, 29, 24, 0.94), rgba(11, 20, 16, 0.92));
}

body.library-dark .external-libraries-eyebrow,
body.library-dark .external-library-card__badge {
    background: rgba(255, 255, 255, 0.08);
    color: #f2cf82;
}

body.library-dark .external-libraries-hero h2,
body.library-dark .external-library-card h3 {
    color: #f8fafc;
}

body.library-dark .external-libraries-hero p,
body.library-dark .external-library-card p,
body.library-dark .external-library-card small {
    color: #adc0b7;
}

body.library-dark .external-library-card {
    border-color: rgba(255, 255, 255, 0.08);
    background: linear-gradient(180deg, rgba(18, 30, 24, 0.94), rgba(11, 20, 16, 0.9));
    box-shadow: 0 18px 36px rgba(0, 0, 0, 0.28);
}

body.library-dark .external-library-card__icon {
    background: linear-gradient(135deg, rgba(242, 207, 130, 0.16), rgba(94, 234, 212, 0.14));
    color: #f2cf82;
}

/* ── Barra de filtros ── */
.external-filter-bar {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding: 1.25rem 1.5rem;
    border-radius: 1.35rem;
    border: 1px solid rgba(24, 77, 59, 0.09);
    background: linear-gradient(180deg, rgba(255,255,255,0.97), rgba(243,248,245,0.9));
    box-shadow: 0 8px 24px rgba(24,77,59,0.06);
}

.external-search-wrap {
    position: relative;
}

.external-search-icon {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    color: #1a7357;
    font-size: 1rem;
    pointer-events: none;
}

.external-search-input {
    width: 100%;
    padding: 0.65rem 0.9rem 0.65rem 2.4rem;
    border-radius: 0.9rem;
    border: 1.5px solid rgba(24,77,59,0.15);
    background: #fff;
    font-size: 0.92rem;
    color: #1a3028;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.external-search-input:focus {
    border-color: #1a7357;
    box-shadow: 0 0 0 3px rgba(26,115,87,0.12);
}

.external-categories {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.ext-cat-btn {
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    border: 1.5px solid rgba(24,77,59,0.15);
    background: transparent;
    color: #3a6155;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}

.ext-cat-btn:hover {
    border-color: #1a7357;
    color: #1a7357;
    background: rgba(26,115,87,0.06);
}

.ext-cat-btn.is-active {
    background: #1a7357;
    border-color: #1a7357;
    color: #fff;
}

.external-results-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.external-results-count {
    font-size: 0.85rem;
    color: #6c8078;
    font-weight: 600;
}

.external-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 1rem;
    color: #7a9a90;
    font-size: 0.95rem;
}

body.library-dark .external-filter-bar {
    border-color: rgba(255,255,255,0.08);
    background: linear-gradient(180deg, rgba(18,30,24,0.94), rgba(11,20,16,0.9));
}

body.library-dark .external-search-input {
    background: rgba(255,255,255,0.06);
    border-color: rgba(255,255,255,0.1);
    color: #e8f4f0;
}

body.library-dark .external-search-input:focus {
    border-color: #4db896;
    box-shadow: 0 0 0 3px rgba(77,184,150,0.15);
}

body.library-dark .external-search-icon {
    color: #86e2c0;
}

body.library-dark .ext-cat-btn {
    border-color: rgba(255,255,255,0.12);
    color: #adc0b7;
}

body.library-dark .ext-cat-btn:hover {
    border-color: #4db896;
    color: #4db896;
}

body.library-dark .ext-cat-btn.is-active {
    background: #1a7357;
    border-color: #1a7357;
    color: #fff;
}

body.library-dark .external-results-count {
    color: #7a9a90;
}

@media (max-width: 576px) {
    .external-libraries-hero {
        padding: 1.25rem;
    }
    .external-filter-bar {
        padding: 1rem;
    }
}
</style>

<div class="external-libraries-page">
    <section class="external-libraries-hero">
        <span class="external-libraries-eyebrow">
            <i class="bi bi-link-45deg"></i>
            Directorio externo
        </span>
        <h2>Links de otras bibliotecas para consulta</h2>
        <p>
            Reune accesos directos a bibliotecas e instituciones con portales oficiales de consulta.
            Puedes usar estos enlaces para ampliar tu busqueda bibliografica y revisar otros catalogos.
        </p>
    </section>

    <div class="external-filter-bar">
        <div class="external-search-wrap">
            <i class="bi bi-search external-search-icon"></i>
            <input type="text"
                   id="extSearch"
                   class="external-search-input"
                   placeholder="Buscar por nombre, institución o descripción...">
        </div>
        <div class="external-categories" id="extCategories"></div>
    </div>

    <div class="external-results-bar">
        <span class="external-results-count" id="extCount">
            {{ $bibliotecasExternas->count() }} recursos disponibles
        </span>
    </div>

    <section class="external-libraries-grid" id="extGrid">
        @foreach($bibliotecasExternas as $biblioteca)
            <article class="external-library-card"
                     data-nombre="{{ strtolower($biblioteca['nombre']) }}"
                     data-institucion="{{ strtolower($biblioteca['institucion']) }}"
                     data-descripcion="{{ strtolower($biblioteca['descripcion']) }}"
                     data-etiqueta="{{ $biblioteca['etiqueta'] }}">
                <div class="external-library-card__top">
                    <span class="external-library-card__icon">
                        <i class="bi {{ $biblioteca['icono'] }}"></i>
                    </span>
                    <span class="external-library-card__badge">{{ $biblioteca['etiqueta'] }}</span>
                </div>

                <div>
                    <h3>{{ $biblioteca['nombre'] }}</h3>
                    <small>{{ $biblioteca['institucion'] }}</small>
                </div>

                <p>{{ $biblioteca['descripcion'] }}</p>

                <a href="{{ $biblioteca['url'] }}" target="_blank" rel="noopener noreferrer" class="external-library-card__link">
                    Visitar sitio
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </article>
        @endforeach
    </section>
</div>

<script>
(function () {
    const search   = document.getElementById('extSearch');
    const grid     = document.getElementById('extGrid');
    const catWrap  = document.getElementById('extCategories');
    const countEl  = document.getElementById('extCount');
    const cards    = Array.from(grid.querySelectorAll('.external-library-card'));

    let activeCategory = 'Todos';

    // Construir botones de categoría
    const etiquetas = ['Todos', ...new Set(cards.map(c => c.dataset.etiqueta))];
    etiquetas.forEach(label => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ext-cat-btn' + (label === 'Todos' ? ' is-active' : '');
        btn.textContent = label;
        btn.addEventListener('click', () => {
            activeCategory = label;
            catWrap.querySelectorAll('.ext-cat-btn').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            filtrar();
        });
        catWrap.appendChild(btn);
    });

    let debounce = null;
    search.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(filtrar, 250);
    });

    function filtrar() {
        const q = search.value.trim().toLowerCase();
        let visibles = 0;

        cards.forEach(card => {
            const porCategoria = activeCategory === 'Todos' || card.dataset.etiqueta === activeCategory;
            const porTexto = q === '' ||
                card.dataset.nombre.includes(q) ||
                card.dataset.institucion.includes(q) ||
                card.dataset.descripcion.includes(q);

            const mostrar = porCategoria && porTexto;
            card.style.display = mostrar ? '' : 'none';
            if (mostrar) visibles++;
        });

        countEl.textContent = visibles + (visibles === 1 ? ' recurso encontrado' : ' recursos encontrados');

        let empty = grid.querySelector('.external-empty');
        if (visibles === 0) {
            if (!empty) {
                empty = document.createElement('div');
                empty.className = 'external-empty';
                empty.innerHTML = '<i class="bi bi-search d-block fs-3 mb-2"></i>No se encontraron resultados para tu búsqueda.';
                grid.appendChild(empty);
            }
        } else if (empty) {
            empty.remove();
        }
    }
})();
</script>
@endsection
