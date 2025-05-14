<?php
$page_title = "Formulaire Cours"; // Sera mis à jour dynamiquement
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/admin_header.php';

// Initialisation des variables
$action = $_GET['action'] ?? 'add';
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$form_action_url = 'course_actions.php';

$course_data = [
    'id' => null,
    'titre' => '',
    'description' => '',
    'contenu_principal' => '',
    'id_categorie' => null,
    'niveau' => '',
    'id_createur' => $_SESSION['admin_id'] ?? null, // Pré-remplir avec l'admin connecté pour un nouveau cours
    'image_url' => '',
    'statut' => 'brouillon',
    'duree_estimee' => '',
    'prix' => null // Initialisation du prix
];
$error_message = '';
$form_page_title = "Ajouter un nouveau cours";

// Récupérer les catégories de cours
try {
    $stmt_categories = $pdo->query("SELECT id, nom_categorie FROM categories_cours ORDER BY nom_categorie");
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message .= "Erreur lors de la récupération des catégories: " . $e->getMessage() . "<br>";
    $categories = [];
}

// Récupérer les utilisateurs (pour le champ créateur)
try {
    $stmt_users = $pdo->query("SELECT id, CONCAT(prenom, ' ', nom) AS full_name FROM utilisateurs ORDER BY nom, prenom");
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message .= "Erreur lors de la récupération des utilisateurs: " . $e->getMessage() . "<br>";
    $users = [];
}


if ($action === 'edit' && $course_id) {
    $form_page_title = "Modifier le cours";
    try {
        $stmt = $pdo->prepare("SELECT * FROM cours WHERE id = ?");
        $stmt->execute([$course_id]);
        $course_data_db = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course_data_db) {
            // Fusionner les données de la BDD avec les valeurs par défaut pour s'assurer que toutes les clés existent
            $course_data = array_merge($course_data, $course_data_db);
        } else {
            $_SESSION['error_message'] = "Cours non trouvé.";
            header("Location: manage_courses.php");
            exit;
        }
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la récupération du cours : " . $e->getMessage();
    }
} elseif ($action !== 'add') {
    $_SESSION['error_message'] = "Action non valide.";
    header("Location: manage_courses.php");
    exit;
}
$page_title = $form_page_title; // Met à jour le titre de la page pour l'en-tête

// Récupérer les messages flash potentiels
$form_error_message = $_SESSION['form_error_message'] ?? null;
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_error_message'], $_SESSION['form_data']);

// Si des données de formulaire en session existent (après une erreur de soumission), les utiliser
if (!empty($form_data)) {
    $course_data = array_merge($course_data, $form_data);
}
if ($form_error_message) {
    $error_message .= $form_error_message;
}

?>

<div class="admin-content">
    <h1><?php echo htmlspecialchars($form_page_title); ?></h1>

    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; // Déjà échappé ou messages système non dangereux ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $form_action_url; ?>" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
        <?php if ($action === 'edit' && $course_data['id']): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($course_data['id']); ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="titre">Titre du cours <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($course_data['titre']); ?>" required>
            <div class="invalid-feedback">Le titre est requis.</div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($course_data['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="contenu_principal">Contenu Principal</label>
            <textarea class="form-control" id="contenu_principal" name="contenu_principal" rows="10"><?php echo htmlspecialchars($course_data['contenu_principal']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="id_categorie">Catégorie</label>
            <select class="form-control" id="id_categorie" name="id_categorie">
                <option value="">-- Sélectionner une catégorie --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($course_data['id_categorie'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['nom_categorie']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="niveau">Niveau</label>
            <input type="text" class="form-control" id="niveau" name="niveau" value="<?php echo htmlspecialchars($course_data['niveau']); ?>">
            <small class="form-text text-muted">Ex: Débutant, Intermédiaire, Avancé, Tous niveaux</small>
        </div>

        <div class="form-group">
            <label for="id_createur">Créateur <span class="text-danger">*</span></label>
            <select class="form-control" id="id_createur" name="id_createur" required>
                <option value="">-- Sélectionner un créateur --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo ($course_data['id_createur'] == $user['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['full_name'] . " (ID: " . $user['id'] . ")"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Le créateur est requis.</div>
        </div>

        <!-- Nouveau champ pour l'upload de l'image de couverture -->
        <div class="form-group">
            <label for="image_couverture">Image de Couverture</label>
            <?php if ($action === 'edit' && !empty($course_data['image_url'])):
 ?>
                <div class="mb-2">
                    <p class="mb-1">Image actuelle :</p>
                    <img src="../<?php echo htmlspecialchars($course_data['image_url']); ?>" alt="Image actuelle" style="max-height: 100px; border-radius: 4px;">
                    <p class="small text-muted mt-1">Chemin: <?php echo htmlspecialchars($course_data['image_url']); ?></p>
                    <p class="small text-muted">Laissez vide pour ne pas modifier ou choisissez un nouveau fichier pour remplacer.</p>
                </div>
            <?php endif; ?>
            <input type="file" class="form-control-file" id="image_couverture" name="image_couverture" accept="image/png, image/jpeg, image/gif">
            <small class="form-text text-muted">Formats autorisés : PNG, JPG/JPEG, GIF. Taille max : 2Mo recommandé.</small>
        </div>

        <div class="form-group">
            <label for="statut">Statut <span class="text-danger">*</span></label>
            <select class="form-control" id="statut" name="statut" required>
                <option value="brouillon" <?php echo ($course_data['statut'] === 'brouillon') ? 'selected' : ''; ?>>Brouillon</option>
                <option value="publie" <?php echo ($course_data['statut'] === 'publie') ? 'selected' : ''; ?>>Publié</option>
                <option value="archive" <?php echo ($course_data['statut'] === 'archive') ? 'selected' : ''; ?>>Archivé</option>
            </select>
            <div class="invalid-feedback">Le statut est requis.</div>
        </div>
        
        <div class="form-group">
            <label for="duree_estimee">Durée Estimée</label>
            <input type="text" class="form-control" id="duree_estimee" name="duree_estimee" value="<?php echo htmlspecialchars($course_data['duree_estimee']); ?>">
            <small class="form-text text-muted">Ex: 10 heures, 3 semaines, 25 modules</small>
        </div>

        <div class="form-group">
            <label for="prix">Prix (€)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="prix" name="prix" value="<?php echo htmlspecialchars($course_data['prix'] ?? ''); ?>" placeholder="Laisser vide pour gratuit">
            <small class="form-text text-muted">Indiquer le prix en euros. Laisser vide ou 0 pour un cours gratuit.</small>
        </div>

        <button type="submit" class="btn btn-success">
            <?php echo ($action === 'edit') ? 'Mettre à jour le cours' : 'Enregistrer le cours'; ?>
        </button>
        <a href="manage_courses.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<script>
// Petite validation Bootstrap côté client
(function() {
  'use strict';
  window.addEventListener('load', function() {
    var forms = document.getElementsByClassName('needs-validation');
    var validation = Array.prototype.filter.call(forms, function(form) {
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
