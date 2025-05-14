<?php
session_start();
require_once __DIR__ . '/admin_auth_check.php'; // Auth check, $pdo, BASE_URL
require_once __DIR__ . '/../utils/redirect_helpers.php'; // Pour la fonction redirect() générique

// admin_auth_check.php gère l'authentification.

$action = $_REQUEST['action'] ?? null;
$categorie_id = $_REQUEST['categorie_id'] ?? $_REQUEST['id'] ?? null;

// --- Fonctions de redirection avec message (similaire à celles pour les livres)
function redirectWithErrorCatCours($message, $formData = null) {
    $_SESSION['error_message_cat_cours'] = $message;
    if ($formData) {
        $_SESSION['form_data_cat_cours'] = $formData;
    }
    $redirectUrl = ($formData && isset($formData['categorie_id']) && $formData['categorie_id']) ? 
                   'categorie_cours_form.php?id=' . $formData['categorie_id'] : 
                   'categorie_cours_form.php';
    redirect($redirectUrl);
}

function redirectWithSuccessCatCours($message) {
    $_SESSION['success_message_cat_cours'] = $message;
    redirect('manage_categories_cours.php');
}
// ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $nom_categorie = trim($_POST['nom_categorie'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation simple
    if (empty($nom_categorie)) {
        redirectWithErrorCatCours('Le nom de la catégorie est obligatoire.', $_POST);
    }

    // --- Action ADD ---
    if ($action === 'add') {
        try {
            // Vérifier si la catégorie existe déjà (nom unique)
            $stmtCheck = $pdo->prepare("SELECT id FROM categories_cours WHERE nom_categorie = ?");
            $stmtCheck->execute([$nom_categorie]);
            if ($stmtCheck->fetch()) {
                redirectWithErrorCatCours('Une catégorie avec ce nom existe déjà.', $_POST);
            }

            $sql = "INSERT INTO categories_cours (nom_categorie, description) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom_categorie, $description]);
            redirectWithSuccessCatCours('Catégorie de cours ajoutée avec succès.');
        } catch (PDOException $e) {
            error_log("Erreur ajout catégorie cours: " . $e->getMessage());
            redirectWithErrorCatCours("Erreur lors de l'ajout de la catégorie: " . $e->getMessage(), $_POST);
        } // Fin du catch pour action ADD
    
    } elseif ($action === 'edit') {
        // --- Action EDIT ---
        if (!$categorie_id) {
            redirectWithErrorCatCours('ID de catégorie manquant pour la modification.', $_POST);
        }

        try {
            // Vérifier si le nouveau nom existe déjà (pour une autre catégorie)
            $stmtCheck = $pdo->prepare("SELECT id FROM categories_cours WHERE nom_categorie = ? AND id != ?");
            $stmtCheck->execute([$nom_categorie, $categorie_id]);
            if ($stmtCheck->fetch()) {
                redirectWithErrorCatCours('Une autre catégorie avec ce nom existe déjà.', $_POST);
            }

            $sql = "UPDATE categories_cours SET nom_categorie = ?, description = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom_categorie, $description, $categorie_id]);
            redirectWithSuccessCatCours('Catégorie de cours mise à jour avec succès.');

        } catch (PDOException $e) {
            error_log("Erreur modification catégorie cours: " . $e->getMessage());
            redirectWithErrorCatCours('Erreur lors de la modification de la catégorie: ' . $e->getMessage(), $_POST);
        } // Fin du catch pour action EDIT

    } // Fin du bloc if/elseif pour POST ADD/EDIT
} elseif ($action === 'delete' && $categorie_id) {
    // --- Action DELETE ---
    // Mesure de sécurité simple: vérifier l'existence avant de supprimer
    try {
        $stmtCheck = $pdo->prepare("SELECT id FROM categories_cours WHERE id = ?");
        $stmtCheck->execute([$categorie_id]);
        if (!$stmtCheck->fetch()) {
            redirectWithErrorCatCours('Catégorie à supprimer non trouvée.'); // Redirige vers manage_categories_cours.php implicitement
        }

        // Note: La clé étrangère dans la table `cours` est ON DELETE SET NULL.
        // Si vous aviez choisi ON DELETE RESTRICT, il faudrait vérifier ici s'il y a des cours associés.
        
        $sql = "DELETE FROM categories_cours WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$categorie_id]);
        redirectWithSuccessCatCours('Catégorie de cours supprimée avec succès.');
    } catch (PDOException $e) {
         // Gérer les erreurs potentielles (ex: contraintes de clé étrangère si pas ON DELETE SET NULL/CASCADE)
        error_log("Erreur suppression catégorie cours: " . $e->getMessage());
        // Tenter de détecter une erreur de contrainte
        if (strpos($e->getMessage(), 'constraint fails') !== false) {
             redirectWithErrorCatCours('Impossible de supprimer cette catégorie car elle est utilisée par un ou plusieurs cours.');
        } else {
             redirectWithErrorCatCours('Erreur lors de la suppression de la catégorie: ' . $e->getMessage());
        }
    }

} else {
    // Si aucune action valide n'est fournie ou méthode incorrecte
    redirect('manage_categories_cours.php');
}
?>
