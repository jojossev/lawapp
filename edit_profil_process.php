<?php
require_once 'includes/config.php'; // Contient db_connect.php et session_start

// Vérifier si l'utilisateur est connecté et si la requête est POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Récupérer et nettoyer les données du formulaire
$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validation simple (à améliorer si nécessaire)
if (empty($prenom) || empty($nom) || empty($email)) {
    $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
    header('Location: edit_profil.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "L'adresse email n'est pas valide.";
    header('Location: edit_profil.php');
    exit;
}

// Vérifier si l'email est déjà utilisé par un autre utilisateur
try {
    $stmt_check_email = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email AND id != :id");
    $stmt_check_email->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt_check_email->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt_check_email->execute();
    if ($stmt_check_email->fetch()) {
        $_SESSION['error_message'] = "Cette adresse email est déjà utilisée par un autre compte.";
        header('Location: edit_profil.php');
        exit;
    }

    // Mettre à jour les informations de l'utilisateur
    $stmt_update = $pdo->prepare("UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email WHERE id = :id");
    $stmt_update->bindParam(':nom', $nom, PDO::PARAM_STR);
    $stmt_update->bindParam(':prenom', $prenom, PDO::PARAM_STR);
    $stmt_update->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt_update->bindParam(':id', $user_id, PDO::PARAM_INT);
    
    if ($stmt_update->execute()) {
        $_SESSION['success_message'] = "Vos informations ont été mises à jour avec succès.";
        // Mettre à jour le prénom dans la session si nécessaire pour l'affichage
        $_SESSION['user_prenom'] = $prenom;
    } else {
        $_SESSION['error_message'] = "Une erreur s'est produite lors de la mise à jour de vos informations.";
    }

} catch (PDOException $e) {
    error_log("Erreur PDO lors de la mise à jour du profil (user ID: {$user_id}): " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur technique est survenue. Veuillez réessayer.";
}

// Rediriger vers la page de profil (qui affichera le message de succès ou d'erreur)
header('Location: profil.php');
exit;
?>
