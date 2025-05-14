<?php
$page_title = "Modifier mon Profil";
require_once 'includes/config.php'; // Contient db_connect.php et session_start
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Veuillez vous connecter pour modifier votre profil.";
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$utilisateur = null;
$error_message = $_SESSION['error_message'] ?? null;
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['error_message'], $_SESSION['success_message']); // Nettoyer les messages

try {
    $stmt = $pdo->prepare("SELECT nom, prenom, email FROM utilisateurs WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$utilisateur) {
        // Gérer l'utilisateur non trouvé (devrait être rare si session valide)
        throw new Exception("Utilisateur non trouvé.");
    }
} catch (Exception $e) {
    error_log("Erreur chargement profil pour modification (user ID: {$user_id}): " . $e->getMessage());
    $error_message = "Impossible de charger vos informations. Veuillez réessayer.";
    // Optionnel : rediriger vers profil.php avec l'erreur
    $_SESSION['error_message'] = $error_message;
    header('Location: profil.php');
    exit;
}

?>

<div class="container page-content mt-4">
    <h2 class="page-title mb-4">Modifier mes informations</h2>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if ($utilisateur): ?>
    <form action="edit_profil_process.php" method="POST" class="card p-4">
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($utilisateur['prenom']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($utilisateur['nom']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($utilisateur['email']); ?>" required>
            <small class="form-text text-muted">Votre adresse email est utilisée pour la connexion et les notifications.</small>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="profil.php" class="btn btn-secondary ms-2">Annuler</a>
        </div>
    </form>
    <?php endif; ?>

</div>

<?php
require_once 'includes/footer.php';
?>
