<?php
session_start();
require_once __DIR__ . '/admin_auth_check.php'; // Auth check, $pdo, BASE_URL
require_once __DIR__ . '/../utils/redirect_helpers.php';

// admin_auth_check.php gère l'authentification.

$pageTitle = 'Ajouter/Modifier un Cours';
$cours_id = $_GET['id'] ?? null;
$cours = null;
$formAction = 'cours_actions.php';
$formMethod = 'POST';
$action = 'add';
$formEnctype = 'multipart/form-data'; // Important pour l'upload de fichiers

// Récupérer les catégories de cours pour le select
try {
    $stmtCat = $pdo->query("SELECT id, nom FROM categories_cours ORDER BY nom ASC");
    $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur récupération catégories cours pour formulaire: " . $e->getMessage());
    $_SESSION['error_message_cours'] = "Impossible de charger les catégories.";
    $categories = [];
    // Rediriger si les catégories sont essentielles ? Ou afficher une erreur dans le form ?
    // Pour l'instant, on laisse le formulaire s'afficher mais le select sera vide/désactivé.
}

// Si un ID est présent, on est en mode modification
if ($cours_id) {
    $action = 'edit';
    try {
        $stmt = $pdo->prepare("SELECT * FROM cours WHERE id = ?");
        $stmt->execute([$cours_id]);
        $cours = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cours) {
            $_SESSION['error_message_cours'] = "Cours non trouvé.";
            redirect('manage_cours.php');
        }
        $pageTitle = 'Modifier le Cours: ' . htmlspecialchars($cours['titre']);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du cours: " . $e->getMessage());
        $_SESSION['error_message_cours'] = "Impossible de charger le cours pour modification.";
        redirect('manage_cours.php');
    }
} else {
    $pageTitle = 'Ajouter un Cours';
    // Pré-remplir avec les données de la session en cas d'erreur de validation précédente
    $cours = $_SESSION['form_data_cours'] ?? [
        'titre' => '', 'description' => '', 'id_categorie' => null, 'niveau' => 'debutant', 
        'duree_estimee' => '', 'prix' => '0.00', 'statut' => 'brouillon', 'date_publication' => null,
        'image_url' => null, 'video_intro_url' => null
    ];
    unset($_SESSION['form_data_cours']);
}

$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

include 'admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $formAction; ?>" method="<?php echo $formMethod; ?>" enctype="<?php echo $formEnctype; ?>">
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <?php if ($cours_id): ?>
            <input type="hidden" name="cours_id" value="<?php echo htmlspecialchars($cours['id']); ?>">
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="form-group mb-3">
                    <label for="titre">Titre du Cours</label>
                    <input type="text" class="form-control" id="titre" name="titre" 
                           value="<?php echo htmlspecialchars($cours['titre'] ?? ''); ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="6"><?php echo htmlspecialchars($cours['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="contenu_principal">Contenu Principal</label>
                    <textarea class="form-control" id="contenu_principal" name="contenu_principal" rows="10"><?php echo htmlspecialchars($cours['contenu_principal'] ?? ''); ?></textarea>
                    <small class="form-text text-muted">Vous pouvez utiliser du HTML simple ici.</small>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="id_categorie">Catégorie</label>
                        <select class="form-control" id="id_categorie" name="id_categorie" <?php echo empty($categories) ? 'disabled' : ''; ?>>
                            <option value="">-- Sélectionner une catégorie --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo (isset($cours['id_categorie']) && $cours['id_categorie'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($categories)): ?>
                            <small class="text-danger">Impossible de charger les catégories.</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 form-group mb-3">
                        <label for="niveau">Niveau</label>
                        <select class="form-control" id="niveau" name="niveau">
                            <option value="debutant" <?php echo (isset($cours['niveau']) && $cours['niveau'] == 'debutant') ? 'selected' : ''; ?>>Débutant</option>
                            <option value="intermediaire" <?php echo (isset($cours['niveau']) && $cours['niveau'] == 'intermediaire') ? 'selected' : ''; ?>>Intermédiaire</option>
                            <option value="avance" <?php echo (isset($cours['niveau']) && $cours['niveau'] == 'avance') ? 'selected' : ''; ?>>Avancé</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="duree_estimee">Durée Estimée (ex: 10 heures)</label>
                        <input type="text" class="form-control" id="duree_estimee" name="duree_estimee" 
                               value="<?php echo htmlspecialchars($cours['duree_estimee'] ?? ''); ?>">
                    </div>
                     <div class="col-md-6 form-group mb-3">
                        <label for="prix">Prix (€)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="prix" name="prix" 
                               value="<?php echo htmlspecialchars($cours['prix'] ?? '0.00'); ?>" required>
                    </div>
                </div>

                </div>

            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Publication</div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="statut">Statut</label>
                            <select class="form-control" id="statut" name="statut">
                                <option value="brouillon" <?php echo (isset($cours['statut']) && $cours['statut'] == 'brouillon') ? 'selected' : ''; ?>>Brouillon</option>
                                <option value="publie" <?php echo (isset($cours['statut']) && $cours['statut'] == 'publie') ? 'selected' : ''; ?>>Publié</option>
                                <option value="archive" <?php echo (isset($cours['statut']) && $cours['statut'] == 'archive') ? 'selected' : ''; ?>>Archivé</option>
                            </select>
                        </div>
                        <!-- Date de publication retirée, gérée par date_creation/mise_a_jour -->
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">Image de Couverture</div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="image_cours">Télécharger une nouvelle image</label>
                            <input type="file" class="form-control-file" id="image_cours" name="image_cours" accept="image/jpeg, image/png, image/gif">
                            <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB.</small>
                        </div>
                        <?php if (!empty($cours['image_url'])): ?>
                            <p>Image actuelle :</p>
                            <img src="<?php echo htmlspecialchars(BASE_URL . '/' . $cours['image_url']); ?>" alt="Image actuelle" class="img-thumbnail" style="max-width: 200px;">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="supprimer_image" name="supprimer_image">
                                <label class="form-check-label" for="supprimer_image">
                                    Supprimer l'image actuelle
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <hr>
        <button type="submit" class="btn btn-primary">
            <?php echo ($cours_id) ? 'Mettre à jour le cours' : 'Ajouter le cours'; ?>
        </button>
        <a href="manage_cours.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include 'admin_footer.php'; ?>
