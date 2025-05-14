<?php
session_start();
require_once __DIR__ . '/../includes/config.php'; // Auth check, $pdo, BASE_URL, UPLOADS_DIR
require_once __DIR__ . '/admin_auth_check.php'; // Vérif auth admin
require_once __DIR__ . '/includes/admin_functions.php'; // Fonctions utilitaires admin (redirects)
require_once __DIR__ . '/../utils/file_upload_helpers.php'; // Helper pour l'upload

// admin_auth_check.php gère l'authentification.

// --- Définir le chemin de base pour les uploads des images de cours ---
// Assurez-vous que UPLOADS_DIR est défini dans config.php (chemin absolu serveur)
// et que BASE_URL est aussi défini (URL web)
if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', realpath(__DIR__ . '/../uploads')); // Chemin serveur
}
$uploadSubDir = 'cours_images';
$uploadPath = UPLOADS_DIR . DIRECTORY_SEPARATOR . $uploadSubDir;
$uploadUrlBase = BASE_URL . '/uploads/' . $uploadSubDir . '/'; // URL web

// Crée le dossier d'upload s'il n'existe pas
if (!is_dir($uploadPath)) {
    if (!mkdir($uploadPath, 0777, true)) {
        // Gérer l'erreur si la création du dossier échoue
        error_log("Erreur: Impossible de créer le dossier d'upload: " . $uploadPath);
        // Peut-être rediriger avec une erreur système grave
        $_SESSION['error_message_cours'] = "Erreur système: Impossible de préparer le stockage des images.";
        redirect('manage_cours.php');
    }
}
// ---

$action = $_POST['action'] ?? $_GET['action'] ?? null; // Priorite a POST pour delete
$cours_id = $_POST['cours_id'] ?? $_POST['id'] ?? $_GET['id'] ?? null; // Priorite a POST

// --- Fonctions de redirection spécifiques aux cours (utilisant les clés de session standard) ---
function redirectWithErrorCours($message, $formData = null) {
    $_SESSION['error_message'] = $message;
    if ($formData) {
        $_SESSION['form_data'] = $formData; // Utiliser form_data general
    }
    $redirectUrl = ($formData && isset($formData['cours_id']) && $formData['cours_id']) ? 
                   'cours_form.php?id=' . $formData['cours_id'] : 
                   'cours_form.php';
    redirect($redirectUrl);
}

function redirectWithSuccessCours($message) {
    $_SESSION['success_message'] = $message;
    redirect('manage_cours.php');
}
// ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    // $contenu_principal = trim($_POST['contenu_principal'] ?? ''); // Supprimé temporairement
    $id_categorie = !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null;
    $niveau = $_POST['niveau'] ?? 'debutant';
    $duree_estimee = trim($_POST['duree_estimee'] ?? '');
    $prix = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);
    $statut = $_POST['statut'] ?? 'brouillon';
    // Retrait: date_publication et video_intro_url

    $supprimer_image = isset($_POST['supprimer_image']) && $_POST['supprimer_image'] == '1';
    
    // Validation simple
    if (empty($titre)) {
        redirectWithErrorCours('Le titre du cours est obligatoire.', $_POST);
    }
    if ($prix === false || $prix < 0) { // Vérifier si la validation a échoué ou négatif
         redirectWithErrorCours('Le prix doit être un nombre positif ou zéro.', $_POST);
    }
    if (!in_array($statut, ['brouillon', 'publie', 'archive'])) {
         redirectWithErrorCours('Statut invalide.', $_POST);
    }
    // Retrait de la validation liée à date_publication
    
    // Gestion de l'image
    $imageUrl = null;
    $oldImageUrl = null;

    // Si modification, récupérer l'ancienne image URL
    if ($action === 'edit' && $cours_id) {
        try {
            $stmtOldImg = $pdo->prepare("SELECT image_url FROM cours WHERE id = ?");
            $stmtOldImg->execute([$cours_id]);
            $oldImageUrl = $stmtOldImg->fetchColumn();
        } catch (PDOException $e) { /* Ignorer, on continuera sans */ }
        $imageUrl = $oldImageUrl; // Conserver l'ancienne par défaut
    }

    // Si une nouvelle image est uploadée
    if (isset($_FILES['image_cours']) && $_FILES['image_cours']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $uploadResult = handleFileUpload('image_cours', $uploadPath, $allowedTypes, $maxSize);

        if ($uploadResult['success']) {
            // Supprimer l'ancienne image si une nouvelle est uploadée avec succès
            if ($oldImageUrl) {
                $oldImagePath = UPLOADS_DIR . DIRECTORY_SEPARATOR . str_replace(BASE_URL . '/uploads/', '', $oldImageUrl); // Reconstruire le chemin serveur
                 if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }
            $imageUrl = $uploadUrlBase . $uploadResult['filename']; // Stocker l'URL web
        } else {
            redirectWithErrorCours("Erreur lors de l'upload de l'image: " . $uploadResult['error'], $_POST);
        }
    } elseif ($supprimer_image && $oldImageUrl) {
        // Supprimer l'image si la case est cochée et qu'il y en avait une
        $oldImagePath = UPLOADS_DIR . DIRECTORY_SEPARATOR . str_replace(BASE_URL . '/uploads/', '', $oldImageUrl);
        if (file_exists($oldImagePath)) {
            @unlink($oldImagePath);
        }
        $imageUrl = null; // Mettre à null dans la BDD
    }
    
    $params = [
        'titre' => $titre,
        'description' => $description,
        // 'contenu_principal' => $contenu_principal, // Supprimé temporairement
        'id_categorie' => $id_categorie,
        'niveau' => $niveau,
        'duree_estimee' => $duree_estimee,
        'prix' => $prix,
        'statut' => $statut,
        'image_url' => $imageUrl // Est défini lors de la gestion d'image plus haut
        // id_createur est géré spécifiquement pour l'ajout
        // date_creation et date_mise_a_jour sont gérées par la BDD
    ];

    try {
        if ($action === 'add') {
            // Utiliser user_id car admin_auth_check confirme que c'est un admin
            $admin_id = $_SESSION['user_id'] ?? null; 
            if (!$admin_id) {
                // Gérer le cas où l'ID admin n'est pas en session (ne devrait pas arriver si auth_check est ok)
                redirectWithErrorCours("Erreur: Session administrateur invalide.", $_POST);
            }
            $params['id_createur'] = $admin_id; // Ajout de l'ID créateur

            $sql = "INSERT INTO cours (titre, description, id_categorie, niveau, duree_estimee, prix, statut, image_url, id_createur, date_creation, date_mise_a_jour) 
                    VALUES (:titre, :description, :id_categorie, :niveau, :duree_estimee, :prix, :statut, :image_url, :id_createur, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            redirectWithSuccessCours('Cours ajouté avec succès.');

        } elseif ($action === 'edit' && $cours_id) {
            // Ne pas mettre à jour id_createur
            $params['cours_id'] = $cours_id; // Ajouter l'ID pour le WHERE

            $sql = "UPDATE cours SET 
                        titre = :titre, 
                        description = :description, 
                        id_categorie = :id_categorie, 
                        image_url = :image_url, 
                        niveau = :niveau, 
                        duree_estimee = :duree_estimee, 
                        prix = :prix, 
                        statut = :statut, 
                        date_mise_a_jour = NOW() 
                    WHERE id = :cours_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            redirectWithSuccessCours('Cours mis à jour avec succès.');
        }
    } catch (PDOException $e) {
        error_log("Erreur DB cours ($action): " . $e->getMessage());
        // Supprimer l'image uploadée en cas d'erreur DB pour éviter les orphelins
        if ($action === 'add' && $imageUrl && isset($uploadResult['success']) && $uploadResult['success']) {
             $newImagePath = $uploadPath . DIRECTORY_SEPARATOR . $uploadResult['filename'];
             if (file_exists($newImagePath)) {
                @unlink($newImagePath);
            }
        }
        redirectWithErrorCours("Erreur lors de la sauvegarde du cours: " . $e->getMessage(), $_POST);
    }

} elseif ($action === 'delete' && $cours_id) {
    // --- Action DELETE ---
    try {
        // Récupérer l'URL de l'image avant de supprimer le cours pour pouvoir supprimer le fichier
        $stmtImg = $pdo->prepare("SELECT image_url FROM cours WHERE id = ?");
        $stmtImg->execute([$cours_id]);
        $imageUrlToDelete = $stmtImg->fetchColumn();

        $pdo->beginTransaction();

        // Supprimer le cours (supprimera aussi leçons/quiz liés si ON DELETE CASCADE est défini)
        $sql = "DELETE FROM cours WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cours_id]);

        // Si la suppression DB réussit, supprimer le fichier image associé
        if ($imageUrlToDelete) {
            $imagePathToDelete = UPLOADS_DIR . DIRECTORY_SEPARATOR . str_replace(BASE_URL . '/uploads/', '', $imageUrlToDelete);
            if (file_exists($imagePathToDelete)) {
                @unlink($imagePathToDelete);
            }
        }

        $pdo->commit();
        redirectWithSuccessCours('Cours supprimé avec succès.');

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Erreur suppression cours: " . $e->getMessage());
        redirectWithErrorCours('Erreur lors de la suppression du cours: ' . $e->getMessage()); // Utiliser la fonction pour rediriger vers manage_cours
    }

} else {
    // Action invalide ou méthode incorrecte
    redirect('manage_cours.php');
}

?>
