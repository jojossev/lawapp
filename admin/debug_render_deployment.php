<?php
// Configuration des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fonction de test de connexion à la base de données
function testDatabaseConnection() {
    try {
        require_once __DIR__ . '/../includes/config.php';
        
        // Récupérer les informations de connexion
        $database_url = defined('DATABASE_URL') ? DATABASE_URL : getenv('DATABASE_URL');
        
        if (empty($database_url)) {
            return "❌ Aucune URL de base de données trouvée";
        }
        
        // Parser l'URL de connexion
        $parsed_url = parse_url($database_url);
        
        // Extraire les composants
        $host = $parsed_url['host'] ?? 'localhost';
        $port = $parsed_url['port'] ?? '5432';
        $path = ltrim($parsed_url['path'] ?? '', '/');
        $user = $parsed_url['user'] ?? '';
        $pass = $parsed_url['pass'] ?? '';
        
        // Déterminer le driver
        $driver = strpos($database_url, 'postgresql://') !== false ? 'pgsql' : 'mysql';
        
        // Options de connexion
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        // DSN de connexion
        $dsn = $driver === 'pgsql' 
            ? "pgsql:host=$host;port=$port;dbname=$path" 
            : "mysql:host=$host;port=$port;dbname=$path";
        
        // Tenter la connexion
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        return "✅ Connexion à la base de données réussie";
    } catch (PDOException $e) {
        return "❌ Échec de connexion à la base de données : " . $e->getMessage();
    }
}

// Fonction de vérification des variables d'environnement
function checkEnvironmentVariables() {
    $env_vars = [
        'DATABASE_URL',
        'ENVIRONMENT',
        'APP_URL',
        'RENDER_EXTERNAL_URL',
        'PHP_VERSION',
        'HOME'
    ];
    
    $results = [];
    foreach ($env_vars as $var) {
        $value = getenv($var);
        $results[] = $value !== false 
            ? "✅ $var: " . substr($value, 0, 50) . (strlen($value) > 50 ? '...' : '')
            : "❌ $var non définie";
    }
    
    return $results;
}

// Fonction de vérification des permissions
function checkFilePermissions() {
    $critical_dirs = [
        __DIR__ . '/../admin',
        __DIR__ . '/../includes',
        __DIR__ . '/../uploads',
        __DIR__ . '/../tmp'
    ];
    
    $results = [];
    foreach ($critical_dirs as $dir) {
        $results[] = is_writable($dir) 
            ? "✅ Écriture autorisée : $dir" 
            : "❌ Écriture non autorisée : $dir";
    }
    
    return $results;
}

// Fonction de vérification des extensions PHP
function checkPHPExtensions() {
    $required_extensions = [
        'pdo', 'pdo_pgsql', 'pdo_mysql', 'json', 'mbstring', 'curl'
    ];
    
    $results = [];
    foreach ($required_extensions as $ext) {
        $results[] = extension_loaded($ext) 
            ? "✅ Extension $ext chargée" 
            : "❌ Extension $ext non chargée";
    }
    
    return $results;
}

// Fonction de diagnostic Docker/BuildKit
function checkDockerBuildkit() {
    $buildkit_socket = '/run/user/1000/buildkit/buildkitd.sock';
    
    $results = [];
    $results[] = file_exists($buildkit_socket) 
        ? "✅ Socket BuildKit trouvé" 
        : "❌ Socket BuildKit manquant";
    
    // Vérifier les permissions du socket
    if (file_exists($buildkit_socket)) {
        $perms = fileperms($buildkit_socket);
        $results[] = "📋 Permissions du socket BuildKit : " . decoct($perms & 0777);
    }
    
    return $results;
}

// Affichage des résultats
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Diagnostic Render Deployment</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Diagnostic Render Deployment</h1>
    
    <h2>🔌 Connexion Base de Données</h2>
    <pre>" . testDatabaseConnection() . "</pre>
    
    <h2>🌍 Variables d'Environnement</h2>
    <pre>" . implode("\n", checkEnvironmentVariables()) . "</pre>
    
    <h2>📂 Permissions de Fichiers</h2>
    <pre>" . implode("\n", checkFilePermissions()) . "</pre>
    
    <h2>🧩 Extensions PHP</h2>
    <pre>" . implode("\n", checkPHPExtensions()) . "</pre>
    
    <h2>🐳 Docker BuildKit</h2>
    <pre>" . implode("\n", checkDockerBuildkit()) . "</pre>
    
    <h2>📋 Informations Système</h2>
    <pre>
PHP Version: " . phpversion() . "
OS: " . php_uname() . "
Current Directory: " . __DIR__ . "
    </pre>
</body>
</html>";
