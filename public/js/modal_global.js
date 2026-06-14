// === FONCTIONS DE LA MODALE GLOBALE === //

function closeModal() {
    const modalOverlay = document.getElementById('globalOverlay');
    const modalBody = document.getElementById('globalModalBody');

    if (modalOverlay) {
        modalOverlay.classList.remove('active');
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

// Fonction pour signaler aux autres scripts que la modale est prête
function triggerModalLoadedEvent(modalBody) {
    const event = new CustomEvent('modal:loaded', {
        detail: { modalBody: modalBody }
    });
    document.dispatchEvent(event);
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
            handleModalFormSubmit(modalBody, url);

            // ON PRÉVIENT TOUT LE MONDE QUE LA MODALE EST CHARGÉE
            triggerModalLoadedEvent(modalBody);
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
                if (response.headers.get('content-type') && response.headers.get('content-type').includes('application/json')) {
                    return response.json();
                }
                return response.text();
            })
            .then(data => {
                if (typeof data === 'object' && data.redirect) {
                    window.location.href = data.redirect;
                } else if (typeof data === 'string') {
                    // Erreur : on met à jour la modale
                    modalBody.innerHTML = data;
                    handleModalFormSubmit(modalBody, submitUrl);

                    // ON PRÉVIENT À NOUVEAU CAR LE HTML A CHANGÉ
                    triggerModalLoadedEvent(modalBody);
                }
            })
            .catch(error => console.error('Erreur :', error));
    });
}
