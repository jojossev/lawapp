<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';

// Ajout de style CSS
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    h2 { color: #333; }
    .success { color: green; }
    .warning { color: orange; }
    .error { color: red; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
</style>";

// Fonctions de vérification de table et colonne
if (!function_exists('tableExists')) {
    function tableExists(PDO $pdo, string $table) {
        try {
            $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            if ($driver_name === 'pgsql') {
                $stmt = $pdo->prepare("
                    SELECT EXISTS (
                        SELECT 1 FROM information_schema.tables 
                        WHERE table_schema = 'public' AND table_name = :table
                    )
                ");
                $stmt->execute([':table' => $table]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM information_schema.tables 
                    WHERE table_schema = DATABASE() AND table_name = :table
                ");
                $stmt->execute([':table' => $table]);
            }
            
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            echo "Erreur lors de la vérification de la table: " . $e->getMessage();
            return false;
        }
    }
}

if (!function_exists('columnExists')) {
    function columnExists(PDO $pdo, string $table, string $column) {
        try {
            $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            if ($driver_name === 'pgsql') {
                $stmt = $pdo->prepare("
                    SELECT EXISTS (
                        SELECT 1 FROM information_schema.columns 
                        WHERE table_schema = 'public' 
                        AND table_name = :table 
                        AND column_name = :column
                    )
                ");
                $stmt->execute([
                    ':table' => $table,
                    ':column' => $column
                ]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM information_schema.columns 
                    WHERE table_schema = DATABASE() 
                    AND table_name = :table 
                    AND column_name = :column
                ");
                $stmt->execute([
                    ':table' => $table,
                    ':column' => $column
                ]);
            }
            
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de la vérification de la colonne: " . $e->getMessage() . "</p>";
            return false;
        }
    }
}

try {
    // Récupération dynamique du nom de base de données
    $database_name = null;
    if (defined('DATABASE_URL')) {
        $parsed_url = parse_url(DATABASE_URL);
        $database_name = ltrim($parsed_url['path'], '/');
    } elseif (getenv('DATABASE_URL')) {
        $parsed_url = parse_url(getenv('DATABASE_URL'));
        $database_name = ltrim($parsed_url['path'], '/');
    }
    $database_name = $database_name ?? 'lawapp';

    // Déterminer le driver de base de données
    $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    // 1. Vérifier si la table cours existe
    echo "<h2>1. Vérification de la table cours</h2>";
    
    if (!tableExists($pdo, 'cours')) {
        echo "<p class='warning'>La table 'cours' n'existe pas. Création en cours...</p>";
        
        // Création de la table cours
        $createTableQuery = $driver_name === 'pgsql' ? "
            CREATE TABLE cours (
                id SERIAL PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                description TEXT,
                categorie_id INT,
                duree INT,
                niveau VARCHAR(50),
                prix DECIMAL(10, 2),
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        " : "
            CREATE TABLE cours (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                description TEXT,
                categorie_id INT,
                duree INT,
                niveau VARCHAR(50),
                prix DECIMAL(10, 2),
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ";
        
        $pdo->exec($createTableQuery);
        echo "<p class='success'>Table 'cours' créée avec succès.</p>";
    }

    // 2. Vérifier et ajouter les colonnes manquantes
    echo "<h2>2. Vérification des colonnes de la table cours</h2>";
    
    $colonnes = [
        'categorie_id' => $driver_name === 'pgsql' 
            ? 'INT' 
            : 'INT',
        'niveau' => $driver_name === 'pgsql'
            ? 'VARCHAR(50)' 
            : 'VARCHAR(50)',
        'prix' => $driver_name === 'pgsql'
            ? 'DECIMAL(10, 2)' 
            : 'DECIMAL(10, 2)'
    ];
    
    foreach ($colonnes as $colonne => $definition) {
        if (!columnExists($pdo, 'cours', $colonne)) {
            $query = "ALTER TABLE cours ADD COLUMN $colonne $definition";
            $pdo->exec($query);
            echo "<p class='success'>Colonne '$colonne' ajoutée.</p>";
        }
    }

    // 3. Vérifier les index
    echo "<h2>3. Vérification des index</h2>";
    
    // Index sur categorie_id
    $categorie_index_exists = false;
    
    if ($driver_name === 'pgsql') {
        // PostgreSQL
        $sql = "
            SELECT COUNT(*) 
            FROM pg_indexes 
            WHERE tablename = 'cours' 
            AND indexdef LIKE '%categorie_id%'
        ";
        $categorie_index_exists = (bool)$pdo->query($sql)->fetchColumn();
    } else {
        // MySQL
        $sql = "
            SELECT COUNT(*) 
            FROM information_schema.table_constraints
            WHERE table_schema = '" . $database_name . "' 
            AND table_name = 'cours' 
            AND constraint_type = 'INDEX'
            AND constraint_name LIKE '%categorie_id%'
        ";
        $categorie_index_exists = (bool)$pdo->query($sql)->fetchColumn();
    }
    
    if (!$categorie_index_exists) {
        echo "<p class='warning'>L'index sur la colonne 'categorie_id' n'existe pas. Création en cours...</p>";
        
        try {
            if ($driver_name === 'pgsql') {
                // PostgreSQL
                $sql = "CREATE INDEX cours_categorie_idx ON cours (categorie_id)";
            } else {
                // MySQL
                $sql = "CREATE INDEX cours_categorie_idx ON cours (categorie_id)";
            }
            
            $pdo->exec($sql);
            echo "<p class='success'>Index sur la colonne 'categorie_id' créé avec succès.</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de la création de l'index : " . $e->getMessage() . "</p>";
        }
    }

    // 4. Afficher la structure de la table
    echo "<h2>4. Structure de la table cours</h2>";
    
    $query = $driver_name === 'pgsql' 
        ? "SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'cours' ORDER BY ordinal_position"
        : "DESCRIBE cours";
    
    $stmt = $pdo->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Colonne</th><th>Type</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . ($column['column_name'] ?? $column['Field']) . "</td>";
        echo "<td>" . ($column['data_type'] ?? $column['Type']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "<p class='error'>Erreur : " . $e->getMessage() . "</p>";
}

// Ajout de style CSS
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    h2 { color: #333; }
    .success { color: green; }
    .warning { color: orange; }
    .error { color: red; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
</style>";

    echo "<h2>2. Vérification des colonnes de la table cours</h2>";
    
    $colonnes = [
        'categorie_id' => $driver_name === 'pgsql' 
            ? 'INT' 
            : 'INT',
        'niveau' => $driver_name === 'pgsql'
            ? 'VARCHAR(50)' 
            : 'VARCHAR(50)',
        'prix' => $driver_name === 'pgsql'
            ? 'DECIMAL(10, 2)' 
            : 'DECIMAL(10, 2)'
    ];
    
    foreach ($colonnes as $colonne => $definition) {
        if (!columnExists($pdo, 'cours', $colonne)) {
            $query = "ALTER TABLE cours ADD COLUMN $colonne $definition";
            $pdo->exec($query);
            echo "<p class='success'>Colonne '$colonne' ajoutée.</p>";
        }
    }

    // 3. Vérifier les index
    echo "<h2>3. Vérification des index</h2>";
    
    // Index sur categorie_id
    $categorie_index_exists = false;
    
    if ($driver_name === 'pgsql') {
        // PostgreSQL
        $sql = "
            SELECT COUNT(*) 
            FROM pg_indexes 
            WHERE tablename = 'cours' 
            AND indexdef LIKE '%categorie_id%'
        ";
        $categorie_index_exists = (bool)$pdo->query($sql)->fetchColumn();
    } else {
        // MySQL
        $sql = "
            SELECT COUNT(*) 
            FROM information_schema.table_constraints
            WHERE table_schema = '" . $database_name . "' 
            AND table_name = 'cours' 
            AND constraint_type = 'INDEX'
            AND constraint_name LIKE '%categorie_id%'
        ";
        $categorie_index_exists = (bool)$pdo->query($sql)->fetchColumn();
    }
    
    if (!$categorie_index_exists) {
        echo "<p class='warning'>L'index sur la colonne 'categorie_id' n'existe pas. Création en cours...</p>";
        
        try {
            if ($driver_name === 'pgsql') {
                // PostgreSQL
                $sql = "CREATE INDEX cours_categorie_idx ON cours (categorie_id)";
            } else {
                // MySQL
                $sql = "CREATE INDEX cours_categorie_idx ON cours (categorie_id)";
            }
            
            $pdo->exec($sql);
            echo "<p class='success'>Index sur la colonne 'categorie_id' créé avec succès.</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de la création de l'index : " . $e->getMessage() . "</p>";
        }
    }

    // 4. Afficher la structure de la table
    echo "<h2>4. Structure de la table cours</h2>";
    
    $query = $driver_name === 'pgsql' 
        ? "SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'cours' ORDER BY ordinal_position"
        : "DESCRIBE cours";
    
    $stmt = $pdo->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Colonne</th><th>Type</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . ($column['column_name'] ?? $column['Field']) . "</td>";
        echo "<td>" . ($column['data_type'] ?? $column['Type']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "<p class='error'>Erreur : " . $e->getMessage() . "</p>";
}

// Style CSS pour une meilleure présentation
echo '<!DOCTYPE html>
<html lang="fr">
<head>
{{ ... }}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction de la table cours</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; }
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
    <h1>Correction de la table cours</h1>';

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
    // 1. Vérifier si la table cours existe
    echo "<h2>1. Vérification de la table cours</h2>";
    
    if (!tableExists($pdo, 'cours')) {
        echo "<p class='warning'>La table 'cours' n'existe pas. Création en cours...</p>";
        
        // Créer la table cours
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "
                CREATE TABLE cours (
                    id SERIAL PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    description TEXT,
                    image VARCHAR(255),
                    duree INT DEFAULT 0,
                    niveau VARCHAR(50) DEFAULT 'débutant',
                    prix DECIMAL(10, 2) DEFAULT 0.00,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    statut VARCHAR(50) DEFAULT 'actif',
                    id_categorie INT,
                    id_formateur INT
                )
            ";
        } else {
            // MySQL
            $sql = "
                CREATE TABLE cours (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    description TEXT,
                    image VARCHAR(255),
                    duree INT DEFAULT 0,
                    niveau VARCHAR(50) DEFAULT 'débutant',
                    prix DECIMAL(10, 2) DEFAULT 0.00,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    statut VARCHAR(50) DEFAULT 'actif',
                    id_categorie INT,
                    id_formateur INT
                )
            ";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Table 'cours' créée avec succès.</p>";
        
        // Insérer des données de test
        echo "<p>Insertion de données de test...</p>";
        
        $sql = "
            INSERT INTO cours (titre, description, image, duree, niveau, prix, statut, id_categorie, id_formateur) VALUES
            ('Introduction au droit civil', 'Ce cours vous introduit aux concepts fondamentaux du droit civil.', 'droit_civil.jpg', 120, 'débutant', 49.99, 'actif', 1, 1),
            ('Droit pénal avancé', 'Approfondissez vos connaissances en droit pénal avec ce cours complet.', 'droit_penal.jpg', 180, 'avancé', 79.99, 'actif', 2, 2),
            ('Droit des affaires', 'Tout ce que vous devez savoir sur le droit des affaires et des sociétés.', 'droit_affaires.jpg', 150, 'intermédiaire', 69.99, 'actif', 3, 1)
        ";
        
        $pdo->exec($sql);
        echo "<p class='success'>Données de test insérées avec succès.</p>";
    } else {
        echo "<p class='success'>La table 'cours' existe déjà.</p>";
        
        // 2. Vérifier la structure de la table cours
        echo "<h2>2. Vérification de la structure de la table cours</h2>";
        
        $required_columns = [
            'titre' => 'VARCHAR',
            'description' => 'TEXT',
            'image' => 'VARCHAR',
            'duree' => 'INT',
            'niveau' => 'VARCHAR',
            'prix' => 'DECIMAL',
            'date_creation' => 'TIMESTAMP',
            'statut' => 'VARCHAR',
            'id_categorie' => 'INT',
            'id_formateur' => 'INT'
        ];
        
        $missing_columns = [];
        
        foreach ($required_columns as $column => $type) {
            if (!columnExists($pdo, 'cours', $column)) {
                $missing_columns[$column] = $type;
            }
        }
        
        if (count($missing_columns) > 0) {
            echo "<p class='warning'>Certaines colonnes sont manquantes dans la table 'cours'. Ajout en cours...</p>";
            
            foreach ($missing_columns as $column => $type) {
                echo "<p>Ajout de la colonne '" . htmlspecialchars($column) . "'...</p>";
                
                if (strpos(DB_URL, 'pgsql') !== false) {
                    // PostgreSQL
                    switch ($column) {
                        case 'titre':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " VARCHAR(255) NOT NULL DEFAULT ''";
                            break;
                        case 'description':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " TEXT";
                            break;
                        case 'image':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " VARCHAR(255)";
                            break;
                        case 'duree':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " INT DEFAULT 0";
                            break;
                        case 'niveau':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " VARCHAR(50) DEFAULT 'débutant'";
                            break;
                        case 'prix':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " DECIMAL(10, 2) DEFAULT 0.00";
                            break;
                        case 'date_creation':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                            break;
                        case 'statut':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " VARCHAR(50) DEFAULT 'actif'";
                            break;
                        case 'id_categorie':
                        case 'id_formateur':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " INT";
                            break;
                    }
                } else {
                    // MySQL
                    switch ($column) {
                        case 'titre':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " VARCHAR(255) NOT NULL DEFAULT ''";
                            break;
                        case 'description':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " TEXT";
                            break;
                        case 'image':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " VARCHAR(255)";
                            break;
                        case 'duree':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " INT DEFAULT 0";
                            break;
                        case 'niveau':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " VARCHAR(50) DEFAULT 'débutant'";
                            break;
                        case 'prix':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " DECIMAL(10, 2) DEFAULT 0.00";
                            break;
                        case 'date_creation':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                            break;
                        case 'statut':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " VARCHAR(50) DEFAULT 'actif'";
                            break;
                        case 'id_categorie':
                        case 'id_formateur':
                            $sql = "ALTER TABLE cours ADD COLUMN " . $column . " INT";
                            break;
                    }
                }
                
                $pdo->exec($sql);
                echo "<p class='success'>Colonne '" . htmlspecialchars($column) . "' ajoutée avec succès.</p>";
            }
        } else {
            echo "<p class='success'>Toutes les colonnes requises existent dans la table 'cours'.</p>";
        }
    }
    
    // 3. Vérifier si la table categories_cours existe
    echo "<h2>3. Vérification de la table categories_cours</h2>";
    
    if (!tableExists($pdo, 'categories_cours')) {
        echo "<p class='warning'>La table 'categories_cours' n'existe pas. Création en cours...</p>";
        
        // Créer la table categories_cours
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "
                CREATE TABLE categories_cours (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    image VARCHAR(255)
                )
            ";
        } else {
            // MySQL
            $sql = "
                CREATE TABLE categories_cours (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    image VARCHAR(255)
                )
            ";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Table 'categories_cours' créée avec succès.</p>";
        
        // Insérer des données de test
        echo "<p>Insertion de données de test...</p>";
        
        $sql = "
            INSERT INTO categories_cours (nom, description, image) VALUES
            ('Droit civil', 'Cours sur le droit civil et les obligations', 'civil.jpg'),
            ('Droit pénal', 'Cours sur le droit pénal et la procédure pénale', 'penal.jpg'),
            ('Droit des affaires', 'Cours sur le droit des affaires et des sociétés', 'affaires.jpg')
        ";
        
        $pdo->exec($sql);
        echo "<p class='success'>Données de test insérées avec succès.</p>";
    } else {
        echo "<p class='success'>La table 'categories_cours' existe déjà.</p>";
    }
    
    // 4. Vérifier si la table formateurs existe
    echo "<h2>4. Vérification de la table formateurs</h2>";
    
    if (!tableExists($pdo, 'formateurs')) {
        echo "<p class='warning'>La table 'formateurs' n'existe pas. Création en cours...</p>";
        
        // Créer la table formateurs
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "
                CREATE TABLE formateurs (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    prenom VARCHAR(100) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    biographie TEXT,
                    photo VARCHAR(255),
                    specialite VARCHAR(100),
                    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
        } else {
            // MySQL
            $sql = "
                CREATE TABLE formateurs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    prenom VARCHAR(100) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    biographie TEXT,
                    photo VARCHAR(255),
                    specialite VARCHAR(100),
                    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Table 'formateurs' créée avec succès.</p>";
        
        // Insérer des données de test
        echo "<p>Insertion de données de test...</p>";
        
        $sql = "
            INSERT INTO formateurs (nom, prenom, email, biographie, specialite) VALUES
            ('Dupont', 'Jean', 'jean.dupont@example.com', 'Avocat spécialisé en droit civil avec 15 ans d''expérience.', 'Droit civil'),
            ('Martin', 'Sophie', 'sophie.martin@example.com', 'Professeure de droit pénal à l''université de Paris.', 'Droit pénal')
        ";
        
        $pdo->exec($sql);
        echo "<p class='success'>Données de test insérées avec succès.</p>";
    } else {
        echo "<p class='success'>La table 'formateurs' existe déjà.</p>";
    }
    
    // 5. Afficher la structure actuelle de la table cours
    echo "<h2>5. Structure actuelle de la table cours</h2>";
    
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql = "
            SELECT column_name, data_type, character_maximum_length, column_default, is_nullable
            FROM information_schema.columns
            WHERE table_name = 'cours'
            ORDER BY ordinal_position
        ";
    } else {
        // MySQL
        $sql = "
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'cours'
            ORDER BY ORDINAL_POSITION
        ";
    }
    
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Longueur</th><th>Défaut</th><th>Nullable</th></tr>";
    
    foreach ($columns as $column) {
        $columnName = $column['column_name'] ?? $column['COLUMN_NAME'];
        $dataType = $column['data_type'] ?? $column['DATA_TYPE'];
        $maxLength = $column['character_maximum_length'] ?? $column['CHARACTER_MAXIMUM_LENGTH'] ?? '';
        $default = $column['column_default'] ?? $column['COLUMN_DEFAULT'] ?? '';
        $nullable = $column['is_nullable'] ?? $column['IS_NULLABLE'];
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($columnName) . "</td>";
        echo "<td>" . htmlspecialchars($dataType) . "</td>";
        echo "<td>" . htmlspecialchars($maxLength) . "</td>";
        echo "<td>" . htmlspecialchars($default) . "</td>";
        echo "<td>" . htmlspecialchars($nullable) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // 6. Afficher les données de la table cours
    echo "<h2>6. Données de la table cours</h2>";
    
    $sql = "SELECT COUNT(*) FROM cours";
    $count = $pdo->query($sql)->fetchColumn();
    
    echo "<p>Nombre de cours: <strong>" . $count . "</strong></p>";
    
    if ($count > 0) {
        $sql = "
            SELECT 
                c.id,
                c.titre,
                c.description,
                c.image,
                c.duree,
                c.niveau,
                c.prix,
                c.date_creation,
                c.statut,
                c.id_categorie,
                c.id_formateur,
                cat.nom AS nom_categorie,
                CONCAT(f.prenom, ' ', f.nom) AS nom_formateur
            FROM cours c
            LEFT JOIN categories_cours cat ON c.id_categorie = cat.id
            LEFT JOIN formateurs f ON c.id_formateur = f.id
            ORDER BY c.id
            LIMIT 10
        ";
        
        $stmt = $pdo->query($sql);
        $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Titre</th>";
        echo "<th>Catégorie</th>";
        echo "<th>Formateur</th>";
        echo "<th>Durée</th>";
        echo "<th>Niveau</th>";
        echo "<th>Prix</th>";
        echo "<th>Statut</th>";
        echo "</tr>";
        
        foreach ($cours as $course) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($course['id']) . "</td>";
            echo "<td>" . htmlspecialchars($course['titre']) . "</td>";
            echo "<td>" . htmlspecialchars($course['nom_categorie'] ?? 'Non défini') . " (ID: " . htmlspecialchars($course['id_categorie'] ?? 'N/A') . ")</td>";
            echo "<td>" . htmlspecialchars($course['nom_formateur'] ?? 'Non défini') . " (ID: " . htmlspecialchars($course['id_formateur'] ?? 'N/A') . ")</td>";
            echo "<td>" . htmlspecialchars($course['duree']) . " min</td>";
            echo "<td>" . htmlspecialchars($course['niveau']) . "</td>";
            echo "<td>" . htmlspecialchars($course['prix']) . " €</td>";
            echo "<td>" . htmlspecialchars($course['statut']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>Aucun cours trouvé dans la base de données.</p>";
        
        // Proposer d'ajouter des données de test
        echo "<form method='post'>";
        echo "<input type='hidden' name='add_test_data' value='1'>";
        echo "<button type='submit'>Ajouter des données de test</button>";
        echo "</form>";
        
        if (isset($_POST['add_test_data'])) {
            $sql = "
                INSERT INTO cours (titre, description, image, duree, niveau, prix, statut, id_categorie, id_formateur) VALUES
                ('Introduction au droit civil', 'Ce cours vous introduit aux concepts fondamentaux du droit civil.', 'droit_civil.jpg', 120, 'débutant', 49.99, 'actif', 1, 1),
                ('Droit pénal avancé', 'Approfondissez vos connaissances en droit pénal avec ce cours complet.', 'droit_penal.jpg', 180, 'avancé', 79.99, 'actif', 2, 2),
                ('Droit des affaires', 'Tout ce que vous devez savoir sur le droit des affaires et des sociétés.', 'droit_affaires.jpg', 150, 'intermédiaire', 69.99, 'actif', 3, 1)
            ";
            
            $pdo->exec($sql);
            echo "<p class='success'>Données de test insérées avec succès. <a href=''>Rafraîchir</a></p>";
        }
    }
    
    // 7. Vérifier les relations entre les tables
    echo "<h2>7. Vérification des relations entre les tables</h2>";
    
    // Vérifier si les cours ont des catégories valides
    $sql = "
        SELECT c.id, c.titre, c.id_categorie
        FROM cours c
        LEFT JOIN categories_cours cat ON c.id_categorie = cat.id
        WHERE c.id_categorie IS NOT NULL AND cat.id IS NULL
    ";
    
    $stmt = $pdo->query($sql);
    $invalid_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($invalid_categories) > 0) {
        echo "<p class='warning'>Certains cours ont des catégories invalides:</p>";
        echo "<ul>";
        
        foreach ($invalid_categories as $course) {
            echo "<li>Cours '" . htmlspecialchars($course['titre']) . "' (ID: " . htmlspecialchars($course['id']) . ") a une catégorie invalide (ID: " . htmlspecialchars($course['id_categorie']) . ")</li>";
        }
        
        echo "</ul>";
    } else {
        echo "<p class='success'>Tous les cours ont des catégories valides.</p>";
    }
    
    // Vérifier si les cours ont des formateurs valides
    $sql = "
        SELECT c.id, c.titre, c.id_formateur
        FROM cours c
        LEFT JOIN formateurs f ON c.id_formateur = f.id
        WHERE c.id_formateur IS NOT NULL AND f.id IS NULL
    ";
    
    $stmt = $pdo->query($sql);
    $invalid_formateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($invalid_formateurs) > 0) {
        echo "<p class='warning'>Certains cours ont des formateurs invalides:</p>";
        echo "<ul>";
        
        foreach ($invalid_formateurs as $course) {
            echo "<li>Cours '" . htmlspecialchars($course['titre']) . "' (ID: " . htmlspecialchars($course['id']) . ") a un formateur invalide (ID: " . htmlspecialchars($course['id_formateur']) . ")</li>";
        }
        
        echo "</ul>";
    } else {
        echo "<p class='success'>Tous les cours ont des formateurs valides.</p>";
    }
    
    echo "<h2>Conclusion</h2>";
    echo "<p class='success'>La vérification et la correction de la table 'cours' ont été effectuées avec succès.</p>";
    echo "<p><a href='../index.php'>Retour à l'accueil</a> | <a href='test_db_connection.php'>Tester la connexion à la base de données</a> | <a href='check_and_fix_database.php'>Vérifier et corriger la base de données</a></p>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
