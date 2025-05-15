<?php
// Script pour résoudre l'erreur "Bad Gateway" sur Render
// Ce script effectue une série de vérifications et de corrections pour résoudre les problèmes courants

// Afficher un message de début
echo "<h1>Diagnostic et correction des problèmes Render</h1>";

// Inclure les fichiers de configuration
require_once __DIR__ . '/includes/config.php';

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_name = '$table'
            )";
            $stmt = $pdo->query($sql);
            return $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SHOW TABLES LIKE '$table'";
            $stmt = $pdo->query($sql);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de l'existence de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT FROM information_schema.columns 
                WHERE table_name = '$table' AND column_name = '$column'
            )";
            $stmt = $pdo->query($sql);
            return $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
            $stmt = $pdo->query($sql);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de l'existence de la colonne: " . $e->getMessage() . "</p>";
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
    echo "<p>Vérifiez la variable d'environnement DATABASE_URL et assurez-vous que la base de données est accessible.</p>";
    
    // Afficher les informations de connexion (masquées)
    echo "<p>URL de connexion: ";
    if (defined('DB_URL')) {
        $masked_url = preg_replace('/(:\/\/)([^:]+):([^@]+)@/', '$1****:****@', DB_URL);
        echo htmlspecialchars($masked_url);
    } else {
        echo "Non définie";
    }
    echo "</p>";
    
    // Vérifier si la variable d'environnement est définie
    echo "<p>Variable d'environnement DATABASE_URL: " . (getenv('DATABASE_URL') ? "Définie" : "Non définie") . "</p>";
    
    die("<p>Impossible de continuer sans connexion à la base de données.</p>");
}

// Vérifier et créer les tables nécessaires
echo "<h2>2. Vérification des tables nécessaires</h2>";

// Tables à vérifier
$required_tables = [
    'categories_livres' => "
        CREATE TABLE categories_livres (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
    'categories_podcasts' => "
        CREATE TABLE categories_podcasts (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
    'livres' => "
        CREATE TABLE livres (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            auteur VARCHAR(255) NOT NULL,
            description TEXT,
            annee_publication INT,
            editeur VARCHAR(255),
            isbn VARCHAR(20),
            id_categorie INT,
            image_couverture VARCHAR(255),
            fichier_pdf VARCHAR(255),
            date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif'
        )",
    'podcasts' => "
        CREATE TABLE podcasts (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            duree VARCHAR(10),
            date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fichier_audio VARCHAR(255),
            image VARCHAR(255),
            id_categorie INT,
            statut VARCHAR(20) DEFAULT 'actif'
        )",
    'administrateurs' => "
        CREATE TABLE administrateurs (
            id SERIAL PRIMARY KEY,
            nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif'
        )"
];

// Vérifier et créer chaque table
foreach ($required_tables as $table => $create_sql) {
    if (!tableExists($pdo, $table)) {
        echo "<p>La table '$table' n'existe pas. Création en cours...</p>";
        executeQuery($pdo, $create_sql, "création de la table $table");
        
        // Ajouter des données de test pour certaines tables
        if ($table == 'categories_livres') {
            $insert_sql = "INSERT INTO categories_livres (nom, description) VALUES 
                ('Droit civil', 'Livres sur le droit civil'),
                ('Droit pénal', 'Livres sur le droit pénal'),
                ('Droit des affaires', 'Livres sur le droit des affaires')";
            executeQuery($pdo, $insert_sql, "insertion des catégories de livres de test");
        } else if ($table == 'categories_podcasts') {
            $insert_sql = "INSERT INTO categories_podcasts (nom, description) VALUES 
                ('Interviews', 'Podcasts d\'interviews avec des experts juridiques'),
                ('Analyses juridiques', 'Analyses de sujets juridiques actuels'),
                ('Conseils pratiques', 'Conseils pratiques sur des questions juridiques')";
            executeQuery($pdo, $insert_sql, "insertion des catégories de podcasts de test");
        } else if ($table == 'administrateurs') {
            $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO administrateurs (nom_utilisateur, mot_de_passe, email, nom, prenom) VALUES 
                ('admin', '$hashed_password', 'admin@lawapp.com', 'Admin', 'LawApp')";
            executeQuery($pdo, $insert_sql, "création du compte administrateur par défaut");
        }
    } else {
        echo "<p class='success'>✅ La table '$table' existe déjà.</p>";
    }
}

// Vérifier les colonnes importantes
echo "<h2>3. Vérification des colonnes importantes</h2>";

// Vérifier la colonne id_categorie dans la table livres
if (tableExists($pdo, 'livres')) {
    if (!columnExists($pdo, 'livres', 'id_categorie')) {
        echo "<p>La colonne 'id_categorie' n'existe pas dans la table 'livres'. Ajout en cours...</p>";
        $sql = "ALTER TABLE livres ADD COLUMN id_categorie INT";
        executeQuery($pdo, $sql, "ajout de la colonne id_categorie à la table livres");
    } else {
        echo "<p class='success'>✅ La colonne 'id_categorie' existe dans la table 'livres'.</p>";
    }
}

// Vérifier la colonne id_categorie dans la table podcasts
if (tableExists($pdo, 'podcasts')) {
    if (!columnExists($pdo, 'podcasts', 'id_categorie')) {
        echo "<p>La colonne 'id_categorie' n'existe pas dans la table 'podcasts'. Ajout en cours...</p>";
        $sql = "ALTER TABLE podcasts ADD COLUMN id_categorie INT";
        executeQuery($pdo, $sql, "ajout de la colonne id_categorie à la table podcasts");
    } else {
        echo "<p class='success'>✅ La colonne 'id_categorie' existe dans la table 'podcasts'.</p>";
    }
}

// Vérifier les variables d'environnement
echo "<h2>4. Vérification des variables d'environnement</h2>";
$required_env_vars = ['DATABASE_URL', 'APP_URL', 'ENVIRONMENT'];
foreach ($required_env_vars as $var) {
    if (getenv($var)) {
        echo "<p class='success'>✅ La variable '$var' est définie.</p>";
    } else {
        echo "<p class='warning'>⚠️ La variable '$var' n'est pas définie.</p>";
    }
}

// Vérifier les extensions PHP
echo "<h2>5. Vérification des extensions PHP</h2>";
$required_extensions = ['pdo', 'pdo_pgsql', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ L'extension '$ext' est chargée.</p>";
    } else {
        echo "<p class='error'>❌ L'extension '$ext' n'est pas chargée.</p>";
    }
}

// Résumé et recommandations
echo "<h2>Résumé et recommandations</h2>";
echo "<p>Le diagnostic est terminé. Voici les recommandations :</p>";
echo "<ol>";
echo "<li>Redémarrez votre application sur Render en allant dans le tableau de bord et en cliquant sur 'Manual Deploy' > 'Clear Build Cache & Deploy'.</li>";
echo "<li>Si l'erreur persiste, vérifiez les logs de l'application dans le tableau de bord Render.</li>";
echo "<li>Assurez-vous que la base de données PostgreSQL est active et accessible.</li>";
echo "<li>Vérifiez que les variables d'environnement sont correctement configurées.</li>";
echo "</ol>";

// Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='test_render.php'>Test Render</a></li>";
echo "<li><a href='debug_render.php'>Debug Render</a></li>";
echo "<li><a href='create_categories_tables.php'>Créer tables catégories</a></li>";
echo "<li><a href='add_column_id_categorie.php'>Ajouter colonne id_categorie</a></li>";
echo "</ul>";
?>

<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
    h1, h2 { color: #333; }
    .container { max-width: 800px; margin: 0 auto; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
</style>
