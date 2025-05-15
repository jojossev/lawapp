<?php
// Script pour tester la connexion à la base de données et afficher des informations détaillées
// Utile pour diagnostiquer les problèmes de connexion à PostgreSQL sur Render

// Inclure les fichiers de configuration
require_once __DIR__ . '/includes/config.php';

// Afficher un message de début
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test de connexion à la base de données - LawApp</title>
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
        .code { font-family: monospace; background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Test de connexion à la base de données - LawApp</h1>
        <p>Ce script teste la connexion à la base de données et affiche des informations détaillées.</p>";

// Vérifier la connexion à la base de données
echo "<h2>1. Vérification de la connexion à la base de données</h2>";
try {
    if (!isset($pdo)) {
        throw new Exception("La connexion à la base de données n'est pas établie.");
    }
    
    // Tester la connexion avec une requête simple
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo "<p class='success'>✅ Connexion à la base de données établie avec succès.</p>";
        
        // Afficher les informations sur la connexion
        echo "<h3>Informations sur la connexion</h3>";
        echo "<table>";
        echo "<tr><th>Paramètre</th><th>Valeur</th></tr>";
        
        // Type de base de données
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        echo "<tr><td>Type de base de données</td><td>" . htmlspecialchars($driver) . "</td></tr>";
        
        // Version du serveur
        $server_version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        echo "<tr><td>Version du serveur</td><td>" . htmlspecialchars($server_version) . "</td></tr>";
        
        // Version du client
        $client_version = $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
        echo "<tr><td>Version du client</td><td>" . htmlspecialchars($client_version) . "</td></tr>";
        
        // Mode d'erreur
        $error_mode = $pdo->getAttribute(PDO::ATTR_ERRMODE);
        $error_mode_str = "Inconnu";
        switch ($error_mode) {
            case PDO::ERRMODE_SILENT: $error_mode_str = "ERRMODE_SILENT"; break;
            case PDO::ERRMODE_WARNING: $error_mode_str = "ERRMODE_WARNING"; break;
            case PDO::ERRMODE_EXCEPTION: $error_mode_str = "ERRMODE_EXCEPTION"; break;
        }
        echo "<tr><td>Mode d'erreur</td><td>" . htmlspecialchars($error_mode_str) . "</td></tr>";
        
        // URL de connexion (masquée)
        if (defined('DB_URL')) {
            $masked_url = preg_replace('/(:\/\/)([^:]+):([^@]+)@/', '$1****:****@', DB_URL);
            echo "<tr><td>URL de connexion</td><td>" . htmlspecialchars($masked_url) . "</td></tr>";
        }
        
        // Variable d'environnement DATABASE_URL
        $env_db_url = getenv('DATABASE_URL');
        if ($env_db_url) {
            $masked_env_url = preg_replace('/(:\/\/)([^:]+):([^@]+)@/', '$1****:****@', $env_db_url);
            echo "<tr><td>Variable d'environnement DATABASE_URL</td><td>" . htmlspecialchars($masked_env_url) . "</td></tr>";
        } else {
            echo "<tr><td>Variable d'environnement DATABASE_URL</td><td>Non définie</td></tr>";
        }
        
        echo "</table>";
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

// Lister les tables existantes
echo "<h2>2. Tables existantes dans la base de données</h2>";
try {
    // Adapter la requête en fonction du type de base de données
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver == 'pgsql') {
        $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name";
    } else {
        $sql = "SHOW TABLES";
    }
    
    $stmt = $pdo->query($sql);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<p>Nombre de tables trouvées: " . count($tables) . "</p>";
        echo "<table>";
        echo "<tr><th>#</th><th>Nom de la table</th></tr>";
        
        $i = 1;
        foreach ($tables as $table) {
            echo "<tr><td>" . $i++ . "</td><td>" . htmlspecialchars($table) . "</td></tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucune table trouvée dans la base de données.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erreur lors de la récupération des tables: " . $e->getMessage() . "</p>";
}

// Tester une requête spécifique à PostgreSQL
if ($driver == 'pgsql') {
    echo "<h2>3. Test de requêtes spécifiques à PostgreSQL</h2>";
    
    try {
        // Tester la création d'une table temporaire
        $sql = "CREATE TEMPORARY TABLE test_table (id SERIAL PRIMARY KEY, name VARCHAR(100))";
        $pdo->exec($sql);
        echo "<p class='success'>✅ Création d'une table temporaire réussie.</p>";
        
        // Tester l'insertion de données
        $sql = "INSERT INTO test_table (name) VALUES ('Test 1'), ('Test 2')";
        $pdo->exec($sql);
        echo "<p class='success'>✅ Insertion de données réussie.</p>";
        
        // Tester la récupération de données
        $sql = "SELECT * FROM test_table";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Données récupérées:</p>";
        echo "<pre>" . print_r($rows, true) . "</pre>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erreur lors du test de requêtes PostgreSQL: " . $e->getMessage() . "</p>";
    }
}

// Afficher des conseils pour résoudre les problèmes
echo "<h2>Conseils pour résoudre les problèmes de connexion</h2>";
echo "<ul>";
echo "<li>Assurez-vous que la variable d'environnement <span class='code'>DATABASE_URL</span> est correctement définie sur Render.</li>";
echo "<li>Vérifiez que la base de données PostgreSQL est accessible depuis votre application Render.</li>";
echo "<li>Pour les problèmes de syntaxe SQL, assurez-vous d'utiliser la syntaxe PostgreSQL et non MySQL.</li>";
echo "<li>Utilisez <span class='code'>SERIAL</span> au lieu de <span class='code'>AUTO_INCREMENT</span> pour les clés primaires auto-incrémentées.</li>";
echo "<li>Utilisez <span class='code'>information_schema.tables</span> au lieu de <span class='code'>SHOW TABLES</span> pour lister les tables.</li>";
echo "</ul>";

// Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='fix_all_tables.php'>Corriger toutes les tables</a></li>";
echo "<li><a href='test_render.php'>Test Render</a></li>";
echo "<li><a href='debug_render.php'>Debug Render</a></li>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "</ul>";

echo "</div></body></html>";
?>
