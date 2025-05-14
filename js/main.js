// Fichier JavaScript principal pour LawApp

// Fonction pour appliquer un thème
function applyTheme(theme) {
    if (theme === 'sombre') {
        document.body.setAttribute('data-theme', 'dark');
    } else {
        document.body.removeAttribute('data-theme');
    }
}

// Fonction pour charger et appliquer le thème sauvegardé
function loadAndApplyTheme() {
    const savedTheme = localStorage.getItem('lawAppTheme') || 'clair'; // 'clair' par défaut
    applyTheme(savedTheme);
    // S'assurer que le select dans profil.php reflète le thème chargé (si la page profil est active)
    const themeSelector = document.getElementById('theme-app');
    if (themeSelector) {
        themeSelector.value = savedTheme;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('LawApp prête !');
    loadAndApplyTheme(); // Charger le thème au démarrage

    // Logique spécifique à la page profil pour le changement de thème
    const themeSelector = document.getElementById('theme-app');
    if (themeSelector) {
        themeSelector.addEventListener('change', function() {
            const selectedTheme = this.value;
            applyTheme(selectedTheme);
            localStorage.setItem('lawAppTheme', selectedTheme);
        });
    }

    // Autres initialisations globales si nécessaires
});

// Fonctions spécifiques aux pages (exemples)
function initAccueilPage() {
    // Logique pour la page d'accueil
    console.log('Page Accueil initialisée');
}

function initCoursPage() {
    // Logique pour la page des cours
    console.log('Page Cours initialisée');
}

// Vous pouvez appeler ces fonctions en fonction de la page chargée
// Exemple : if (document.body.id === 'page-accueil') initAccueilPage();
