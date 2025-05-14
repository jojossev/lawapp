<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter les colonnes manquantes à la table podcasts
    $sql = "ALTER TABLE podcasts 
            ADD COLUMN IF NOT EXISTS audio_url VARCHAR(255),
            ADD COLUMN IF NOT EXISTS image_url VARCHAR(255),
            ADD COLUMN IF NOT EXISTS nombre_ecoutes INT DEFAULT 0,
            ADD COLUMN IF NOT EXISTS duree INT DEFAULT 0,
            ADD COLUMN IF NOT EXISTS format VARCHAR(10) DEFAULT 'MP3',
            ADD COLUMN IF NOT EXISTS taille_fichier INT DEFAULT 0,
            ADD COLUMN IF NOT EXISTS est_premium BOOLEAN DEFAULT FALSE,
            ADD COLUMN IF NOT EXISTS tags TEXT";
    
    $pdo->exec($sql);
    echo "Colonnes ajoutées à la table 'podcasts' avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
