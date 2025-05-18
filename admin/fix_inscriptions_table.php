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
                    statut VARCHAR(50) DEFAULT 'actif',
                    progression INT DEFAULT 0,
                    date_derniere_activite TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(id_utilisateur, id_cours)
                )
            ";
        } else {
            // MySQL
            $sql = "
                CREATE TABLE inscriptions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_utilisateur INT NOT NULL,
                    id_cours INT NOT NULL,
                    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    statut VARCHAR(50) DEFAULT 'actif',
                    progression INT DEFAULT 0,
                    date_derniere_activite TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(id_utilisateur, id_cours)
                )
            ";
        }
        
        $pdo->exec($sql);
        echo "<p class='success'>Table 'inscriptions' créée avec succès.</p>";
        
        // Vérifier si les tables utilisateurs et cours existent
        $utilisateurs_exists = tableExists($pdo, 'utilisateurs');
        $cours_exists = tableExists($pdo, 'cours');
        
        if ($utilisateurs_exists && $cours_exists) {
            // Insérer des données de test
            echo "<p>Insertion de données de test...</p>";
            
            // Récupérer quelques utilisateurs
            $stmt = $pdo->query("SELECT id FROM utilisateurs LIMIT 3");
            $utilisateurs = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Récupérer quelques cours
            $stmt = $pdo->query("SELECT id FROM cours LIMIT 3");
            $cours = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($utilisateurs) > 0 && count($cours) > 0) {
                $inserted = 0;
                
                foreach ($utilisateurs as $id_utilisateur) {
                    foreach ($cours as $id_cours) {
                        try {
                            $stmt = $pdo->prepare("
                                INSERT INTO inscriptions (id_utilisateur, id_cours, statut, progression)
                                VALUES (:id_utilisateur, :id_cours, :statut, :progression)
                            ");
                            
                            $stmt->execute([
                                ':id_utilisateur' => $id_utilisateur,
                                ':id_cours' => $id_cours,
                                ':statut' => 'actif',
                                ':progression' => rand(0, 100)
                            ]);
                            
                            $inserted++;
                        } catch (PDOException $e) {
                            // Ignorer les erreurs de duplication
                            if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                                strpos($e->getMessage(), 'duplicate key') === false) {
                                throw $e;
                            }
                        }
                    }
                }
                
                echo "<p class='success'>" . $inserted . " inscriptions de test insérées.</p>";
            } else {
                echo "<p class='warning'>Impossible d'insérer des données de test: pas assez d'utilisateurs ou de cours.</p>";
            }
        } else {
            echo "<p class='warning'>Impossible d'insérer des données de test: les tables 'utilisateurs' ou 'cours' n'existent pas.</p>";
        }
    } else {
        echo "<p class='success'>La table 'inscriptions' existe déjà.</p>";
        
        // 2. Vérifier la structure de la table inscriptions
        echo "<h2>2. Vérification de la structure de la table inscriptions</h2>";
        
        $required_columns = [
            'id_utilisateur' => 'INT',
            'id_cours' => 'INT',
            'date_inscription' => 'TIMESTAMP',
            'statut' => 'VARCHAR',
            'progression' => 'INT',
            'date_derniere_activite' => 'TIMESTAMP'
        ];
        
        $missing_columns = [];
        
        foreach ($required_columns as $column => $type) {
            if (!columnExists($pdo, 'inscriptions', $column)) {
                $missing_columns[$column] = $type;
            }
        }
        
        if (count($missing_columns) > 0) {
            echo "<p class='warning'>Certaines colonnes sont manquantes dans la table 'inscriptions'. Ajout en cours...</p>";
            
            foreach ($missing_columns as $column => $type) {
                echo "<p>Ajout de la colonne '" . htmlspecialchars($column) . "'...</p>";
                
                if (strpos(DB_URL, 'pgsql') !== false) {
                    // PostgreSQL
                    switch ($column) {
                        case 'id_utilisateur':
                        case 'id_cours':
                        case 'progression':
                            $sql = "ALTER TABLE inscriptions ADD COLUMN " . $column . " INT NOT NULL DEFAULT 0";
                            break;
                        case 'date_inscription':
                        case 'date_derniere_activite':
                            $sql = "ALTER TABLE inscriptions ADD COLUMN " . $column . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                            break;
                        case 'statut':
                            $sql = "ALTER TABLE inscriptions ADD COLUMN " . $column . " VARCHAR(50) DEFAULT 'actif'";
                            break;
                    }
                } else {
                    // MySQL
                    switch ($column) {
                        case 'id_utilisateur':
                        case 'id_cours':
                        case 'progression':
                            $sql = "ALTER TABLE inscriptions ADD COLUMN " . $column . " INT NOT NULL DEFAULT 0";
                            break;
                        case 'date_inscription':
                        case 'date_derniere_activite':
                            $sql = "ALTER TABLE inscriptions ADD COLUMN " . $column . " TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                            break;
                        case 'statut':
                            $sql = "ALTER TABLE inscriptions ADD COLUMN " . $column . " VARCHAR(50) DEFAULT 'actif'";
                            break;
                    }
                }
                
                $pdo->exec($sql);
                echo "<p class='success'>Colonne '" . htmlspecialchars($column) . "' ajoutée avec succès.</p>";
            }
        } else {
            echo "<p class='success'>Toutes les colonnes requises existent dans la table 'inscriptions'.</p>";
        }
    }
    
    // 3. Afficher la structure actuelle de la table inscriptions
    echo "<h2>3. Structure actuelle de la table inscriptions</h2>";
    
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql = "
            SELECT column_name, data_type, character_maximum_length, column_default, is_nullable
            FROM information_schema.columns
            WHERE table_name = 'inscriptions'
            ORDER BY ordinal_position
        ";
    } else {
        // MySQL
        $sql = "
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'inscriptions'
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
    
    // 4. Afficher les données de la table inscriptions
    echo "<h2>4. Données de la table inscriptions</h2>";
    
    $sql = "SELECT COUNT(*) FROM inscriptions";
    $count = $pdo->query($sql)->fetchColumn();
    
    echo "<p>Nombre d'inscriptions: <strong>" . $count . "</strong></p>";
    
    if ($count > 0) {
        $sql = "
            SELECT 
                i.id,
                i.id_utilisateur,
                i.id_cours,
                i.date_inscription,
                i.statut,
                i.progression,
                i.date_derniere_activite,
                u.prenom AS prenom_utilisateur,
                u.nom AS nom_utilisateur,
                c.titre AS titre_cours
            FROM inscriptions i
            LEFT JOIN utilisateurs u ON i.id_utilisateur = u.id
            LEFT JOIN cours c ON i.id_cours = c.id
            ORDER BY i.date_inscription DESC
            LIMIT 10
        ";
        
        $stmt = $pdo->query($sql);
        $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Utilisateur</th>";
        echo "<th>Cours</th>";
        echo "<th>Date d'inscription</th>";
        echo "<th>Statut</th>";
        echo "<th>Progression</th>";
        echo "<th>Dernière activité</th>";
        echo "</tr>";
        
        foreach ($inscriptions as $inscription) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($inscription['id']) . "</td>";
            echo "<td>" . htmlspecialchars($inscription['prenom_utilisateur'] . ' ' . $inscription['nom_utilisateur']) . " (ID: " . htmlspecialchars($inscription['id_utilisateur']) . ")</td>";
            echo "<td>" . htmlspecialchars($inscription['titre_cours']) . " (ID: " . htmlspecialchars($inscription['id_cours']) . ")</td>";
            echo "<td>" . htmlspecialchars($inscription['date_inscription']) . "</td>";
            echo "<td>" . htmlspecialchars($inscription['statut']) . "</td>";
            echo "<td>" . htmlspecialchars($inscription['progression']) . "%</td>";
            echo "<td>" . htmlspecialchars($inscription['date_derniere_activite']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>Aucune inscription trouvée dans la base de données.</p>";
        
        // Proposer d'ajouter des données de test
        if (tableExists($pdo, 'utilisateurs') && tableExists($pdo, 'cours')) {
            echo "<form method='post'>";
            echo "<input type='hidden' name='add_test_data' value='1'>";
            echo "<button type='submit'>Ajouter des données de test</button>";
            echo "</form>";
            
            if (isset($_POST['add_test_data'])) {
                // Récupérer quelques utilisateurs
                $stmt = $pdo->query("SELECT id FROM utilisateurs LIMIT 3");
                $utilisateurs = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Récupérer quelques cours
                $stmt = $pdo->query("SELECT id FROM cours LIMIT 3");
                $cours = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (count($utilisateurs) > 0 && count($cours) > 0) {
                    $inserted = 0;
                    
                    foreach ($utilisateurs as $id_utilisateur) {
                        foreach ($cours as $id_cours) {
                            try {
                                $stmt = $pdo->prepare("
                                    INSERT INTO inscriptions (id_utilisateur, id_cours, statut, progression)
                                    VALUES (:id_utilisateur, :id_cours, :statut, :progression)
                                ");
                                
                                $stmt->execute([
                                    ':id_utilisateur' => $id_utilisateur,
                                    ':id_cours' => $id_cours,
                                    ':statut' => 'actif',
                                    ':progression' => rand(0, 100)
                                ]);
                                
                                $inserted++;
                            } catch (PDOException $e) {
                                // Ignorer les erreurs de duplication
                                if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                                    strpos($e->getMessage(), 'duplicate key') === false) {
                                    throw $e;
                                }
                            }
                        }
                    }
                    
                    echo "<p class='success'>" . $inserted . " inscriptions de test insérées. <a href=''>Rafraîchir</a></p>";
                } else {
                    echo "<p class='warning'>Impossible d'insérer des données de test: pas assez d'utilisateurs ou de cours.</p>";
                }
            }
        } else {
            echo "<p class='warning'>Impossible d'ajouter des données de test: les tables 'utilisateurs' ou 'cours' n'existent pas.</p>";
        }
    }
    
    echo "<h2>Conclusion</h2>";
    echo "<p class='success'>La vérification et la correction de la table 'inscriptions' ont été effectuées avec succès.</p>";
    echo "<p><a href='../index.php'>Retour à l'accueil</a> | <a href='test_db_connection.php'>Tester la connexion à la base de données</a> | <a href='check_and_fix_database.php'>Vérifier et corriger la base de données</a></p>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
