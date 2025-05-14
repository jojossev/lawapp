<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

try {
    // Récupérer l'admin
    $stmt = $pdo->query("SELECT id, prenom, nom, email, role FROM administrateurs WHERE email = 'admin@lawapp.com' AND statut = 'actif' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Connexion directe
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_prenom'] = $admin['prenom'];
        $_SESSION['admin_nom'] = $admin['nom'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        
        // Mettre à jour la dernière connexion
        $pdo->prepare("UPDATE administrateurs SET derniere_connexion = CURRENT_TIMESTAMP WHERE id = ?")->execute([$admin['id']]);

        echo "Connexion réussie ! Redirection...";
        header("Refresh: 1; URL=admin_dashboard.php");
    } else {
        echo "Erreur : Aucun administrateur trouvé.<br>";
        echo "<a href='setup_admin.php'>Cliquez ici pour créer un compte admin</a>";
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
