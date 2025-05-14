<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter les colonnes manquantes à la table livres
    $sql = "ALTER TABLE livres 
            ADD COLUMN IF NOT EXISTS url_document VARCHAR(255),
            ADD COLUMN IF NOT EXISTS type_document VARCHAR(50),
            ADD COLUMN IF NOT EXISTS couverture_url VARCHAR(255),
            ADD COLUMN IF NOT EXISTS annee_publication INT,
            ADD COLUMN IF NOT EXISTS niveau VARCHAR(50),
            ADD COLUMN IF NOT EXISTS id_categorie INT,
            ADD COLUMN IF NOT EXISTS id_createur INT,
            ADD COLUMN IF NOT EXISTS date_mise_a_jour TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            ADD FOREIGN KEY IF NOT EXISTS (id_createur) REFERENCES utilisateurs(id)";
    
    $pdo->exec($sql);
    echo "Colonnes ajoutées à la table 'livres' avec succès.\n";

    // Créer la table des catégories de livres si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS livre_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Table 'livre_categories' créée avec succès.\n";

    // Ajouter une catégorie par défaut
    $sql = "INSERT INTO livre_categories (nom, description) 
            SELECT 'Droit Civil', 'Livres sur le droit civil'
            WHERE NOT EXISTS (SELECT 1 FROM livre_categories WHERE nom = 'Droit Civil')";
    
    $pdo->exec($sql);
    echo "Catégorie par défaut ajoutée avec succès.\n";

    // Créer la table des accès aux livres
    $sql = "CREATE TABLE IF NOT EXISTS acces_livres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_livre INT NOT NULL,
        date_acces TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
        FOREIGN KEY (id_livre) REFERENCES livres(id),
        UNIQUE KEY unique_acces (id_utilisateur, id_livre)
    )";
    
    $pdo->exec($sql);
    echo "Table 'acces_livres' créée avec succès.\n";

    // Mettre à jour le livre de test
    $sql = "UPDATE livres 
            SET type_document = 'PDF',
                url_document = '/LawApp/assets/documents/droit-civil.pdf',
                couverture_url = '/LawApp/assets/images/livres/droit-civil.jpg',
                annee_publication = 2025,
                niveau = 'Débutant',
                id_categorie = (SELECT id FROM livre_categories WHERE nom = 'Droit Civil' LIMIT 1)
            WHERE titre = 'Introduction au Droit Civil'";
    
    $pdo->exec($sql);
    echo "Livre de test mis à jour avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
