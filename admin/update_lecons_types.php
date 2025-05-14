<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Désactiver temporairement les contraintes de clé étrangère
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Modifier la colonne type_contenu pour inclure les nouveaux types
    $sql = "ALTER TABLE lecons 
            MODIFY COLUMN type_contenu ENUM('texte', 'video', 'pdf', 'docx', 'mp3', 'mp4') 
            DEFAULT 'texte'";
    
    $pdo->exec($sql);
    
    // Ajouter une colonne pour stocker le chemin du fichier
    $sql_add_file = "ALTER TABLE lecons 
                     ADD COLUMN fichier_path VARCHAR(255) 
                     AFTER contenu_principal";
    
    $pdo->exec($sql_add_file);
    
    // Réactiver les contraintes de clé étrangère
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Types de contenu mis à jour avec succès !";

} catch (PDOException $e) {
    die("Erreur lors de la mise à jour des types : " . $e->getMessage());
}
