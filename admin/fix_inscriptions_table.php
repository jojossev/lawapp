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
    <title>Correction de la table inscriptions</title>
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
    <h1>Correction de la table inscriptions</h1>';

require_once __DIR__ . '/../includes/config.php';

// Fonction pour obtenir le nom de la base de données
function getDatabaseName(PDO $pdo) {
    $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver_name === 'pgsql') {
        return $pdo->query("SELECT current_database()")
            ->fetch(PDO::FETCH_COLUMN);
    } else {
        return $pdo->query("SELECT DATABASE()")
            ->fetch(PDO::FETCH_COLUMN);
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        if ($driver_name === 'pgsql') {
            $stmt = $pdo->prepare("            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' AND table_name = :table
            )");
        } else {
            $stmt = $pdo->prepare("            SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() AND table_name = :table
            ");
        }
        
        $stmt->execute([':table' => $table]);
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        if ($driver_name === 'pgsql') {
            $stmt = $pdo->prepare("            SELECT EXISTS (
                SELECT FROM information_schema.columns 
                WHERE table_schema = 'public' AND table_name = :table AND column_name = :column
            )");
        } else {
            $stmt = $pdo->prepare("                SELECT COUNT(*) 
                FROM information_schema.columns 
                WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column
            ");
        }
        
        $stmt->execute([':table' => $table, ':column' => $column]);
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de la colonne: " . $e->getMessage() . "</p>";
        return false;
    }
}

try {
    // 1. Vérifier si la table inscriptions existe
    echo "<h2>1. Vérification de la table inscriptions</h2>";
    
    if (!tableExists($pdo, 'inscriptions')) {
        echo "<p class='warning'>La table 'inscriptions' n'existe pas. Création en cours...</p>";
        
        // Créer la table inscriptions
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "
                CREATE TABLE inscriptions (
                    id SERIAL PRIMARY KEY,
                    id_utilisateur INT NOT NULL,
                    id_cours INT NOT NULL,
                    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    progres INT DEFAULT 0,
                    statut VARCHAR(50) DEFAULT 'en_cours',
                    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
                    FOREIGN KEY (id_cours) REFERENCES cours(id)
                )
            ";
        } else {
            // MySQL
            $sql = "
                CREATE TABLE inscriptions (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    id_utilisateur INT NOT NULL,
                    id_cours INT NOT NULL,
                    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    progres INT DEFAULT 0,
                    statut VARCHAR(50) DEFAULT 'en_cours',
                    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id),
                    FOREIGN KEY (id_cours) REFERENCES cours(id)
                ) ENGINE=InnoDB
            ";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Table inscriptions créée avec succès</p>";
    } else {
        echo "<p class='info'>La table inscriptions existe déjà</p>";
    }
    
    // 2. Vérifier et ajouter les colonnes manquantes
    echo "<h2>2. Vérification des colonnes</h2>";
    
    $required_columns = [
        'id' => 'INT',
        'id_utilisateur' => 'INT',
        'id_cours' => 'INT',
        'date_inscription' => 'TIMESTAMP',
        'progres' => 'INT',
        'statut' => 'VARCHAR(50)'
    ];

    foreach ($required_columns as $column => $type) {
        if (!columnExists($pdo, 'inscriptions', $column)) {
            echo "<p class='warning'>La colonne '$column' n'existe pas. Ajout en cours...</p>";
            
            if (strpos(DB_URL, 'pgsql') !== false) {
                $sql = "ALTER TABLE inscriptions ADD COLUMN $column $type";
            } else {
                $sql = "ALTER TABLE inscriptions ADD COLUMN $column $type";
            }
            
            $pdo->exec($sql);
            echo "<p class='success'>Colonne '$column' ajoutée avec succès</p>";
        }
    }
    
    // 3. Vérifier les clés étrangères
    echo "<h2>3. Vérification des clés étrangères</h2>";
    
    // Vérifier la clé étrangère vers utilisateurs
    $sql = "
        SELECT COUNT(*) 
        FROM information_schema.constraint_column_usage 
        WHERE table_name = 'inscriptions' 
        AND column_name = 'id_utilisateur' 
        AND constraint_name LIKE 'fk_%'
    ";
    
    if ($pdo->query($sql)->fetchColumn() === 0) {
        echo "<p class='warning'>Clé étrangère vers utilisateurs manquante. Ajout en cours...</p>";
        
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "ALTER TABLE inscriptions ADD CONSTRAINT fk_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id)";
        } else {
            $sql = "ALTER TABLE inscriptions ADD CONSTRAINT fk_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id)";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Clé étrangère vers utilisateurs ajoutée avec succès</p>";
    }
    
    // Vérifier la clé étrangère vers cours
    $sql = "
        SELECT COUNT(*) 
        FROM information_schema.constraint_column_usage 
        WHERE table_name = 'inscriptions' 
        AND column_name = 'id_cours' 
        AND constraint_name LIKE 'fk_%'
    ";
    
    if ($pdo->query($sql)->fetchColumn() === 0) {
        echo "<p class='warning'>Clé étrangère vers cours manquante. Ajout en cours...</p>";
        
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "ALTER TABLE inscriptions ADD CONSTRAINT fk_cours FOREIGN KEY (id_cours) REFERENCES cours(id)";
        } else {
            $sql = "ALTER TABLE inscriptions ADD CONSTRAINT fk_cours FOREIGN KEY (id_cours) REFERENCES cours(id)";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Clé étrangère vers cours ajoutée avec succès</p>";
    }
    
    // 4. Vérifier les données de test
    echo "<h2>4. Vérification des données de test</h2>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscriptions");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count === 0) {
        echo "<p class='warning'>Pas de données de test. Insertion en cours...</p>";
        
        // Insérer des données de test
        $sql = "
            INSERT INTO inscriptions (id_utilisateur, id_cours, progres, statut) VALUES
            (1, 1, 50, 'en_cours'),
            (2, 2, 80, 'complet'),
            (3, 1, 30, 'en_cours')
        ";
        
        $pdo->exec($sql);
        echo "<p class='success'>Données de test insérées avec succès</p>";
    } else {
        echo "<p class='info'>$count enregistrements trouvés dans la table inscriptions</p>";
    }
    
    // 5. Afficher la structure de la table
    echo "<h2>5. Structure de la table inscriptions</h2>";
    
    if (strpos(DB_URL, 'pgsql') !== false) {
        $sql = "
            SELECT column_name, data_type, character_maximum_length, column_default, is_nullable
            FROM information_schema.columns
            WHERE table_name = 'inscriptions'
            ORDER BY ordinal_position
        ";
    } else {
        $sql = "
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inscriptions'
            ORDER BY ORDINAL_POSITION
        ";
    }
    
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Nom</th><th>Type</th><th>Longueur</th><th>Défaut</th><th>Nullable</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['COLUMN_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($column['DATA_TYPE']) . "</td>";
        echo "<td>" . htmlspecialchars($column['CHARACTER_MAXIMUM_LENGTH'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['COLUMN_DEFAULT'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['IS_NULLABLE']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // 6. Afficher les données de test
    echo "<h2>6. Données de test</h2>";
    
    $stmt = $pdo->query("SELECT * FROM inscriptions");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>ID</th><th>ID Utilisateur</th><th>ID Cours</th><th>Date</th><th>Progression</th><th>Statut</th></tr>";
    
    foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['id_utilisateur']) . "</td>";
        echo "<td>" . htmlspecialchars($row['id_cours']) . "</td>";
        echo "<td>" . htmlspecialchars($row['date_inscription']) . "</td>";
        echo "<td>" . htmlspecialchars($row['progres']) . "</td>";
        echo "<td>" . htmlspecialchars($row['statut']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p class='error'>Erreur critique: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
