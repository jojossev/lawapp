<?php
// Fichier config.php

// Détection de l'environnement
$is_production = getenv('ENVIRONMENT') === 'production';

if ($is_production) {
    // Parse DATABASE_URL from Heroku
    $db_url = parse_url(getenv('CLEARDB_DATABASE_URL'));
    
    define('DB_HOST', $db_url['host']);
    define('DB_NAME', substr($db_url['path'], 1));
    define('DB_USER', $db_url['user']);
    define('DB_PASS', $db_url['pass']);
    define('BASE_URL', getenv('APP_URL'));
} else {
    // Configuration locale
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'lawapp');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('BASE_URL', 'http://localhost/LawApp');
}

// Démarrage de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/db_connect.php';
?>
