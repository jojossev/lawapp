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
    <title>Test de connexion à la base de données</title>
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
    <h1>Test de connexion à la base de données</h1>';

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

try {
    echo "<h2>1. Informations de connexion</h2>";
    echo "<p><strong>Type de base de données:</strong> " . (strpos(DB_URL, 'pgsql') !== false ? 'PostgreSQL' : 'MySQL') . "</p>";
    echo "<p><strong>État de la connexion:</strong> <span class='success'>Connecté</span></p>";
    echo "<p><strong>Version du serveur:</strong> " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
    echo "<p><strong>Nom de la base de données:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>Hôte:</strong> " . DB_HOST . "</p>";
    
    // Vérifier les tables existantes
    echo "<h2>2. Tables existantes dans la base de données</h2>";
    
    // Adapter la requête en fonction du type de base de données
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    } else {
        // MySQL
        $stmt = $pdo->query("SHOW TABLES");
    }
    
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<table>";
        echo "<tr><th>Nom de la table</th><th>Statut</th></tr>";
        foreach ($tables as $table) {
            echo "<tr><td>" . htmlspecialchars($table) . "</td><td class='success'>Existe</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>Aucune table n'a été trouvée dans la base de données.</p>";
    }
    
    // Vérifier les tables requises
    echo "<h2>3. Vérification des tables requises</h2>";
    $required_tables = [
        'utilisateurs',
        'administrateurs',
        'categories_cours',
        'cours',
        'categories_livres',
        'livres',
        'categories_podcasts',
        'podcasts',
        'categories_videos',
        'videos',
        'inscriptions',
        'modules',
        'lecons',
        'quiz'
    ];
    
    echo "<table>";
    echo "<tr><th>Nom de la table</th><th>Statut</th></tr>";
    foreach ($required_tables as $table) {
        $exists = tableExists($pdo, $table);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($table) . "</td>";
        if ($exists) {
            echo "<td class='success'>Existe</td>";
        } else {
            echo "<td class='error'>Manquante</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Vérifier les permissions
    echo "<h2>4. Permissions de l'utilisateur</h2>";
    try {
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $stmt = $pdo->query("
                SELECT grantee, privilege_type 
                FROM information_schema.role_table_grants 
                WHERE grantee = CURRENT_USER 
            }
        } else {
            // MySQL
            $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
            $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<pre>";
            foreach ($permissions as $permission) {
                echo htmlspecialchars($permission) . "\n";
            }
            echo "</pre>";
        }
    } catch (PDOException $e) {}
    
    // Tester les tables spécifiques si elles existent
    echo "<h2>5. Tests des tables spécifiques</h2>";
    
    // Test de la table lecons
    if (tableExists($pdo, 'lecons')) {
        echo "<h3>Table 'lecons'</h3>";
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM lecons");
            $count = $stmt->fetchColumn();
            echo "<p class='info'>Nombre de leçons: " . $count . "</p>";
            
            if ($count > 0) {
                $stmt = $pdo->query("SELECT * FROM lecons LIMIT 1");
                $lecon = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>Exemple de leçon:</p>";
                echo "<pre>" . print_r($lecon, true) . "</pre>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors du test de la table 'lecons': " . $e->getMessage() . "</p>";
        }
    }
    
    // Test de la table quiz
    if (tableExists($pdo, 'quiz')) {
        echo "<h3>Table 'quiz'</h3>";
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM quiz");
            $count = $stmt->fetchColumn();
            echo "<p class='info'>Nombre de quiz: " . $count . "</p>";
            
            if ($count > 0) {
                $stmt = $pdo->query("SELECT * FROM quiz LIMIT 1");
                $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>Exemple de quiz:</p>";
                echo "<pre>" . print_r($quiz, true) . "</pre>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors du test de la table 'quiz': " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Conclusion</h2>";
    echo "<p class='success'>Le test de connexion à la base de données a été effectué avec succès.</p>";
    echo "<p><a href='../index.php'>Retour à l'accueil</a> | <a href='setup_database.php'>Configurer la base de données</a> | <a href='fix_livres_structure.php'>Corriger la structure des tables</a></p>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
