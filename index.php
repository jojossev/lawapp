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
            <h2>Bienvenue sur la plateforme LawApp</h2>
            <p>Votre portail d'apprentissage juridique en ligne. Accédez à des cours, des livres et des podcasts pour approfondir vos connaissances en droit.</p>
            

            
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
