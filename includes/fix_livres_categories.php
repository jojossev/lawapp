<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Supprimer l'ancienne contrainte de clé étrangère
    $sql = "ALTER TABLE livres 
            DROP FOREIGN KEY IF EXISTS livres_ibfk_1";
    $pdo->exec($sql);
    echo "Ancienne contrainte supprimée.\n";

    // Ajouter la nouvelle contrainte
    $sql = "ALTER TABLE livres 
            ADD CONSTRAINT fk_livre_categorie 
            FOREIGN KEY (id_categorie) 
            REFERENCES livre_categories(id) 
            ON DELETE SET NULL";
    $pdo->exec($sql);
    echo "Nouvelle contrainte ajoutée.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
