document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('.table-sortable');

    tables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        // 1. Mémoriser l'ordre initial des lignes
        // On ajoute un attribut 'data-original-index' à chaque ligne (tr)
        rows.forEach((row, index) => {
            row.dataset.originalIndex = index;
        });

        headers.forEach(header => {
            // Style de base pour l'entête
            header.style.cursor = 'pointer';
            header.innerHTML += ' <i class="bi bi-arrow-down-up text-muted small ms-1 sort-icon"></i>';

            header.addEventListener('click', () => {
                const index = Array.from(header.parentNode.children).indexOf(header);
                const type = header.dataset.sort; // 'string' ou 'number'

                // Déterminer l'état actuel de la colonne AVANT le clic
                let currentState = 'none';
                if (header.classList.contains('sort-asc')) currentState = 'asc';
                else if (header.classList.contains('sort-desc')) currentState = 'desc';

                // Réinitialiser visuellement TOUS les entêtes
                headers.forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                    h.querySelector('.sort-icon').className = 'bi bi-arrow-down-up text-muted small ms-1 sort-icon';
                });

                // On récupère les lignes dans leur ordre actuel
                const currentRows = Array.from(tbody.querySelectorAll('tr'));

                if (currentState === 'none') {
                    // ÉTAPE 1 : Passer en mode CROISSANT (ASC)
                    header.classList.add('sort-asc');
                    header.querySelector('.sort-icon').className = 'bi bi-arrow-up text-primary small ms-1 sort-icon';

                    currentRows.sort((a, b) => {
                        let valA = a.children[index].innerText.trim();
                        let valB = b.children[index].innerText.trim();

                        if (type === 'number') {
                            valA = parseFloat(valA.replace(/[^0-9.-]+/g,"")) || 0;
                            valB = parseFloat(valB.replace(/[^0-9.-]+/g,"")) || 0;
                            return valA - valB;
                        }
                        return valA.localeCompare(valB);
                    });

                } else if (currentState === 'asc') {
                    // ÉTAPE 2 : Passer en mode DÉCROISSANT (DESC)
                    header.classList.add('sort-desc');
                    header.querySelector('.sort-icon').className = 'bi bi-arrow-down text-primary small ms-1 sort-icon';

                    currentRows.sort((a, b) => {
                        let valA = a.children[index].innerText.trim();
                        let valB = b.children[index].innerText.trim();

                        if (type === 'number') {
                            valA = parseFloat(valA.replace(/[^0-9.-]+/g,"")) || 0;
                            valB = parseFloat(valB.replace(/[^0-9.-]+/g,"")) || 0;
                            return valB - valA;
                        }
                        return valB.localeCompare(valA);
                    });

                } else {
                    // ÉTAPE 3 : Retour au mode AUCUN TRI (Ordre d'origine)
                    // (Les classes CSS ont déjà été enlevées dans la boucle de réinitialisation)
                    currentRows.sort((a, b) => {
                        return parseInt(a.dataset.originalIndex) - parseInt(b.dataset.originalIndex);
                    });
                }

                // Réinjecter les lignes dans le nouveau bon ordre
                currentRows.forEach(row => tbody.appendChild(row));
            });
        });
    });
});
