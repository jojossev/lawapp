<?php
$page_title = "Paiement réussi";
$extra_css = "<link rel='stylesheet' href='css/auth.css'>";
require_once 'includes/header.php';

// Vérifier si on vient bien d'un paiement réussi
if (!isset($_SESSION['payment_success']) || !isset($_SESSION['transaction_id'])) {
    header('Location: accueil.php');
    exit;
}

$transaction_id = $_SESSION['transaction_id'];

// Nettoyer les variables de session
unset($_SESSION['payment_success']);
unset($_SESSION['transaction_id']);
?>

<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <div class="success-icon animate-bounce">
                <img src="assets/icons/check-circle.svg" alt="Succès" width="64" height="64">
            </div>
            <h1 class="gradient-text animate-slideIn">Paiement réussi !</h1>
            <p class="auth-subtitle animate-fadeIn delay-200">
                Votre transaction a été effectuée avec succès.
            </p>
        </div>

        <div class="transaction-details animate-fadeIn delay-300">
            <div class="detail-item">
                <span class="detail-label">ID Transaction :</span>
                <span class="detail-value"><?php echo htmlspecialchars($transaction_id); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Date :</span>
                <span class="detail-value"><?php echo date('d/m/Y H:i'); ?></span>
            </div>
        </div>

        <div class="action-buttons animate-fadeIn delay-400">
            <a href="mes_achats.php" class="btn btn-primary hover-scale">
                Voir mes achats
            </a>
            <a href="index.php" class="btn btn-outline hover-lift">
                Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
