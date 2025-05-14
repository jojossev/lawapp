<?php
require_once 'config.php';

try {
    // 1. Créer la table utilisateurs si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        mot_de_passe VARCHAR(255) NOT NULL,
        prenom VARCHAR(100),
        nom VARCHAR(100),
        date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        derniere_connexion DATETIME,
        statut ENUM('actif', 'inactif') DEFAULT 'actif',
        type_compte ENUM('gratuit', 'premium') DEFAULT 'gratuit',
        avatar_url VARCHAR(255),
        bio TEXT
    )";
    
    $pdo->exec($sql);
    echo "Table 'utilisateurs' créée ou déjà existante.<br>";

    // 2. Créer un utilisateur de test
    $email = "test@lawapp.com";
    $password = password_hash("test123", PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() == 0) {
        $sql = "INSERT INTO utilisateurs (email, mot_de_passe, prenom, nom, type_compte) 
                VALUES (?, ?, 'Test', 'Utilisateur', 'premium')";
        $pdo->prepare($sql)->execute([$email, $password]);
        echo "Utilisateur de test créé :<br>";
        echo "Email : test@lawapp.com<br>";
        echo "Mot de passe : test123<br>";
    }

    echo "<br>Configuration terminée avec succès !";

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
