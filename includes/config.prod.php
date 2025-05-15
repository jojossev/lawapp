<?php
// Configuration de la base de données pour la production
define('DB_HOST', 'REMPLACER_PAR_VOTRE_HOST'); // Exemple : sql309.infinityfree.com
define('DB_NAME', 'REMPLACER_PAR_VOTRE_NOM_DB');
define('DB_USER', 'REMPLACER_PAR_VOTRE_USER');
define('DB_PASS', 'REMPLACER_PAR_VOTRE_PASSWORD');

// URL de base de l'application
define('BASE_URL', 'REMPLACER_PAR_VOTRE_URL'); // Exemple : https://votreapp.infinityfree.net

// Configuration de l'environnement
define('APP_ENV', 'production');

// Paramètres de sécurité
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Protection contre les attaques XSS
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Forcer HTTPS en production
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (!headers_sent()) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Démarrage de la session avec des paramètres sécurisés
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', 1);
}
session_start();
?>
