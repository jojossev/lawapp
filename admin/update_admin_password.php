<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Mettre à jour le mot de passe en texte clair
    $stmt = $pdo->prepare("UPDATE administrateurs SET mot_de_passe = :password WHERE email = :email");
    
    // Utiliser 'admin' comme mot de passe en texte clair
    $stmt->execute([
        'password' => 'admin',
        'email' => 'admin@lawapp.com' // ou l'email de l'administrateur que vous utilisez
    ]);

    if ($stmt->rowCount() > 0) {
        echo "Mot de passe mis à jour avec succès !<br>";
        echo "Nouveau mot de passe : admin";
    } else {
        echo "Aucun administrateur trouvé avec cet email.";
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
