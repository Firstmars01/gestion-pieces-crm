// On écoute le "cri" du fichier global
document.addEventListener('modal:loaded', function(e) {
    const modalBody = e.detail.modalBody;

    // On vérifie si on est bien sur un formulaire de pièce en cherchant le selecteur de type
    if (modalBody.querySelector('#piece_type')) {
        setupPieceFormDynamics(modalBody);
    }
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
        // Pour éviter d'ajouter de multiples écouteurs si la page se recharge avec une erreur
        // On clone et remplace le bouton pour vider les anciens eventListeners
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
        });

        // La délégation d'événement pour la suppression peut se faire directement
        list.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-composant')) {
                e.target.closest('.item-composant').remove();
            }
        });
    }

    // On écoute tous les changements sur la page
    document.addEventListener('change', function(event) {

        // On vérifie si l'élément modifié est bien notre menu déroulant "Type"
        // (Symfony génère généralement l'ID "piece_type" pour ce champ)
        if (event.target && event.target.tagName === 'SELECT' && event.target.id.includes('type')) {

            // 1. On trouve le formulaire dans lequel on se trouve
            const form = event.target.closest('form');
            if (!form) return;

            // 2. On enlève les bordures rouges de tous les champs
            form.querySelectorAll('.is-invalid').forEach(function(input) {
                input.classList.remove('is-invalid');
            });

            // 3. On supprime les textes d'erreur rouges (générés par Bootstrap/Symfony)
            form.querySelectorAll('.invalid-feedback').forEach(function(errorMessage) {
                errorMessage.remove(); // On supprime complètement le texte
            });

            // Bonus : Si tu as des alertes globales en haut du formulaire, tu peux aussi les cacher
            form.querySelectorAll('.alert-danger').forEach(function(alertBox) {
                alertBox.style.display = 'none';
            });
        }
    });
}
