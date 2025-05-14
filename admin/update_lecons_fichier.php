<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Vérifier si la colonne fichier_path existe déjà
    $sql = "SHOW COLUMNS FROM lecons LIKE 'fichier_path'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $column_exists = $stmt->fetch();

    if (!$column_exists) {
        // Ajouter la colonne fichier_path
        $sql = "ALTER TABLE lecons ADD COLUMN fichier_path VARCHAR(255) DEFAULT NULL AFTER contenu_principal";
        $pdo->exec($sql);
        echo "La colonne fichier_path a été ajoutée avec succès à la table lecons.";
    } else {
        echo "La colonne fichier_path existe déjà dans la table lecons.";
    }

} catch (PDOException $e) {
    die("Erreur lors de la mise à jour de la table : " . $e->getMessage());
}
?>
