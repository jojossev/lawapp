<?php
// Script de diagnostic pour PostgreSQL
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Activer la mise en tampon de sortie au début du script
ob_start();

// Fonction pour afficher les messages
function showMessage($message, $type = 'info') {
    $class = ($type == 'success') ? 'success' : (($type == 'error') ? 'error' : 'info');
    echo "<div style='margin: 10px; padding: 10px; border-radius: 5px; background-color: " . 
        ($type == 'success' ? '#d4edda' : ($type == 'error' ? '#f8d7da' : '#d1ecf1')) . 
        "; color: " . 
        ($type == 'success' ? '#155724' : ($type == 'error' ? '#721c24' : '#0c5460')) . 
        ";'><strong>" . 
        ($type == 'success' ? 'Succès' : ($type == 'error' ? 'Erreur' : 'Info')) . 
        ":</strong> $message</div>";
    
    // Vider le tampon de sortie seulement s'il est actif
    if (ob_get_level() > 0) {
        @ob_flush();
        @flush();
    }
}

// Fonction pour tester une connexion à PostgreSQL
function testPostgresConnection($dsn, $user, $password, $description) {
    showMessage("Test de connexion: $description", 'info');
    try {
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        showMessage("Connexion réussie: $description", 'success');
        return $pdo;
    } catch (PDOException $e) {
        showMessage("Échec de connexion: $description - " . $e->getMessage(), 'error');
        return null;
    }
}

// Récupérer l'URL de la base de données
$database_url = getenv('DATABASE_URL');
if (empty($database_url)) {
    showMessage("Variable d'environnement DATABASE_URL non définie.", 'error');
    showMessage("Vérification des variables d'environnement disponibles:", 'info');
    foreach ($_ENV as $key => $value) {
        if (strpos(strtolower($key), 'database') !== false || strpos(strtolower($key), 'db') !== false || strpos(strtolower($key), 'postgres') !== false) {
            showMessage("$key = " . (strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value), 'info');
        }
    }
} else {
    showMessage("DATABASE_URL trouvée: " . (strlen($database_url) > 30 ? substr($database_url, 0, 30) . '...' : $database_url), 'success');
}

// Analyser l'URL de la base de données
if (!empty($database_url)) {
    $url = parse_url($database_url);
    
    // Afficher les composants de l'URL
    showMessage("Composants de l'URL:", 'info');
    foreach ($url as $key => $value) {
        if ($key != 'pass') {
            showMessage("$key = $value", 'info');
        } else {
            showMessage("$key = " . (strlen($value) > 3 ? substr($value, 0, 3) . '***' : '***'), 'info');
        }
    }
    
    // Extraire les informations de connexion
    $host = $url['host'] ?? 'localhost';
    $port = $url['port'] ?? '5432';
    $dbname = ltrim($url['path'] ?? '', '/');
    $user = $url['user'] ?? '';
    $password = $url['pass'] ?? '';
    
    // Tentatives de connexion avec différentes configurations
    showMessage("Tentatives de connexion avec différentes configurations:", 'info');
    
    // Test 1: Connexion avec le nom de base de données tel quel
    $dsn1 = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo1 = testPostgresConnection($dsn1, $user, $password, "Configuration originale");
    
    // Test 2: Connexion avec le nom de base de données sans underscore à la fin
    if (substr($dbname, -1) === '_') {
        $dbname2 = substr($dbname, 0, -1);
        $dsn2 = "pgsql:host=$host;port=$port;dbname=$dbname2";
        $pdo2 = testPostgresConnection($dsn2, $user, $password, "Sans underscore à la fin");
    }
    
    // Test 3: Connexion sans spécifier de base de données
    $dsn3 = "pgsql:host=$host;port=$port";
    $pdo3 = testPostgresConnection($dsn3, $user, $password, "Sans spécifier de base de données");
    
    // Test 4: Connexion avec 'postgres' comme base de données
    $dsn4 = "pgsql:host=$host;port=$port;dbname=postgres";
    $pdo4 = testPostgresConnection($dsn4, $user, $password, "Base de données 'postgres'");
    
    // Si une connexion a réussi, afficher les bases de données disponibles
    $pdo = $pdo1 ?? $pdo2 ?? $pdo3 ?? $pdo4 ?? null;
    if ($pdo) {
        showMessage("Connexion établie. Recherche des bases de données disponibles...", 'success');
        try {
            $stmt = $pdo->query("SELECT datname FROM pg_database WHERE datistemplate = false;");
            $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
            showMessage("Bases de données disponibles:", 'info');
            foreach ($databases as $db) {
                showMessage("- $db", 'info');
            }
            
            // Recommandation pour la configuration
            showMessage("Recommandation de configuration:", 'success');
            if (in_array('lawapp', $databases)) {
                showMessage("Utilisez 'lawapp' comme nom de base de données", 'success');
            } elseif (in_array(rtrim($dbname, '_'), $databases)) {
                showMessage("Utilisez '" . rtrim($dbname, '_') . "' comme nom de base de données", 'success');
            } else {
                showMessage("Créez une base de données nommée 'lawapp' ou utilisez l'une des bases de données existantes", 'info');
            }
        } catch (PDOException $e) {
            showMessage("Impossible de lister les bases de données: " . $e->getMessage(), 'error');
        }
    }
}

// Vérifier la configuration de PHP pour PostgreSQL
showMessage("Vérification de la configuration PHP pour PostgreSQL:", 'info');
if (function_exists('pg_connect')) {
    showMessage("Extension PostgreSQL (pg_connect) est disponible", 'success');
} else {
    showMessage("Extension PostgreSQL (pg_connect) n'est pas disponible", 'error');
}

if (in_array('pgsql', PDO::getAvailableDrivers())) {
    showMessage("Driver PDO pour PostgreSQL est disponible", 'success');
} else {
    showMessage("Driver PDO pour PostgreSQL n'est pas disponible", 'error');
    showMessage("Drivers PDO disponibles: " . implode(', ', PDO::getAvailableDrivers()), 'info');
}

// Afficher la version de PHP
showMessage("Version de PHP: " . phpversion(), 'info');

// Afficher les extensions chargées
$extensions = get_loaded_extensions();
showMessage("Extensions PHP chargées liées aux bases de données:", 'info');
foreach ($extensions as $ext) {
    if (strpos(strtolower($ext), 'sql') !== false || strpos(strtolower($ext), 'db') !== false || strpos(strtolower($ext), 'pg') !== false || strpos(strtolower($ext), 'pdo') !== false) {
        showMessage("- $ext", 'info');
    }
}

// Afficher les informations sur le serveur
showMessage("Informations sur le serveur:", 'info');
showMessage("Serveur: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu'), 'info');
showMessage("Nom d'hôte: " . (gethostname() ?: 'Inconnu'), 'info');
showMessage("IP du serveur: " . ($_SERVER['SERVER_ADDR'] ?? 'Inconnue'), 'info');

// Conclusion
showMessage("Diagnostic terminé. Utilisez ces informations pour corriger la configuration de la base de données.", 'success');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic PostgreSQL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        h1 {
            color: #343a40;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">&larr; Retour à l'accueil</a>
        <h1>Diagnostic de connexion PostgreSQL</h1>
        <!-- Les messages sont affichés ici par le script PHP -->
    </div>
</body>
</html>
