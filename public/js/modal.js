// === FONCTIONS DE LA MODALE GLOBALE === //

function closeModal() {
    const modalOverlay = document.getElementById('globalOverlay');
    const modalBody = document.getElementById('globalModalBody');

    if (modalOverlay) {
        modalOverlay.classList.remove('active');

        // On masque en JS
        modalOverlay.style.visibility = 'hidden';
        modalOverlay.style.opacity = '0';

        setTimeout(() => {
            if (modalBody) modalBody.innerHTML = '';
        }, 200);
    }
}

document.addEventListener('click', function(e) {
    const modalOverlay = document.getElementById('globalOverlay');
    if (modalOverlay && e.target === modalOverlay) {
        closeModal();
    }
});

// Fonction indépendante pour la dynamique du formulaire Pièce
function setupPieceFormDynamics(modalBody) {
    const typeSelect = modalBody.querySelector('#piece_type');
    const prixVenteInput = modalBody.querySelector('#piece_prixVente');
    const prixCatalogueInput = modalBody.querySelector('#piece_prixCatalogue');
    const sectionComposants = modalBody.querySelector('#section-composants');

    if (!typeSelect || !prixVenteInput || !prixCatalogueInput) return;

    // --- 1. GESTION DES RÈGLES MÉTIER (Types et Prix) ---
    function updateFields() {
        const type = typeSelect.value;

        prixVenteInput.disabled = true;
        prixCatalogueInput.disabled = true;

        // On masque ou affiche la nomenclature selon le type
        if (sectionComposants) {
            if (type === 'LIVRABLE' || type === 'INTERMEDIAIRE') {
                sectionComposants.style.display = 'block'; // Ce sont des pièces fabriquées
            } else {
                sectionComposants.style.display = 'none'; // Matières 1eres / Achetées = pas de nomenclature
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

    // --- 2. GESTION DU BOUTON "AJOUTER UN COMPOSANT" (CollectionType) ---
    const addBtn = modalBody.querySelector('#add-composant-btn');
    const wrapper = modalBody.querySelector('.composants-wrapper');
    const list = modalBody.querySelector('.composants-list');

    if (addBtn && wrapper && list) {
        addBtn.addEventListener('click', function() {
            // Récupère le code HTML vide (le prototype) généré par Symfony
            let prototype = wrapper.dataset.prototype;
            // Récupère l'index actuel et l'incrémente pour ne pas écraser les précédents
            let index = parseInt(wrapper.dataset.index);

            // Remplace "__name__" (valeur par défaut de Symfony) par le vrai numéro
            let newForm = prototype.replace(/__name__/g, index);
            wrapper.dataset.index = index + 1;

            // Structure HTML de la nouvelle ligne
            const newRow = document.createElement('div');
            newRow.className = 'row align-items-center mb-2 item-composant';
            newRow.innerHTML = `
                <div class="col-8">${newForm}</div>
                <div class="col-3"></div> <div class="col-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-composant">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;

            // On nettoie un peu le HTML généré par Symfony pour le faire entrer dans nos colonnes proprement
            const fields = newRow.querySelectorAll('select, input');
            if (fields.length >= 2) {
                const col8 = newRow.querySelector('.col-8');
                const col3 = newRow.querySelector('.col-3');
                col8.innerHTML = '';
                col8.appendChild(fields[0]); // Le select (Pièce)
                col3.appendChild(fields[1]); // L'input (Quantité)
            }

            list.appendChild(newRow);
        });

        // Suppression d'un composant (Délégation d'événement)
        list.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-composant')) {
                e.target.closest('.item-composant').remove();
            }
        });
    }
}

function openModalAjax(title, url) {
    const modalOverlay = document.getElementById('globalOverlay');
    const modalTitle = document.getElementById('globalModalTitle');
    const modalBody = document.getElementById('globalModalBody');

    if (!modalOverlay || !modalTitle || !modalBody) return;

    modalTitle.innerText = title;
    modalOverlay.classList.add('active');
    modalOverlay.style.visibility = 'visible';
    modalOverlay.style.opacity = '1';

    modalBody.innerHTML = `
    <div class="text-center text-muted my-5">
        <div class="spinner-border spinner-border-sm" role="status"></div>
        <span class="ms-2">Chargement en cours...</span>
    </div>`;

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;

            // On intercepte la soumission
            handleModalFormSubmit(modalBody, url);
            // On active la dynamique au chargement
            setupPieceFormDynamics(modalBody);
        })
        .catch(error => {
            modalBody.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement.</div>';
            console.error('Erreur AJAX:', error);
        });
}

function handleModalFormSubmit(modalBody, submitUrl) {
    const form = modalBody.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enregistrement...';
        }

        fetch(submitUrl, {
            method: form.method || 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.text();
                }
            })
            .then(html => {
                if (html) {
                    modalBody.innerHTML = html;

                    // On ré-attache les écouteurs sur le nouveau HTML généré (après erreur)
                    handleModalFormSubmit(modalBody, submitUrl);
                    // On relance la dynamique car le HTML a été remplacé
                    setupPieceFormDynamics(modalBody);
                }
            })
            .catch(error => console.error('Erreur :', error));
    });
}
