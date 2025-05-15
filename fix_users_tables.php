<?php
// Script pour corriger la structure des tables liées aux utilisateurs
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

// Fonction pour exécuter une requête SQL et gérer les erreurs
function executeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "<p style='color:green'>✓ $description : Succès</p>";
        return true;
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ $description: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.tables 
                WHERE table_name = :table
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return (bool) $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT table_name FROM information_schema.tables 
                    WHERE table_schema = DATABASE() AND table_name = :table";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.columns 
                WHERE table_name = :table AND column_name = :column
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return (bool) $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT column_name FROM information_schema.columns 
                    WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la colonne: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Correction des tables liées aux utilisateurs</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Correction des tables liées aux utilisateurs</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

echo "<p class='success'>Connexion à la base de données établie avec succès.</p>";

// 1. Vérifier si la table utilisateurs existe
if (!tableExists($pdo, 'utilisateurs')) {
    echo "<p class='warning'>La table 'utilisateurs' n'existe pas. Création de la table...</p>";
    
    // Créer la table utilisateurs
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_users = "
        CREATE TABLE utilisateurs (
            id SERIAL PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            statut VARCHAR(20) DEFAULT 'actif',
            role VARCHAR(20) DEFAULT 'etudiant',
            bio TEXT NULL,
            avatar VARCHAR(255) NULL
        )";
    } else {
        // MySQL
        $sql_create_users = "
        CREATE TABLE utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            statut VARCHAR(20) DEFAULT 'actif',
            role VARCHAR(20) DEFAULT 'etudiant',
            bio TEXT NULL,
            avatar VARCHAR(255) NULL
        )";
    }
    
    executeQuery($pdo, $sql_create_users, "Création de la table utilisateurs");
    
    // Insérer des utilisateurs de test
    $sql_insert_users = "
    INSERT INTO utilisateurs (email, mot_de_passe, nom, prenom, role) VALUES 
        ('user@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'Utilisateur', 'Test', 'etudiant'),
        ('prof@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'Professeur', 'Test', 'enseignant')";
    
    executeQuery($pdo, $sql_insert_users, "Insertion des utilisateurs de test");
} else {
    echo "<p class='success'>La table 'utilisateurs' existe déjà.</p>";
}

// 2. Vérifier si la table user_progression existe
if (!tableExists($pdo, 'user_progression')) {
    echo "<p class='warning'>La table 'user_progression' n'existe pas. Création de la table...</p>";
    
    // Créer la table user_progression
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_progression = "
        CREATE TABLE user_progression (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            id_module INT,
            id_lecon INT,
            statut VARCHAR(20) DEFAULT 'en_cours',
            progression INT DEFAULT 0,
            date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_derniere_activite TIMESTAMP NULL,
            date_completion TIMESTAMP NULL
        )";
    } else {
        // MySQL
        $sql_create_progression = "
        CREATE TABLE user_progression (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            id_module INT,
            id_lecon INT,
            statut VARCHAR(20) DEFAULT 'en_cours',
            progression INT DEFAULT 0,
            date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_derniere_activite TIMESTAMP NULL,
            date_completion TIMESTAMP NULL
        )";
    }
    
    executeQuery($pdo, $sql_create_progression, "Création de la table user_progression");
} else {
    echo "<p class='success'>La table 'user_progression' existe déjà.</p>";
}

// 3. Vérifier si la table user_badges existe
if (!tableExists($pdo, 'user_badges')) {
    echo "<p class='warning'>La table 'user_badges' n'existe pas. Création de la table...</p>";
    
    // Créer la table user_badges
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_badges = "
        CREATE TABLE user_badges (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_badge INT NOT NULL,
            date_obtention TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_badges = "
        CREATE TABLE user_badges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_badge INT NOT NULL,
            date_obtention TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_badges, "Création de la table user_badges");
} else {
    echo "<p class='success'>La table 'user_badges' existe déjà.</p>";
}

// 4. Vérifier si la table badges existe
if (!tableExists($pdo, 'badges')) {
    echo "<p class='warning'>La table 'badges' n'existe pas. Création de la table...</p>";
    
    // Créer la table badges
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_badges_table = "
        CREATE TABLE badges (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            image VARCHAR(255),
            condition_obtention TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_badges_table = "
        CREATE TABLE badges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            image VARCHAR(255),
            condition_obtention TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_badges_table, "Création de la table badges");
    
    // Insérer des badges de test
    $sql_insert_badges = "
    INSERT INTO badges (nom, description, condition_obtention) VALUES 
        ('Débutant', 'Badge pour les nouveaux utilisateurs', 'S''inscrire sur la plateforme'),
        ('Lecteur assidu', 'Badge pour les lecteurs réguliers', 'Lire au moins 5 livres'),
        ('Expert', 'Badge pour les utilisateurs expérimentés', 'Compléter au moins 10 cours')";
    
    executeQuery($pdo, $sql_insert_badges, "Insertion des badges de test");
} else {
    echo "<p class='success'>La table 'badges' existe déjà.</p>";
}

// 5. Vérifier si la table user_favoris existe
if (!tableExists($pdo, 'user_favoris')) {
    echo "<p class='warning'>La table 'user_favoris' n'existe pas. Création de la table...</p>";
    
    // Créer la table user_favoris
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_favoris = "
        CREATE TABLE user_favoris (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            type_item VARCHAR(50) NOT NULL,
            id_item INT NOT NULL,
            date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_favoris = "
        CREATE TABLE user_favoris (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            type_item VARCHAR(50) NOT NULL,
            id_item INT NOT NULL,
            date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_favoris, "Création de la table user_favoris");
} else {
    echo "<p class='success'>La table 'user_favoris' existe déjà.</p>";
}

// 6. Vérifier si la table inscriptions existe
if (!tableExists($pdo, 'inscriptions')) {
    echo "<p class='warning'>La table 'inscriptions' n'existe pas. Création de la table...</p>";
    
    // Créer la table inscriptions
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_inscriptions = "
        CREATE TABLE inscriptions (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif',
            progression INT DEFAULT 0,
            date_derniere_activite TIMESTAMP NULL
        )";
    } else {
        // MySQL
        $sql_create_inscriptions = "
        CREATE TABLE inscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif',
            progression INT DEFAULT 0,
            date_derniere_activite TIMESTAMP NULL
        )";
    }
    
    executeQuery($pdo, $sql_create_inscriptions, "Création de la table inscriptions");
} else {
    echo "<p class='success'>La table 'inscriptions' existe déjà.</p>";
}

// Afficher la structure actuelle des tables
echo "<h2>Structure actuelle des tables</h2>";

$tables = ['utilisateurs', 'user_progression', 'user_badges', 'badges', 'user_favoris', 'inscriptions'];

foreach ($tables as $table) {
    if (tableExists($pdo, $table)) {
        echo "<h3>Table '$table'</h3>";
        echo "<pre>";
        try {
            if (strpos(DB_URL, 'pgsql') !== false) {
                // PostgreSQL
                $sql = "SELECT column_name, data_type, character_maximum_length 
                        FROM information_schema.columns 
                        WHERE table_name = '$table'";
            } else {
                // MySQL
                $sql = "DESCRIBE $table";
            }
            
            $stmt = $pdo->query($sql);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            print_r($columns);
        } catch (PDOException $e) {
            echo "Erreur lors de l'affichage de la structure: " . $e->getMessage();
        }
        echo "</pre>";
        
        // Afficher quelques données
        echo "<h4>Données dans la table '$table'</h4>";
        echo "<pre>";
        try {
            $sql = "SELECT * FROM $table LIMIT 5";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            print_r($data);
        } catch (PDOException $e) {
            echo "Erreur lors de l'affichage des données: " . $e->getMessage();
        }
        echo "</pre>";
    }
}

// Liens de retour
echo "<h2>Liens utiles :</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='fix_all_tables.php'>Corriger toutes les tables</a></li>";
echo "<li><a href='fix_admin_table.php'>Corriger la table administrateurs</a></li>";
echo "<li><a href='fix_foreign_keys.php'>Corriger les clés étrangères</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
