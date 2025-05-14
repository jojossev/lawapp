<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Variables d'environnement :<br>";
echo "ENVIRONMENT: " . getenv('ENVIRONMENT') . "<br>";
echo "DATABASE_URL: " . getenv('DATABASE_URL') . "<br>";

// Tester la connexion à la base de données
require_once 'includes/config.php';

echo "<br>Configuration de la base de données :<br>";
echo "DB_TYPE: " . DB_TYPE . "<br>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_PORT: " . DB_PORT . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";

try {
    require_once 'includes/db_connect.php';
    echo "<br>Connexion réussie à la base de données !";
} catch (Exception $e) {
    echo "<br>Erreur : " . $e->getMessage();
}
