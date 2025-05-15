<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Rediriger si ce n'est pas une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'admin_login.php';</script>";
    echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Méthode non autorisée. Redirection en cours...</div>";
    die();
}

$email = trim($_POST['email'] ?? '');

// Vérifier uniquement l'email
if (empty($email)) {
    $_SESSION['login_error_message'] = "L'adresse email est requise.";
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'admin_login.php';</script>";
    echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>L'adresse email est requise. Redirection en cours...</div>";
    die();
}

if (!isset($pdo)) {
    $_SESSION['login_error_message'] = "Erreur de configuration : BDD non disponible.";
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'admin_login.php';</script>";
    echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Erreur de configuration : BDD non disponible. Redirection en cours...</div>";
    die();
}

try {
    // Vérifier si l'administrateur existe
    $stmt = $pdo->prepare("SELECT id, prenom, nom, email, role FROM administrateurs WHERE email = :email AND statut = 'actif'");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Connexion automatique
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_prenom'] = $admin['prenom'];
        $_SESSION['admin_nom'] = $admin['nom'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        
        // Mettre à jour la dernière connexion
        $pdo->prepare("UPDATE administrateurs SET derniere_connexion = CURRENT_TIMESTAMP WHERE id = :id")
            ->execute(['id' => $admin['id']]);

        // Redirection vers le tableau de bord avec JavaScript
        echo "<script>window.location.href = 'admin_dashboard.php';</script>";
        echo "<div style='background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Connexion réussie. Redirection vers le tableau de bord...</div>";
        die();
    } else {
        $_SESSION['login_error_message'] = "Administrateur non trouvé.";
        // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
        echo "<script>window.location.href = 'admin_login.php';</script>";
        echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Administrateur non trouvé. Redirection en cours...</div>";
        die();
    }
} catch (PDOException $e) {
    error_log("Erreur login admin : " . $e->getMessage());
    $_SESSION['login_error_message'] = "Erreur technique.";
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'admin_login.php';</script>";
    echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Erreur technique. Redirection en cours...</div>";
    die();
}

// Si on arrive ici, l'authentification a échoué
// Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
echo "<script>window.location.href = 'admin_login.php';</script>";
echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Authentification échouée. Redirection en cours...</div>";
die();
?>
