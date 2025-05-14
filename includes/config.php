<?php
// Fichier config.php

// Configuration de la base de données
if (getenv('DATABASE_URL')) {
    $db_url = parse_url(getenv('DATABASE_URL'));
    define('DB_TYPE', 'pgsql');
    define('DB_HOST', $db_url['host']);
    define('DB_PORT', $db_url['port']);
    define('DB_NAME', ltrim($db_url['path'], '/'));
    define('DB_USER', $db_url['user']);
    define('DB_PASS', $db_url['pass']);
} else {
    define('DB_TYPE', 'mysql');
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'lawapp');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Configuration de l'URL de base
define('BASE_URL', getenv('APP_URL') ?: 'http://localhost/LawApp');

// Définir l'environnement
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

// Démarrage de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/db_connect.php';
?>
