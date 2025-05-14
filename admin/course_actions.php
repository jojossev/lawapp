<?php
require_once __DIR__ . '/admin_auth_check.php'; // Inclut la connexion PDO et la session

// Définir le dossier de destination pour les images de couverture des cours
define('UPLOAD_DIR_COURS', __DIR__ . '/../uploads/cours_couvertures/');

$action = $_POST['action'] ?? $_GET['action'] ?? null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Assainissement et récupération des données du formulaire
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $contenu_principal = trim($_POST['contenu_principal'] ?? '');
        $id_categorie = !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null;
        $niveau = trim($_POST['niveau'] ?? '');
        $id_createur = isset($_POST['id_createur']) ? (int)$_POST['id_createur'] : null;
        $statut = trim($_POST['statut'] ?? 'brouillon');
        $duree_estimee = trim($_POST['duree_estimee'] ?? '');
        $prix = isset($_POST['prix']) && $_POST['prix'] !== '' ? (float)$_POST['prix'] : null;

        $uploaded_image_path = null; // Chemin relatif de l'image uploadée à stocker en BDD
        $uploadError = '';

        // Validation de base
        $errors = [];
        if (empty($titre)) {
            $errors[] = "Le titre du cours est obligatoire.";
        }
        if (empty($id_createur)) {
            $errors[] = "Le créateur du cours est obligatoire.";
        }
        $valid_statuts = ['brouillon', 'publie', 'archive'];
        if (!in_array($statut, $valid_statuts)) {
            $errors[] = "Le statut du cours n'est pas valide.";
        }
        if ($id_categorie !== null && !filter_var($id_categorie, FILTER_VALIDATE_INT)) {
             $errors[] = "L'ID de catégorie n'est pas valide.";
        }
        if ($id_createur !== null && !filter_var($id_createur, FILTER_VALIDATE_INT)) {
             $errors[] = "L'ID du créateur n'est pas valide.";
        }

        // --- Gestion du Téléversement de l'Image de Couverture ---
        if (isset($_FILES['image_couverture']) && $_FILES['image_couverture']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image_couverture']['tmp_name'];
            $fileName = $_FILES['image_couverture']['name'];
            $fileSize = $_FILES['image_couverture']['size'];
            $fileType = $_FILES['image_couverture']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                if ($fileSize < 2000000) { // Limite à 2MB (ajuster si besoin)
                    // Créer le dossier s'il n'existe pas
                    if (!file_exists(UPLOAD_DIR_COURS)) {
                        if (!mkdir(UPLOAD_DIR_COURS, 0777, true)) {
                            $uploadError = 'Impossible de créer le dossier de destination.';
                        }
                    }
                    
                    if(empty($uploadError)) {
                        // Nouveau nom de fichier unique
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $dest_path = UPLOAD_DIR_COURS . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            // Chemin relatif à stocker dans la BDD (depuis la racine du site)
                            $uploaded_image_path = 'uploads/cours_couvertures/' . $newFileName;
                        } else {
                            $uploadError = 'Erreur lors du déplacement du fichier téléversé.';
                        }
                    }
                } else {
                    $uploadError = 'Le fichier est trop volumineux (max 2MB).';
                }
            } else {
                $uploadError = 'Type de fichier non autorisé (jpg, gif, png, jpeg uniquement).';
            }
        } elseif (isset($_FILES['image_couverture']) && $_FILES['image_couverture']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Gérer les autres erreurs d'upload
            $uploadError = 'Erreur de téléversement: code ' . $_FILES['image_couverture']['error'];
        }

        // Ajouter l'erreur d'upload aux erreurs générales si elle existe
        if (!empty($uploadError)) {
            $errors[] = $uploadError;
        }
        // Validation du prix (doit être numérique et positif si défini)
        if ($prix !== null && (!is_numeric($prix) || $prix < 0)) {
            $errors[] = "Le prix doit être un nombre positif ou nul.";
        }

        // Si erreurs (validation ou upload), rediriger vers le formulaire
        if (!empty($errors)) {
            $_SESSION['form_error_message'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST; // Sauvegarder les données texte
            header("Location: course_form.php?action=" . ($id ? "edit&id=$id" : "add"));
            exit;
        }
        
        // Préparer les paramètres pour la BDD (sans l'image pour l'instant)
        $params = [
            ':titre' => $titre,
            ':description' => $description,
            ':contenu_principal' => $contenu_principal,
            ':id_categorie' => $id_categorie,
            ':niveau' => $niveau,
            ':id_createur' => $id_createur,
            ':statut' => $statut,
            ':duree_estimee' => $duree_estimee,
            ':prix' => $prix // Ajouter le prix aux paramètres
        ];

        // --- Action ADD --- 
        if ($action === 'add') {
            $params[':image_url'] = $uploaded_image_path; // Peut être null

            $sql = "INSERT INTO cours (titre, description, contenu_principal, id_categorie, niveau, id_createur, image_url, statut, duree_estimee, prix, date_creation, date_mise_a_jour) 
                    VALUES (:titre, :description, :contenu_principal, :id_categorie, :niveau, :id_createur, :image_url, :statut, :duree_estimee, :prix, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['success_message'] = "Cours ajouté avec succès.";

        // --- Action EDIT --- 
        } elseif ($action === 'edit' && $id) {
            $params[':id'] = $id;
            $sql_image_update = '';

            // Si une nouvelle image a été uploadée, on met à jour le champ et on supprime l'ancienne
            if ($uploaded_image_path !== null) {
                $params[':image_url'] = $uploaded_image_path;
                $sql_image_update = ', image_url = :image_url'; // Ajouter la colonne à l'UPDATE

                // Supprimer l'ancienne image du serveur
                try {
                    $stmt_old_img = $pdo->prepare("SELECT image_url FROM cours WHERE id = ?");
                    $stmt_old_img->execute([$id]);
                    $oldImageUrl = $stmt_old_img->fetchColumn();
                    if ($oldImageUrl && $oldImageUrl !== $uploaded_image_path && file_exists(__DIR__ . '/../' . $oldImageUrl)) {
                        unlink(__DIR__ . '/../' . $oldImageUrl);
                    }
                } catch (PDOException $e) {
                    error_log("Erreur suppression ancienne image cours: " . $e->getMessage());
                    // Ne pas bloquer la mise à jour même si la suppression échoue
                }
            } 
            // Si pas de nouvelle image uploadée ($uploaded_image_path est null),
            // $sql_image_update reste vide, et le champ image_url n'est pas mis à jour dans la BDD.

            $sql = "UPDATE cours SET 
                        titre = :titre, 
                        description = :description, 
                        contenu_principal = :contenu_principal, 
                        id_categorie = :id_categorie, 
                        niveau = :niveau, 
                        id_createur = :id_createur, 
                        statut = :statut, 
                        duree_estimee = :duree_estimee,
                        prix = :prix, /* <-- Ajouter cette ligne */
                        date_mise_a_jour = NOW()
                        {$sql_image_update} 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params); // Execute avec ou sans :image_url selon le cas
            $_SESSION['success_message'] = "Cours mis à jour avec succès.";

        } else {
            throw new Exception("Action POST non valide ou ID manquant.");
        }

    // --- Action DELETE --- 
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            throw new Exception("ID de cours manquant pour la suppression.");
        }

        // Supprimer l'image associée avant de supprimer le cours
        try {
            $stmt_img = $pdo->prepare("SELECT image_url FROM cours WHERE id = ?");
            $stmt_img->execute([$id]);
            $imageUrl = $stmt_img->fetchColumn();
            if ($imageUrl && file_exists(__DIR__ . '/../' . $imageUrl)) {
                unlink(__DIR__ . '/../' . $imageUrl);
            }
        } catch (PDOException $e) {
             error_log("Erreur suppression image cours (delete action): " . $e->getMessage());
             // Continuer la suppression du cours même si l'image ne peut être supprimée
        }
        
        // Supprimer le cours de la BDD
        $stmt = $pdo->prepare("DELETE FROM cours WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_message'] = "Cours supprimé avec succès.";

    } else {
        throw new Exception("Méthode de requête ou action non supportée.");
    }

    header("Location: manage_courses.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur de base de données : " . $e->getMessage();
    error_log("PDO Error in course_actions.php: " . $e->getMessage()); // Log pour débogage
    // En cas d'erreur PDO lors d'un POST, rediriger vers le formulaire avec les données
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['form_data'] = $_POST; // Conserver les données texte
        // Ne pas sauvegarder le fichier uploadé en cas d'erreur BDD
        $current_action = $_POST['action'] ?? 'add';
        $current_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        header("Location: course_form.php?action=" . ($current_id ? "edit&id=$current_id" : $current_action));
    } else {
        header("Location: manage_courses.php");
    }
    exit;
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    error_log("General Error in course_actions.php: " . $e->getMessage()); // Log pour débogage
    header("Location: manage_courses.php");
    exit;
}
?>
