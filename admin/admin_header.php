<?php
// admin_auth_check.php (qui inclut config.php) doit avoir été inclus par la page appelante.
// BASE_URL est défini dans config.php.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : "Administration - LawApp"; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/admin/css/admin_style.css">
</head>
<body>

<header class="admin-header">
    <div class="logo">
        <a href="<?php echo BASE_URL; ?>/admin/index.php">LawApp Admin</a>
    </div>
    <nav class="admin-nav">
        <ul>
            <li><a href="<?php echo BASE_URL; ?>/admin/admin_dashboard.php">Tableau de Bord</a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/manage_users.php">Utilisateurs</a></li>
            <li class="separator">|</li>
            <li><a href="<?php echo BASE_URL; ?>/admin/manage_categories_cours.php">Catégories Cours</a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/manage_cours.php">Cours</a></li>
            <li class="separator">|</li>
            <li><a href="<?php echo BASE_URL; ?>/admin/manage_categories_livres.php">Catégories Livres</a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/manage_livres.php">Livres</a></li>
            <li class="separator">|</li>
            <li><a href="<?php echo BASE_URL; ?>/admin/manage_video_categories.php">Catégories Vidéos</a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/manage_videos.php">Vidéos</a></li>
            <li class="separator">|</li>
            <li><a href="<?php echo BASE_URL; ?>/admin/manage_categories_podcasts.php">Catégories Podcasts</a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/manage_podcasts.php">Podcasts</a></li>
            <li class="separator">|</li>
            <li><a href="<?php echo BASE_URL; ?>/accueil.php" target="_blank">Voir le site</a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/admin_logout.php">Déconnexion</a></li>
        </ul>
    </nav>
</header>

<main class="admin-main">
