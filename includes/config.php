<?php
// Fichier config.php
define('BASE_URL', 'http://localhost/LawApp');

// Démarrage de la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/db_connect.php';
?>
