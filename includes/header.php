<?php
require_once __DIR__ . '/config.php'; // Assure que config.php est inclus

// Logique de changement de thème
$current_theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
if (isset($_GET['theme'])) {
    $new_theme = $_GET['theme'] === 'dark' ? 'dark' : 'light';
    setcookie('theme', $new_theme, time() + (86400 * 30), "/"); // Cookie pour 30 jours
    $current_theme = $new_theme;
    // Nettoyer l'URL et appliquer le thème
    $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
    header('Location: ' . $uri_parts[0]);
    exit;
}

$site_name = "LawApp";
$default_page_title = "Votre plateforme d'apprentissage juridique";
$page_title_display = isset($page_title) ? htmlspecialchars($page_title) . " - " . $site_name : htmlspecialchars($default_page_title) . " - " . $site_name;

// Préparer le prénom de l'utilisateur pour un message d'accueil personnalisé
$user_greeting_name = '';
if (isset($_SESSION['user_prenom'])) {
    $user_greeting_name = htmlspecialchars($_SESSION['user_prenom']);
}

?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?php echo htmlspecialchars($current_theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title_display; ?></title>
    <?php if (isset($extra_css)): ?>
    <style>
        <?php echo $extra_css; ?>
    </style>
    <?php endif; ?>
    <!-- Le chemin vers style.css doit être relatif à la racine du site ou absolu -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/animations.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/public.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        function toggleTheme() {
            // Construire l'URL de base sans les paramètres GET existants pour le thème
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('theme'); // Supprimer l'ancien paramètre de thème s'il existe
            const newThemeValue = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            currentUrl.searchParams.set('theme', newThemeValue); // Ajouter le nouveau paramètre de thème
            window.location.href = currentUrl.toString();
        }
    </script>
</head>
<body>

<header class="main-header glass animate-fadeIn">
    <div class="container header-container">
        <div class="logo hover-scale">
            <a href="<?php echo BASE_URL; ?>/accueil.php" class="gradient-primary">LawApp</a>
        </div>
        <div class="header-search glass animate-fadeIn delay-100">
            <form action="<?php echo BASE_URL; ?>/recherche.php" method="get">
                <input type="search" name="q" class="form-input" placeholder="Rechercher cours, lois...">
            </form>
        </div>
        <nav class="main-nav animate-fadeIn delay-200">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/accueil.php" class="hover-lift">Accueil</a></li>
                <li><a href="<?php echo BASE_URL; ?>/cours.php" class="hover-lift">Cours</a></li>
                <li><a href="<?php echo BASE_URL; ?>/livres.php" class="hover-lift">Livres</a></li>
                <li><a href="<?php echo BASE_URL; ?>/podcasts_liste.php" class="hover-lift">Podcasts</a></li>
                <li><a href="<?php echo BASE_URL; ?>/lois_recherche.php" class="hover-lift">Lois</a></li>
            </ul>
        </nav>
        <div class="user-actions animate-fadeIn delay-300">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-greeting glass">Bonjour, <?php echo $user_greeting_name; ?> !</span>
                <a href="<?php echo BASE_URL; ?>/profil.php" class="btn btn-primary hover-scale">Profil</a>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-outline hover-lift">Déconnexion</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline hover-lift">Connexion</a>
                <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary hover-scale">Inscription</a>
            <?php endif; ?>
            <button onclick="toggleTheme()" title="Changer de thème" class="theme-toggle-btn hover-scale glass">
                <img src="<?php echo BASE_URL; ?>/assets/icons/<?php echo $current_theme === 'dark' ? 'sun' : 'moon'; ?>.svg" alt="Changer de thème">
            </button>
        </div>
    </div>
</header>

<main> <!-- La balise main s'ouvre ici et se fermera dans footer.php -->
