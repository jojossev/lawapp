<?php
$page_title = "Accueil";
require_once 'includes/header.php';

?>

<div style="text-align: center; padding: 50px; max-width: 800px; margin: 0 auto;">
    <h1>Bienvenue sur LawApp</h1>
    <p>Votre plateforme d'apprentissage du droit, simplifiée et accessible.</p>
    
    <div style="margin: 30px 0;">
        <h2>Accès rapide</h2>
        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
            <a href="livres.php" style="text-decoration: none; padding: 15px 25px; background-color: #4a6fa5; color: white; border-radius: 5px;">Livres</a>
            <a href="podcasts_liste.php" style="text-decoration: none; padding: 15px 25px; background-color: #4a6fa5; color: white; border-radius: 5px;">Podcasts</a>
            <a href="admin/admin_login.php" style="text-decoration: none; padding: 15px 25px; background-color: #4a6fa5; color: white; border-radius: 5px;">Admin</a>
        </div>
    </div>
    
    <div style="margin: 30px 0;">
        <h2>Outils de diagnostic</h2>
        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
            <a href="test_render.php" style="text-decoration: none; padding: 15px 25px; background-color: #6c757d; color: white; border-radius: 5px;">Test Render</a>
            <a href="debug_render.php" style="text-decoration: none; padding: 15px 25px; background-color: #6c757d; color: white; border-radius: 5px;">Debug Render</a>
            <a href="check_admin_path.php" style="text-decoration: none; padding: 15px 25px; background-color: #6c757d; color: white; border-radius: 5px;">Check Admin</a>
        </div>
    </div>
    
    <div style="margin: 30px 0;">
        <h2>Correction de la base de données</h2>
        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
            <a href="create_categories_tables.php" style="text-decoration: none; padding: 15px 25px; background-color: #28a745; color: white; border-radius: 5px;">Créer tables catégories</a>
            <a href="add_column_id_categorie.php" style="text-decoration: none; padding: 15px 25px; background-color: #28a745; color: white; border-radius: 5px;">Ajouter colonne id_categorie</a>
            <a href="fix_livres_table.php" style="text-decoration: none; padding: 15px 25px; background-color: #28a745; color: white; border-radius: 5px;">Corriger table livres</a>
            <a href="fix_podcasts_table.php" style="text-decoration: none; padding: 15px 25px; background-color: #28a745; color: white; border-radius: 5px;">Corriger table podcasts</a>
            <a href="fix_admin_table.php" style="text-decoration: none; padding: 15px 25px; background-color: #28a745; color: white; border-radius: 5px;">Corriger table admin</a>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php'; // Inclut le footer commun
?>
