<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Informations sur l'environnement
echo "<h1>Informations de débogage</h1>";
echo "<h2>Variables d'environnement</h2>";
echo "<pre>";
print_r($_ENV);
echo "</pre>";

echo "<h2>Variables serveur</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

// Test de connexion à la base de données
echo "<h2>Test de connexion à la base de données</h2>";
try {
    // Inclure le fichier de configuration
    require_once 'includes/config.php';
    
    // Tenter de se connecter à la base de données
    require_once 'includes/db_connect.php';
    
    echo "<p style='color:green'>Connexion à la base de données réussie!</p>";
    
    // Vérifier si les tables existent
    $tables = ['categories_cours', 'cours'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = ?
        )");
        $stmt->execute([$table]);
        $exists = $stmt->fetchColumn();
        
        echo "<p>Table '{$table}': " . ($exists ? "<span style='color:green'>Existe</span>" : "<span style='color:red'>N'existe pas</span>") . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Erreur de connexion: " . $e->getMessage() . "</p>";
}

// Vérifier les chemins de fichiers
echo "<h2>Vérification des chemins de fichiers</h2>";
$files = [
    'index.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/config.php',
    'includes/db_connect.php',
    'css/style.css',
    'css/home.css',
    'css/animations.css',
    'assets/icons/facebook.svg',
    'assets/icons/twitter.svg',
    'assets/icons/linkedin.svg'
];

foreach ($files as $file) {
    echo "<p>Fichier '{$file}': " . (file_exists($file) ? "<span style='color:green'>Existe</span>" : "<span style='color:red'>N'existe pas</span>") . "</p>";
}

// Vérifier les constantes définies
echo "<h2>Constantes définies</h2>";
echo "<pre>";
$constants = [
    'BASE_URL',
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASS',
    'DB_PORT',
    'DB_TYPE',
    'DB_URL'
];

foreach ($constants as $constant) {
    echo "{$constant}: " . (defined($constant) ? constant($constant) : "Non défini") . "\n";
}
echo "</pre>";
?>
