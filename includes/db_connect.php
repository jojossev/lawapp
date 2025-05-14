<?php
// Informations de connexion à la base de données
require_once 'config.php';

try {
    $database_url = getenv('DATABASE_URL');
    if (!$database_url) {
        throw new Exception('DATABASE_URL environment variable is not set');
    }

    // Parse the URL to get components
    $url = parse_url($database_url);
    if (!$url) {
        throw new Exception('Invalid DATABASE_URL format');
    }

    // Ensure all required components are present
    if (!isset($url['host'], $url['user'], $url['pass'], $url['path'])) {
        throw new Exception('DATABASE_URL missing required components');
    }

    // Extract database name without trailing underscore
    $dbname = rtrim(ltrim($url['path'], '/'), '_');
    $port = isset($url['port']) ? $url['port'] : '5432';
    
    // Build the PDO DSN
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $url['host'],
        $port,
        $dbname
    );

    // Create PDO instance with credentials
    $pdo = new PDO($dsn, $url['user'], $url['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

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
