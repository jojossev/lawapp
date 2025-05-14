<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter la colonne ordre aux modules et leçons
    $sql = "ALTER TABLE modules 
            ADD COLUMN IF NOT EXISTS ordre INT DEFAULT 0";
    $pdo->exec($sql);
    echo "Colonne ajoutée à la table 'modules' avec succès.\n";

    $sql = "ALTER TABLE lecons 
            ADD COLUMN IF NOT EXISTS ordre INT DEFAULT 0";
    $pdo->exec($sql);
    echo "Colonne ajoutée à la table 'lecons' avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
