// 1. On crée une fonction réutilisable
// Le paramètre "container" permet de cibler soit la page entière, soit juste une modale
function initGlobalTomSelect(container = document) {
    container.querySelectorAll('.select-searchable:not(.tomselected)').forEach(function(select) {
        new TomSelect(select, {
            create: false,
            sortField: { field: "text", direction: "asc" },
            placeholder: select.getAttribute('placeholder') || 'Sélectionner...',

            onChange: function(value) {
                // Déclenche l'événement "change" classique pour que le navigateur et Symfony comprennent
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    });
}

function initFournisseurAddPiecePriceSync(container = document) {
    const form = container.querySelector('#form-add-piece-fournisseur');
    if (!form || form.dataset.priceSyncBound === '1') return;

    const pieceSelect = form.querySelector('[name$="[piece]"]');
    const prixInput = form.querySelector('.prix-input-auto');
    if (!pieceSelect || !prixInput) return;

    const syncPrice = function() {
        const selectedOption = pieceSelect.options[pieceSelect.selectedIndex];
        const rawPrice = selectedOption ? (selectedOption.getAttribute('data-prix') || '') : '';
        prixInput.value = rawPrice.replace(',', '.');
    };

    pieceSelect.addEventListener('change', syncPrice);
    syncPrice();
    form.dataset.priceSyncBound = '1';
}

// 2. On lance la fonction quand une page normale charge (hors modale)
document.addEventListener('DOMContentLoaded', function() {
    initGlobalTomSelect();
    initFournisseurAddPiecePriceSync();
});

// 3. On lance la fonction quand N'IMPORTE QUELLE modale AJAX s'ouvre
document.addEventListener('modal:loaded', function(e) {
    if (e.detail && e.detail.modalBody) {
        initGlobalTomSelect(e.detail.modalBody);
        initFournisseurAddPiecePriceSync(e.detail.modalBody);
    }
});
