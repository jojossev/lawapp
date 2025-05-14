<?php
$page_title = "Formulaire Livre"; // Sera mis à jour dynamiquement
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/admin_header.php';

// Initialisation des variables
$action = $_GET['action'] ?? 'add';
$livre_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$form_action_url = 'livre_actions.php'; // Script qui traitera la soumission

$livre_data = [
    'id' => null,
    'titre' => '',
    'auteur' => '',
    'description' => '',
    'id_categorie_livre' => null, // Si vous avez des catégories de livres
    // 'image_url' => '', // Le chemin sera stocké après upload
    'statut' => 'brouillon', // 'brouillon', 'publie', 'archive'
    'fichier_pdf_url' => '' // Si vous stockez le PDF
];
$error_message = '';
$form_page_title = "Ajouter un nouveau livre";

// --- Placeholder: Récupérer les catégories de livres (si applicable) ---
$categories_livres = [];
try {
    $stmt_categories = $pdo->query("SELECT id, nom_categorie FROM categories_livres ORDER BY nom_categorie");
    $categories_livres = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message .= "Erreur lors de la récupération des catégories de livres: " . $e->getMessage() . "<br>";
}

if ($action === 'edit' && $livre_id) {
    $form_page_title = "Modifier le livre";
    try {
        $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
        $stmt->execute([$livre_id]);
        $livre_data_db = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($livre_data_db) {
            $livre_data = array_merge($livre_data, $livre_data_db);
        } else {
            $_SESSION['error_message'] = "Livre non trouvé.";
            header("Location: manage_livres.php"); // Vers la page de gestion des livres
            exit;
        }
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la récupération du livre : " . $e->getMessage();
        // S'assurer que $livre_data conserve sa structure par défaut si la BDD échoue ici
        $livre_data['id'] = $livre_id; // Garder l'ID pour le formulaire
    }
} elseif ($action !== 'add') {
    $_SESSION['error_message'] = "Action non valide.";
    header("Location: manage_livres.php");
    exit;
}
$page_title = $form_page_title;

// Récupérer les messages flash potentiels et données de formulaire
$form_error_message = $_SESSION['form_error_message'] ?? null;
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_error_message'], $_SESSION['form_data']);

if (!empty($form_data)) {
    $livre_data = array_merge($livre_data, $form_data);
}
if ($form_error_message) {
    $error_message .= $form_error_message;
}

?>

<div class="admin-content">
    <h1><?php echo htmlspecialchars($form_page_title); ?></h1>

    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; // Contient des messages système ou des erreurs déjà formatées ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $form_action_url; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
        <?php if ($action === 'edit' && $livre_data['id']): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($livre_data['id']); ?>">
        <?php endif; ?>

        <div class="form-group mb-3">
            <label for="titre">Titre du livre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($livre_data['titre']); ?>" required>
            <div class="invalid-feedback">Le titre est requis.</div>
        </div>

        <div class="form-group mb-3">
            <label for="auteur">Auteur</label>
            <input type="text" class="form-control" id="auteur" name="auteur" value="<?php echo htmlspecialchars($livre_data['auteur']); ?>">
        </div>

        <div class="form-group mb-3">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($livre_data['description']); ?></textarea>
        </div>
        
        <!-- Catégories de Livres -->
        <div class="form-group mb-3">
            <label for="id_categorie_livre">Catégorie du Livre</label>
            <select class="form-control" id="id_categorie_livre" name="id_categorie_livre">
                <option value="">-- Sélectionner une catégorie --</option>
                <?php if (!empty($categories_livres)): ?>
                    <?php foreach ($categories_livres as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($livre_data['id_categorie_livre'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['nom_categorie']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Aucune catégorie disponible</option> 
                <?php endif; ?>
            </select>
            <?php if (empty($categories_livres)): ?>
                <small class="form-text text-muted">Aucune catégorie de livre définie. <a href="manage_categories_livres.php">Gérer les catégories</a></small>
            <?php endif; ?>
        </div>


        <div class="form-group mb-3">
            <label for="image_couverture">Image de Couverture</label>
            <input type="file" class="form-control" id="image_couverture" name="image_couverture" accept="image/jpeg, image/png, image/gif">
            <?php if ($action === 'edit' && !empty($livre_data['image_url'])): ?>
                <small class="form-text text-muted">Image actuelle : <a href="../<?php echo htmlspecialchars($livre_data['image_url']); ?>" target="_blank"><?php echo htmlspecialchars(basename($livre_data['image_url'])); ?></a>. Laisser vide pour ne pas modifier.</small>
            <?php endif; ?>
            <div class="invalid-feedback">Veuillez sélectionner une image valide (jpg, png, gif).</div>
        </div>

        <div class="form-group mb-3">
            <label for="fichier_pdf_url">URL du fichier PDF (optionnel)</label>
            <input type="url" class="form-control" id="fichier_pdf_url" name="fichier_pdf_url" value="<?php echo htmlspecialchars($livre_data['fichier_pdf_url']); ?>" placeholder="https://example.com/livre.pdf">
            <small class="form-text text-muted">Lien direct vers le fichier PDF du livre, si hébergé ailleurs.</small>
        </div>

        <div class="form-group mb-3">
            <label for="statut">Statut <span class="text-danger">*</span></label>
            <select class="form-control" id="statut" name="statut" required>
                <option value="brouillon" <?php echo ($livre_data['statut'] === 'brouillon') ? 'selected' : ''; ?>>Brouillon</option>
                <option value="publie" <?php echo ($livre_data['statut'] === 'publie') ? 'selected' : ''; ?>>Publié</option>
                <option value="archive" <?php echo ($livre_data['statut'] === 'archive') ? 'selected' : ''; ?>>Archivé</option>
            </select>
            <div class="invalid-feedback">Le statut est requis.</div>
        </div>

        <button type="submit" class="btn btn-success">
            <?php echo ($action === 'edit') ? 'Mettre à jour le livre' : 'Enregistrer le livre'; ?>
        </button>
        <a href="manage_livres.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<script>
// Validation Bootstrap
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
