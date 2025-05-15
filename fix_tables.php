<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers de configuration
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Correction des tables manquantes</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Correction des tables manquantes</h1>";

// Fonction pour exécuter une requête SQL et gérer les erreurs
function executeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "<p style='color:green'>✓ $description</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ $description: " . $e->getMessage() . "</p>";
    }
}

// Vérifier si la table categories_livres existe
$sql_check_categories_livres = "SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE()
    AND table_name = 'categories_livres'";

$stmt = $pdo->prepare($sql_check_categories_livres);
$stmt->execute();
$categories_livres_exists = ($stmt->fetchColumn() > 0);

if (!$categories_livres_exists) {
    echo "<p>La table 'categories_livres' n'existe pas. Création en cours...</p>";
    
    // Création de la table categories_livres
    $sql_categories_livres = "
    CREATE TABLE IF NOT EXISTS categories_livres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT,
        statut VARCHAR(20) DEFAULT 'actif',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";

    executeQuery($pdo, $sql_categories_livres, "Création de la table categories_livres");
    
    // Insertion de données de test pour les catégories de livres
    $sql_insert_categories_livres = "
    INSERT IGNORE INTO categories_livres (nom, description, statut) VALUES 
    ('Manuels Juridiques', 'Manuels et ouvrages de référence en droit', 'actif'),
    ('Codes Annotés', 'Codes juridiques avec annotations et jurisprudence', 'actif'),
    ('Essais Juridiques', 'Essais et analyses sur des questions juridiques', 'actif'),
    ('Revues Spécialisées', 'Publications périodiques en droit', 'actif');";

    executeQuery($pdo, $sql_insert_categories_livres, "Insertion des catégories de livres de test");
} else {
    echo "<p style='color:green'>✓ La table 'categories_livres' existe déjà.</p>";
}

// Vérifier si la table categories_podcasts existe
$sql_check_categories_podcasts = "SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE()
    AND table_name = 'categories_podcasts'";

$stmt = $pdo->prepare($sql_check_categories_podcasts);
$stmt->execute();
$categories_podcasts_exists = ($stmt->fetchColumn() > 0);

if (!$categories_podcasts_exists) {
    echo "<p>La table 'categories_podcasts' n'existe pas. Création en cours...</p>";
    
    // Création de la table categories_podcasts
    $sql_categories_podcasts = "
    CREATE TABLE IF NOT EXISTS categories_podcasts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT,
        statut VARCHAR(20) DEFAULT 'actif',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";

    executeQuery($pdo, $sql_categories_podcasts, "Création de la table categories_podcasts");
    
    // Insertion de données de test pour les catégories de podcasts
    $sql_insert_categories_podcasts = "
    INSERT IGNORE INTO categories_podcasts (nom, description, statut) VALUES 
    ('Actualités Juridiques', 'Podcasts sur les actualités récentes en droit', 'actif'),
    ('Entretiens avec des Experts', 'Interviews de professionnels du droit', 'actif'),
    ('Cas Pratiques', 'Analyses de cas juridiques concrets', 'actif'),
    ('Débats Juridiques', 'Discussions sur des sujets juridiques controversés', 'actif');";

    executeQuery($pdo, $sql_insert_categories_podcasts, "Insertion des catégories de podcasts de test");
} else {
    echo "<p style='color:green'>✓ La table 'categories_podcasts' existe déjà.</p>";
}

// Afficher un lien pour retourner à la page d'accueil
echo "
        <p><a href='index.php'>Retour à l'accueil</a></p>
        <p><a href='profil.php'>Aller à mon profil</a></p>
    </div>
</body>
</html>";
?>
