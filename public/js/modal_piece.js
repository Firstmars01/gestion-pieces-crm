
// Fonction pour initialiser Tom Select sur les champs ciblés
function initTomSelects(container) {
    container.querySelectorAll('.select-searchable:not(.tomselected)').forEach(function(select) {
        new TomSelect(select, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            placeholder: select.getAttribute('placeholder') || 'Sélectionner...'
        });
    });
}

// Gestion de l'ouverture de la modale
document.addEventListener('modal:loaded', function(e) {
    const modalBody = e.detail.modalBody;

    if (modalBody.querySelector('#piece_type')) {
        setupPieceFormDynamics(modalBody);
    }

    // Initialiser Tom Select sur les champs déjà présents au chargement
    initTomSelects(modalBody);
});

function setupPieceFormDynamics(modalBody) {
    const typeSelect = modalBody.querySelector('#piece_type');
    const prixVenteInput = modalBody.querySelector('#piece_prixVente');
    const prixCatalogueInput = modalBody.querySelector('#piece_prixCatalogue');
    const sectionComposants = modalBody.querySelector('#section-composants');

    if (!typeSelect || !prixVenteInput || !prixCatalogueInput) return;

    function updateFields() {
        const type = typeSelect.value;
        prixVenteInput.disabled = true;
        prixCatalogueInput.disabled = true;

        if (sectionComposants) {
            if (type === 'LIVRABLE' || type === 'INTERMEDIAIRE') {
                sectionComposants.style.display = 'block';
            } else {
                sectionComposants.style.display = 'none';
            }
        }

        if (type === 'LIVRABLE') {
            prixVenteInput.disabled = false;
            prixCatalogueInput.value = '';
        } else if (type === 'MATIERE_PREMIERE' || type === 'ACHETEE') {
            prixCatalogueInput.disabled = false;
            prixVenteInput.value = '';
        }
    }

    updateFields();
    typeSelect.addEventListener('change', updateFields);

    const addBtn = modalBody.querySelector('#add-composant-btn');
    const wrapper = modalBody.querySelector('.composants-wrapper');
    const list = modalBody.querySelector('.composants-list');

    if (addBtn && wrapper && list) {
        const newAddBtn = addBtn.cloneNode(true);
        addBtn.parentNode.replaceChild(newAddBtn, addBtn);

        newAddBtn.addEventListener('click', function() {
            let prototype = wrapper.dataset.prototype;
            let index = parseInt(wrapper.dataset.index);

            let newForm = prototype.replace(/__name__/g, index);
            wrapper.dataset.index = index + 1;

            const newRow = document.createElement('div');
            newRow.className = 'row align-items-center mb-2 item-composant';
            newRow.innerHTML = `
                <div class="col-8">${newForm}</div>
                <div class="col-3"></div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-composant">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;

            const fields = newRow.querySelectorAll('select, input');
            if (fields.length >= 2) {
                newRow.querySelector('.col-8').innerHTML = '';
                newRow.querySelector('.col-8').appendChild(fields[0]);
                newRow.querySelector('.col-3').appendChild(fields[1]);
            }

            list.appendChild(newRow);

            // On initialise Tom Select SUR LA NOUVELLE LIGNE qu'on vient d'ajouter !
            initTomSelects(newRow);
        });

        list.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-composant')) {
                e.target.closest('.item-composant').remove();
            }
        });
    }
}

// Nettoyage des messages d'erreur lors du changement de type de pièce
document.addEventListener('change', function(event) {
    if (event.target && event.target.tagName === 'SELECT' && event.target.id.includes('type')) {
        const form = event.target.closest('form');
        if (!form) return;

        form.querySelectorAll('.is-invalid').forEach(function(input) {
            input.classList.remove('is-invalid');
        });

        form.querySelectorAll('.invalid-feedback').forEach(function(errorMessage) {
            errorMessage.remove();
        });

        form.querySelectorAll('.alert-danger').forEach(function(alertBox) {
            alertBox.style.display = 'none';
        });
    }
});
