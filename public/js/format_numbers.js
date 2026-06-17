document.addEventListener('DOMContentLoaded', function() {
    // On récupère la région du navigateur (ex: 'fr-FR', 'en-US', 'en-GB')
    const userLocale = navigator.language || 'fr-FR';

    // Fonction pour déduire la devise (monnaie) en fonction du pays du navigateur
    function getCurrencyFromLocale(locale) {
        // locale.split('-')[1] permet d'extraire 'US' depuis 'en-US'
        const country = locale.split('-')[1]?.toUpperCase() || locale.toUpperCase();

        if (country === 'US') return 'USD'; // Dollar américain ($)
        if (country === 'GB') return 'GBP'; // Livre sterling (£)
        if (country === 'CH') return 'CHF'; // Franc suisse (CHF)
        if (country === 'CA') return 'CAD'; // Dollar canadien ($ CA)
        if (country === 'JP') return 'JPY'; // Yen (¥)

        return 'EUR'; // Par défaut, on affiche des Euros (€)
    }

    const localCurrency = getCurrencyFromLocale(userLocale);

    // Formateur intelligent pour les nombres (séparateurs de milliers)
    const numberFormatter = new Intl.NumberFormat(userLocale);

    // Formateur intelligent pour les devises (s'adapte à la monnaie locale !)
    const currencyFormatter = new Intl.NumberFormat(userLocale, {
        style: 'currency',
        currency: localCurrency
    });

    // 1. Appliquer le format aux quantités
    document.querySelectorAll('.format-number').forEach(function(el) {
        const val = parseFloat(el.getAttribute('data-value'));
        if (!isNaN(val)) {
            el.textContent = numberFormatter.format(val);
        }
    });

    // 2. Appliquer le format aux prix
    document.querySelectorAll('.format-currency').forEach(function(el) {
        const val = parseFloat(el.getAttribute('data-value'));
        if (!isNaN(val)) {
            el.textContent = currencyFormatter.format(val);
        }
    });
});
