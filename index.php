<?php
$page_title = "Accueil";
$extra_css = "<style>
    .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
    .card { background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
    .btn { display: inline-block; background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin: 5px; }
    .btn-primary { background: #2196F3; }
    .btn-warning { background: #FF9800; }
    .btn-danger { background: #F44336; }
    .btn-info { background: #00BCD4; }
    .section { margin-bottom: 30px; }
    h1, h2, h3 { color: #333; }
    .links-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
</style>";
require_once 'includes/header.php';

// Récupérer le prénom de l'utilisateur s'il est connecté
$prenom_utilisateur = "Visiteur";
if (isset($_SESSION['user_prenom'])) {
    $prenom_utilisateur = $_SESSION['user_prenom'];
}
?>


<div class="container">
    <div class="card">
        <h1>Bienvenue sur LawApp, <?php echo htmlspecialchars($prenom_utilisateur); ?> !</h1>
        <p>Votre plateforme d'apprentissage du droit, simplifiée et accessible.</p>
        
        <div class="section">
            <h2>Résolution des problèmes de base de données</h2>
            <p>Cette page simplifiée a été créée pour résoudre les problèmes de base de données sur Render. Utilisez les liens ci-dessous pour accéder aux différentes fonctionnalités.</p>
            
            <div class="section">
                <h3>Outils de diagnostic et correction</h3>
                <div class="links-grid">
                    <a href="fix_all_tables.php" class="btn btn-primary">Corriger toutes les tables</a>
                    <a href="fix_render.php" class="btn btn-primary">Corriger Render</a>
                    <a href="test_db_connection.php" class="btn btn-primary">Test DB Connection</a>
                    <a href="test_render.php" class="btn btn-info">Test Render</a>
                    <a href="debug_render.php" class="btn btn-info">Debug Render</a>
                    <a href="check_admin_path.php" class="btn btn-info">Vérifier Admin</a>
                </div>
            </div>
            
            <div class="section">
                <h3>Scripts de correction spécifiques</h3>
                <div class="links-grid">
                    <a href="add_column_id_categorie.php" class="btn">Ajouter colonne id_categorie</a>
                    <a href="create_categories_tables.php" class="btn">Créer tables catégories</a>
                    <a href="fix_categories_tables_pg.php" class="btn btn-warning">Corriger tables catégories PG</a>
                    <a href="fix_admin_table.php" class="btn">Corriger table admin</a>
                    <a href="fix_admin_table_pg.php" class="btn btn-warning">Corriger table admin PG</a>
                    <a href="fix_podcasts_table.php" class="btn">Corriger table podcasts</a>
                    <a href="fix_livres_table.php" class="btn">Corriger table livres</a>
                </div>
            </div>
            
            <div class="section">
                <h3>Accès rapide</h3>
                <div class="links-grid">
                    <a href="livres.php" class="btn">Livres</a>
                    <a href="podcasts.php" class="btn">Podcasts</a>
                    <a href="cours.php" class="btn">Cours</a>
                    <a href="admin/login.php" class="btn">Admin</a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
require_once 'includes/footer.php'; // Inclut le footer commun
?>
