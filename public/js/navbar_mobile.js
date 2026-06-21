function toggleSidebar() {
    // Ajoute ou retire la classe "mobile-open" sur la sidebar
    document.getElementById('main-sidebar').classList.toggle('mobile-open');
    // Affiche ou cache le fond sombre
    document.getElementById('mobile-sidebar-overlay').classList.toggle('active');
}
