<?php
// admin/course_category_form.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/../includes/header_admin.php';

$page_title = 'Ajouter/Modifier une Catégorie de Cours';

// Initialisation des variables
$category_id = null;
$nom_categorie = '';
$description = '';
$form_action = 'course_category_actions.php?action=add';
$submit_button_text = 'Ajouter la Catégorie';

// Vérifier si un ID est passé pour la modification
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $category_id = (int)$_GET['id'];
    $page_title = 'Modifier la Catégorie de Cours';
    $form_action = 'course_category_actions.php?action=edit&id=' . $category_id;
    $submit_button_text = 'Mettre à jour la Catégorie';

    try {
        $stmt = $pdo->prepare("SELECT nom_categorie, description FROM categories_cours WHERE id = :id");
        $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($category) {
            $nom_categorie = $category['nom_categorie'];
            $description = $category['description'];
        } else {
            $_SESSION['error_message'] = "Catégorie non trouvée.";
            header("Location: manage_course_categories.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de la catégorie de cours : " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors du chargement de la catégorie.";
        header("Location: manage_course_categories.php");
        exit;
    }
}

// Récupérer les messages de la session
$error_message = $_SESSION['error_message'] ?? null;
$success_message = $_SESSION['success_message'] ?? null;
$form_data = $_SESSION['form_data'] ?? [];

unset($_SESSION['error_message'], $_SESSION['success_message'], $_SESSION['form_data']);

// Remplir avec les données du formulaire en cas d'erreur
$nom_categorie = $form_data['nom_categorie'] ?? $nom_categorie;
$description = $form_data['description'] ?? $description;

?>

<div class="container-fluid">
    <h1 class="mt-4"><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Détails de la Catégorie
        </div>
        <div class="card-body">
            <form action="<?php echo $form_action; ?>" method="POST">
                <div class="mb-3">
                    <label for="nom_categorie" class="form-label">Nom de la Catégorie <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom_categorie" name="nom_categorie" 
                           value="<?php echo htmlspecialchars($nom_categorie); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $submit_button_text; ?></button>
                <a href="manage_course_categories.php" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer_admin.php';
?>
