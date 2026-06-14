// public/js/modal_admin.js

document.addEventListener('modal:loaded', function(e) {
    const modalBody = e.detail.modalBody;

    // On vérifie si c'est le formulaire d'administration des utilisateurs (ex: on cherche l'ID email)
    if (modalBody.querySelector('form.admin-user-form')) {
        setupAdminUserForm(modalBody);
    }
});

function setupAdminUserForm(modalBody) {
    // Tu pourras ajouter ta logique dynamique pour les utilisateurs ici plus tard
    // Exemple :
    // const roleAdminCheckbox = modalBody.querySelector('#role_1');
    // if (roleAdminCheckbox) { ... }
}
