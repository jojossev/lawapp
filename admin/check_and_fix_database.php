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
    <title>Vérification et correction de la base de données</title>
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
        .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
        .card-header { font-weight: bold; margin-bottom: 10px; }
        .btn { display: inline-block; padding: 8px 16px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
        .btn-warning { background-color: #ff9800; }
        .btn-danger { background-color: #f44336; }
    </style>
</head>
<body>
    <h1>Vérification et correction de la base de données</h1>';

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
            $sql = "
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = current_database() AND table_name = :table
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':table' => $table]);
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
    echo "<h2>Connexion à la base de données</h2>";
    echo "<p><strong>Type de base de données:</strong> " . (strpos(DB_URL, 'pgsql') !== false ? 'PostgreSQL' : 'MySQL') . "</p>";
    echo "<p><strong>État de la connexion:</strong> <span class='success'>Connecté</span></p>";
    echo "<p><strong>Version du serveur:</strong> " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</p>";
    
    // Vérification des tables requises
    echo "<h2>Vérification des tables requises</h2>";
    $required_tables = [
        'utilisateurs' => 'Utilisateurs du système',
        'administrateurs' => 'Administrateurs du système',
        'categories_cours' => 'Catégories de cours',
        'cours' => 'Cours disponibles',
        'categories_livres' => 'Catégories de livres',
        'livres' => 'Livres juridiques',
        'categories_podcasts' => 'Catégories de podcasts',
        'podcasts' => 'Podcasts juridiques',
        'categories_videos' => 'Catégories de vidéos',
        'videos' => 'Vidéos juridiques',
        'inscriptions' => 'Inscriptions aux cours',
        'modules' => 'Modules de cours',
        'lecons' => 'Leçons des modules',
        'quiz' => 'Quiz des leçons'
    ];
    
    $missing_tables = [];
    $existing_tables = [];
    
    echo "<table>";
    echo "<tr><th>Table</th><th>Description</th><th>Statut</th><th>Action</th></tr>";
    
    foreach ($required_tables as $table => $description) {
        $exists = tableExists($pdo, $table);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($table) . "</td>";
        echo "<td>" . htmlspecialchars($description) . "</td>";
        
        if ($exists) {
            echo "<td class='success'>Existe</td>";
            echo "<td><a href='test_" . $table . ".php' class='btn'>Tester</a></td>";
            $existing_tables[] = $table;
        } else {
            echo "<td class='error'>Manquante</td>";
            echo "<td><a href='fix_" . $table . "_structure.php' class='btn btn-warning'>Créer</a></td>";
            $missing_tables[] = $table;
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Vérification des colonnes spécifiques
    echo "<h2>Vérification des colonnes spécifiques</h2>";
    
    $required_columns = [
        'livres' => [
            'image_url' => 'URL de l\'image du livre',
            'id_categorie' => 'ID de la catégorie du livre'
        ],
        'videos' => [
            'id_createur' => 'ID du créateur de la vidéo',
            'id_categorie' => 'ID de la catégorie de la vidéo'
        ],
        'podcasts' => [
            'id_createur' => 'ID du créateur du podcast',
            'id_categorie' => 'ID de la catégorie du podcast'
        ]
    ];
    
    $missing_columns = [];
    
    foreach ($required_columns as $table => $columns) {
        if (tableExists($pdo, $table)) {
            echo "<h3>Table: " . htmlspecialchars($table) . "</h3>";
            echo "<table>";
            echo "<tr><th>Colonne</th><th>Description</th><th>Statut</th></tr>";
            
            foreach ($columns as $column => $description) {
                $exists = columnExists($pdo, $table, $column);
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($column) . "</td>";
                echo "<td>" . htmlspecialchars($description) . "</td>";
                
                if ($exists) {
                    echo "<td class='success'>Existe</td>";
                } else {
                    echo "<td class='error'>Manquante</td>";
                    $missing_columns[$table][] = $column;
                }
                
                echo "</tr>";
            }
            
            echo "</table>";
        }
    }
    
    // Résumé et actions recommandées
    echo "<h2>Résumé et actions recommandées</h2>";
    
    if (count($missing_tables) > 0) {
        echo "<div class='card'>";
        echo "<div class='card-header'>Tables manquantes</div>";
        echo "<p>Les tables suivantes sont manquantes dans la base de données:</p>";
        echo "<ul>";
        foreach ($missing_tables as $table) {
            echo "<li>" . htmlspecialchars($table) . " - <a href='fix_" . $table . "_structure.php' class='btn btn-warning'>Créer</a></li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='card'>";
        echo "<div class='card-header'>Tables</div>";
        echo "<p class='success'>Toutes les tables requises existent dans la base de données.</p>";
        echo "</div>";
    }
    
    if (count($missing_columns) > 0) {
        echo "<div class='card'>";
        echo "<div class='card-header'>Colonnes manquantes</div>";
        echo "<p>Les colonnes suivantes sont manquantes dans certaines tables:</p>";
        echo "<ul>";
        foreach ($missing_columns as $table => $columns) {
            echo "<li>" . htmlspecialchars($table) . ": " . implode(", ", $columns) . " - <a href='fix_" . $table . "_structure.php' class='btn btn-warning'>Corriger</a></li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='card'>";
        echo "<div class='card-header'>Colonnes</div>";
        echo "<p class='success'>Toutes les colonnes requises existent dans les tables.</p>";
        echo "</div>";
    }
    
    // Scripts de correction disponibles
    echo "<h2>Scripts de correction disponibles</h2>";
    
    $fix_scripts = [
        'setup_database.php' => 'Initialiser la base de données et créer les tables manquantes',
        'fix_livres_structure.php' => 'Corriger la structure de la table livres',
        'fix_videos_structure.php' => 'Corriger la structure de la table videos',
        'fix_podcasts_table.php' => 'Corriger la structure de la table podcasts',
        'fix_admin_table.php' => 'Corriger la table des administrateurs'
    ];
    
    echo "<div class='card'>";
    echo "<div class='card-header'>Scripts disponibles</div>";
    echo "<ul>";
    foreach ($fix_scripts as $script => $description) {
        echo "<li><a href='" . $script . "' class='btn'>" . htmlspecialchars($script) . "</a> - " . htmlspecialchars($description) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // Liens utiles
    echo "<h2>Liens utiles</h2>";
    
    echo "<div class='card'>";
    echo "<div class='card-header'>Navigation</div>";
    echo "<p><a href='../index.php' class='btn'>Retour à l'accueil</a> | <a href='test_db_connection.php' class='btn'>Tester la connexion à la base de données</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
