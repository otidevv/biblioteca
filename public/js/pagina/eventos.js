document.addEventListener('DOMContentLoaded', () => {
    // Marcar que los eventos están listos
    document.body.dataset.eventsReady = 'true';

    // === Filtrado de Eventos ===
    const filterCategory = document.getElementById('filter-category');
    const filterModality = document.getElementById('filter-modality');
    const filterSearch = document.getElementById('filter-search');

    const eventCards = document.querySelectorAll('.events-card, .events-agenda-item');

    function applyFilters() {
        const filterCatValue = filterCategory ? filterCategory.value.toLowerCase() : '';
        const filterModValue = filterModality ? filterModality.value.toLowerCase() : '';
        const filterSearchValue = filterSearch ? filterSearch.value.toLowerCase() : '';

        eventCards.forEach(card => {
            let shouldShow = true;

            // Filtro por categoría
            if (filterCatValue) {
                const cardCategory = card.dataset.category || '';
                shouldShow = shouldShow && cardCategory === filterCatValue;
            }

            // Filtro por modalidad
            if (filterModValue) {
                const cardModality = card.dataset.modality ? card.dataset.modality.toLowerCase() : '';
                shouldShow = shouldShow && cardModality === filterModValue;
            }

            // Filtro por búsqueda
            if (filterSearchValue) {
                const cardText = card.textContent.toLowerCase();
                shouldShow = shouldShow && cardText.includes(filterSearchValue);
            }

            // Aplicar estilos
            card.style.display = shouldShow ? '' : 'none';
            card.style.opacity = shouldShow ? '1' : '0';
            card.style.pointerEvents = shouldShow ? 'auto' : 'none';
        });
    }

    // Event listeners para los filtros
    if (filterCategory) filterCategory.addEventListener('change', applyFilters);
    if (filterModality) filterModality.addEventListener('change', applyFilters);
    if (filterSearch) filterSearch.addEventListener('input', applyFilters);

    // === Botones "Ver detalles" ===
    const detailButtons = document.querySelectorAll('.events-btn-primary');
    detailButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            // Aquí puedes agregar lógica para mostrar más detalles del evento
            // Por ejemplo, scroll a eventos, modal, etc.
            console.log('Ver detalles del evento');
        });
    });

    // === Botones "Ver actividades" en categorías ===
    const categoryButtons = document.querySelectorAll('.events-btn-secondary');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const categoryId = btn.dataset.category;
            if (categoryId && filterCategory) {
                filterCategory.value = categoryId;
                filterCategory.dispatchEvent(new Event('change'));
                // Scroll a la sección de eventos
                const eventsSection = document.querySelector('.events-featured-section');
                if (eventsSection) {
                    eventsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    // === Efectos visuales al hacer hover ===
    const cards = document.querySelectorAll('.events-card, .events-category-card, .events-agenda-item');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });
});
