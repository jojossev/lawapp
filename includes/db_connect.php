<?php
// Informations de connexion à la base de données
require_once 'config.php';

try {
    $database_url = getenv('DATABASE_URL');
    if (!$database_url) {
        throw new Exception('DATABASE_URL environment variable is not set');
    }

    // Log the database URL (masking sensitive information)
    $masked_url = preg_replace('/\/\/[^:]+:[^@]+@/', '//*****:*****@', $database_url);
    error_log("Trying to connect to database: " . $masked_url);

    // Parse the URL to get components
    $url = parse_url($database_url);
    
    // Extract database name without trailing underscore
    $dbname = rtrim(ltrim($url['path'], '/'), '_');
    
    // Log parsed components for debugging
    error_log("Parsed components: host={$url['host']}, port={$url['port']}, dbname={$dbname}");
    
    // Build the PDO DSN
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $url['host'],
        $url['port'] ?? '5432',
        $dbname
    );

    // Create PDO instance with credentials
    $pdo = new PDO($dsn, $url['user'], $url['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    error_log("Database connection successful");

} catch(Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    die("Erreur de connexion à la base de données. Détail : " . $e->getMessage());
}
?>
