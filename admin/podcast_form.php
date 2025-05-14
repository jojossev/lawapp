<?php
session_start();
require_once '../db_connect.php';
require_once 'admin_auth_check.php';
require_once 'admin_includes/admin_functions.php'; 

$action = $_GET['action'] ?? 'add'; // 'add' ou 'edit'
$podcast_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$podcast_data = [];
$categories = [];
$form_errors = $_SESSION['form_errors_podcast'] ?? [];
$form_data = $_SESSION['form_data_podcast'] ?? [];
$error_message = '';
$pageTitle = ($action === 'edit') ? "Modifier le Podcast" : "Ajouter un Podcast";

// Récupérer les catégories de podcasts pour la liste déroulante
try {
    $stmt_cat = $pdo->query("SELECT id, nom_categorie FROM categories_podcasts ORDER BY nom_categorie ASC");
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur récupération catégories podcasts: " . $e->getMessage());
    $error_message = "Impossible de charger les catégories de podcasts.";
}

// Si action = 'edit', récupérer les données du podcast existant
if ($action === 'edit' && $podcast_id) {
    if (empty($form_data)) { // Charger depuis la DB seulement si pas de données de formulaire en session (après erreur)
        try {
            $stmt = $pdo->prepare("SELECT * FROM podcasts WHERE id = ?");
            $stmt->execute([$podcast_id]);
            $podcast_data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$podcast_data) {
                redirectWithErrorPodcast("Podcast non trouvé."); // Fonction à créer ou message direct
            }
        } catch (PDOException $e) {
            error_log("Erreur récupération podcast pour édition: " . $e->getMessage());
            redirectWithErrorPodcast("Impossible de charger les données du podcast pour modification.");
        }
    } else {
        // Utiliser les données du formulaire en session (après une erreur de validation)
        $podcast_data = $form_data;
    }
} elseif ($action === 'add' && !empty($form_data)) {
    // Si ajout et erreur précédente, re-peupler avec les anciennes données
    $podcast_data = $form_data;
}

// Nettoyer les variables de session après utilisation
unset($_SESSION['form_errors_podcast']);
unset($_SESSION['form_data_podcast']);

include 'admin_header.php';
?>

<div class="container mt-4">
    <h2><?php echo $pageTitle; ?></h2>

    <?php if (!empty($_SESSION['error_message_podcast'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error_message_podcast']); unset($_SESSION['error_message_podcast']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($form_errors)): ?>
        <div class="alert alert-warning">
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul>
                <?php foreach ($form_errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="podcast_actions.php" method="post" enctype="multipart/form-data"> 
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <?php if ($action === 'edit'): ?>
            <input type="hidden" name="podcast_id" value="<?php echo htmlspecialchars($podcast_id ?? ''); ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="titre_episode" class="form-label">Titre de l'épisode <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="titre_episode" name="titre_episode" 
                   value="<?php echo htmlspecialchars($podcast_data['titre_episode'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="nom_podcast_serie" class="form-label">Nom de la série de podcasts</label>
            <input type="text" class="form-control" id="nom_podcast_serie" name="nom_podcast_serie"
                   value="<?php echo htmlspecialchars($podcast_data['nom_podcast_serie'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="auteur" class="form-label">Auteur / Intervenant</label>
            <input type="text" class="form-control" id="auteur" name="auteur"
                   value="<?php echo htmlspecialchars($podcast_data['auteur'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($podcast_data['description'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="id_categorie" class="form-label">Catégorie</label>
            <select class="form-select" id="id_categorie" name="id_categorie">
                <option value="">-- Sélectionner une catégorie --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"
                        <?php echo (isset($podcast_data['id_categorie']) && $podcast_data['id_categorie'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['nom_categorie']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
             <label for="fichier_audio" class="form-label">Fichier Audio (MP3, etc.) <?php echo ($action == 'add') ? '<span class="text-danger">*</span>' : '(Laisser vide pour ne pas changer)'; ?></label>
             <input type="file" class="form-control" id="fichier_audio" name="fichier_audio" accept="audio/*" <?php echo ($action == 'add') ? 'required' : ''; ?>>
             <?php if ($action == 'edit' && !empty($podcast_data['url_audio'])): ?>
                 <small class="form-text text-muted">Actuel: <?php echo htmlspecialchars($podcast_data['url_audio']); ?></small>
             <?php endif; ?>
        </div>

        <div class="mb-3">
             <label for="image_cover" class="form-label">Image de Couverture (JPG, PNG, etc.) <?php echo ($action == 'edit' && !empty($podcast_data['image_url'])) ? '(Laisser vide pour ne pas changer)' : ''; ?></label>
             <input type="file" class="form-control" id="image_cover" name="image_cover" accept="image/*">
             <?php if ($action == 'edit' && !empty($podcast_data['image_url'])): ?>
                 <small class="form-text text-muted">Actuelle: <?php echo htmlspecialchars($podcast_data['image_url']); ?></small>
                 <img src="../<?php echo htmlspecialchars($podcast_data['image_url']); ?>" alt="Cover actuelle" style="max-height: 50px; margin-left: 10px;">
             <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="duree_secondes" class="form-label">Durée (en secondes)</label>
            <input type="number" class="form-control" id="duree_secondes" name="duree_secondes"
                   value="<?php echo htmlspecialchars($podcast_data['duree_secondes'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="date_publication_episode" class="form-label">Date de Publication</label>
            <input type="datetime-local" class="form-control" id="date_publication_episode" name="date_publication_episode"
                   value="<?php echo isset($podcast_data['date_publication_episode']) ? date('Y-m-d\TH:i', strtotime($podcast_data['date_publication_episode'])) : date('Y-m-d\TH:i'); ?>">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="est_publie" name="est_publie" value="1"
                <?php echo (isset($podcast_data['est_publie']) && $podcast_data['est_publie']) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="est_publie">
                Publier ce podcast
            </label>
        </div>


        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="manage_podcasts.php" class="btn btn-secondary">Annuler</a>
    </form>

</div>

<?php include 'admin_footer.php'; ?>
