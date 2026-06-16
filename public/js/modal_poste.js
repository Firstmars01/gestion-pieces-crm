document.addEventListener('modal:loaded', function(e) {
    const modalBody = e.detail.modalBody;

    // Fonction pour activer la barre de recherche sur les select
    function initTomSelects(container) {
        container.querySelectorAll('.select-searchable:not(.tomselected)').forEach(function(select) {
            new TomSelect(select, {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: select.getAttribute('placeholder') || 'Sélectionner...'
            });
        });
    }

    // Initialisation au démarrage de la modale
    initTomSelects(modalBody);

    // Gestion de l'ajout d'une ligne
    const addBtn = modalBody.querySelector('#add-machine-btn');
    const wrapper = modalBody.querySelector('.machines-wrapper');
    const list = modalBody.querySelector('.machines-list');

    if (addBtn && wrapper && list) {
        // Nettoyage d'anciens écouteurs d'événements
        const newAddBtn = addBtn.cloneNode(true);
        addBtn.parentNode.replaceChild(newAddBtn, addBtn);

        newAddBtn.addEventListener('click', function() {
            let prototype = wrapper.dataset.prototype;
            let index = parseInt(wrapper.dataset.index);

            let newForm = prototype.replace(/__name__/g, index);
            wrapper.dataset.index = index + 1;

            const newRow = document.createElement('div');
            newRow.className = 'row align-items-center mb-2 item-machine';
            newRow.innerHTML = `
                <div class="col-10">${newForm}</div>
                <div class="col-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-machine">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;

            list.appendChild(newRow);
            initTomSelects(newRow); // On active Tom Select sur la nouvelle ligne !
        });

        // Gestion de la suppression d'une ligne
        list.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.btn-remove-machine');
            if (removeBtn) {
                removeBtn.closest('.item-machine').remove();
            }
        });
    }
});
