<?php
require_once __DIR__ . '/admin_auth_check.php'; // Auth et connexion BDD ($pdo)

$action = $_GET['action'] ?? 'add'; // 'add' or 'edit'
$video_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$page_title = ($action === 'edit' && $video_id) ? "Modifier la Vidéo" : "Ajouter une Vidéo";
require_once __DIR__ . '/admin_header.php';

// Valeurs par défaut et récupération des données du formulaire en cas d'erreur de soumission
$form_data = $_SESSION['form_data'] ?? [];
$form_error_message = $_SESSION['form_error_message'] ?? null;
unset($_SESSION['form_data'], $_SESSION['form_error_message']);

$video_data = [
    'id' => $video_id,
    'titre' => '',
    'description' => '',
    'url_video' => '',
    'duree' => '',
    'niveau' => '',
    'id_categorie_video' => null,
    'image_thumbnail_url' => '',
    'id_createur' => $_SESSION['user_id'], // Par défaut l'admin connecté
    'statut' => 'brouillon',
    'date_publication' => '' 
];

if ($action === 'edit' && $video_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
        $stmt->execute([$video_id]);
        $fetched_video = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fetched_video) {
            $video_data = array_merge($video_data, $fetched_video);
        } else {
            $_SESSION['error_message'] = "Vidéo non trouvée.";
            header("Location: manage_videos.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la récupération de la vidéo : " . $e->getMessage();
        header("Location: manage_videos.php");
        exit;
    }
}

// Surcharger avec les données du formulaire sauvegardées en cas d'erreur, si elles existent
if (!empty($form_data)) {
    $video_data = array_merge($video_data, $form_data);
     // Assurez-vous que les ID ne sont pas écrasés par des valeurs vides de $form_data s'ils n'y sont pas
    if (isset($form_data['id_categorie_video'])) $video_data['id_categorie_video'] = (int)$form_data['id_categorie_video'];
    if (isset($form_data['id_createur'])) $video_data['id_createur'] = (int)$form_data['id_createur'];
    if ($video_id && !isset($form_data['id'])) $video_data['id'] = $video_id; // S'assurer que l'ID est conservé en mode édition
}


// Récupérer les catégories de vidéos
try {
    $stmt_categories_videos = $pdo->query("SELECT id, nom_categorie FROM categories_videos ORDER BY nom_categorie");
    $categories_videos = $stmt_categories_videos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories_videos = [];
    $form_error_message = ($form_error_message ? $form_error_message . "<br>" : "") . "Erreur de chargement des catégories de vidéos: " . $e->getMessage();
}

// Récupérer les utilisateurs (créateurs potentiels)
try {
    $stmt_users = $pdo->query("SELECT id, CONCAT(prenom, ' ', nom) AS full_name FROM utilisateurs WHERE role IN ('admin', 'editeur') ORDER BY nom, prenom");
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $form_error_message = ($form_error_message ? $form_error_message . "<br>" : "") . "Erreur de chargement des utilisateurs: " . $e->getMessage();
}

// Options pour les listes déroulantes
$niveaux_options = ['Débutant', 'Intermédiaire', 'Avancé', 'Tous niveaux'];
$statuts_options = ['brouillon' => 'Brouillon', 'publie' => 'Publié', 'archive' => 'Archivé'];

?>

<div class="admin-content">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($form_error_message): ?>
        <div class="alert alert-danger">
            <strong>Erreur :</strong><br>
            <?php echo $form_error_message; // HTML est permis ici car il peut venir de la validation ?>
        </div>
    <?php endif; ?>

    <form action="video_actions.php" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <?php if ($video_id): ?>
            <input type="hidden" name="id" value="<?php echo $video_id; ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="titre" class="form-label">Titre de la vidéo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($video_data['titre']); ?>" required>
            <div class="invalid-feedback">Le titre est obligatoire.</div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($video_data['description']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="url_video" class="form-label">URL de la vidéo (YouTube, Vimeo, etc.) <span class="text-danger">*</span></label>
            <input type="url" class="form-control" id="url_video" name="url_video" value="<?php echo htmlspecialchars($video_data['url_video']); ?>" required placeholder="https://www.youtube.com/watch?v=...">
            <div class="invalid-feedback">Une URL valide est obligatoire.</div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="id_categorie_video" class="form-label">Catégorie de Vidéo</label>
                <select class="form-select" id="id_categorie_video" name="id_categorie_video">
                    <option value="">-- Aucune catégorie --</option>
                    <?php foreach ($categories_videos as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($video_data['id_categorie_video'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nom_categorie']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                 <div class="form-text">Si la catégorie n'existe pas, vous devrez la créer <a href="manage_video_categories.php">ici</a>.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="niveau" class="form-label">Niveau</label>
                <select class="form-select" id="niveau" name="niveau">
                    <option value="">-- Non spécifié --</option>
                    <?php foreach ($niveaux_options as $niveau_opt): ?>
                        <option value="<?php echo htmlspecialchars($niveau_opt); ?>" <?php echo ($video_data['niveau'] == $niveau_opt) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($niveau_opt); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="duree" class="form-label">Durée (ex: 15:30, 1h20m)</label>
                <input type="text" class="form-control" id="duree" name="duree" value="<?php echo htmlspecialchars($video_data['duree']); ?>" placeholder="HH:MM:SS ou texte libre">
            </div>
             <div class="col-md-6 mb-3">
                <label for="image_thumbnail_url" class="form-label">URL de l'image miniature</label>
                <input type="url" class="form-control" id="image_thumbnail_url" name="image_thumbnail_url" value="<?php echo htmlspecialchars($video_data['image_thumbnail_url']); ?>" placeholder="https://example.com/image.jpg">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="id_createur" class="form-label">Créateur <span class="text-danger">*</span></label>
                <select class="form-select" id="id_createur" name="id_createur" required>
                    <option value="">-- Sélectionnez un créateur --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo ($video_data['id_createur'] == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['full_name']); ?> (ID: <?php echo $user['id']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Le créateur est obligatoire.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                <select class="form-select" id="statut" name="statut" required>
                    <?php foreach ($statuts_options as $val => $label): ?>
                        <option value="<?php echo htmlspecialchars($val); ?>" <?php echo ($video_data['statut'] == $val) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Le statut est obligatoire.</div>
            </div>
        </div>

        <div class="mb-3">
            <label for="date_publication" class="form-label">Date de Publication (optionnel)</label>
            <input type="datetime-local" class="form-control" id="date_publication" name="date_publication" 
                   value="<?php echo !empty($video_data['date_publication']) ? (new DateTime($video_data['date_publication']))->format('Y-m-d\TH:i') : ''; ?>">
            <div class="form-text">Laissez vide pour une publication immédiate si le statut est "Publié". Format attendu pour la BDD si non vide: YYYY-MM-DD HH:MM:SS</div>
        </div>


        <button type="submit" class="btn btn-primary">
            <?php echo ($action === 'edit') ? 'Mettre à jour la vidéo' : 'Ajouter la vidéo'; ?>
        </button>
        <a href="manage_videos.php" class="btn btn-secondary">Annuler</a>
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
