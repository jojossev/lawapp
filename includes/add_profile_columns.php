<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter les colonnes manquantes pour les paramètres utilisateur
    $sql = "ALTER TABLE utilisateurs 
            ADD COLUMN IF NOT EXISTS receive_email_notifications BOOLEAN DEFAULT TRUE,
            ADD COLUMN IF NOT EXISTS ui_theme VARCHAR(10) DEFAULT 'light'";
    
    $pdo->exec($sql);
    echo "Colonnes de paramètres utilisateur ajoutées avec succès.\n";

    // Créer la table inscriptions si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS inscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_cours INT NOT NULL,
        date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        statut ENUM('en_cours', 'termine', 'abandonne') DEFAULT 'en_cours',
        progression INT DEFAULT 0,
        derniere_activite TIMESTAMP NULL,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
        FOREIGN KEY (id_cours) REFERENCES cours(id),
        UNIQUE KEY unique_inscription (id_utilisateur, id_cours)
    )";
    
    $pdo->exec($sql);
    echo "Table 'inscriptions' créée/vérifiée avec succès.\n";

    // Créer la table cours si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS cours (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        prix DECIMAL(10,2) DEFAULT 0.00,
        duree INT DEFAULT 0,
        niveau ENUM('debutant', 'intermediaire', 'avance') DEFAULT 'debutant',
        statut ENUM('brouillon', 'publie', 'archive') DEFAULT 'brouillon',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Table 'cours' créée/vérifiée avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
