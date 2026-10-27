(() => {
    const inventorySearch = document.getElementById('inventorySearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const stateFilter = document.getElementById('stateFilter');
    const emptyMessage = document.getElementById('inventoryEmpty');
    const inventoryGrid = document.querySelector('.inventory');
    const cards = Array.from(document.querySelectorAll('.card'));

    if (!inventorySearch || !categoryFilter || !stateFilter || !emptyMessage || !inventoryGrid) {
        throw new Error('Inventory filters could not find their required page elements.');
    }

    function filterInventory() {
        const query = inventorySearch.value.trim().toLocaleLowerCase();
        const category = categoryFilter.value;
        const state = stateFilter.value;
        let visibleCards = 0;

        cards.forEach((card) => {
            const matches = (!query || card.textContent.toLocaleLowerCase().includes(query))
                && (!category || card.dataset.category === category)
                && (!state || card.dataset.state === state);
            card.hidden = !matches;
            if (matches) visibleCards++;
        });

        emptyMessage.hidden = visibleCards > 0;
    }

    inventorySearch.addEventListener('input', filterInventory);
    categoryFilter.addEventListener('change', filterInventory);
    stateFilter.addEventListener('change', filterInventory);
    filterInventory();
})();
