<?php
$page_title = "Tableau de Bord Admin";
require_once __DIR__ . '/admin_auth_check.php'; // Vérifie l'authentification et établit la connexion BDD
require_once __DIR__ . '/admin_header.php';

// Récupérer le prénom de l'admin depuis la session pour un message personnalisé
$admin_prenom = $_SESSION['admin_prenom'] ?? 'Admin';
?>

<div class="admin-content">
    <h1>Tableau de Bord</h1>
    <p>Bienvenue, <?php echo htmlspecialchars($admin_prenom); ?> !</p>

    <div class="dashboard-widgets">
        <div class="widget">
            <h2>Gestion des Utilisateurs</h2>
            <p>Gérer les comptes utilisateurs, leurs rôles et informations.</p>
            <p><a href="manage_users.php" class="btn btn-secondary">Gérer les Utilisateurs</a></p>
        </div>

        <div class="widget">
            <h2>Gestion des Cours</h2>
            <p>Créer, modifier et organiser les cours de la plateforme.</p>
            <p><a href="manage_courses.php" class="btn btn-secondary">Gérer les Cours</a></p>
        </div>

        <div class="widget">
            <h2>Gestion des Vidéos</h2>
            <p>Ajouter, modifier et organiser les vidéos de la plateforme.</p>
            <p><a href="manage_videos.php" class="btn btn-secondary">Gérer les Vidéos</a></p>
        </div>
        
        <!-- Vous pourrez ajouter d'autres widgets ici -->
        <!--
        <div class="widget">
            <h2>Statistiques Rapides</h2>
            <p>Nombre total d'utilisateurs: X</p>
            <p>Nombre total de cours: Y</p>
        </div>
        -->
    </div>

</div>

<?php
require_once __DIR__ . '/admin_footer.php';
?>
