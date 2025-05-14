<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter la colonne duree
    $sql = "ALTER TABLE cours 
            ADD COLUMN IF NOT EXISTS duree INT DEFAULT 0";
    
    $pdo->exec($sql);
    echo "Colonne ajoutée à la table 'cours' avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
