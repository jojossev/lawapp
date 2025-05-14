<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Créer la table des livres
    $sql = "CREATE TABLE IF NOT EXISTS livres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        auteur VARCHAR(255) NOT NULL,
        description TEXT,
        prix DECIMAL(10,2) DEFAULT 0.00,
        image_url VARCHAR(255),
        fichier_url VARCHAR(255),
        isbn VARCHAR(13),
        editeur VARCHAR(255),
        date_publication DATE,
        nombre_pages INT,
        langue VARCHAR(50) DEFAULT 'Français',
        categories JSON,
        tags JSON,
        statut ENUM('disponible', 'indisponible') DEFAULT 'disponible',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "Table 'livres' créée avec succès.\n";

    // Créer la table des achats de livres
    $sql = "CREATE TABLE IF NOT EXISTS achats_livres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_livre INT NOT NULL,
        date_achat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        prix_paye DECIMAL(10,2) NOT NULL,
        transaction_id VARCHAR(50),
        statut ENUM('en_cours', 'complete', 'rembourse') DEFAULT 'en_cours',
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
        FOREIGN KEY (id_livre) REFERENCES livres(id),
        UNIQUE KEY unique_achat (id_utilisateur, id_livre)
    )";
    
    $pdo->exec($sql);
    echo "Table 'achats_livres' créée avec succès.\n";

    // Créer la table des avis de livres
    $sql = "CREATE TABLE IF NOT EXISTS avis_livres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_livre INT NOT NULL,
        note INT CHECK (note BETWEEN 1 AND 5),
        commentaire TEXT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
        FOREIGN KEY (id_livre) REFERENCES livres(id),
        UNIQUE KEY unique_avis (id_utilisateur, id_livre)
    )";
    
    $pdo->exec($sql);
    echo "Table 'avis_livres' créée avec succès.\n";

    // Insérer un livre de test
    $stmt = $pdo->prepare("SELECT id FROM livres WHERE titre = 'Introduction au Droit Civil'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        $sql = "INSERT INTO livres (titre, auteur, description, prix, image_url) 
                VALUES (
                    'Introduction au Droit Civil',
                    'Jean Dupont',
                    'Un guide complet pour comprendre les bases du droit civil.',
                    29.99,
                    '/LawApp/assets/images/livres/droit-civil.jpg'
                )";
        
        $pdo->exec($sql);
        echo "Livre de test ajouté avec succès.\n";
    } else {
        echo "Le livre de test existe déjà.\n";
    }

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
