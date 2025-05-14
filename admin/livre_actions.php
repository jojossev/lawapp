<?php
require_once __DIR__ . '/admin_auth_check.php'; // Auth et config ($pdo)

// Définir le dossier de destination pour les images de couverture
define('UPLOAD_DIR_LIVRES', __DIR__ . '/../uploads/livres_couvertures/');

// --- Gestion des erreurs et des redirections ---
function redirectWithError($message, $formData = []) {
    $_SESSION['form_error_message'] = $message;
    $_SESSION['form_data'] = $formData;
    $id = isset($formData['id']) ? '&id=' . $formData['id'] : '';
    $action = isset($formData['action']) ? $formData['action'] : 'add';
    header('Location: livre_form.php?action=' . $action . $id);
    exit;
}

function redirectWithSuccess($message, $target = 'manage_livres.php') {
    $_SESSION['success_message'] = $message;
    header("Location: $target");
    exit;
}

// --- Vérification de la méthode POST --- 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Méthode non autorisée.', $_POST);
}

// --- Récupération et Nettoyage des Données --- 
$action = $_POST['action'] ?? 'add';
$livre_id = ($action === 'edit' || $action === 'delete') ? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) : null;

$livre_data = [
    'id' => $livre_id,
    'action' => $action, // Pour la redirection en cas d'erreur
    'titre' => trim($_POST['titre'] ?? ''),
    'auteur' => trim($_POST['auteur'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'id_categorie_livre' => filter_input(INPUT_POST, 'id_categorie_livre', FILTER_VALIDATE_INT) ?: null, // Peut être null
    'statut' => $_POST['statut'] ?? 'brouillon',
    'fichier_pdf_url' => filter_input(INPUT_POST, 'fichier_pdf_url', FILTER_VALIDATE_URL) ?: '' // Valider comme URL ou laisser vide
];

// --- Gestion de l'action DELETE --- 
if ($action === 'delete') {
    if (!$livre_id) {
        redirectWithSuccess('ID du livre manquant pour la suppression.', 'manage_livres.php');
    }
    try {
        // Optionnel : Supprimer aussi l'image associée du serveur
        $stmt_img = $pdo->prepare("SELECT image_url FROM livres WHERE id = ?");
        $stmt_img->execute([$livre_id]);
        $imageUrl = $stmt_img->fetchColumn();
        if ($imageUrl && file_exists(__DIR__ . '/../' . $imageUrl)) {
            unlink(__DIR__ . '/../' . $imageUrl);
        }

        // Supprimer le livre de la BDD
        $stmt = $pdo->prepare("DELETE FROM livres WHERE id = ?");
        $stmt->execute([$livre_id]);
        redirectWithSuccess('Le livre a été supprimé avec succès.');
    } catch (PDOException $e) {
        error_log("Erreur suppression livre: " . $e->getMessage());
        redirectWithSuccess('Erreur lors de la suppression du livre.', 'manage_livres.php'); // Utiliser success pour afficher sur la page de liste
    }
}

// --- Validation des données pour ADD/EDIT ---
if (empty($livre_data['titre'])) {
    redirectWithError('Le titre du livre est obligatoire.', $livre_data);
}
if (!in_array($livre_data['statut'], ['brouillon', 'publie', 'archive'])) {
    redirectWithError('Statut invalide.', $livre_data);
}

// --- Gestion du Téléversement de l'Image --- 
$image_url_to_save = null;
$uploadError = '';

if (isset($_FILES['image_couverture']) && $_FILES['image_couverture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image_couverture']['tmp_name'];
    $fileName = $_FILES['image_couverture']['name'];
    $fileSize = $_FILES['image_couverture']['size'];
    $fileType = $_FILES['image_couverture']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Validation simple
    $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];
    if (in_array($fileExtension, $allowedfileExtensions)) {
        if ($fileSize < 4000000) { // Limite à 4MB
            // Créer le dossier s'il n'existe pas
            if (!file_exists(UPLOAD_DIR_LIVRES)) {
                mkdir(UPLOAD_DIR_LIVRES, 0777, true);
            }
            // Nouveau nom de fichier unique
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = UPLOAD_DIR_LIVRES . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Chemin relatif à stocker dans la BDD (depuis la racine du site)
                $image_url_to_save = 'uploads/livres_couvertures/' . $newFileName;
            } else {
                $uploadError = 'Erreur lors du déplacement du fichier téléversé.';
            }
        } else {
            $uploadError = 'Le fichier est trop volumineux (max 4MB).';
        }
    } else {
        $uploadError = 'Type de fichier non autorisé (jpg, gif, png, jpeg uniquement).';
    }
} elseif (isset($_FILES['image_couverture']) && $_FILES['image_couverture']['error'] !== UPLOAD_ERR_NO_FILE) {
    // Gérer les autres erreurs d'upload
    $uploadError = 'Erreur de téléversement: code ' . $_FILES['image_couverture']['error'];
}

// Si une erreur d'upload s'est produite, rediriger
if (!empty($uploadError)) {
    redirectWithError($uploadError, $livre_data);
}

// --- Préparation des Données pour la BDD --- 
$params = [
    ':titre' => $livre_data['titre'],
    ':auteur' => $livre_data['auteur'],
    ':description' => $livre_data['description'],
    ':id_categorie_livre' => $livre_data['id_categorie_livre'],
    ':statut' => $livre_data['statut'],
    ':fichier_pdf_url' => $livre_data['fichier_pdf_url']
];

// --- Action ADD --- 
if ($action === 'add') {
    $params[':image_url'] = $image_url_to_save; // Peut être null si aucune image n'a été uploadée
    
    $sql = "INSERT INTO livres (titre, auteur, description, id_categorie_livre, image_url, statut, fichier_pdf_url, date_creation) 
            VALUES (:titre, :auteur, :description, :id_categorie_livre, :image_url, :statut, :fichier_pdf_url, NOW())";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        redirectWithSuccess('Livre ajouté avec succès.');
    } catch (PDOException $e) {
        error_log("Erreur ajout livre: " . $e->getMessage());
        redirectWithError('Erreur lors de l'ajout du livre: ' . $e->getMessage(), $livre_data);
    }
}

// --- Action EDIT --- 
elseif ($action === 'edit') {
    if (!$livre_id) {
        redirectWithError('ID du livre manquant pour la modification.', $livre_data);
    }
    $params[':id'] = $livre_id;

    // Gérer la mise à jour de l'image
    $sql_update_image = '';
    if ($image_url_to_save !== null) {
        $params[':image_url'] = $image_url_to_save;
        $sql_update_image = ', image_url = :image_url';
        
        // Supprimer l'ancienne image si une nouvelle est uploadée
        try {
            $stmt_old_img = $pdo->prepare("SELECT image_url FROM livres WHERE id = ?");
            $stmt_old_img->execute([$livre_id]);
            $oldImageUrl = $stmt_old_img->fetchColumn();
            if ($oldImageUrl && $oldImageUrl !== $image_url_to_save && file_exists(__DIR__ . '/../' . $oldImageUrl)) {
                 unlink(__DIR__ . '/../' . $oldImageUrl);
            }
        } catch (PDOException $e) {
             error_log("Erreur suppression ancienne image livre: " . $e->getMessage());
             // Continuer quand même, la nouvelle image sera liée
        }
    }
    
    $sql = "UPDATE livres SET 
                titre = :titre, 
                auteur = :auteur, 
                description = :description, 
                id_categorie_livre = :id_categorie_livre, 
                statut = :statut, 
                fichier_pdf_url = :fichier_pdf_url 
                {$sql_update_image}
            WHERE id = :id";
            
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        redirectWithSuccess('Livre mis à jour avec succès.', 'manage_livres.php'); // Rediriger vers la liste
    } catch (PDOException $e) {
        error_log("Erreur màj livre: " . $e->getMessage());
        redirectWithError('Erreur lors de la mise à jour du livre: ' . $e->getMessage(), $livre_data);
    }
} else {
    redirectWithError('Action non reconnue.', $livre_data);
}

?>
