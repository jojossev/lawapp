<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter la colonne nombre_pages
    $sql = "ALTER TABLE livres 
            ADD COLUMN IF NOT EXISTS nombre_pages INT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS statut ENUM('brouillon', 'publie', 'archive') DEFAULT 'publie'";
    
    $pdo->exec($sql);
    echo "Colonnes ajoutées à la table 'livres' avec succès.\n";

    // Mettre à jour le livre de test
    $sql = "UPDATE livres 
            SET nombre_pages = 350,
                statut = 'publie'
            WHERE titre = 'Introduction au Droit Civil'";
    
    $pdo->exec($sql);
    echo "Livre de test mis à jour avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
