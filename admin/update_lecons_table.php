<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Vérifier si la colonne ordre existe déjà
    $sql_check = "SHOW COLUMNS FROM lecons LIKE 'ordre'";
    $result = $pdo->query($sql_check);
    
    if ($result->rowCount() == 0) {
        // Ajouter la colonne ordre si elle n'existe pas
        $sql_alter = "ALTER TABLE lecons ADD COLUMN ordre INT DEFAULT 0 AFTER contenu_principal";
        $pdo->exec($sql_alter);
        echo "Colonne 'ordre' ajoutée avec succès à la table lecons !";
    } else {
        echo "La colonne 'ordre' existe déjà.";
    }

} catch (PDOException $e) {
    die("Erreur lors de la modification de la table : " . $e->getMessage());
}
