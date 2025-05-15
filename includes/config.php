<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir l'environnement
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

// URLs de l'application
// Si nous sommes sur Render et que APP_URL ne contient pas l'hostname externe correct, utiliser RENDER_EXTERNAL_URL
if (getenv('RENDER') === 'true' && getenv('RENDER_EXTERNAL_URL') && strpos(getenv('APP_URL'), getenv('RENDER_EXTERNAL_HOSTNAME')) === false) {
    define('APP_URL', getenv('RENDER_EXTERNAL_URL'));
} else {
    define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
}
define('BASE_URL', rtrim(APP_URL, '/')); // Assure qu'il n'y a pas de slash à la fin

// Configuration de la base de données
if (getenv('DATABASE_URL')) {
    define('DB_URL', getenv('DATABASE_URL'));
} else {
    // Configuration par défaut pour le développement local (MySQL)
    define('DB_TYPE', 'mysql');
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'lawapp');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_URL', "mysql:host=localhost;dbname=lawapp;charset=utf8mb4");
}

// Démarrer la session si ce n'est pas déjà fait et si les headers n'ont pas été envoyés
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/db_connect.php';
?>
