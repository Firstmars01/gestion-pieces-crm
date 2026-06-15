document.addEventListener('DOMContentLoaded', function() {
    // On cible globalement tous les liens de tri de KnpPaginator dans l'application
    const sortLinks = document.querySelectorAll('.table-sortable th a');

    sortLinks.forEach(link => {
        // 1. GESTION DES ICÔNES
        // État par défaut : double flèche grise
        let iconClass = 'bi-arrow-down-up text-muted';

        // On adapte l'icône selon la classe ajoutée par KnpPaginator
        if (link.classList.contains('asc')) {
            iconClass = 'bi-arrow-up text-primary';
        } else if (link.classList.contains('desc')) {
            iconClass = 'bi-arrow-down text-primary';
        }

        // On injecte l'icône à côté du texte de l'entête
        link.innerHTML += ` <i class="bi ${iconClass} ms-1" style="font-size: 0.85rem;"></i>`;

        // 2. GESTION DU 3ÈME CLIC (Annulation du tri)
        // Si la colonne est en mode "desc", le prochain clic doit réinitialiser la vue
        if (link.classList.contains('desc')) {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // On bloque la requête par défaut

                const url = new URL(window.location.href);
                // On nettoie l'URL de ses paramètres de tri
                url.searchParams.delete('sort');
                url.searchParams.delete('direction');

                // On recharge la page proprement
                window.location.href = url.toString();
            });
        }
    });
});
