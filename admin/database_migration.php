<?php
require_once __DIR__ . '/../includes/config.php';

// Style CSS pour une meilleure présentation
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration de la base de données LawApp</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Migration de la base de données LawApp</h1>';

try {
    // Déterminer le type de base de données
    $is_postgres = strpos(DB_URL, 'pgsql') !== false;
    $db_type = $is_postgres ? 'pgsql' : 'mysql';

    // Fonction pour vérifier l'existence d'une table
    function tableExists($pdo, $table, $is_postgres) {
        try {
            if ($is_postgres) {
                $stmt = $pdo->prepare("
                    SELECT EXISTS (
                        SELECT FROM information_schema.tables 
                        WHERE table_name = :table
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
            echo "<p class='error'>Erreur lors de la vérification de la table $table : " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Fonction pour créer une table
    function createTable($pdo, $table, $columns, $is_postgres, $test_data = []) {
        if (!tableExists($pdo, $table, $is_postgres)) {
            $id_column = $is_postgres ? 
                "id SERIAL PRIMARY KEY" : 
                "id INT AUTO_INCREMENT PRIMARY KEY";
            
            $columns_str = str_replace('__ID_COLUMN__', $id_column, $columns);
            
            try {
                $pdo->exec($columns_str);
                echo "<p class='success'>Table '$table' créée avec succès.</p>";

                // Insérer des données de test
                if (!empty($test_data)) {
                    $keys = implode(', ', array_keys($test_data[0]));
                    $placeholders = '(' . implode(', ', array_fill(0, count($test_data[0]), '?')) . ')';
                    $stmt = $pdo->prepare("INSERT INTO $table ($keys) VALUES $placeholders");
                    
                    foreach ($test_data as $data) {
                        $stmt->execute(array_values($data));
                    }
                    echo "<p class='success'>Données de test pour '$table' insérées.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='error'>Erreur lors de la création de la table '$table': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='info'>La table '$table' existe déjà.</p>";
        }
    }

    // Connexion à la base de données
    $pdo = new PDO(DB_URL, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Création des tables principales</h2>";

    // Table utilisateurs
    createTable($pdo, 'utilisateurs', "
        CREATE TABLE __ID_COLUMN__,
        email VARCHAR(255) UNIQUE NOT NULL,
        mot_de_passe VARCHAR(255) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        nom VARCHAR(100) NOT NULL,
        date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        derniere_connexion TIMESTAMP,
        statut VARCHAR(20) DEFAULT 'actif'
    ", $is_postgres, [
        [
            'email' => 'user@example.com',
            'mot_de_passe' => password_hash('password123', PASSWORD_DEFAULT),
            'prenom' => 'Jean',
            'nom' => 'Dupont',
            'statut' => 'actif'
        ]
    ]);

    // Table administrateurs
    createTable($pdo, 'administrateurs', "
        CREATE TABLE __ID_COLUMN__,
        email VARCHAR(255) UNIQUE NOT NULL,
        mot_de_passe VARCHAR(255) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        nom VARCHAR(100) NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        derniere_connexion TIMESTAMP
    ", $is_postgres, [
        [
            'email' => 'admin@lawapp.com',
            'mot_de_passe' => password_hash('admin', PASSWORD_DEFAULT),
            'prenom' => 'Admin',
            'nom' => 'LawApp',
            'role' => 'super_admin'
        ]
    ]);

    // Autres tables essentielles
    $tables_to_create = [
        'categories_livres' => "
            CREATE TABLE __ID_COLUMN__,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(20) DEFAULT 'actif'
        ",
        'livres' => "
            CREATE TABLE __ID_COLUMN__,
            titre VARCHAR(255) NOT NULL,
            auteur VARCHAR(255) NOT NULL,
            description TEXT,
            id_categorie INT,
            prix DECIMAL(10, 2),
            disponible BOOLEAN DEFAULT TRUE
        ",
        'categories_podcasts' => "
            CREATE TABLE __ID_COLUMN__,
            nom VARCHAR(100) NOT NULL,
            description TEXT
        ",
        'podcasts' => "
            CREATE TABLE __ID_COLUMN__,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            id_categorie INT,
            duree INT,
            audio_url VARCHAR(255)
        "
    ];

    foreach ($tables_to_create as $table => $schema) {
        createTable($pdo, $table, $schema, $is_postgres);
    }

    echo "<p class='success'>Migration de la base de données terminée avec succès.</p>";
} catch (PDOException $e) {
    echo "<p class='error'>Erreur lors de la migration : " . $e->getMessage() . "</p>";
}

echo '</body></html>';
