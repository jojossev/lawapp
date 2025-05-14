<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter les colonnes manquantes
    $sql = "ALTER TABLE livres 
            ADD COLUMN IF NOT EXISTS editeur VARCHAR(100),
            ADD COLUMN IF NOT EXISTS langue VARCHAR(50) DEFAULT 'Français',
            ADD COLUMN IF NOT EXISTS isbn VARCHAR(13),
            ADD COLUMN IF NOT EXISTS date_publication DATE";
    
    $pdo->exec($sql);
    echo "Colonnes ajoutées à la table 'livres' avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
