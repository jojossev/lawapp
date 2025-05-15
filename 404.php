<?php
$page_title = "Page non trouvée";
$extra_css = "<link rel='stylesheet' href='css/error.css'>";
require_once 'includes/header.php';
?>

<div class="error-container animate-fadeIn">
    <div class="error-content glass">
        <h1 class="error-title gradient-text">404</h1>
        <h2>Page non trouvée</h2>
        <p>Désolé, la page que vous recherchez n'existe pas ou a été déplacée.</p>
        <div class="error-actions">
            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary hover-scale">Retour à l'accueil</a>
            <a href="<?php echo BASE_URL; ?>/contact.php" class="btn btn-outline hover-lift">Contactez-nous</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
