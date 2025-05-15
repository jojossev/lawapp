<?php
// Script simple pour créer les tables categories_livres et categories_podcasts
// Ce script est conçu pour être le plus simple possible afin d'éviter les erreurs

// Inclure uniquement les fichiers nécessaires
require_once __DIR__ . '/includes/config.php';

// Afficher un message de début
echo "<h1>Création des tables de catégories</h1>";

try {
    // Vérifier la connexion à la base de données
    if (!isset($pdo)) {
        throw new Exception("La connexion à la base de données n'est pas établie.");
    }
    
    echo "<p>Connexion à la base de données établie.</p>";
    
    // Fonction pour vérifier si une table existe
    function tableExists($pdo, $table) {
        try {
            // Pour PostgreSQL
            if (strpos(DB_URL, 'pgsql') !== false) {
                $sql = "SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_name = '$table'
                )";
                $stmt = $pdo->query($sql);
                return $stmt->fetchColumn();
            } 
            // Pour MySQL
            else {
                $sql = "SHOW TABLES LIKE '$table'";
                $stmt = $pdo->query($sql);
                return $stmt->rowCount() > 0;
            }
        } catch (PDOException $e) {
            echo "<p>Erreur lors de la vérification de l'existence de la table: " . $e->getMessage() . "</p>";
            return false;
        }
    }
    
    // Créer la table categories_livres si elle n'existe pas
    if (!tableExists($pdo, 'categories_livres')) {
        echo "<p>La table 'categories_livres' n'existe pas. Création de la table...</p>";
        
        try {
            if (strpos(DB_URL, 'pgsql') !== false) {
                // PostgreSQL
                $sql = "
                CREATE TABLE categories_livres (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
            } else {
                // MySQL
                $sql = "
                CREATE TABLE categories_livres (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
            }
            
            $pdo->exec($sql);
            echo "<p>Table 'categories_livres' créée avec succès.</p>";
            
            // Insérer des catégories de test
            $sql = "INSERT INTO categories_livres (nom, description) VALUES 
                ('Droit civil', 'Livres sur le droit civil'),
                ('Droit pénal', 'Livres sur le droit pénal'),
                ('Droit des affaires', 'Livres sur le droit des affaires')";
            $pdo->exec($sql);
            echo "<p>Catégories de livres de test insérées avec succès.</p>";
        } catch (PDOException $e) {
            echo "<p>Erreur lors de la création de la table categories_livres: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>La table 'categories_livres' existe déjà.</p>";
    }
    
    // Créer la table categories_podcasts si elle n'existe pas
    if (!tableExists($pdo, 'categories_podcasts')) {
        echo "<p>La table 'categories_podcasts' n'existe pas. Création de la table...</p>";
        
        try {
            if (strpos(DB_URL, 'pgsql') !== false) {
                // PostgreSQL
                $sql = "
                CREATE TABLE categories_podcasts (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
            } else {
                // MySQL
                $sql = "
                CREATE TABLE categories_podcasts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
            }
            
            $pdo->exec($sql);
            echo "<p>Table 'categories_podcasts' créée avec succès.</p>";
            
            // Insérer des catégories de test
            $sql = "INSERT INTO categories_podcasts (nom, description) VALUES 
                ('Interviews', 'Podcasts d\'interviews avec des experts juridiques'),
                ('Analyses juridiques', 'Analyses de sujets juridiques actuels'),
                ('Conseils pratiques', 'Conseils pratiques sur des questions juridiques')";
            $pdo->exec($sql);
            echo "<p>Catégories de podcasts de test insérées avec succès.</p>";
        } catch (PDOException $e) {
            echo "<p>Erreur lors de la création de la table categories_podcasts: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>La table 'categories_podcasts' existe déjà.</p>";
    }
    
    // Afficher les tables existantes
    echo "<h2>Tables existantes dans la base de données :</h2>";
    
    try {
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "SELECT table_name 
                    FROM information_schema.tables 
                    WHERE table_schema = 'public'";
        } else {
            // MySQL
            $sql = "SHOW TABLES";
        }
        
        $stmt = $pdo->query($sql);
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } catch (PDOException $e) {
        echo "<p>Erreur lors de l'affichage des tables: " . $e->getMessage() . "</p>";
    }
    
    echo "<p>Opération terminée.</p>";
    
} catch (Exception $e) {
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
}

// Liens de retour
echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
echo "<p><a href='add_column_id_categorie.php'>Ajouter la colonne id_categorie à la table livres</a></p>";
echo "<p><a href='admin/init_db.php'>Initialiser la base de données</a></p>";
?>
