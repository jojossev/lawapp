<?php
// Script complet pour créer toutes les tables nécessaires dans la base de données
// Ce script vérifie et crée toutes les tables manquantes en se basant sur la structure complète de l'application

// Inclure les fichiers de configuration
require_once __DIR__ . '/includes/config.php';

// Afficher un message de début
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Correction complète des tables - LawApp</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        .status-ok { background-color: #d4edda; }
        .status-error { background-color: #f8d7da; }
        .status-warning { background-color: #fff3cd; }
        .progress-container { 
            width: 100%; 
            background-color: #f1f1f1; 
            border-radius: 5px;
            margin: 10px 0;
        }
        .progress-bar {
            height: 30px;
            background-color: #4CAF50;
            text-align: center;
            line-height: 30px;
            color: white;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Correction complète des tables - LawApp</h1>
        <p>Ce script vérifie et crée toutes les tables nécessaires dans la base de données.</p>";

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false || strpos(getenv('DATABASE_URL'), 'postgres') !== false) {
            $sql = "SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' AND table_name = '$table'
            )";
            $stmt = $pdo->query($sql);
            return $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SHOW TABLES LIKE '$table'";
            $stmt = $pdo->query($sql);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de l'existence de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false || strpos(getenv('DATABASE_URL'), 'postgres') !== false) {
            $sql = "SELECT EXISTS (
                SELECT FROM information_schema.columns 
                WHERE table_schema = 'public' AND table_name = '$table' AND column_name = '$column'
            )";
            $stmt = $pdo->query($sql);
            return $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
            $stmt = $pdo->query($sql);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de l'existence de la colonne: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour exécuter une requête SQL avec gestion d'erreur
function executeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "<p class='success'>✅ $description réussie.</p>";
        return true;
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erreur lors de $description: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Vérifier la connexion à la base de données
echo "<h2>1. Vérification de la connexion à la base de données</h2>";
try {
    if (!isset($pdo)) {
        throw new Exception("La connexion à la base de données n'est pas établie.");
    }
    
    // Tester la connexion avec une requête simple
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        // Détecter le type de base de données
        $is_postgresql = strpos(DB_URL, 'pgsql') !== false || strpos(getenv('DATABASE_URL'), 'postgres') !== false;
        $db_type = $is_postgresql ? 'PostgreSQL' : 'MySQL';
        
        echo "<p class='success'>✅ Connexion à la base de données établie avec succès.</p>";
        echo "<p>Type de base de données détecté: <strong>$db_type</strong></p>";
    } else {
        throw new Exception("Impossible d'exécuter une requête simple sur la base de données.");
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez la variable d'environnement DATABASE_URL et assurez-vous que la base de données est accessible.</p>";
    
    // Afficher les informations de connexion (masquées)
    echo "<p>URL de connexion: ";
    if (defined('DB_URL')) {
        $masked_url = preg_replace('/(:\/\/)([^:]+):([^@]+)@/', '$1****:****@', DB_URL);
        echo htmlspecialchars($masked_url);
    } else {
        echo "Non définie";
    }
    echo "</p>";
    
    // Vérifier si la variable d'environnement est définie
    echo "<p>Variable d'environnement DATABASE_URL: " . (getenv('DATABASE_URL') ? "Définie" : "Non définie") . "</p>";
    
    die("<p>Impossible de continuer sans connexion à la base de données.</p></div></body></html>");
}

// Inclure les définitions des tables
require_once __DIR__ . '/table_definitions.php';

// Vérifier et créer les tables nécessaires
echo "<h2>2. Vérification et création des tables</h2>";

$total_tables = count($table_definitions);
$tables_created = 0;

echo "<div class='progress-container'>
    <div class='progress-bar' style='width: 0%'>0%</div>
</div>";

foreach ($table_definitions as $table => $definition) {
    $tables_created++;
    $progress = round(($tables_created / $total_tables) * 100);
    
    echo "<script>
        document.querySelector('.progress-bar').style.width = '$progress%';
        document.querySelector('.progress-bar').innerText = '$progress%';
    </script>";
    echo str_pad('', 4096);
    ob_flush();
    flush();
    
    echo "<h3>Table: $table</h3>";
    
    if (!tableExists($pdo, $table)) {
        echo "<p>La table '$table' n'existe pas. Création en cours...</p>";
        
        // Adapter la requête SQL en fonction du type de base de données
        $is_postgresql = strpos(DB_URL, 'pgsql') !== false || strpos(getenv('DATABASE_URL'), 'postgres') !== false;
        $create_sql = $is_postgresql ? $definition['postgresql'] : $definition['mysql'];
        
        if (executeQuery($pdo, $create_sql, "création de la table $table")) {
            // Insérer des données de test si disponibles
            if (isset($definition['sample_data']) && !empty($definition['sample_data'])) {
                echo "<p>Insertion de données de test dans la table '$table'...</p>";
                executeQuery($pdo, $definition['sample_data'], "insertion de données de test dans la table $table");
            }
        }
    } else {
        echo "<p class='success'>✅ La table '$table' existe déjà.</p>";
        
        // Vérifier les colonnes importantes si spécifiées
        if (isset($definition['important_columns']) && !empty($definition['important_columns'])) {
            foreach ($definition['important_columns'] as $column) {
                if (!columnExists($pdo, $table, $column)) {
                    echo "<p>La colonne '$column' n'existe pas dans la table '$table'. Ajout en cours...</p>";
                    
                    // Adapter la requête SQL en fonction du type de base de données
                    $alter_sql = "ALTER TABLE $table ADD COLUMN $column ";
                    $alter_sql .= isset($definition['column_definitions'][$column]) ? $definition['column_definitions'][$column] : "VARCHAR(255)";
                    
                    executeQuery($pdo, $alter_sql, "ajout de la colonne $column à la table $table");
                } else {
                    echo "<p class='success'>✅ La colonne '$column' existe dans la table '$table'.</p>";
                }
            }
        }
    }
}

// Résumé et recommandations
echo "<h2>Résumé et recommandations</h2>";
echo "<p>Le processus de correction des tables est terminé. Voici les recommandations :</p>";
echo "<ol>";
echo "<li>Redémarrez votre application sur Render en allant dans le tableau de bord et en cliquant sur 'Manual Deploy' > 'Clear Build Cache & Deploy'.</li>";
echo "<li>Si l'erreur persiste, vérifiez les logs de l'application dans le tableau de bord Render.</li>";
echo "<li>Assurez-vous que les variables d'environnement sont correctement configurées.</li>";
echo "</ol>";

// Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='test_render.php'>Test Render</a></li>";
echo "<li><a href='debug_render.php'>Debug Render</a></li>";
echo "</ul>";

echo "</div></body></html>";
?>
