<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Style CSS pour une meilleure présentation
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction de la structure de la table videos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Correction de la structure de la table videos</h1>';

require_once __DIR__ . '/../includes/config.php';

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $stmt = $pdo->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_name = :table
                )
            ");
            $stmt->execute([':table' => $table]);
        } else {
            // MySQL
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = :dbname AND table_name = :table
            ");
            $stmt->execute([':dbname' => DB_NAME, ':table' => $table]);
        }
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        // PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $stmt = $pdo->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.columns 
                    WHERE table_name = :table AND column_name = :column
                )
            ");
            $stmt->execute([':table' => $table, ':column' => $column]);
        } else {
            // MySQL
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.columns 
                WHERE table_schema = :dbname AND table_name = :table AND column_name = :column
            ");
            $stmt->execute([':dbname' => DB_NAME, ':table' => $table, ':column' => $column]);
        }
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de la colonne: " . $e->getMessage() . "</p>";
        return false;
    }
}

try {
    // 1. Vérifier si la table categories_videos existe
    echo "<h2>1. Vérification de la table categories_videos</h2>";
    
    if (!tableExists($pdo, 'categories_videos')) {
        echo "<p class='warning'>La table 'categories_videos' n'existe pas. Création en cours...</p>";
        
        // Créer la table categories_videos
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "
                CREATE TABLE categories_videos (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
        } else {
            // MySQL
            $sql = "
                CREATE TABLE categories_videos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Table 'categories_videos' créée avec succès.</p>";
        
        // Insérer des données de test
        $sql = "
            INSERT INTO categories_videos (nom, description) VALUES
            ('Droit civil', 'Vidéos sur le droit civil'),
            ('Droit pénal', 'Vidéos sur le droit pénal'),
            ('Droit des affaires', 'Vidéos sur le droit des affaires'),
            ('Droit du travail', 'Vidéos sur le droit du travail'),
            ('Droit international', 'Vidéos sur le droit international')
        ";
        $pdo->exec($sql);
        echo "<p class='success'>Données de test insérées dans la table 'categories_videos'.</p>";
    } else {
        echo "<p class='success'>La table 'categories_videos' existe déjà.</p>";
    }
    
    // 2. Vérifier si la table videos existe
    echo "<h2>2. Vérification de la table videos</h2>";
    
    if (!tableExists($pdo, 'videos')) {
        echo "<p class='warning'>La table 'videos' n'existe pas. Création en cours...</p>";
        
        // Créer la table videos
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "
                CREATE TABLE videos (
                    id SERIAL PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    description TEXT,
                    url_video VARCHAR(255) NOT NULL,
                    type_video VARCHAR(50) DEFAULT 'youtube',
                    duree VARCHAR(10),
                    miniature_url VARCHAR(255),
                    niveau VARCHAR(20) DEFAULT 'débutant',
                    prix DECIMAL(10, 2) DEFAULT 0.00,
                    statut VARCHAR(20) DEFAULT 'actif',
                    id_categorie INT,
                    id_createur INT,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_categorie) REFERENCES categories_videos(id)
                )
            ";
        } else {
            // MySQL
            $sql = "
                CREATE TABLE videos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    description TEXT,
                    url_video VARCHAR(255) NOT NULL,
                    type_video VARCHAR(50) DEFAULT 'youtube',
                    duree VARCHAR(10),
                    miniature_url VARCHAR(255),
                    niveau VARCHAR(20) DEFAULT 'débutant',
                    prix DECIMAL(10, 2) DEFAULT 0.00,
                    statut VARCHAR(20) DEFAULT 'actif',
                    id_categorie INT,
                    id_createur INT,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_categorie) REFERENCES categories_videos(id)
                )
            ";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Table 'videos' créée avec succès.</p>";
        
        // Insérer des données de test
        $sql = "
            INSERT INTO videos (titre, description, url_video, duree, miniature_url, niveau, id_categorie, id_createur) VALUES
            ('Introduction au droit civil', 'Une introduction au droit civil', 'https://www.youtube.com/watch?v=abc123', '10:30', 'https://example.com/miniature1.jpg', 'débutant', 1, 1),
            ('Les bases du droit pénal', 'Les fondamentaux du droit pénal', 'https://www.youtube.com/watch?v=def456', '15:45', 'https://example.com/miniature2.jpg', 'intermédiaire', 2, 1),
            ('Droit des contrats', 'Tout savoir sur le droit des contrats', 'https://www.youtube.com/watch?v=ghi789', '20:15', 'https://example.com/miniature3.jpg', 'avancé', 3, 2)
        ";
        $pdo->exec($sql);
        echo "<p class='success'>Données de test insérées dans la table 'videos'.</p>";
    } else {
        echo "<p class='success'>La table 'videos' existe déjà.</p>";
        
        // 3. Vérifier si la colonne id_createur existe dans la table videos
        echo "<h2>3. Vérification de la colonne id_createur</h2>";
        
        if (!columnExists($pdo, 'videos', 'id_createur')) {
            echo "<p class='warning'>La colonne 'id_createur' n'existe pas dans la table 'videos'. Ajout en cours...</p>";
            
            // Ajouter la colonne id_createur
            if (strpos(DB_URL, 'pgsql') !== false) {
                // PostgreSQL
                $sql = "ALTER TABLE videos ADD COLUMN id_createur INT";
            } else {
                // MySQL
                $sql = "ALTER TABLE videos ADD COLUMN id_createur INT";
            }
            
            $pdo->exec($sql);
            echo "<p class='success'>Colonne 'id_createur' ajoutée avec succès à la table 'videos'.</p>";
            
            // Mettre à jour les données existantes avec une valeur par défaut
            $sql = "UPDATE videos SET id_createur = 1 WHERE id_createur IS NULL";
            $pdo->exec($sql);
            echo "<p class='success'>Données existantes mises à jour avec une valeur par défaut pour 'id_createur'.</p>";
        } else {
            echo "<p class='success'>La colonne 'id_createur' existe déjà dans la table 'videos'.</p>";
        }
    }
    
    // 4. Afficher la structure actuelle de la table videos
    echo "<h2>4. Structure actuelle de la table videos</h2>";
    
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql = "
            SELECT column_name, data_type, character_maximum_length, column_default, is_nullable
            FROM information_schema.columns
            WHERE table_name = 'videos'
            ORDER BY ordinal_position
        ";
    } else {
        // MySQL
        $sql = "
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'videos'
            ORDER BY ORDINAL_POSITION
        ";
    }
    
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Longueur</th><th>Défaut</th><th>Nullable</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['column_name'] ?? $column['COLUMN_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($column['data_type'] ?? $column['DATA_TYPE']) . "</td>";
        echo "<td>" . htmlspecialchars($column['character_maximum_length'] ?? $column['CHARACTER_MAXIMUM_LENGTH'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['column_default'] ?? $column['COLUMN_DEFAULT'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['is_nullable'] ?? $column['IS_NULLABLE']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 5. Afficher les données de la table videos
    echo "<h2>5. Données de la table videos</h2>";
    
    $sql = "SELECT * FROM videos LIMIT 10";
    $stmt = $pdo->query($sql);
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($videos) > 0) {
        echo "<table>";
        echo "<tr>";
        foreach (array_keys($videos[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        
        foreach ($videos as $video) {
            echo "<tr>";
            foreach ($video as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>Aucune donnée dans la table 'videos'.</p>";
    }
    
    echo "<h2>Conclusion</h2>";
    echo "<p class='success'>La vérification et la correction de la structure de la table 'videos' ont été effectuées avec succès.</p>";
    echo "<p><a href='../index.php'>Retour à l'accueil</a> | <a href='test_db_connection.php'>Tester la connexion à la base de données</a></p>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
