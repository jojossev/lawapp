<?php
require_once __DIR__ . '/../includes/config.php';

// Style CSS pour une meilleure présentation
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration de la base de données</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Configuration de la base de données LawApp</h1>';

try {
    // Déterminer le type de base de données
    $is_postgres = strpos(DB_URL, 'pgsql') !== false;

    // Créer les tables si elles n'existent pas
    function tableExists($pdo, $table, $is_postgres) {
        try {
            if ($is_postgres) {
                $stmt = $pdo->prepare("
                    SELECT EXISTS (
                        SELECT FROM information_schema.tables 
                        WHERE table_name = :table
                    )
                ");
                $stmt->execute([':table' => $table]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM information_schema.tables 
                    WHERE table_schema = :dbname AND table_name = :table
                ");
                $stmt->execute([':dbname' => DB_NAME, ':table' => $table]);
            }
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Fonction pour créer une séquence PostgreSQL
    function createSequence($pdo, $table, $column, $is_postgres) {
        if ($is_postgres) {
            $pdo->exec("CREATE SEQUENCE IF NOT EXISTS {$table}_{$column}_seq");
        }
    }

    // Fonction pour obtenir la définition du type d'identifiant
    function getIdColumnDefinition($is_postgres) {
        return $is_postgres ? 
            "id SERIAL PRIMARY KEY" : 
            "id INT AUTO_INCREMENT PRIMARY KEY";
    }

    function createTable($pdo, $table, $columns, $is_postgres, $test_data = []) {
        if (!tableExists($pdo, $table, $is_postgres)) {
            $id_column = getIdColumnDefinition($is_postgres);
            $columns_str = str_replace('__ID_COLUMN__', $id_column, $columns);
            
            try {
                $pdo->exec($columns_str);
                echo "<p class='success'>Table '$table' créée avec succès.</p>";

                // Insérer des données de test
                if (!empty($test_data)) {
                    foreach ($test_data as $data) {
                        $keys = implode(', ', array_keys($data));
                        $placeholders = implode(', ', array_fill(0, count($data), '?'));
                        $stmt = $pdo->prepare("INSERT INTO $table ($keys) VALUES ($placeholders)");
                        $stmt->execute(array_values($data));
                    }
                    echo "<p class='success'>Données de test pour '$table' insérées.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='error'>Erreur lors de la création de la table '$table': " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='info'>La table '$table' existe déjà.</p>";
        }
    }

    echo "<h2>Création des tables</h2>";
    
    // Table utilisateurs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'etudiant',
            statut VARCHAR(50) DEFAULT 'actif',
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            preferences_theme VARCHAR(50) DEFAULT 'light'
        )
    ");
    echo "<p class='success'>Table 'utilisateurs' vérifiée/créée.</p>";
    
    // Table administrateurs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS administrateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'admin',
            statut VARCHAR(50) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL
        )
    ");
    echo "<p class='success'>Table 'administrateurs' vérifiée/créée.</p>";
    
    // Table categories_cours
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories_cours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(50) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p class='success'>Table 'categories_cours' vérifiée/créée.</p>";
    
    // Table cours
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            prix DECIMAL(10, 2) DEFAULT NULL,
            image_url VARCHAR(255),
            niveau VARCHAR(50) DEFAULT 'debutant',
            duree VARCHAR(50),
            id_categorie INT,
            id_createur INT,
            statut VARCHAR(50) DEFAULT 'brouillon',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<p class='success'>Table 'cours' vérifiée/créée.</p>";
    
    // Table categories_livres
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories_livres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(50) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p class='success'>Table 'categories_livres' vérifiée/créée.</p>";
    
    // Table livres
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS livres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            auteur VARCHAR(255) NOT NULL,
            description TEXT,
            id_categorie INT,
            url_telechargement VARCHAR(255),
            image_url VARCHAR(255),
            statut VARCHAR(50) DEFAULT 'brouillon',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<p class='success'>Table 'livres' vérifiée/créée.</p>";
    
    // Table categories_podcasts
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories_podcasts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(50) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p class='success'>Table 'categories_podcasts' vérifiée/créée.</p>";
    
    // Table podcasts
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS podcasts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            url_audio VARCHAR(255) NOT NULL,
            duree VARCHAR(10),
            image_url VARCHAR(255),
            id_categorie INT,
            id_createur INT,
            statut VARCHAR(50) DEFAULT 'brouillon',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<p class='success'>Table 'podcasts' vérifiée/créée.</p>";
    
    // Table categories_videos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories_videos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(50) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p class='success'>Table 'categories_videos' vérifiée/créée.</p>";
    
    // Table videos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS videos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            url_video VARCHAR(255) NOT NULL,
            type_video VARCHAR(50) DEFAULT 'youtube',
            duree VARCHAR(10),
            miniature_url VARCHAR(255),
            niveau VARCHAR(50) DEFAULT 'debutant',
            prix DECIMAL(10, 2) DEFAULT NULL,
            statut VARCHAR(50) DEFAULT 'brouillon',
            id_categorie INT,
            id_createur INT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<p class='success'>Table 'videos' vérifiée/créée.</p>";
    
    // Table inscriptions
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            progres INT DEFAULT 0,
            statut VARCHAR(50) DEFAULT 'actif'
        )
    ");
    echo "<p class='success'>Table 'inscriptions' vérifiée/créée.</p>";
    
    // Vérifier si un administrateur existe déjà
    $stmt = $pdo->query("SELECT COUNT(*) FROM administrateurs");
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount == 0) {
        // Créer un administrateur par défaut
        $admin_password_hash = password_hash('admin', PASSWORD_DEFAULT);
        $pdo->exec("
            INSERT INTO administrateurs (nom, prenom, email, mot_de_passe, role, statut)
            VALUES ('Admin', 'LawApp', 'admin@lawapp.com', '$admin_password_hash', 'admin', 'actif')
        ");
        echo "<p class='success'>Administrateur par défaut créé avec les identifiants suivants :</p>";
        echo "<p>Email : admin@lawapp.com<br>Mot de passe : admin</p>";
    } else {
        echo "<p class='info'>Des administrateurs existent déjà dans la base de données.</p>";
    }
    
    // Insérer des données de test si les tables sont vides
    echo "<h2>Insertion de données de test</h2>";
    
    // Catégories de cours
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories_cours");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO categories_cours (nom, description, statut) VALUES
            ('Droit civil', 'Cours sur le droit civil', 'actif'),
            ('Droit pénal', 'Cours sur le droit pénal', 'actif'),
            ('Droit des affaires', 'Cours sur le droit des affaires', 'actif'),
            ('Droit constitutionnel', 'Cours sur le droit constitutionnel', 'actif')
        ");
        echo "<p class='success'>Catégories de cours de test ajoutées.</p>";
    } else {
        echo "<p class='info'>Des catégories de cours existent déjà.</p>";
    }
    
    // Catégories de livres
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories_livres");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO categories_livres (nom, description, statut) VALUES
            ('Codes juridiques', 'Recueils de lois et codes', 'actif'),
            ('Manuels', 'Manuels d\'études juridiques', 'actif'),
            ('Essais', 'Essais et analyses juridiques', 'actif'),
            ('Revues', 'Revues juridiques périodiques', 'actif')
        ");
        echo "<p class='success'>Catégories de livres de test ajoutées.</p>";
    } else {
        echo "<p class='info'>Des catégories de livres existent déjà.</p>";
    }
    
    // Catégories de podcasts
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories_podcasts");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO categories_podcasts (nom, description, statut) VALUES
            ('Actualités juridiques', 'Podcasts sur les actualités juridiques', 'actif'),
            ('Études de cas', 'Analyses de cas juridiques', 'actif'),
            ('Interviews', 'Entretiens avec des professionnels du droit', 'actif'),
            ('Débats', 'Débats sur des questions juridiques', 'actif')
        ");
        echo "<p class='success'>Catégories de podcasts de test ajoutées.</p>";
    } else {
        echo "<p class='info'>Des catégories de podcasts existent déjà.</p>";
    }
    
    // Catégories de vidéos
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories_videos");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO categories_videos (nom, description, statut) VALUES
            ('Tutoriels', 'Vidéos tutorielles sur le droit', 'actif'),
            ('Conférences', 'Enregistrements de conférences juridiques', 'actif'),
            ('Cours magistraux', 'Vidéos de cours magistraux', 'actif'),
            ('Documentaires', 'Documentaires juridiques', 'actif')
        ");
        echo "<p class='success'>Catégories de vidéos de test ajoutées.</p>";
    } else {
        echo "<p class='info'>Des catégories de vidéos existent déjà.</p>";
    }
    
    // Utilisateurs de test
    $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
    if ($stmt->fetchColumn() == 0) {
        $user_password_hash = password_hash('password123', PASSWORD_DEFAULT);
        $pdo->exec("
            INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, statut) VALUES
            ('Dupont', 'Jean', 'jean.dupont@example.com', '$user_password_hash', 'etudiant', 'actif'),
            ('Martin', 'Sophie', 'sophie.martin@example.com', '$user_password_hash', 'etudiant', 'actif'),
            ('Leroy', 'Thomas', 'thomas.leroy@example.com', '$user_password_hash', 'professeur', 'actif')
        ");
        echo "<p class='success'>Utilisateurs de test ajoutés.</p>";
        echo "<p>Email : jean.dupont@example.com<br>Mot de passe : password123</p>";
    } else {
        echo "<p class='info'>Des utilisateurs existent déjà.</p>";
    }
    
    echo "<h2>Configuration terminée</h2>";
    echo "<p class='success'>La base de données a été configurée avec succès.</p>";
    echo "<p><a href='../index.php'>Retour à l'accueil</a></p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>Erreur lors de la configuration de la base de données : " . $e->getMessage() . "</p>";
}

echo '</body></html>';
?>
