<?php
$page_title = "Scripts d'administration";
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
    .back-link { display: inline-block; margin-bottom: 20px; }
</style>";
require_once 'includes/header.php';
?>

<div class="container">
    <div class="card">
        <a href="index.php" class="back-link">&larr; Retour à l'accueil</a>
        <h1>Scripts d'administration et de diagnostic</h1>
        <p>Cette page contient des liens vers les scripts de diagnostic et de correction de l'application LawApp. Ces scripts sont destinés aux administrateurs et développeurs uniquement.</p>
        
        <div class="section">
            <h2>Outils de diagnostic et correction</h2>
            <div class="links-grid">
                <a href="check_app_integrity.php" class="btn btn-info" style="font-weight: bold;">Vérifier l'intégrité de l'application</a>
                <a href="fix_all_tables.php" class="btn btn-primary">Corriger toutes les tables</a>
                <a href="fix_render.php" class="btn btn-primary">Corriger Render</a>
                <a href="test_db_connection.php" class="btn btn-primary">Test DB Connection</a>
                <a href="test_pg_connection.php" class="btn btn-primary">Test PostgreSQL</a>
                <a href="debug_postgres.php" class="btn btn-primary" style="font-weight: bold; background-color: #dc3545;">Diagnostic PostgreSQL</a>
                <a href="test_render.php" class="btn btn-info">Test Render</a>
                <a href="debug_render.php" class="btn btn-info">Debug Render</a>
                <a href="check_admin_path.php" class="btn btn-info">Vérifier Admin</a>
                <a href="test_sessions.php" class="btn btn-info">Test Sessions</a>
                <a href="check_render_sessions.php" class="btn btn-info">Sessions Render</a>
                <a href="test_redirections.php" class="btn btn-info">Test Redirections</a>
                <a href="fix_postgres_tables.php" class="btn btn-warning">Fix PostgreSQL Tables</a>
            </div>
        </div>
        
        <div class="section">
            <h2>Scripts de correction spécifiques</h2>
            <div class="links-grid">
                <a href="fix_database_name.php" class="btn btn-primary" style="font-weight: bold; font-size: 1.2em; background-color: #dc3545;">CORRIGER NOM BASE DE DONNÉES</a>
                <a href="fix_all_issues.php" class="btn btn-primary" style="font-weight: bold; font-size: 1.2em; background-color: #007bff;">CORRIGER TOUS LES PROBLÈMES</a>
                <a href="add_column_id_categorie.php" class="btn">Ajouter colonne id_categorie</a>
                <a href="create_categories_tables.php" class="btn">Créer tables catégories</a>
                <a href="fix_categories_tables_pg.php" class="btn btn-warning">Corriger tables catégories PG</a>
                <a href="fix_admin_table.php" class="btn">Corriger table admin</a>
                <a href="fix_admin_table_pg.php" class="btn btn-warning">Corriger table admin PG</a>
                <a href="fix_podcasts_table.php" class="btn">Corriger table podcasts</a>
                <a href="fix_livres_table.php" class="btn">Corriger table livres</a>
                <a href="fix_cours_table.php" class="btn">Corriger table cours</a>
                <a href="fix_videos_table.php" class="btn">Corriger table videos</a>
                <a href="fix_users_tables.php" class="btn">Corriger tables utilisateurs</a>
                <a href="fix_inscriptions_table.php" class="btn">Corriger table inscriptions</a>
                <a href="fix_foreign_keys.php" class="btn btn-warning">Corriger clés étrangères</a>
                <a href="fix_db_compatibility.php" class="btn btn-warning">Compatibilité MySQL/PostgreSQL</a>
                <a href="fix_db_performance.php" class="btn btn-info">Optimiser performances DB</a>
                <a href="fix_db_security.php" class="btn btn-danger">Sécurité base de données</a>
                <a href="fix_session_cookies.php" class="btn btn-danger">Vérifier sessions/cookies</a>
                <a href="fix_files_permissions.php" class="btn btn-danger">Vérifier fichiers/permissions</a>
                <a href="fix_redirections.php" class="btn btn-danger">Corriger redirections</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
