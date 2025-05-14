<?php
require_once 'includes/config.php'; // Contient db_connect.php et session_start

// Vérifier si l'utilisateur est connecté et si la requête est POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Récupérer et valider les données
// Si la case n'est pas cochée, $_POST['notifications_email'] ne sera pas envoyée.
$receive_notifications = isset($_POST['notifications_email']) ? 1 : 0;
$theme = $_POST['theme_app'] ?? 'light'; // Valeur par défaut

// Valider le thème (doit être 'light' ou 'dark')
if (!in_array($theme, ['light', 'dark'])) {
    $theme = 'light'; // Revenir au défaut si la valeur est invalide
}

try {
    // Mettre à jour les paramètres dans la base de données
    $stmt_update = $pdo->prepare("UPDATE utilisateurs SET receive_email_notifications = :notifications, ui_theme = :theme WHERE id = :id");
    $stmt_update->bindParam(':notifications', $receive_notifications, PDO::PARAM_INT);
    $stmt_update->bindParam(':theme', $theme, PDO::PARAM_STR);
    $stmt_update->bindParam(':id', $user_id, PDO::PARAM_INT);

    if ($stmt_update->execute()) {
        $_SESSION['success_message'] = "Paramètres mis à jour avec succès.";
        // Mettre à jour la session si l'apparence dépend directement du thème
        // $_SESSION['ui_theme'] = $theme; 
    } else {
        $_SESSION['error_message'] = "Une erreur s'est produite lors de la mise à jour des paramètres.";
    }

} catch (PDOException $e) {
    error_log("Erreur PDO lors de la mise à jour des paramètres (user ID: {$user_id}): " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur technique est survenue. Veuillez réessayer.";
}

// Rediriger vers la page de profil
header('Location: profil.php');
exit;
?>
