<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php'; // Pour la fonction redirect

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('manage_cours.php'); // Seulement POST
}

// Constantes pour l'upload de fichiers
define('UPLOAD_DIR', __DIR__ . '/../uploads/lecons/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_EXTENSIONS', [
    'pdf' => ['application/pdf'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'mp3' => ['audio/mpeg'],
    'mp4' => ['video/mp4']
]);

// Récupération et validation des données communes
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
$module_id = filter_input(INPUT_POST, 'module_id', FILTER_VALIDATE_INT);
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT); // Pour edit et delete
$titre = trim(filter_input(INPUT_POST, 'titre', FILTER_SANITIZE_SPECIAL_CHARS));
$type_contenu = filter_input(INPUT_POST, 'type_contenu', FILTER_SANITIZE_SPECIAL_CHARS);
$contenu_principal = trim(filter_input(INPUT_POST, 'contenu_principal', FILTER_DEFAULT)); // Permettre HTML/iframe, mais nettoyer si besoin plus tard
$ordre = filter_input(INPUT_POST, 'ordre', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
$statut = filter_input(INPUT_POST, 'statut', FILTER_SANITIZE_SPECIAL_CHARS);

// Types de contenu et statuts autorisés
$allowed_types = ['texte', 'video', 'pdf', 'docx', 'mp3', 'mp4']; // Types de contenu supportés
$allowed_statuts = ['brouillon', 'publie'];

// Vérification essentielle: module_id doit exister pour savoir où rediriger et où insérer/modifier
if (!$module_id) {
    $_SESSION['error_message'] = "ID de module manquant.";
    redirect('manage_cours.php'); // Redirection générique
}
$redirect_url = 'manage_module_contenu.php?module_id=' . $module_id;


// --- Action: Ajouter une leçon ---
if ($action === 'add') {
    // Validation spécifique à l'ajout
    if (empty($titre) || !$module_id || !in_array($type_contenu, $allowed_types) || !in_array($statut, $allowed_statuts)) {
        $_SESSION['error_message'] = "Données du formulaire invalides ou manquantes pour l'ajout.";
        $_SESSION['form_values'] = $_POST;
        redirect('lecon_form.php?module_id=' . $module_id);
    }

    // Traitement du fichier si nécessaire
    $fichier_path = null;
    if (in_array($type_contenu, ['pdf', 'docx', 'mp3', 'mp4'])) {
        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['error_message'] = "Le fichier est requis pour ce type de contenu.";
            $_SESSION['form_values'] = $_POST;
            redirect('lecon_form.php?module_id=' . $module_id);
        }

        $file = $_FILES['fichier'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Vérifier l'extension et le type MIME
        if ($ext !== $type_contenu || !in_array($file['type'], ALLOWED_EXTENSIONS[$type_contenu])) {
            $_SESSION['error_message'] = "Type de fichier non autorisé. Seuls les fichiers {$type_contenu} sont acceptés.";
            $_SESSION['form_values'] = $_POST;
            redirect('lecon_form.php?module_id=' . $module_id);
        }

        // Vérifier la taille
        if ($file['size'] > MAX_FILE_SIZE) {
            $_SESSION['error_message'] = "Le fichier est trop volumineux. Taille maximale : 50MB.";
            $_SESSION['form_values'] = $_POST;
            redirect('lecon_form.php?module_id=' . $module_id);
        }

        // Créer le dossier d'upload si nécessaire
        if (!file_exists(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }

        // Générer un nom de fichier unique
        $fichier_path = 'uploads/lecons/' . uniqid() . '_' . $file['name'];
        $destination = __DIR__ . '/../' . $fichier_path;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $_SESSION['error_message'] = "Erreur lors de l'upload du fichier.";
            $_SESSION['form_values'] = $_POST;
            redirect('lecon_form.php?module_id=' . $module_id);
        }
    }

    if (empty($titre) || !$module_id || !in_array($type_contenu, $allowed_types) || !in_array($statut, $allowed_statuts)) {
        $_SESSION['error_message'] = "Données du formulaire invalides ou manquantes pour l'ajout.";
        $_SESSION['form_values'] = $_POST; // Sauvegarder pour pré-remplir
        redirect('lecon_form.php?module_id=' . $module_id);
    }

    try {
        // Construire la requête SQL en fonction de la présence ou non d'un fichier
        if ($fichier_path !== null) {
            $sql = "INSERT INTO lecons (id_module, titre, type_contenu, contenu_principal, fichier_path, ordre, statut, date_creation, date_mise_a_jour) 
                    VALUES (:id_module, :titre, :type_contenu, :contenu_principal, :fichier_path, :ordre, :statut, NOW(), NOW())";
        } else {
            $sql = "INSERT INTO lecons (id_module, titre, type_contenu, contenu_principal, ordre, statut, date_creation, date_mise_a_jour) 
                    VALUES (:id_module, :titre, :type_contenu, :contenu_principal, :ordre, :statut, NOW(), NOW())";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_module', $module_id, PDO::PARAM_INT);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':type_contenu', $type_contenu);
        $stmt->bindParam(':contenu_principal', $contenu_principal);
        $stmt->bindParam(':ordre', $ordre, PDO::PARAM_INT);
        $stmt->bindParam(':statut', $statut);

        // Lier le paramètre fichier_path seulement s'il existe
        if ($fichier_path !== null) {
            $stmt->bindParam(':fichier_path', $fichier_path);
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Leçon ajoutée avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de l'ajout de la leçon.";
            $_SESSION['form_values'] = $_POST;
            redirect('lecon_form.php?module_id=' . $module_id); // Retour au formulaire
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL (Add Lecon): " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données lors de l'ajout. Code: " . $e->getCode();
         if ($e->getCode() == '23000') {
             $_SESSION['error_message'] = "Erreur de contrainte : Vérifiez que le module parent existe.";
         }
        $_SESSION['form_values'] = $_POST;
        redirect('lecon_form.php?module_id=' . $module_id); // Retour au formulaire
    }
    redirect($redirect_url); // Rediriger vers la liste des leçons du module

}
// --- Action: Modifier une leçon ---
elseif ($action === 'edit') {
    // Validation spécifique à la modification
    if (empty($titre) || !$id || !$module_id || !in_array($type_contenu, $allowed_types) || !in_array($statut, $allowed_statuts)) {
        $_SESSION['error_message'] = "Données du formulaire invalides ou manquantes pour la modification.";
        $_SESSION['form_values'] = $_POST;
        redirect('lecon_form.php?id=' . $id . '&module_id=' . $module_id);
    }

    // Traitement du fichier si nécessaire
    $fichier_path = null;
    if (in_array($type_contenu, ['pdf', 'docx', 'mp3', 'mp4'])) {
        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['fichier'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // Vérifier l'extension et le type MIME
            if ($ext !== $type_contenu || !in_array($file['type'], ALLOWED_EXTENSIONS[$type_contenu])) {
                $_SESSION['error_message'] = "Type de fichier non autorisé. Seuls les fichiers {$type_contenu} sont acceptés.";
                $_SESSION['form_values'] = $_POST;
                redirect('lecon_form.php?id=' . $id . '&module_id=' . $module_id);
            }

            // Vérifier la taille
            if ($file['size'] > MAX_FILE_SIZE) {
                $_SESSION['error_message'] = "Le fichier est trop volumineux. Taille maximale : 50MB.";
                $_SESSION['form_values'] = $_POST;
                redirect('lecon_form.php?id=' . $id . '&module_id=' . $module_id);
            }

            // Créer le dossier d'upload si nécessaire
            if (!file_exists(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0777, true);
            }

            // Générer un nom de fichier unique
            $fichier_path = 'uploads/lecons/' . uniqid() . '_' . $file['name'];
            $destination = __DIR__ . '/../' . $fichier_path;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $_SESSION['error_message'] = "Erreur lors de l'upload du fichier.";
                $_SESSION['form_values'] = $_POST;
                redirect('lecon_form.php?id=' . $id . '&module_id=' . $module_id);
            }

            // Supprimer l'ancien fichier si un nouveau est uploadé
            try {
                $sql = "SELECT fichier_path FROM lecons WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $old_file = $stmt->fetchColumn();
                
                if ($old_file && file_exists(__DIR__ . '/../' . $old_file)) {
                    unlink(__DIR__ . '/../' . $old_file);
                }
            } catch (PDOException $e) {
                error_log("Erreur lors de la suppression de l'ancien fichier : " . $e->getMessage());
            }
        }
    }

    if (empty($titre) || !$id || !$module_id || !in_array($type_contenu, $allowed_types) || !in_array($statut, $allowed_statuts)) {
        $_SESSION['error_message'] = "Données du formulaire invalides ou manquantes pour la modification.";
        $_SESSION['form_values'] = $_POST;
        redirect('lecon_form.php?id=' . $id . '&module_id=' . $module_id); // Retour au formulaire d'édition
    }

    try {
        // Construire la requête SQL en fonction de la présence ou non d'un nouveau fichier
        $sql = "UPDATE lecons SET 
                titre = :titre, 
                type_contenu = :type_contenu, 
                contenu_principal = :contenu_principal," .
                ($fichier_path !== null ? " fichier_path = :fichier_path," : "") .
                " ordre = :ordre, 
                statut = :statut, 
                date_mise_a_jour = NOW() 
                WHERE id = :id AND id_module = :id_module"; // Sécurité: s'assurer qu'on modifie bien une leçon du bon module
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':type_contenu', $type_contenu);
        $stmt->bindParam(':contenu_principal', $contenu_principal);
        $stmt->bindParam(':ordre', $ordre, PDO::PARAM_INT);
        if ($fichier_path) {
            $stmt->bindParam(':fichier_path', $fichier_path);
        }
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':id_module', $module_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
             if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Leçon mise à jour avec succès.";
             } else {
                 $_SESSION['error_message'] = "Aucune modification effectuée (données identiques ou leçon non trouvée pour ce module).";
             }
        } else {
            $_SESSION['error_message'] = "Erreur lors de la mise à jour de la leçon.";
            $_SESSION['form_values'] = $_POST;
            redirect('lecon_form.php?id=' . $id . '&module_id=' . $module_id); // Retour au formulaire
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL (Edit Lecon): " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données lors de la mise à jour.";
        $_SESSION['form_values'] = $_POST;
        redirect('lecon_form.php?id=' . $id . '&module_id=' . $module_id); // Retour au formulaire
    }
     redirect($redirect_url);

}
// --- Action: Supprimer une leçon ---
elseif ($action === 'delete') {
     // Note: La suppression via POST est plus sûre que GET pour éviter les suppressions accidentelles via URL
     $id_lecon_delete = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
     $id_module_redirect = filter_input(INPUT_POST, 'module_id', FILTER_VALIDATE_INT); // Récupérer pour redirection

     if (!$id_lecon_delete || !$id_module_redirect) {
         $_SESSION['error_message'] = "ID de leçon ou de module manquant pour la suppression.";
         redirect($id_module_redirect ? 'manage_module_contenu.php?module_id=' . $id_module_redirect : 'manage_cours.php');
     }

    // S'assurer que les clés étrangères sont configurées avec ON DELETE CASCADE pour les quiz_questions, user_progression liés aux leçons.
    // Sinon, implémenter la suppression en cascade ici.
    try {
        $sql = "DELETE FROM lecons WHERE id = :id AND id_module = :id_module"; // Sécurité supplémentaire
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_lecon_delete, PDO::PARAM_INT);
         $stmt->bindParam(':id_module', $id_module_redirect, PDO::PARAM_INT);

        if ($stmt->execute()) {
             if ($stmt->rowCount() > 0) {
                 $_SESSION['success_message'] = "Leçon (et son contenu associé potentiel) supprimée avec succès.";
             } else {
                  $_SESSION['error_message'] = "Leçon non trouvée dans ce module ou déjà supprimée.";
             }
        } else {
            $_SESSION['error_message'] = "Erreur lors de la suppression de la leçon.";
        }
    } catch (PDOException $e) {
        error_log("Erreur SQL (Delete Lecon): " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données lors de la suppression. Vérifiez les dépendances (quiz, progression...). Code: " . $e->getCode();
         if ($e->getCode() == '23000') { // Foreign key constraint
             $_SESSION['error_message'] = "Impossible de supprimer cette leçon car elle est référencée ailleurs (quiz, progression utilisateur...).";
         }
    }
    // Rediriger vers la page du module
    redirect('manage_module_contenu.php?module_id=' . $id_module_redirect);
}
// --- Action Inconnue ---
else {
    $_SESSION['error_message'] = "Action non valide.";
    redirect($redirect_url); // Rediriger vers la page contenu du module si possible
}

exit; // Terminer le script
?>
