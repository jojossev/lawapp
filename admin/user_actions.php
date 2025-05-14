<?php
require_once __DIR__ . '/admin_auth_check.php'; // Authentification et configuration

if (!isset($pdo)) {
    // Redirection ou message d'erreur si $pdo n'est pas disponible
    $_SESSION['admin_flash_message'] = "Erreur critique : La connexion à la base de données n'est pas disponible.";
    $_SESSION['admin_flash_type'] = "error";
    header("Location: manage_users.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$user_id = $_POST['user_id'] ?? $_GET['id'] ?? null;

$errors = [];
$prenom = '';
$nom = '';
$email = '';
$role = 'user'; // Rôle par défaut

// Traitement pour l'ajout ou la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $mot_de_passe_confirm = $_POST['mot_de_passe_confirm'] ?? '';

    // Validation des champs
    if (empty($prenom)) {
        $errors[] = "Le prénom est requis.";
    }
    if (empty($nom)) {
        $errors[] = "Le nom est requis.";
    }
    if (empty($email)) {
        $errors[] = "L'email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L\'format de l\'email est invalide.";
    } else {
        // Vérifier l'unicité de l'email (différemment pour ajout et modification)
        $sql_check_email = "SELECT id FROM utilisateurs WHERE email = :email";
        if ($action === 'edit' && $user_id) {
            $sql_check_email .= " AND id != :user_id";
        }
        $stmt_check_email = $pdo->prepare($sql_check_email);
        $stmt_check_email->bindParam(':email', $email);
        if ($action === 'edit' && $user_id) {
            $stmt_check_email->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }
        $stmt_check_email->execute();
        if ($stmt_check_email->fetch()) {
            $errors[] = "Cette adresse email est déjà utilisée par un autre compte.";
        }
    }

    if (!in_array($role, ['user', 'admin'])) { // Valider les rôles permis
        $errors[] = "Le rôle sélectionné n'est pas valide.";
    }

    // Validation du mot de passe
    if ($action === 'add' || ($action === 'edit' && !empty($mot_de_passe))) { // Mot de passe requis pour ajout, optionnel pour modification
        if (empty($mot_de_passe)) {
            $errors[] = "Le mot de passe est requis.";
        } elseif (strlen($mot_de_passe) < 6) { // Exemple de règle de longueur minimale
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
        } elseif ($mot_de_passe !== $mot_de_passe_confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
    }

    if (empty($errors)) {
        // Procéder à l'insertion ou à la mise à jour
        if ($action === 'add') {
            try {
                $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $stmt_insert = $pdo->prepare("INSERT INTO utilisateurs (prenom, nom, email, mot_de_passe, role, date_inscription) VALUES (:prenom, :nom, :email, :mot_de_passe, :role, NOW())");
                $stmt_insert->bindParam(':prenom', $prenom);
                $stmt_insert->bindParam(':nom', $nom);
                $stmt_insert->bindParam(':email', $email);
                $stmt_insert->bindParam(':mot_de_passe', $hashed_password);
                $stmt_insert->bindParam(':role', $role);
                $stmt_insert->execute();

                $_SESSION['admin_flash_message'] = "Utilisateur '" . htmlspecialchars($prenom . " " . $nom) . "' ajouté avec succès.";
                $_SESSION['admin_flash_type'] = "success";
                header("Location: manage_users.php");
                exit;
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de l\'ajout de l\'utilisateur : " . $e->getMessage();
                error_log("Admin user_actions.php (add) - PDOException: " . $e->getMessage());
            }
        } elseif ($action === 'edit' && $user_id) {
            try {
                // Construire la requête de mise à jour
                $sql_update = "UPDATE utilisateurs SET prenom = :prenom, nom = :nom, email = :email, role = :role";
                
                // Ajouter la mise à jour du mot de passe seulement s'il est fourni
                if (!empty($mot_de_passe)) {
                    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                    $sql_update .= ", mot_de_passe = :mot_de_passe";
                }
                
                $sql_update .= " WHERE id = :user_id";
                
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':prenom', $prenom);
                $stmt_update->bindParam(':nom', $nom);
                $stmt_update->bindParam(':email', $email);
                $stmt_update->bindParam(':role', $role);
                $stmt_update->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                
                if (!empty($mot_de_passe)) {
                    $stmt_update->bindParam(':mot_de_passe', $hashed_password);
                }
                
                $stmt_update->execute();

                $_SESSION['admin_flash_message'] = "Utilisateur '" . htmlspecialchars($prenom . " " . $nom) . "' (ID: " . htmlspecialchars($user_id) . ") mis à jour avec succès.";
                $_SESSION['admin_flash_type'] = "success";
                header("Location: manage_users.php");
                exit;

            } catch (PDOException $e) {
                $errors[] = "Erreur lors de la mise à jour de l\'utilisateur : " . $e->getMessage();
                error_log("Admin user_actions.php (edit) - PDOException: " . $e->getMessage());
                // Si une erreur PDO survient ici, elle sera gérée par le bloc d'erreur plus bas
            }
        }
    }

    // Si des erreurs, rediriger vers le formulaire avec les erreurs et les données soumises
    if (!empty($errors)) {
        $_SESSION['admin_form_errors'] = $errors;
        $_SESSION['admin_form_data'] = $_POST; // Conserver les données soumises pour re-remplir
        // Redirection vers le formulaire d'origine
        $redirect_url = "user_form.php?action=" . htmlspecialchars($action);
        if ($action === 'edit' && $user_id) {
            $redirect_url .= "&id=" . htmlspecialchars($user_id);
        }
        header("Location: " . $redirect_url);
        exit;
    }

} elseif ($action === 'delete' && $user_id && $_SERVER['REQUEST_METHOD'] === 'GET') { // Assurer que c'est une requête GET pour la suppression via le lien
    try {
        // Avant de supprimer, on pourrait vouloir récupérer le nom de l'utilisateur pour le message flash
        // C'est optionnel, mais peut rendre le message plus informatif.
        $stmt_get_user = $pdo->prepare("SELECT prenom, nom FROM utilisateurs WHERE id = :id");
        $stmt_get_user->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt_get_user->execute();
        $user_to_delete = $stmt_get_user->fetch(PDO::FETCH_ASSOC);
        $user_name_display = $user_to_delete ? htmlspecialchars($user_to_delete['prenom'] . ' ' . $user_to_delete['nom']) : "ID: " . htmlspecialchars($user_id);

        // Empêcher l'auto-suppression de l'administrateur actuellement connecté (si applicable)
        // Pour l'instant, avec le bypass d'authentification, $_SESSION['user_id'] est simulé dans admin_auth_check.php
        // Assurez-vous que $_SESSION['user_id'] est bien l'ID de l'admin connecté
        if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) {
             $_SESSION['admin_flash_message'] = "Erreur : Vous ne pouvez pas supprimer votre propre compte administrateur.";
             $_SESSION['admin_flash_type'] = "error";
        } else {
            $stmt_delete = $pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
            $stmt_delete->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt_delete->execute();

            if ($stmt_delete->rowCount() > 0) {
                $_SESSION['admin_flash_message'] = "Utilisateur '" . $user_name_display . "' supprimé avec succès.";
                $_SESSION['admin_flash_type'] = "success";
            } else {
                $_SESSION['admin_flash_message'] = "Erreur : Utilisateur non trouvé ou déjà supprimé (ID: " . htmlspecialchars($user_id) . ").";
                $_SESSION['admin_flash_type'] = "warning"; // ou "error"
            }
        }
        header("Location: manage_users.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['admin_flash_message'] = "Erreur lors de la suppression de l\'utilisateur : " . $e->getMessage();
        $_SESSION['admin_flash_type'] = "error";
        error_log("Admin user_actions.php (delete) - PDOException: " . $e->getMessage());
        header("Location: manage_users.php");
        exit;
    }
} else {
    // Action non reconnue ou méthode non POST pour add/edit
    $_SESSION['admin_flash_message'] = "Action non valide ou méthode incorrecte.";
    $_SESSION['admin_flash_type'] = "error";
    header("Location: manage_users.php");
    exit;
}

?>
