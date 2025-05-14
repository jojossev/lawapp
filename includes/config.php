<?php
// Définir l'environnement
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

// URL de l'application
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// Configuration de la base de données
if (getenv('DATABASE_URL')) {
    define('DB_URL', getenv('DATABASE_URL'));
} else {
    // Configuration par défaut pour le développement local (PostgreSQL)
    define('DB_TYPE', 'pgsql');
    define('DB_HOST', 'localhost');
    define('DB_PORT', '5432');
    define('DB_NAME', 'lawapp');
    define('DB_USER', 'postgres');
    define('DB_PASS', 'postgres');
    define('DB_URL', "postgresql://{DB_USER}:{DB_PASS}@{DB_HOST}:{DB_PORT}/{DB_NAME}");
}

// Démarrer la session si ce n'est pas déjà fait et si les headers n'ont pas été envoyés
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/db_connect.php';
?>
