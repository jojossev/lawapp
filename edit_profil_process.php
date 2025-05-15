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

// Récupérer et nettoyer les données du formulaire
$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validation simple (à améliorer si nécessaire)
if (empty($prenom) || empty($nom) || empty($email)) {
    $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'edit_profil.php';</script>";
    echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Tous les champs sont obligatoires. Redirection en cours...</div>";
    die();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "L'adresse email n'est pas valide.";
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'edit_profil.php';</script>";
    echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>L'adresse email n'est pas valide. Redirection en cours...</div>";
    die();
}

// Vérifier si l'email est déjà utilisé par un autre utilisateur
try {
    $stmt_check_email = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email AND id != :id");
    $stmt_check_email->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt_check_email->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt_check_email->execute();
    if ($stmt_check_email->fetch()) {
        $_SESSION['error_message'] = "Cette adresse email est déjà utilisée par un autre compte.";
        // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
        echo "<script>window.location.href = 'edit_profil.php';</script>";
        echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Cette adresse email est déjà utilisée par un autre compte. Redirection en cours...</div>";
        die();
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

// Rediriger vers la page de profil avec JavaScript
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mise à jour du profil</title>
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
        <h2>Profil mis à jour</h2>
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
