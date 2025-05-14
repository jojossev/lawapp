<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Afficher les extensions PHP chargées
echo "<h2>Extensions PHP chargées :</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";

// Vérifier spécifiquement PDO et PostgreSQL
echo "<h2>Vérification des extensions critiques :</h2>";
echo "PDO installé : " . (extension_loaded('pdo') ? 'Oui' : 'Non') . "<br>";
echo "PDO PostgreSQL installé : " . (extension_loaded('pdo_pgsql') ? 'Oui' : 'Non') . "<br>";
echo "PostgreSQL installé : " . (extension_loaded('pgsql') ? 'Oui' : 'Non') . "<br>";

// Afficher les pilotes PDO disponibles
echo "<h2>Pilotes PDO disponibles :</h2>";
echo "<pre>";
print_r(PDO::getAvailableDrivers());
echo "</pre>";

echo "<h2>Variables d'environnement :</h2>";
echo "ENVIRONMENT: " . getenv('ENVIRONMENT') . "<br>";
echo "DATABASE_URL: " . getenv('DATABASE_URL') . "<br>";

// Tester la connexion à la base de données
require_once 'includes/config.php';

echo "<h2>Configuration de la base de données :</h2>";
echo "DB_TYPE: " . DB_TYPE . "<br>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_PORT: " . DB_PORT . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";

try {
    require_once 'includes/db_connect.php';
    echo "<h2>✅ Connexion réussie à la base de données !</h2>";
} catch (Exception $e) {
    echo "<h2>❌ Erreur de connexion :</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    
    // Afficher la trace complète pour le débogage
    echo "<h3>Trace de l'erreur :</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
