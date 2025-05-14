<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter la colonne derniere_connexion si elle n'existe pas
    $sql = "ALTER TABLE utilisateurs 
            ADD COLUMN IF NOT EXISTS derniere_connexion TIMESTAMP NULL";
    
    $pdo->exec($sql);
    echo "Colonne 'derniere_connexion' ajoutée avec succès à la table utilisateurs.\n";

} catch(PDOException $e) {
    die("Erreur lors de l'ajout de la colonne derniere_connexion : " . $e->getMessage());
}
?>
