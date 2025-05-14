<?php
require_once __DIR__ . '/admin_auth_check.php';

$action = $_GET['action'] ?? 'add'; // 'add' or 'edit'
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$page_title = ($action === 'edit' && $category_id) ? "Modifier la Catégorie de Vidéo" : "Ajouter une Catégorie de Vidéo";
require_once __DIR__ . '/admin_header.php';

// Valeurs par défaut et récupération des données du formulaire en cas d'erreur
$form_data = $_SESSION['form_data'] ?? [];
$form_error_message = $_SESSION['form_error_message'] ?? null;
unset($_SESSION['form_data'], $_SESSION['form_error_message']);

$category_data = [
    'id' => $category_id,
    'nom_categorie' => '',
    'description_categorie' => ''
];

if ($action === 'edit' && $category_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories_videos WHERE id = ?");
        $stmt->execute([$category_id]);
        $fetched_category = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched_category) {
            $category_data = array_merge($category_data, $fetched_category);
        } else {
            $_SESSION['error_message'] = "Catégorie de vidéo non trouvée.";
            header("Location: manage_video_categories.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la récupération de la catégorie : " . $e->getMessage();
        header("Location: manage_video_categories.php");
        exit;
    }
}

// Surcharger avec les données du formulaire sauvegardées si elles existent
if (!empty($form_data)) {
    $category_data = array_merge($category_data, $form_data);
    if ($category_id && !isset($form_data['id'])) $category_data['id'] = $category_id;
}
?>

<div class="admin-content">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($form_error_message): ?>
        <div class="alert alert-danger">
            <strong>Erreur :</strong><br>
            <?php echo $form_error_message; ?>
        </div>
    <?php endif; ?>

    <form action="video_category_actions.php" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <?php if ($category_id): ?>
            <input type="hidden" name="id" value="<?php echo $category_id; ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="nom_categorie" class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nom_categorie" name="nom_categorie" 
                   value="<?php echo htmlspecialchars($category_data['nom_categorie']); ?>" required>
            <div class="invalid-feedback">Le nom de la catégorie est obligatoire.</div>
        </div>

        <div class="mb-3">
            <label for="description_categorie" class="form-label">Description</label>
            <textarea class="form-control" id="description_categorie" name="description_categorie" 
                      rows="4"><?php echo htmlspecialchars($category_data['description_categorie']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            <?php echo ($action === 'edit') ? 'Mettre à jour la catégorie' : 'Ajouter la catégorie'; ?>
        </button>
        <a href="manage_video_categories.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<script>
// Activer la validation Bootstrap standard
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>

<?php
require_once __DIR__ . '/admin_footer.php';
?>
