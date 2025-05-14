<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/admin_includes/admin_functions.php';

$cours_id = filter_input(INPUT_GET, 'cours_id', FILTER_VALIDATE_INT);
$module_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$isEditing = ($module_id !== null);

$moduleData = ['titre' => '', 'description' => '', 'ordre' => 0, 'statut' => 'brouillon', 'id_cours' => $cours_id];
$pageTitle = "Ajouter un Module";
$action = 'add';

// Vérification initiale de cours_id si on ajoute un nouveau module
if (!$isEditing && !$cours_id) {
    $_SESSION['error_message'] = "ID de cours manquant pour l'ajout d'un module.";
    redirect('manage_cours.php');
}

// Si on édite, récupérer les données du module existant
if ($isEditing) {
    $pageTitle = "Modifier le Module";
    $action = 'edit';
    try {
        $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = :id");
        $stmt->bindParam(':id', $module_id, PDO::PARAM_INT);
        $stmt->execute();
        $moduleData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$moduleData) {
            $_SESSION['error_message'] = "Module non trouvé.";
            redirect('manage_cours.php'); // Rediriger vers la liste générale car on ne sait pas à quel cours il appartenait
        }
        // Si cours_id n'est pas passé dans l'URL en édition, le récupérer depuis les données du module
        if (!$cours_id) {
            $cours_id = $moduleData['id_cours'];
        } elseif ($cours_id != $moduleData['id_cours']) {
             // Cohérence: si on passe un cours_id différent en URL de celui stocké
             $_SESSION['error_message'] = "Incohérence dans l'ID du cours.";
             redirect('manage_cours_contenu.php?cours_id=' . $moduleData['id_cours']);
        }

    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du module: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors du chargement du module.";
        // Essayer de rediriger vers la page du cours si on a l'ID, sinon liste générale
        if ($cours_id) {
            redirect('manage_cours_contenu.php?cours_id=' . $cours_id);
        } else {
            redirect('manage_cours.php');
        }
    }
} elseif ($cours_id) {
     // Si on ajoute, s'assurer que le cours parent existe
     try {
        $stmtCheck = $pdo->prepare("SELECT id FROM cours WHERE id = :cours_id");
        $stmtCheck->bindParam(':cours_id', $cours_id, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch() === false) {
             $_SESSION['error_message'] = "Le cours auquel vous essayez d'ajouter un module n'existe pas.";
             redirect('manage_cours.php');
        }
     } catch (PDOException $e) {
        error_log("Erreur vérification cours parent: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la vérification du cours parent.";
        redirect('manage_cours.php');
     }
}


// Récupérer le titre du cours pour le fil d'ariane
$coursTitre = '';
if ($cours_id) {
    try {
        $stmtCours = $pdo->prepare("SELECT titre FROM cours WHERE id = :id");
        $stmtCours->bindParam(':id', $cours_id, PDO::PARAM_INT);
        $stmtCours->execute();
        $coursParent = $stmtCours->fetch(PDO::FETCH_ASSOC);
        if ($coursParent) {
            $coursTitre = $coursParent['titre'];
        }
    } catch (PDOException $e) {
        error_log("Erreur récupération titre cours parent: " . $e->getMessage());
        // Pas critique, le fil d'ariane sera moins précis
    }
}


$formError = $_SESSION['form_error'] ?? null;
$formValues = $_SESSION['form_values'] ?? $moduleData;
unset($_SESSION['form_error'], $_SESSION['form_values']);

include 'admin_header.php';
?>

<div class="container mt-4">
     <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard Admin</a></li>
            <li class="breadcrumb-item"><a href="manage_cours.php">Gestion des Cours</a></li>
            <?php if ($coursTitre): ?>
            <li class="breadcrumb-item"><a href="manage_cours_contenu.php?cours_id=<?php echo $cours_id; ?>"><?php echo htmlspecialchars($coursTitre); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $pageTitle; ?></li>
        </ol>
    </nav>

    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>

    <?php if ($formError): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($formError); ?>
        </div>
    <?php endif; ?>
     <?php if (isset($_SESSION['success_message'])):
        // Correction: Afficher le message et le supprimer
        ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
     <?php if (isset($_SESSION['error_message'])):
         // Correction: Afficher le message et le supprimer
         ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="module_actions.php" method="POST">
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <input type="hidden" name="id" value="<?php echo $module_id ?? ''; ?>">
        <input type="hidden" name="id_cours" value="<?php echo $cours_id; ?>"> <!-- Toujours inclure id_cours -->

        <div class="mb-3">
            <label for="titre" class="form-label">Titre du Module <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($formValues['titre'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($formValues['description'] ?? ''); ?></textarea>
             <small class="form-text text-muted">Brève description du contenu du module.</small>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                 <label for="ordre" class="form-label">Ordre d'affichage</label>
                <input type="number" class="form-control" id="ordre" name="ordre" value="<?php echo htmlspecialchars($formValues['ordre'] ?? 0); ?>" min="0">
                <small class="form-text text-muted">Position du module dans la liste (0, 1, 2...). Les modules sont triés par ordre croissant.</small>
            </div>
            <div class="col-md-6 mb-3">
                 <label for="statut" class="form-label">Statut</label>
                <select class="form-select" id="statut" name="statut">
                    <option value="brouillon" <?php echo ($formValues['statut'] ?? 'brouillon') === 'brouillon' ? 'selected' : ''; ?>>Brouillon</option>
                    <option value="publie" <?php echo ($formValues['statut'] ?? '') === 'publie' ? 'selected' : ''; ?>>Publié</option>
                    <!-- Ajoutez d'autres statuts si nécessaire -->
                </select>
                 <small class="form-text text-muted">Seuls les modules publiés seront visibles par les étudiants.</small>
            </div>
        </div>

        <hr>

        <button type="submit" class="btn btn-primary">
            <?php echo $isEditing ? 'Mettre à jour le Module' : 'Ajouter le Module'; ?>
        </button>
        <a href="manage_cours_contenu.php?cours_id=<?php echo $cours_id; ?>" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include 'admin_footer.php'; ?>
