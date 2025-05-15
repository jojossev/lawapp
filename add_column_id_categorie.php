<?php
// Script simple pour ajouter la colonne id_categorie à la table livres
// Ce script est conçu pour être le plus simple possible afin d'éviter les erreurs

// Inclure uniquement les fichiers nécessaires
require_once __DIR__ . '/includes/config.php';

// Afficher un message de début
echo "<h1>Ajout de la colonne id_categorie à la table livres</h1>";

try {
    // Vérifier la connexion à la base de données
    if (!isset($pdo)) {
        throw new Exception("La connexion à la base de données n'est pas établie.");
    }
    
    echo "<p>Connexion à la base de données établie.</p>";
    
    // Vérifier si la table livres existe
    $table_exists = false;
    
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.tables 
                WHERE table_name = 'livres'
            )";
            $stmt = $pdo->query($sql);
            $table_exists = $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SHOW TABLES LIKE 'livres'";
            $stmt = $pdo->query($sql);
            $table_exists = $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p>Erreur lors de la vérification de l'existence de la table: " . $e->getMessage() . "</p>";
    }
    
    if (!$table_exists) {
        echo "<p>La table 'livres' n'existe pas. Création de la table...</p>";
        
        // Créer la table livres
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql_create = "
            CREATE TABLE livres (
                id SERIAL PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                auteur VARCHAR(255) NOT NULL,
                description TEXT,
                annee_publication INT,
                editeur VARCHAR(255),
                isbn VARCHAR(20),
                id_categorie INT,
                image_couverture VARCHAR(255),
                fichier_pdf VARCHAR(255),
                date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                statut VARCHAR(20) DEFAULT 'actif'
            )";
        } else {
            // MySQL
            $sql_create = "
            CREATE TABLE livres (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                auteur VARCHAR(255) NOT NULL,
                description TEXT,
                annee_publication INT,
                editeur VARCHAR(255),
                isbn VARCHAR(20),
                id_categorie INT,
                image_couverture VARCHAR(255),
                fichier_pdf VARCHAR(255),
                date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                statut VARCHAR(20) DEFAULT 'actif'
            )";
        }
        
        $pdo->exec($sql_create);
        echo "<p>Table 'livres' créée avec succès.</p>";
    } else {
        echo "<p>La table 'livres' existe déjà.</p>";
        
        // Vérifier si la colonne id_categorie existe
        $column_exists = false;
        
        try {
            // Pour PostgreSQL
            if (strpos(DB_URL, 'pgsql') !== false) {
                $sql = "SELECT EXISTS (
                    SELECT 1 FROM information_schema.columns 
                    WHERE table_name = 'livres' AND column_name = 'id_categorie'
                )";
                $stmt = $pdo->query($sql);
                $column_exists = $stmt->fetchColumn();
            } 
            // Pour MySQL
            else {
                $sql = "SHOW COLUMNS FROM livres LIKE 'id_categorie'";
                $stmt = $pdo->query($sql);
                $column_exists = $stmt->rowCount() > 0;
            }
        } catch (PDOException $e) {
            echo "<p>Erreur lors de la vérification de l'existence de la colonne: " . $e->getMessage() . "</p>";
        }
        
        if (!$column_exists) {
            echo "<p>La colonne 'id_categorie' n'existe pas. Ajout de la colonne...</p>";
            
            // Ajouter la colonne id_categorie
            try {
                if (strpos(DB_URL, 'pgsql') !== false) {
                    // PostgreSQL
                    $sql_add_column = "ALTER TABLE livres ADD COLUMN id_categorie INT";
                } else {
                    // MySQL
                    $sql_add_column = "ALTER TABLE livres ADD COLUMN id_categorie INT";
                }
                
                $pdo->exec($sql_add_column);
                echo "<p>Colonne 'id_categorie' ajoutée avec succès.</p>";
            } catch (PDOException $e) {
                echo "<p>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>La colonne 'id_categorie' existe déjà.</p>";
        }
    }
    
    // Afficher la structure de la table
    echo "<h2>Structure actuelle de la table livres :</h2>";
    
    try {
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "SELECT column_name, data_type 
                    FROM information_schema.columns 
                    WHERE table_name = 'livres'";
        } else {
            // MySQL
            $sql = "DESCRIBE livres";
        }
        
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
    } catch (PDOException $e) {
        echo "<p>Erreur lors de l'affichage de la structure: " . $e->getMessage() . "</p>";
    }
    
    echo "<p>Opération terminée.</p>";
    
} catch (Exception $e) {
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
}

// Liens de retour
echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
echo "<p><a href='admin/init_db.php'>Initialiser la base de données</a></p>";
?>
