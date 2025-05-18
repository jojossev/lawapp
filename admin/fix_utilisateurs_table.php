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
    <title>Correction de la table utilisateurs</title>
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
    <h1>Correction de la table utilisateurs</h1>';

require_once __DIR__ . '/../includes/config.php';

// Fonction pour vérifier si une table existe
function tableExists(PDO $pdo, string $table) {
    try {
        $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        // Extraire dynamiquement le nom de la base de données
        if (defined('DB_NAME')) {
            $db_name = DB_NAME;
        } elseif (defined('DATABASE_URL')) {
            $parsed_url = parse_url(DATABASE_URL);
            $db_name = ltrim($parsed_url['path'], '/');
        } else {
            // PostgreSQL ne supporte pas SELECT DATABASE()
            $db_name = 'lawapp';
        }
        
        if (empty($db_name)) {
            $db_name = 'lawapp_';
        }
        
        if ($driver_name === 'pgsql') {
            // PostgreSQL
            $stmt = $pdo->prepare("
                SELECT EXISTS (
                    SELECT 1 FROM information_schema.tables 
                    WHERE table_schema = 'public' AND table_name = :table
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
            $stmt->execute([':dbname' => $db_name, ':table' => $table]);
        }
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
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
            // Récupérer dynamiquement le nom de la base de données
            $database_name = null;
            
            // Essayer de récupérer le nom de la base de données
            if (defined('DATABASE_URL')) {
                $parsed_url = parse_url(DATABASE_URL);
                $database_name = ltrim($parsed_url['path'], '/');
            } elseif (getenv('DATABASE_URL')) {
                $parsed_url = parse_url(getenv('DATABASE_URL'));
                $database_name = ltrim($parsed_url['path'], '/');
            }
            
            // Requête générique pour vérifier l'existence de la colonne
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.columns 
                WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column
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

try {
    // 1. Vérifier si la table utilisateurs existe
    echo "<h2>1. Vérification de la table utilisateurs</h2>";
    
    if (!tableExists($pdo, 'utilisateurs')) {
        echo "<p class='warning'>La table 'utilisateurs' n'existe pas. Création en cours...</p>";
        
        // Créer la table utilisateurs
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $createTableQuery = "
                CREATE TABLE utilisateurs (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                CREATE TABLE utilisateurs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    mot_de_passe VARCHAR(255) NOT NULL,
                    prenom VARCHAR(100) NOT NULL,
                    nom VARCHAR(100) NOT NULL,
                    date_naissance DATE,
                    adresse TEXT,
                    telephone VARCHAR(20),
                    role VARCHAR(20) DEFAULT 'utilisateur',
                    statut VARCHAR(20) DEFAULT 'actif',
                    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    derniere_connexion TIMESTAMP NULL
                )
            ";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Table 'utilisateurs' créée avec succès.</p>";
        
        // Insérer des données de test
        echo "<p>Insertion de données de test...</p>";
        
        // Mot de passe hashé pour 'password123'
        $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
        
        $sql = "
            INSERT INTO utilisateurs (email, mot_de_passe, prenom, nom, role, statut) VALUES
            ('admin@example.com', :password1, 'Admin', 'User', 'admin', 'actif'),
            ('user1@example.com', :password2, 'Jean', 'Dupont', 'utilisateur', 'actif'),
            ('user2@example.com', :password3, 'Marie', 'Martin', 'utilisateur', 'actif')
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':password1' => $hashed_password,
            ':password2' => $hashed_password,
            ':password3' => $hashed_password
        ]);
        
        echo "<p class='success'>Données de test insérées avec succès.</p>";
    } else {
        echo "<p class='success'>La table 'utilisateurs' existe déjà.</p>";
        
        // 2. Vérifier la structure de la table utilisateurs
        echo "<h2>2. Vérification de la structure de la table utilisateurs</h2>";
        
        $required_columns = [
            'email' => 'VARCHAR',
            'mot_de_passe' => 'VARCHAR',
            'prenom' => 'VARCHAR',
            'nom' => 'VARCHAR',
            'role' => 'VARCHAR',
            'statut' => 'VARCHAR',
            'date_inscription' => 'TIMESTAMP',
            'derniere_connexion' => 'TIMESTAMP'
        ];
        
        $missing_columns = [];
        
        foreach ($required_columns as $column => $type) {
            if (!columnExists($pdo, 'utilisateurs', $column)) {
                $missing_columns[$column] = $type;
            }
        }
        
        if (count($missing_columns) > 0) {
            echo "<p class='warning'>Certaines colonnes sont manquantes dans la table 'utilisateurs'. Ajout en cours...</p>";
            
            foreach ($missing_columns as $column => $type) {
                echo "<p>Ajout de la colonne '" . htmlspecialchars($column) . "'...</p>";
                
                if (strpos(DB_URL, 'pgsql') !== false) {
                    // PostgreSQL
                    switch ($column) {
                        case 'email':
                        case 'mot_de_passe':
                        case 'prenom':
                        case 'nom':
                            $sql = "ALTER TABLE utilisateurs ADD COLUMN " . $column . " VARCHAR(255) NOT NULL DEFAULT ''";
                            break;
                        case 'role':
                        case 'statut':
                            $sql = "ALTER TABLE utilisateurs ADD COLUMN " . $column . " VARCHAR(20) DEFAULT 'utilisateur'";
                            break;
                        case 'date_inscription':
                            $sql = "ALTER TABLE utilisateurs ADD COLUMN " . $column . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                            break;
                        case 'derniere_connexion':
                            $sql = "ALTER TABLE utilisateurs ADD COLUMN " . $column . " TIMESTAMP NULL";
                            break;
                    }
                } else {
                    // MySQL
                    switch ($column) {
                        case 'email':
                        case 'mot_de_passe':
                        case 'prenom':
                        case 'nom':
                            $sql = "ALTER TABLE utilisateurs ADD COLUMN " . $column . " VARCHAR(255) NOT NULL DEFAULT ''";
                            break;
                        case 'role':
                        case 'statut':
                            $sql = "ALTER TABLE utilisateurs ADD COLUMN " . $column . " VARCHAR(20) DEFAULT 'utilisateur'";
                            break;
                        case 'date_inscription':
                            $sql = "ALTER TABLE utilisateurs ADD COLUMN " . $column . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                            break;
                        case 'derniere_connexion':
                            $sql = "ALTER TABLE utilisateurs ADD COLUMN " . $column . " TIMESTAMP NULL";
                            break;
                    }
                }
                
                $pdo->exec($sql);
                echo "<p class='success'>Colonne '" . htmlspecialchars($column) . "' ajoutée avec succès.</p>";
            }
        } else {
            echo "<p class='success'>Toutes les colonnes requises existent dans la table 'utilisateurs'.</p>";
        }
    }
    
    // 3. Afficher la structure actuelle de la table utilisateurs
    echo "<h2>3. Structure actuelle de la table utilisateurs</h2>";
    
    if ($driver_name === 'pgsql') {
        $sql = "
            SELECT column_name, data_type, character_maximum_length, column_default, is_nullable
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = 'utilisateurs'
            ORDER BY ordinal_position
        ";
    } else {
        // MySQL
        $database_name = null;
        
        // Essayer de récupérer le nom de la base de données
        if (defined('DATABASE_URL')) {
            $parsed_url = parse_url(DATABASE_URL);
            $database_name = ltrim($parsed_url['path'], '/');
        } elseif (getenv('DATABASE_URL')) {
            $parsed_url = parse_url(getenv('DATABASE_URL'));
            $database_name = ltrim($parsed_url['path'], '/');
        }
        
        $sql = "
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '" . ($database_name ?? 'lawapp') . "' AND TABLE_NAME = 'utilisateurs'
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
    
    // 4. Afficher les données de la table utilisateurs
    echo "<h2>4. Données de la table utilisateurs</h2>";
    
    $sql = "SELECT COUNT(*) FROM utilisateurs";
    $count = $pdo->query($sql)->fetchColumn();
    
    echo "<p>Nombre d'utilisateurs: <strong>" . $count . "</strong></p>";
    
    if ($count > 0) {
        $sql = "
            SELECT 
                id,
                email,
                prenom,
                nom,
                role,
                statut,
                date_inscription,
                derniere_connexion
            FROM utilisateurs
            ORDER BY id
            LIMIT 10
        ";
        
        $stmt = $pdo->query($sql);
        $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Email</th>";
        echo "<th>Nom complet</th>";
        echo "<th>Rôle</th>";
        echo "<th>Statut</th>";
        echo "<th>Inscription</th>";
        echo "<th>Dernière connexion</th>";
        echo "</tr>";
        
        foreach ($utilisateurs as $utilisateur) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($utilisateur['id']) . "</td>";
            echo "<td>" . htmlspecialchars($utilisateur['email']) . "</td>";
            echo "<td>" . htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($utilisateur['role']) . "</td>";
            echo "<td>" . htmlspecialchars($utilisateur['statut']) . "</td>";
            echo "<td>" . htmlspecialchars($utilisateur['date_inscription']) . "</td>";
            echo "<td>" . htmlspecialchars($utilisateur['derniere_connexion'] ?? 'Jamais') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>Aucun utilisateur trouvé dans la base de données.</p>";
        
        // Proposer d'ajouter des données de test
        echo "<form method='post'>";
        echo "<input type='hidden' name='add_test_data' value='1'>";
        echo "<button type='submit'>Ajouter des données de test</button>";
        echo "</form>";
        
        if (isset($_POST['add_test_data'])) {
            // Mot de passe hashé pour 'password123'
            $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
            
            $sql = "
                INSERT INTO utilisateurs (email, mot_de_passe, prenom, nom, role, statut) VALUES
                ('admin@example.com', :password1, 'Admin', 'User', 'admin', 'actif'),
                ('user1@example.com', :password2, 'Jean', 'Dupont', 'utilisateur', 'actif'),
                ('user2@example.com', :password3, 'Marie', 'Martin', 'utilisateur', 'actif')
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':password1' => $hashed_password,
                ':password2' => $hashed_password,
                ':password3' => $hashed_password
            ]);
            
            echo "<p class='success'>Données de test insérées avec succès. <a href=''>Rafraîchir</a></p>";
        }
    }
    
    echo "<h2>5. Vérification des index</h2>";
    
    // Vérifier si l'index unique sur email existe
    $email_index_exists = false;
    
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

    // Déterminer le driver si non défini
    $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver_name === 'pgsql') {
        // PostgreSQL
        $sql = "
            SELECT COUNT(*) 
            FROM pg_indexes 
            WHERE tablename = 'utilisateurs' 
            AND indexdef LIKE '%email%'
        ";
        $email_index_exists = (bool)$pdo->query($sql)->fetchColumn();
    } else {
        // MySQL
        $sql = "
            SELECT COUNT(*) 
            FROM information_schema.table_constraints
            WHERE table_schema = '" . $database_name . "' 
            AND table_name = 'utilisateurs' 
            AND constraint_type = 'UNIQUE'
            AND constraint_name LIKE '%email%'
        ";
        $email_index_exists = (bool)$pdo->query($sql)->fetchColumn();
    }
    
    if (!$email_index_exists) {
        echo "<p class='warning'>L'index unique sur la colonne 'email' n'existe pas. Création en cours...</p>";
        
        try {
            if ($driver_name === 'pgsql') {
                // PostgreSQL
                $sql = "CREATE UNIQUE INDEX utilisateurs_email_idx ON utilisateurs (email)";
            } else {
                // MySQL
                $sql = "CREATE UNIQUE INDEX utilisateurs_email_idx ON utilisateurs (email)";
            }
            
            $pdo->exec($sql);
            echo "<p class='success'>Index unique sur la colonne 'email' créé avec succès.</p>";
        } catch (PDOException $e) {
            // Si l'erreur est due à des doublons, proposer de les corriger
            if (strpos($e->getMessage(), 'duplicate key') !== false || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "<p class='error'>Impossible de créer l'index unique car il existe des doublons dans la colonne 'email'.</p>";
                
                // Trouver les doublons
                $sql = "
                    SELECT email, COUNT(*) as count
                    FROM utilisateurs
                    GROUP BY email
                    HAVING COUNT(*) > 1
                ";
                
                $stmt = $pdo->query($sql);
                $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($duplicates) > 0) {
                    echo "<p>Emails en double:</p>";
                    echo "<ul>";
                    
                    foreach ($duplicates as $duplicate) {
                        echo "<li>" . htmlspecialchars($duplicate['email']) . " (" . $duplicate['count'] . " occurrences)</li>";
                    }
                    
                    echo "</ul>";
                    
                    echo "<p>Vous devez corriger manuellement ces doublons avant de pouvoir créer l'index unique.</p>";
                }
            } else {
                echo "<p class='error'>Erreur lors de la création de l'index: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p class='success'>L'index unique sur la colonne 'email' existe déjà.</p>";
    }
    
    echo "<h2>Conclusion</h2>";
    echo "<p class='success'>La vérification et la correction de la table 'utilisateurs' ont été effectuées avec succès.</p>";
    echo "<p><a href='../index.php'>Retour à l'accueil</a> | <a href='test_db_connection.php'>Tester la connexion à la base de données</a> | <a href='check_and_fix_database.php'>Vérifier et corriger la base de données</a></p>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
