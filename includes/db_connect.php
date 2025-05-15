<?php
// Informations de connexion à la base de données
require_once 'config.php';

try {
    // Utiliser la constante DB_URL définie dans config.php
    $database_url = DB_URL;
    if (!$database_url) {
        throw new Exception('DATABASE_URL is not set in config.php');
    }

    // Pour MySQL, on peut utiliser directement le DSN
    $dsn = $database_url;
    
    // Créer l'instance PDO avec les identifiants
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
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
