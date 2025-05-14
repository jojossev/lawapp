<?php
session_start();
require_once __DIR__ . '/admin_auth_check.php'; // Auth check, $pdo, BASE_URL
require_once __DIR__ . '/../utils/redirect_helpers.php';

// admin_auth_check.php gère l'authentification.

$pageTitle = 'Ajouter/Modifier une Catégorie de Cours';
$categorie_id = $_GET['id'] ?? null;
$categorie = null;
$formAction = 'categorie_cours_actions.php';
$formMethod = 'POST';
$action = 'add';

// Si un ID est présent, on est en mode modification
if ($categorie_id) {
    $action = 'edit';
    try {
        $stmt = $pdo->prepare("SELECT id, nom_categorie, description FROM categories_cours WHERE id = ?");
        $stmt->execute([$categorie_id]);
        $categorie = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$categorie) {
            $_SESSION['error_message_cat_cours'] = "Catégorie non trouvée.";
            redirect('manage_categories_cours.php');
        }
        $pageTitle = 'Modifier la Catégorie: ' . htmlspecialchars($categorie['nom_categorie']);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de la catégorie de cours: " . $e->getMessage());
        $_SESSION['error_message_cat_cours'] = "Impossible de charger la catégorie pour modification.";
        redirect('manage_categories_cours.php');
    }
} else {
    $pageTitle = 'Ajouter une Catégorie de Cours';
    // Pré-remplir avec les données de la session en cas d'erreur de validation précédente
    $categorie = $_SESSION['form_data_cat_cours'] ?? ['nom_categorie' => '', 'description' => ''];
    unset($_SESSION['form_data_cat_cours']);
}

// Récupérer les messages d'erreur de la session
$errorMessage = $_SESSION['error_message_cat_cours'] ?? null;
unset($_SESSION['error_message_cat_cours']);

include 'admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $formAction; ?>" method="<?php echo $formMethod; ?>">
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <?php if ($categorie_id): ?>
            <input type="hidden" name="categorie_id" value="<?php echo htmlspecialchars($categorie['id']); ?>">
        <?php endif; ?>

        <div class="form-group mb-3">
            <label for="nom_categorie">Nom de la Catégorie</label>
            <input type="text" class="form-control" id="nom_categorie" name="nom_categorie" 
                   value="<?php echo htmlspecialchars($categorie['nom_categorie'] ?? ''); ?>" required>
        </div>

        <div class="form-group mb-3">
            <label for="description">Description (Optionnel)</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($categorie['description'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            <?php echo ($categorie_id) ? 'Mettre à jour' : 'Ajouter la catégorie'; ?>
        </button>
        <a href="manage_categories_cours.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include 'admin_footer.php'; ?>
