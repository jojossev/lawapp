<?php
// Informations de connexion à la base de données
require_once 'config.php';

try {
    // Utiliser directement DATABASE_URL si disponible
    if (getenv('DATABASE_URL')) {
        $pdo = new PDO(getenv('DATABASE_URL'));
    } else {
        // Sinon, utiliser la configuration standard
        $dsn = DB_TYPE === 'pgsql'
            ? "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require"
            : "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
    }

    // Configuration commune
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Configuration spécifique MySQL
    if (DB_TYPE === 'mysql') {
        $pdo->exec("SET NAMES utf8mb4");
    }
} catch(PDOException $e) {
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    http_response_code(500);
    die("Erreur de connexion à la base de données. Détail : " . $e->getMessage());
}
?>
