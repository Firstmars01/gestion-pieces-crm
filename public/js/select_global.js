// 1. On crée une fonction réutilisable
// Le paramètre "container" permet de cibler soit la page entière, soit juste une modale
function initGlobalTomSelect(container = document) {
    container.querySelectorAll('.select-searchable:not(.tomselected)').forEach(function(select) {
        new TomSelect(select, {
            create: false,
            sortField: { field: "text", direction: "asc" },
            placeholder: select.getAttribute('placeholder') || 'Sélectionner...'
        });
    });
}

// 2. On lance la fonction quand une page normale charge (hors modale)
document.addEventListener('DOMContentLoaded', function() {
    initGlobalTomSelect();
});

// 3. On lance la fonction quand N'IMPORTE QUELLE modale AJAX s'ouvre
document.addEventListener('modal:loaded', function(e) {
    if (e.detail && e.detail.modalBody) {
        initGlobalTomSelect(e.detail.modalBody);
    }
});
