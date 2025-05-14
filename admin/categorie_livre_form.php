<?php
$page_title = "Formulaire Catégorie Livre"; // Titre dynamique
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/admin_header.php';

// Initialisation
$action = $_GET['action'] ?? 'add';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$form_action_url = 'categorie_livre_actions.php';

$category_data = [
    'id' => null,
    'nom_categorie' => ''
];
$error_message = '';
$form_page_title = ($action === 'edit') ? "Modifier la catégorie" : "Ajouter une nouvelle catégorie";

// Si action = 'edit', récupérer les données
if ($action === 'edit' && $category_id) {
    try {
        $stmt = $pdo->prepare("SELECT id, nom_categorie FROM categories_livres WHERE id = ?");
        $stmt->execute([$category_id]);
        $category_data_db = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($category_data_db) {
            $category_data = $category_data_db;
        } else {
            $_SESSION['error_message'] = "Catégorie non trouvée.";
            header("Location: manage_categories_livres.php");
            exit;
        }
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la récupération de la catégorie : " . $e->getMessage();
        $category_data['id'] = $category_id; // Conserver l'ID pour le formulaire même en cas d'erreur
    }
} elseif ($action !== 'add') {
    $_SESSION['error_message'] = "Action non valide.";
    header("Location: manage_categories_livres.php");
    exit;
}

$page_title = $form_page_title;

// Récupérer les messages et données flash éventuels
$form_error_message = $_SESSION['form_error_message'] ?? null;
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_error_message'], $_SESSION['form_data']);

if (!empty($form_data)) {
    $category_data = array_merge($category_data, $form_data);
}
if ($form_error_message) {
    $error_message .= $form_error_message;
}

?>

<div class="admin-content">
    <h1><?php echo htmlspecialchars($form_page_title); ?></h1>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form action="<?php echo $form_action_url; ?>" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
        <?php if ($action === 'edit' && $category_data['id']): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($category_data['id']); ?>">
        <?php endif; ?>

        <div class="form-group mb-3">
            <label for="nom_categorie">Nom de la Catégorie <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nom_categorie" name="nom_categorie" value="<?php echo htmlspecialchars($category_data['nom_categorie']); ?>" required>
            <div class="invalid-feedback">Le nom de la catégorie est requis.</div>
        </div>

        <button type="submit" class="btn btn-success">
            <?php echo ($action === 'edit') ? 'Mettre à jour la catégorie' : 'Enregistrer la catégorie'; ?>
        </button>
        <a href="manage_categories_livres.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<script>
// Validation Bootstrap simple
(function() {
  'use strict';
  window.addEventListener('load', function() {
    var forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
</script>

<?php
require_once __DIR__ . '/admin_footer.php';
?>
