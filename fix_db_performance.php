<?php
// Script pour optimiser les performances de la base de données
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

// Fonction pour vérifier si un index existe
function indexExists($pdo, $table, $index) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM pg_indexes 
                WHERE tablename = :table AND indexname = :index
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'index' => $index]);
            return (bool) $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SHOW INDEX FROM $table WHERE Key_name = :index";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['index' => $index]);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de l'index: " . $e->getMessage() . "</p>";
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
    <title>Optimisation des performances de la base de données</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
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
        <h1>Optimisation des performances de la base de données</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

// Déterminer le type de base de données
$is_postgres = strpos(DB_URL, 'pgsql') !== false;
$db_type = $is_postgres ? 'PostgreSQL' : 'MySQL';

echo "<p class='success'>Connexion à la base de données établie avec succès. Type de base de données : $db_type</p>";

// 1. Vérification et création des index pour améliorer les performances
echo "<h2>Vérification et création des index</h2>";

$indexes = [
    // Index pour les recherches fréquentes
    ['table' => 'livres', 'index' => 'idx_livres_titre', 'columns' => 'titre'],
    ['table' => 'podcasts', 'index' => 'idx_podcasts_titre', 'columns' => 'titre'],
    ['table' => 'cours', 'index' => 'idx_cours_titre', 'columns' => 'titre'],
    ['table' => 'videos', 'index' => 'idx_videos_titre', 'columns' => 'titre'],
    
    // Index pour les jointures fréquentes
    ['table' => 'livres', 'index' => 'idx_livres_categorie', 'columns' => 'id_categorie'],
    ['table' => 'podcasts', 'index' => 'idx_podcasts_categorie', 'columns' => 'id_categorie'],
    ['table' => 'cours', 'index' => 'idx_cours_categorie', 'columns' => 'id_categorie'],
    ['table' => 'videos', 'index' => 'idx_videos_categorie', 'columns' => 'id_categorie'],
    
    // Index pour les tables de relation
    ['table' => 'modules', 'index' => 'idx_modules_cours', 'columns' => 'id_cours'],
    ['table' => 'lecons', 'index' => 'idx_lecons_module', 'columns' => 'id_module'],
    ['table' => 'inscriptions', 'index' => 'idx_inscriptions_user_cours', 'columns' => 'id_utilisateur, id_cours'],
    ['table' => 'progression_utilisateurs', 'index' => 'idx_progression_user_cours', 'columns' => 'id_utilisateur, id_cours'],
    
    // Index pour les recherches par date
    ['table' => 'livres', 'index' => 'idx_livres_date', 'columns' => 'date_publication'],
    ['table' => 'podcasts', 'index' => 'idx_podcasts_date', 'columns' => 'date_publication'],
    ['table' => 'cours', 'index' => 'idx_cours_date', 'columns' => 'date_creation'],
    ['table' => 'videos', 'index' => 'idx_videos_date', 'columns' => 'date_publication'],
    
    // Index pour les colonnes de statut
    ['table' => 'utilisateurs', 'index' => 'idx_utilisateurs_statut', 'columns' => 'statut'],
    ['table' => 'cours', 'index' => 'idx_cours_statut', 'columns' => 'statut'],
    ['table' => 'inscriptions', 'index' => 'idx_inscriptions_statut', 'columns' => 'statut']
];

foreach ($indexes as $index_info) {
    $table = $index_info['table'];
    $index = $index_info['index'];
    $columns = $index_info['columns'];
    
    if (tableExists($pdo, $table)) {
        if (!indexExists($pdo, $table, $index)) {
            echo "<p class='warning'>L'index '$index' n'existe pas sur la table '$table'. Tentative de création...</p>";
            
            try {
                $sql_create_index = "CREATE INDEX $index ON $table ($columns)";
                executeQuery($pdo, $sql_create_index, "Création de l'index $index");
            } catch (PDOException $e) {
                echo "<p class='error'>Erreur lors de la création de l'index: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='success'>L'index '$index' existe déjà sur la table '$table'.</p>";
        }
    } else {
        echo "<p class='warning'>La table '$table' n'existe pas. Impossible de créer l'index.</p>";
    }
}

// 2. Analyse des tables pour PostgreSQL
if ($is_postgres) {
    echo "<h2>Analyse des tables PostgreSQL</h2>";
    
    $tables = [
        'administrateurs', 'utilisateurs', 'categories_livres', 'categories_podcasts',
        'categories_cours', 'categories_videos', 'livres', 'podcasts', 'cours', 'videos',
        'modules', 'lecons', 'inscriptions', 'progression_utilisateurs', 'badges', 'user_badges'
    ];
    
    foreach ($tables as $table) {
        if (tableExists($pdo, $table)) {
            echo "<p>Analyse de la table '$table'...</p>";
            
            try {
                $sql_analyze = "ANALYZE $table";
                executeQuery($pdo, $sql_analyze, "Analyse de la table $table");
            } catch (PDOException $e) {
                echo "<p class='error'>Erreur lors de l'analyse de la table: " . $e->getMessage() . "</p>";
            }
        }
    }
}

// 3. Optimisation des tables pour MySQL
if (!$is_postgres) {
    echo "<h2>Optimisation des tables MySQL</h2>";
    
    $tables = [
        'administrateurs', 'utilisateurs', 'categories_livres', 'categories_podcasts',
        'categories_cours', 'categories_videos', 'livres', 'podcasts', 'cours', 'videos',
        'modules', 'lecons', 'inscriptions', 'progression_utilisateurs', 'badges', 'user_badges'
    ];
    
    foreach ($tables as $table) {
        if (tableExists($pdo, $table)) {
            echo "<p>Optimisation de la table '$table'...</p>";
            
            try {
                $sql_optimize = "OPTIMIZE TABLE $table";
                executeQuery($pdo, $sql_optimize, "Optimisation de la table $table");
            } catch (PDOException $e) {
                echo "<p class='error'>Erreur lors de l'optimisation de la table: " . $e->getMessage() . "</p>";
            }
        }
    }
}

// 4. Vérification des statistiques de la base de données
echo "<h2>Statistiques de la base de données</h2>";

try {
    if ($is_postgres) {
        // PostgreSQL
        $sql_stats = "
        SELECT 
            relname as table_name,
            n_live_tup as row_count,
            pg_size_pretty(pg_relation_size(relid)) as table_size
        FROM 
            pg_stat_user_tables
        ORDER BY 
            n_live_tup DESC;
        ";
    } else {
        // MySQL
        $sql_stats = "
        SELECT 
            table_name,
            table_rows as row_count,
            CONCAT(ROUND(data_length / 1024 / 1024, 2), ' MB') as table_size
        FROM 
            information_schema.tables
        WHERE 
            table_schema = DATABASE()
        ORDER BY 
            table_rows DESC;
        ";
    }
    
    $stmt = $pdo->query($sql_stats);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Table</th><th>Nombre de lignes</th><th>Taille</th></tr>";
    
    foreach ($stats as $stat) {
        echo "<tr>";
        echo "<td>" . $stat['table_name'] . "</td>";
        echo "<td>" . $stat['row_count'] . "</td>";
        echo "<td>" . $stat['table_size'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} catch (PDOException $e) {
    echo "<p class='error'>Erreur lors de la récupération des statistiques: " . $e->getMessage() . "</p>";
}

// 5. Recommandations pour améliorer les performances
echo "<h2>Recommandations pour améliorer les performances</h2>";

echo "<ul>";
echo "<li>Utilisez des requêtes préparées pour toutes les opérations SQL.</li>";
echo "<li>Limitez le nombre de résultats retournés avec LIMIT.</li>";
echo "<li>Sélectionnez uniquement les colonnes nécessaires (évitez SELECT *).</li>";
echo "<li>Utilisez des jointures appropriées (INNER JOIN, LEFT JOIN) selon vos besoins.</li>";
echo "<li>Évitez les sous-requêtes complexes, utilisez plutôt des jointures.</li>";
echo "<li>Mettez en cache les résultats des requêtes fréquentes.</li>";
echo "<li>Utilisez des transactions pour les opérations multiples.</li>";
echo "<li>Optimisez régulièrement les tables et mettez à jour les statistiques.</li>";
echo "</ul>";

// Liens de retour
echo "<h2>Liens utiles :</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='fix_all_tables.php'>Corriger toutes les tables</a></li>";
echo "<li><a href='fix_foreign_keys.php'>Corriger les clés étrangères</a></li>";
echo "<li><a href='fix_db_compatibility.php'>Compatibilité MySQL/PostgreSQL</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
