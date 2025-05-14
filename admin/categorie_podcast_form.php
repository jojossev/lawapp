<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once 'admin_auth_check.php';
require_once 'admin_includes/admin_functions.php';

$action = $_GET['action'] ?? 'add'; // 'add' ou 'edit'
$categorie_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$categorie_data = [
    'nom_categorie' => '',
    'description' => ''
];
$form_errors = $_SESSION['form_errors_cat_podcast'] ?? [];
$form_data_old = $_SESSION['form_data_cat_podcast'] ?? [];

if ($action === 'edit' && $categorie_id) {
    $page_title = "Modifier la Catégorie de Podcast";
    try {
        $stmt = $pdo->prepare("SELECT nom_categorie, description FROM categories_podcasts WHERE id = :id");
        $stmt->bindParam(':id', $categorie_id, PDO::PARAM_INT);
        $stmt->execute();
        $categorie_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$categorie_data) {
            redirectWithError('cat_podcast', "Catégorie non trouvée.");
        }
    } catch (PDOException $e) {
        redirectWithError('cat_podcast', "Erreur lors de la récupération de la catégorie: " . $e->getMessage());
    }
} elseif ($action === 'add') {
    $page_title = "Ajouter une Catégorie de Podcast";
    $categorie_data = $form_data_old ?: $categorie_data; // Pré-remplir si erreurs précédentes
} else {
    redirectWithError('cat_podcast', "Action non valide.");
}

// Nettoyer les données de session après utilisation
if (isset($_SESSION['form_errors_cat_podcast'])) unset($_SESSION['form_errors_cat_podcast']);
if (isset($_SESSION['form_data_cat_podcast'])) unset($_SESSION['form_data_cat_podcast']);

require_once 'admin_header.php';
?>

<div class="container admin-container">
    <h2 class="admin-title"><?php echo $page_title; ?></h2>

    <?php if (!empty($form_errors)): ?>
        <div class="alert alert-danger">
            <strong>Erreurs de validation :</strong>
            <ul>
                <?php foreach ($form_errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="categorie_podcast_actions.php" method="POST" class="admin-form">
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="categorie_id" value="<?php echo $categorie_id; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="nom_categorie">Nom de la catégorie</label>
            <input type="text" id="nom_categorie" name="nom_categorie" class="form-control" value="<?php echo htmlspecialchars($categorie_data['nom_categorie']); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($categorie_data['description']); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo ($action === 'edit') ? 'Mettre à jour' : 'Ajouter'; ?></button>
            <a href="manage_categories_podcasts.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php
require_once 'admin_footer.php';
?>
