<?php
// Informations de connexion à la base de données
require_once 'config.php';

try {
    // Utiliser la constante DB_URL définie dans config.php
    $database_url = DB_URL;
    if (!$database_url) {
        throw new Exception('DATABASE_URL is not set in config.php');
    }

    // Vérifier si nous sommes en environnement de production ou de développement
    if (getenv('DATABASE_URL')) {
        // En production, utiliser l'URL de connexion complète
        $url = parse_url($database_url);
        if (!$url) {
            throw new Exception('Invalid DATABASE_URL format');
        }

        // S'assurer que tous les composants requis sont présents
        if (!isset($url['host'], $url['user'], $url['pass'], $url['path'])) {
            throw new Exception('DATABASE_URL missing required components');
        }

        // Extraire le nom de la base de données sans le tiret bas final
        $dbname = rtrim(ltrim($url['path'], '/'), '_');
        $port = isset($url['port']) ? $url['port'] : '5432';
        
        // Construire le DSN PDO
        if (strpos($database_url, 'postgres') !== false) {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $url['host'],
                $port,
                $dbname
            );
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $url['host'],
                $port,
                $dbname
            );
        }

        // Créer l'instance PDO avec les identifiants extraits de l'URL
        $pdo = new PDO($dsn, $url['user'], $url['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } else {
        // En développement local, utiliser les constantes définies
        $dsn = $database_url;
        
        // Créer l'instance PDO avec les identifiants
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }

    // Log success to error log only
    error_log("Database connection successful");

} catch(Exception $e) {
    // Log error to error log
    error_log("Database connection error: " . $e->getMessage());
    
    // Only set response code if headers haven't been sent
    if (!headers_sent()) {
        http_response_code(500);
    }
    
    // Return error message
    die("Erreur de connexion à la base de données. Détail : " . $e->getMessage());
}
?>
