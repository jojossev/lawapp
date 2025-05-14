<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Rediriger si ce n'est pas une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');

// Vérifier uniquement l'email
if (empty($email)) {
    $_SESSION['login_error_message'] = "L'adresse email est requise.";
    header("Location: admin_login.php");
    exit;
}

if (!isset($pdo)) {
    $_SESSION['login_error_message'] = "Erreur de configuration : BDD non disponible.";
    header("Location: admin_login.php");
    exit;
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

        // Redirection vers le tableau de bord
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $_SESSION['login_error_message'] = "Administrateur non trouvé.";
        header("Location: admin_login.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Erreur login admin : " . $e->getMessage());
    $_SESSION['login_error_message'] = "Erreur technique.";
    header("Location: admin_login.php");
    exit;
}

// Si on arrive ici, l'authentification a échoué
header("Location: admin_login.php");
exit;
?>
