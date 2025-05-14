<?php
require_once __DIR__ . '/admin_auth_check.php'; // Auth et config ($pdo)

// --- Fonctions utilitaires pour la redirection ---
function redirectWithErrorCat($message, $formData = []) {
    $_SESSION['form_error_message'] = $message;
    $_SESSION['form_data'] = $formData;
    $id = isset($formData['id']) ? '&id=' . $formData['id'] : '';
    $action = isset($formData['action']) ? $formData['action'] : 'add';
    header('Location: categorie_livre_form.php?action=' . $action . $id);
    exit;
}

function redirectWithSuccessCat($message, $target = 'manage_categories_livres.php') {
    $_SESSION['success_message'] = $message;
    header("Location: $target");
    exit;
}

// --- Vérification de la méthode POST --- 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithSuccessCat('Méthode non autorisée.'); // Redirect vers la liste avec un message d'erreur doux
}

// --- Récupération et Nettoyage des Données --- 
$action = $_POST['action'] ?? null;
$category_id = ($action === 'edit' || $action === 'delete') ? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) : null;
$nom_categorie = trim($_POST['nom_categorie'] ?? '');

// --- Gestion de l'action DELETE --- 
if ($action === 'delete') {
    if (!$category_id) {
        redirectWithSuccessCat('ID de catégorie manquant pour la suppression.');
    }
    
    // Vérifier si la catégorie est utilisée par des livres (sécurité)
    try {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM livres WHERE id_categorie_livre = ?");
        $stmt_check->execute([$category_id]);
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            redirectWithSuccessCat('Impossible de supprimer cette catégorie car elle est associée à ' . $count . ' livre(s).');
        }

        // Supprimer la catégorie
        $stmt = $pdo->prepare("DELETE FROM categories_livres WHERE id = ?");
        $stmt->execute([$category_id]);
        redirectWithSuccessCat('Catégorie supprimée avec succès.');
    } catch (PDOException $e) {
        error_log("Erreur suppression catégorie livre: " . $e->getMessage());
        redirectWithSuccessCat('Erreur lors de la suppression de la catégorie.');
    }
}

// --- Validation pour ADD/EDIT --- 
if ($action !== 'add' && $action !== 'edit') {
    redirectWithErrorCat('Action non reconnue.', $_POST);
}

if (empty($nom_categorie)) {
    redirectWithErrorCat('Le nom de la catégorie est obligatoire.', $_POST);
}

// --- Action ADD --- 
if ($action === 'add') {
    try {
        $sql = "INSERT INTO categories_livres (nom_categorie) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom_categorie]);
        redirectWithSuccessCat('Catégorie ajoutée avec succès.');
    } catch (PDOException $e) {
        error_log("Erreur ajout catégorie livre: " . $e->getMessage());
        $errorMessage = 'Erreur lors de l\'ajout de la catégorie: ' . $e->getMessage();
        redirectWithErrorCat($errorMessage, $_POST);
    } // Fin catch
} // Fin if action === add

// --- Action EDIT --- 
elseif ($action === 'edit') {
    if (!$category_id) {
        redirectWithErrorCat('ID de catégorie manquant pour la modification.', $_POST);
    }
    
    try {
        $sql = "UPDATE categories_livres SET nom_categorie = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nom_categorie, $category_id]);
        redirectWithSuccessCat('Catégorie mise à jour avec succès.');
    } catch (PDOException $e) {
        error_log("Erreur màj catégorie livre: " . $e->getMessage());
        redirectWithErrorCat('Erreur lors de la mise à jour de la catégorie: ' . $e->getMessage(), $_POST);
    }
}

?>
