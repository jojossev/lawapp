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
    
    // Build the PDO DSN
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s',
        $url['host'],
        $url['port'] ?? '5432',
        ltrim($url['path'], '/'),
        $url['user'],
        $url['pass']
    );

    // Create PDO instance with the DSN
    $pdo = new PDO($dsn);
    
    // Configure PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    error_log("Database connection successful");

} catch(Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    die("Erreur de connexion à la base de données. Détail : " . $e->getMessage());
}
?>
