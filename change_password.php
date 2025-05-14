<?php
$page_title = "Changer mon mot de passe";
require_once 'includes/config.php'; // Contient db_connect.php et session_start
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Veuillez vous connecter pour changer votre mot de passe.";
    header('Location: login.php');
    exit;
}

$error_message = $_SESSION['error_message'] ?? null;
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['error_message'], $_SESSION['success_message']); // Nettoyer les messages

?>

<div class="container page-content mt-4">
    <h2 class="page-title mb-4">Changer mon mot de passe</h2>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <form action="change_password_process.php" method="POST" class="card p-4">
        <div class="mb-3">
            <label for="current_password" class="form-label">Mot de passe actuel</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>
        <hr>
        <div class="mb-3">
            <label for="new_password" class="form-label">Nouveau mot de passe</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
            <small class="form-text text-muted">Doit contenir au moins 8 caractères.</small>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            <a href="profil.php" class="btn btn-secondary ms-2">Annuler</a>
        </div>
    </form>

</div>

<?php
require_once 'includes/footer.php';
?>
