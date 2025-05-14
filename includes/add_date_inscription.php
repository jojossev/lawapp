<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter la colonne date_inscription si elle n'existe pas
    $sql = "ALTER TABLE utilisateurs 
            ADD COLUMN IF NOT EXISTS date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    
    $pdo->exec($sql);
    echo "Colonne 'date_inscription' ajoutée avec succès à la table utilisateurs.\n";

} catch(PDOException $e) {
    die("Erreur lors de l'ajout de la colonne date_inscription : " . $e->getMessage());
}
?>
