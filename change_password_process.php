<?php
require_once 'includes/config.php'; // Contient db_connect.php et session_start

// Vérifier si l'utilisateur est connecté et si la requête est POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Récupérer les données du formulaire
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
    header('Location: change_password.php');
    exit;
}

if (strlen($new_password) < 8) {
    $_SESSION['error_message'] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    header('Location: change_password.php');
    exit;
}

if ($new_password !== $confirm_password) {
    $_SESSION['error_message'] = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
    header('Location: change_password.php');
    exit;
}

try {
    // 1. Récupérer le hash du mot de passe actuel de l'utilisateur
    $stmt_get_pass = $pdo->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = :id");
    $stmt_get_pass->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt_get_pass->execute();
    $user = $stmt_get_pass->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Utilisateur non trouvé."); // Ne devrait pas arriver
    }

    $current_password_hash = $user['mot_de_passe'];

    // 2. Vérifier si le mot de passe actuel fourni correspond au hash stocké
    // IMPORTANT: Ceci suppose que les mots de passe des utilisateurs sont hachés en BDD
    if (!password_verify($current_password, $current_password_hash)) {
        $_SESSION['error_message'] = "Le mot de passe actuel est incorrect.";
        header('Location: change_password.php');
        exit;
    }

    // 3. Hasher le nouveau mot de passe
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT); // Utilise l'algorithme par défaut (recommandé)

    // 4. Mettre à jour le mot de passe dans la base de données
    $stmt_update_pass = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = :new_hash WHERE id = :id");
    $stmt_update_pass->bindParam(':new_hash', $new_password_hash, PDO::PARAM_STR);
    $stmt_update_pass->bindParam(':id', $user_id, PDO::PARAM_INT);

    if ($stmt_update_pass->execute()) {
        $_SESSION['success_message'] = "Votre mot de passe a été changé avec succès.";
        // Optionnel: rediriger vers profil.php au lieu de change_password.php
        header('Location: profil.php'); 
    } else {
        $_SESSION['error_message'] = "Une erreur s'est produite lors de la mise à jour de votre mot de passe.";
        header('Location: change_password.php');
    }

} catch (PDOException $e) {
    error_log("Erreur PDO lors du changement de MDP (user ID: {$user_id}): " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur technique est survenue. Veuillez réessayer.";
    header('Location: change_password.php');
} catch (Exception $e) {
    error_log("Erreur lors du changement de MDP (user ID: {$user_id}): " . $e->getMessage());
    $_SESSION['error_message'] = $e->getMessage(); // Peut être "Utilisateur non trouvé."
    header('Location: change_password.php');
}

exit;
?>
