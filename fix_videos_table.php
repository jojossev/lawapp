<?php
// Script pour corriger la structure de la table videos
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

// Fonction pour exécuter une requête SQL et gérer les erreurs
function executeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "<p style='color:green'>✓ $description : Succès</p>";
        return true;
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ $description: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.tables 
                WHERE table_name = :table
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return (bool) $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT table_name FROM information_schema.tables 
                    WHERE table_schema = DATABASE() AND table_name = :table";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.columns 
                WHERE table_name = :table AND column_name = :column
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return (bool) $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT column_name FROM information_schema.columns 
                    WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la colonne: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Correction de la table videos</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Correction de la table videos</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

echo "<p class='success'>Connexion à la base de données établie avec succès.</p>";

// 1. Vérifier si la table categories_videos existe
if (!tableExists($pdo, 'categories_videos')) {
    echo "<p class='warning'>La table 'categories_videos' n'existe pas. Création de la table...</p>";
    
    // Créer la table categories_videos
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_categories = "
        CREATE TABLE categories_videos (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_categories = "
        CREATE TABLE categories_videos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_categories, "Création de la table categories_videos");
    
    // Insérer des catégories de test
    $sql_insert_categories = "
    INSERT INTO categories_videos (nom, description) VALUES 
        ('Tutoriels', 'Vidéos tutorielles sur des concepts juridiques'),
        ('Conférences', 'Enregistrements de conférences juridiques'),
        ('Explications', 'Explications détaillées de concepts juridiques')";
    
    executeQuery($pdo, $sql_insert_categories, "Insertion des catégories de vidéos de test");
} else {
    echo "<p class='success'>La table 'categories_videos' existe déjà.</p>";
}

// 2. Vérifier si la table videos existe
if (!tableExists($pdo, 'videos')) {
    echo "<p class='warning'>La table 'videos' n'existe pas. Création de la table...</p>";
    
    // Créer la table videos
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_videos = "
        CREATE TABLE videos (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            url VARCHAR(255) NOT NULL,
            duree VARCHAR(10),
            id_categorie INT,
            vues INT DEFAULT 0,
            date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif'
        )";
    } else {
        // MySQL
        $sql_create_videos = "
        CREATE TABLE videos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            url VARCHAR(255) NOT NULL,
            duree VARCHAR(10),
            id_categorie INT,
            vues INT DEFAULT 0,
            date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif'
        )";
    }
    
    executeQuery($pdo, $sql_create_videos, "Création de la table videos");
    
    // Insérer des vidéos de test
    $sql_insert_videos = "
    INSERT INTO videos (titre, description, url, duree, id_categorie) VALUES 
        ('Introduction au droit civil', 'Une introduction complète au droit civil', 'https://www.youtube.com/watch?v=example1', '10:30', 1),
        ('Conférence sur le droit pénal', 'Enregistrement d''une conférence sur le droit pénal', 'https://www.youtube.com/watch?v=example2', '45:20', 2),
        ('Comment rédiger un contrat', 'Tutoriel sur la rédaction de contrats', 'https://www.youtube.com/watch?v=example3', '15:45', 1)";
    
    executeQuery($pdo, $sql_insert_videos, "Insertion des vidéos de test");
} else {
    echo "<p class='success'>La table 'videos' existe déjà.</p>";
    
    // Vérifier les colonnes importantes
    $important_columns = ['id_categorie', 'vues'];
    $column_definitions = [
        'id_categorie' => 'INT',
        'vues' => 'INT DEFAULT 0'
    ];
    
    foreach ($important_columns as $column) {
        if (!columnExists($pdo, 'videos', $column)) {
            echo "<p class='warning'>La colonne '$column' n'existe pas dans la table 'videos'. Ajout de la colonne...</p>";
            
            $column_type = $column_definitions[$column];
            $sql_add_column = "ALTER TABLE videos ADD COLUMN $column $column_type";
            
            executeQuery($pdo, $sql_add_column, "Ajout de la colonne '$column'");
        } else {
            echo "<p class='success'>La colonne '$column' existe dans la table 'videos'.</p>";
        }
    }
}

// Afficher la structure actuelle des tables
echo "<h2>Structure actuelle des tables</h2>";

$tables = ['categories_videos', 'videos'];

foreach ($tables as $table) {
    if (tableExists($pdo, $table)) {
        echo "<h3>Table '$table'</h3>";
        echo "<pre>";
        try {
            if (strpos(DB_URL, 'pgsql') !== false) {
                // PostgreSQL
                $sql = "SELECT column_name, data_type, character_maximum_length 
                        FROM information_schema.columns 
                        WHERE table_name = '$table'";
            } else {
                // MySQL
                $sql = "DESCRIBE $table";
            }
            
            $stmt = $pdo->query($sql);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            print_r($columns);
        } catch (PDOException $e) {
            echo "Erreur lors de l'affichage de la structure: " . $e->getMessage();
        }
        echo "</pre>";
        
        // Afficher quelques données
        echo "<h4>Données dans la table '$table'</h4>";
        echo "<pre>";
        try {
            $sql = "SELECT * FROM $table LIMIT 5";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            print_r($data);
        } catch (PDOException $e) {
            echo "Erreur lors de l'affichage des données: " . $e->getMessage();
        }
        echo "</pre>";
    }
}

// Liens de retour
echo "<h2>Liens utiles :</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='fix_all_tables.php'>Corriger toutes les tables</a></li>";
echo "<li><a href='fix_admin_table.php'>Corriger la table administrateurs</a></li>";
echo "<li><a href='fix_livres_table.php'>Corriger la table livres</a></li>";
echo "<li><a href='fix_podcasts_table.php'>Corriger la table podcasts</a></li>";
echo "<li><a href='fix_cours_table.php'>Corriger la table cours</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
