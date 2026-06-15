// Gestion de l'ouverture de la modale pour les Gammes
document.addEventListener('modal:loaded', function(e) {
    const modalBody = e.detail.modalBody;

    // On cherche tous les <select> qui ont notre classe
    const searchableSelects = modalBody.querySelectorAll('.select-searchable');

    searchableSelects.forEach(function(selectElement) {
        // On initialise Tom Select dessus
        new TomSelect(selectElement, {
            create: false,         // Empêche l'utilisateur de créer de nouveaux éléments qui ne sont pas dans la BDD
            sortField: {
                field: "text",
                direction: "asc"   // Trie les résultats par ordre alphabétique
            },
            placeholder: selectElement.getAttribute('placeholder') // Reprend le texte "Sélectionnez..."
        });
    });
});
