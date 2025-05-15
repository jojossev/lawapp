<?php
// Script spécifique pour créer les tables de catégories avec la syntaxe PostgreSQL correcte
// Ce script est une version corrigée de create_categories_tables.php pour PostgreSQL

// Inclure les fichiers de configuration
require_once __DIR__ . '/includes/config.php';

// Afficher un message de début
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Correction des tables de catégories pour PostgreSQL - LawApp</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Correction des tables de catégories pour PostgreSQL</h1>
        <p>Ce script crée les tables de catégories avec la syntaxe PostgreSQL correcte.</p>";

// Vérifier la connexion à la base de données
echo "<h2>1. Vérification de la connexion à la base de données</h2>";
try {
    if (!isset($pdo)) {
        throw new Exception("La connexion à la base de données n'est pas établie.");
    }
    
    // Tester la connexion avec une requête simple
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo "<p class='success'>✅ Connexion à la base de données établie avec succès.</p>";
    } else {
        throw new Exception("Impossible d'exécuter une requête simple sur la base de données.");
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
    die("<p>Impossible de continuer sans connexion à la base de données.</p></div></body></html>");
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        $sql = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' AND table_name = '$table'
        )";
        $stmt = $pdo->query($sql);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de l'existence de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour exécuter une requête SQL avec gestion d'erreur
function executeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "<p class='success'>✅ $description réussie.</p>";
        return true;
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erreur lors de $description: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Créer la table categories_livres
echo "<h2>2. Création de la table categories_livres</h2>";
if (!tableExists($pdo, 'categories_livres')) {
    echo "<p>La table 'categories_livres' n'existe pas. Création de la table...</p>";
    
    $sql = "CREATE TABLE categories_livres (
        id SERIAL PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (executeQuery($pdo, $sql, "création de la table categories_livres")) {
        // Insérer des données de test
        $sql = "INSERT INTO categories_livres (nom, description) VALUES 
            ('Droit civil', 'Livres sur le droit civil'),
            ('Droit pénal', 'Livres sur le droit pénal'),
            ('Droit des affaires', 'Livres sur le droit des affaires')";
        
        executeQuery($pdo, $sql, "insertion de données de test dans la table categories_livres");
    }
} else {
    echo "<p class='success'>✅ La table 'categories_livres' existe déjà.</p>";
}

// Créer la table categories_podcasts
echo "<h2>3. Création de la table categories_podcasts</h2>";
if (!tableExists($pdo, 'categories_podcasts')) {
    echo "<p>La table 'categories_podcasts' n'existe pas. Création de la table...</p>";
    
    $sql = "CREATE TABLE categories_podcasts (
        id SERIAL PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (executeQuery($pdo, $sql, "création de la table categories_podcasts")) {
        // Insérer des données de test
        $sql = "INSERT INTO categories_podcasts (nom, description) VALUES 
            ('Interviews', 'Podcasts d''interviews avec des experts juridiques'),
            ('Analyses juridiques', 'Analyses de sujets juridiques actuels'),
            ('Conseils pratiques', 'Conseils pratiques sur des questions juridiques')";
        
        executeQuery($pdo, $sql, "insertion de données de test dans la table categories_podcasts");
    }
} else {
    echo "<p class='success'>✅ La table 'categories_podcasts' existe déjà.</p>";
}

// Afficher les tables existantes
echo "<h2>4. Tables existantes dans la base de données</h2>";
try {
    $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name";
    $stmt = $pdo->query($sql);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<p>Tables trouvées:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='warning'>⚠️ Aucune table trouvée dans la base de données.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erreur lors de l'affichage des tables: " . $e->getMessage() . "</p>";
}

// Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='fix_all_tables.php'>Corriger toutes les tables</a></li>";
echo "<li><a href='test_db_connection.php'>Test de connexion à la base de données</a></li>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "</ul>";

echo "<p>Opération terminée.</p>";
echo "</div></body></html>";
?>
