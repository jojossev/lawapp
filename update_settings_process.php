<?php
require_once 'includes/config.php'; // Contient db_connect.php et session_start

// Vérifier si l'utilisateur est connecté et si la requête est POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'login.php';</script>";
    echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Accès non autorisé. Redirection en cours...</div>";
    die();
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

// Rediriger vers la page de profil avec JavaScript
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mise à jour des paramètres</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .message {
            padding: 15px;
            margin: 20px auto;
            max-width: 500px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="message success">
        <h2>Paramètres mis à jour</h2>
        <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
    </div>
    <?php elseif (isset($_SESSION['error_message'])): ?>
    <div class="message error">
        <h2>Erreur</h2>
        <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
    </div>
    <?php else: ?>
    <div class="message success">
        <h2>Traitement terminé</h2>
        <p>Redirection vers votre profil...</p>
    </div>
    <?php endif; ?>
    
    <script>
        // Redirection après 2 secondes
        setTimeout(function() {
            window.location.href = 'profil.php';
        }, 2000);
    </script>
</body>
</html>
