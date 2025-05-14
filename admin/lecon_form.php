<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/admin_includes/admin_functions.php';

// 1. Récupérer et valider l'ID du module depuis l'URL (obligatoire)
$module_id = filter_input(INPUT_GET, 'module_id', FILTER_VALIDATE_INT);
if (!$module_id) {
    $_SESSION['error_message'] = "ID de module manquant pour créer/modifier une leçon.";
    redirect('manage_cours.php');
}

// 2. Récupérer l'ID de la leçon (si modification)
$lecon_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$isEditing = ($lecon_id !== null && $lecon_id > 0);

// 3. Récupérer les informations du module parent et du cours
try {
    $sql_parent = "SELECT m.id AS module_id, m.titre AS module_titre, m.id_cours, c.titre AS cours_titre
                   FROM modules m
                   JOIN cours c ON m.id_cours = c.id
                   WHERE m.id = :module_id";
    $stmt_parent = $pdo->prepare($sql_parent);
    $stmt_parent->bindParam(':module_id', $module_id, PDO::PARAM_INT);
    $stmt_parent->execute();
    $parentInfo = $stmt_parent->fetch(PDO::FETCH_ASSOC);

    if (!$parentInfo) {
        $_SESSION['error_message'] = "Module parent non trouvé.";
        redirect('manage_cours.php');
    }
     $cours_id = $parentInfo['id_cours']; // Pour les liens retour

} catch (PDOException $e) {
    error_log("Erreur récupération module/cours parent (Lecon Form): " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors du chargement des informations du module parent.";
    redirect('manage_cours_contenu.php?cours_id=' . ($parentInfo['id_cours'] ?? 0)); // Tente redirection vers le contenu du cours
}


// 4. Initialiser les variables de formulaire
$lecon = [
    'titre' => '',
    'type_contenu' => 'texte', // Défaut
    'contenu_principal' => '',
    'ordre' => 0,
    'statut' => 'brouillon' // Défaut
];
$pageTitle = 'Ajouter une Leçon à "' . htmlspecialchars($parentInfo['module_titre']) . '"';
$action = 'add';

// 5. Si modification, récupérer les données de la leçon existante
if ($isEditing) {
    try {
        $sql_lecon = "SELECT * FROM lecons WHERE id = :lecon_id AND id_module = :module_id";
        $stmt_lecon = $pdo->prepare($sql_lecon);
        $stmt_lecon->bindParam(':lecon_id', $lecon_id, PDO::PARAM_INT);
        $stmt_lecon->bindParam(':module_id', $module_id, PDO::PARAM_INT); // Sécurité : vérifier que la leçon appartient bien au module
        $stmt_lecon->execute();
        $lecon_data = $stmt_lecon->fetch(PDO::FETCH_ASSOC);

        if ($lecon_data) {
            $lecon = $lecon_data;
            $pageTitle = 'Modifier la Leçon: ' . htmlspecialchars($lecon['titre']);
            $action = 'edit';
        } else {
            $_SESSION['error_message'] = "Leçon non trouvée ou n'appartient pas à ce module.";
            redirect('manage_module_contenu.php?module_id=' . $module_id);
        }
    } catch (PDOException $e) {
        error_log("Erreur récupération leçon (Lecon Form Edit): " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors du chargement de la leçon à modifier.";
        redirect('manage_module_contenu.php?module_id=' . $module_id);
    }
}

// 6. Gérer les valeurs de formulaire sauvegardées en cas d'erreur précédente
$form_values = $_SESSION['form_values'] ?? [];
if (!empty($form_values)) {
    $lecon['titre'] = $form_values['titre'] ?? $lecon['titre'];
    $lecon['type_contenu'] = $form_values['type_contenu'] ?? $lecon['type_contenu'];
    $lecon['contenu_principal'] = $form_values['contenu_principal'] ?? $lecon['contenu_principal'];
    $lecon['ordre'] = $form_values['ordre'] ?? $lecon['ordre'];
    $lecon['statut'] = $form_values['statut'] ?? $lecon['statut'];
    unset($_SESSION['form_values']); // Nettoyer après utilisation
}


$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

include 'admin_header.php';
?>

<div class="container mt-4">
     <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard Admin</a></li>
            <li class="breadcrumb-item"><a href="manage_cours.php">Gestion des Cours</a></li>
             <li class="breadcrumb-item"><a href="manage_cours_contenu.php?cours_id=<?php echo $cours_id; ?>"><?php echo htmlspecialchars($parentInfo['cours_titre']); ?></a></li>
            <li class="breadcrumb-item"><a href="manage_module_contenu.php?module_id=<?php echo $module_id; ?>"><?php echo htmlspecialchars($parentInfo['module_titre']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $isEditing ? 'Modifier Leçon' : 'Ajouter Leçon'; ?></li>
        </ol>
    </nav>

    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

    <?php if ($successMessage): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="lecon_actions.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
                <?php if ($isEditing): ?>
                    <input type="hidden" name="id" value="<?php echo $lecon_id; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="titre" class="form-label">Titre de la Leçon <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($lecon['titre']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="type_contenu" class="form-label">Type de Contenu <span class="text-danger">*</span></label>
                    <select class="form-select" id="type_contenu" name="type_contenu" required onchange="toggleContentFields()">
                        <option value="texte" <?php echo ($lecon['type_contenu'] === 'texte') ? 'selected' : ''; ?>>Texte</option>
                        <option value="video" <?php echo ($lecon['type_contenu'] === 'video') ? 'selected' : ''; ?>>Vidéo (URL/Embed)</option>
                        <option value="pdf" <?php echo ($lecon['type_contenu'] === 'pdf') ? 'selected' : ''; ?>>PDF</option>
                        <option value="docx" <?php echo ($lecon['type_contenu'] === 'docx') ? 'selected' : ''; ?>>Document Word (DOCX)</option>
                        <option value="mp3" <?php echo ($lecon['type_contenu'] === 'mp3') ? 'selected' : ''; ?>>Audio (MP3)</option>
                        <option value="mp4" <?php echo ($lecon['type_contenu'] === 'mp4') ? 'selected' : ''; ?>>Vidéo (MP4)</option>
                    </select>
                </div>

                <div id="contenu-texte" class="mb-3 content-field">
                    <label for="contenu_principal" class="form-label">Contenu Principal</label>
                    <textarea class="form-control" id="contenu_principal" name="contenu_principal" rows="10"><?php echo htmlspecialchars($lecon['contenu_principal']); ?></textarea>
                    <small class="form-text text-muted">Pour le type "Texte", entrez le contenu formaté ici.</small>
                </div>

                <div id="contenu-video" class="mb-3 content-field" style="display: none;">
                    <label for="video_url" class="form-label">URL de la Vidéo ou Code d'Intégration</label>
                    <textarea class="form-control" id="video_url" name="video_url" rows="3"><?php echo ($lecon['type_contenu'] === 'video') ? htmlspecialchars($lecon['contenu_principal']) : ''; ?></textarea>
                    <small class="form-text text-muted">Collez l'URL YouTube ou le code d'intégration (iframe) ici.</small>
                </div>

                <div id="contenu-fichier" class="mb-3 content-field" style="display: none;">
                    <label for="fichier" class="form-label">Fichier</label>
                    <input type="file" class="form-control" id="fichier" name="fichier" accept=".pdf,.docx,.mp3,.mp4">
                    <?php if (!empty($lecon['fichier_path'])): ?>
                        <div class="mt-2">
                            <p>Fichier actuel : <?php echo htmlspecialchars(basename($lecon['fichier_path'])); ?></p>
                        </div>
                    <?php endif; ?>
                    <small class="form-text text-muted">Formats acceptés : PDF, DOCX, MP3, MP4. Taille maximale : 50MB</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ordre" class="form-label">Ordre d'Affichage</label>
                        <input type="number" class="form-control" id="ordre" name="ordre" value="<?php echo (int)$lecon['ordre']; ?>" min="0">
                         <small class="form-text text-muted">Les leçons seront triées par ce numéro (0 vient en premier).</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                        <select class="form-select" id="statut" name="statut" required>
                            <option value="brouillon" <?php echo ($lecon['statut'] === 'brouillon') ? 'selected' : ''; ?>>Brouillon</option>
                            <option value="publie" <?php echo ($lecon['statut'] === 'publie') ? 'selected' : ''; ?>>Publié</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                     <a href="manage_module_contenu.php?module_id=<?php echo $module_id; ?>" class="btn btn-secondary me-2">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $isEditing ? 'Mettre à jour la Leçon' : 'Ajouter la Leçon'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts spécifiques au formulaire -->
<script src="js/lecon_form.js"></script>

<?php include 'admin_footer.php'; ?>
