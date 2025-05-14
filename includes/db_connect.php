<?php
// Informations de connexion à la base de données
require_once 'config.php';

try {
    // Construction du DSN en fonction du type de base de données
    if (DB_TYPE === 'pgsql') {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
    } else {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    if (DB_TYPE === 'mysql') {
        $pdo->exec("SET NAMES utf8mb4");
    }
} catch(PDOException $e) {
    // Dans un environnement de production, vous voudriez logger cette erreur
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    // Afficher une erreur plus conviviale
    http_response_code(500);
    die("Erreur de connexion à la base de données. Détail : " . $e->getMessage());
}
?>
