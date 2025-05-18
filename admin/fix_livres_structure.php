<?php
require_once __DIR__ . '/../includes/config.php';

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        // PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $stmt = $pdo->prepare("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = :table AND column_name = :column
            ");
            $stmt->execute([':table' => $table, ':column' => $column]);
        } else {
            // MySQL
            $stmt = $pdo->prepare("
                SELECT COLUMN_NAME 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = :dbname AND TABLE_NAME = :table AND COLUMN_NAME = :column
            ");
            $stmt->execute([':dbname' => DB_NAME, ':table' => $table, ':column' => $column]);
        }
        return $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
        echo "<p>Erreur lors de la vérification de la colonne: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $stmt = $pdo->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_name = :table
                )
            ");
            $stmt->execute([':table' => $table]);
        } else {
            // MySQL
            $sql = "
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = current_database() AND table_name = :table
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':table' => $table]);
        }
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<p>Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Style CSS pour une meilleure présentation
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction de la structure des tables</title>
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
    <h1>Correction de la structure des tables</h1>';

// 1. Vérifier et corriger la table livres
echo '<h2>Table "livres"</h2>';

if (!tableExists($pdo, 'livres')) {
    echo '<p class="error">La table "livres" n\'existe pas. Création de la table...</p>';
    
    try {
        // Créer la table livres
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $pdo->exec("
                CREATE TABLE livres (
                    id SERIAL PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    auteur VARCHAR(255) NOT NULL,
                    description TEXT,
                    id_categorie INTEGER,
                    url_telechargement VARCHAR(255),
                    image_url VARCHAR(255),
                    statut VARCHAR(50) DEFAULT 'brouillon',
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            // MySQL
            $pdo->exec("
                CREATE TABLE livres (
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
        }
        echo '<p class="success">Table "livres" créée avec succès.</p>';
    } catch (PDOException $e) {
        echo '<p class="error">Erreur lors de la création de la table "livres": ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p class="success">La table "livres" existe.</p>';
    
    // Vérifier et ajouter la colonne image_url si elle n'existe pas
    if (!columnExists($pdo, 'livres', 'image_url')) {
        echo '<p class="warning">La colonne "image_url" n\'existe pas dans la table "livres". Ajout de la colonne...</p>';
        
        try {
            if (strpos(DB_URL, 'pgsql') !== false) {
                // PostgreSQL
                $pdo->exec("ALTER TABLE livres ADD COLUMN image_url VARCHAR(255)");
            } else {
                // MySQL
                $pdo->exec("ALTER TABLE livres ADD COLUMN image_url VARCHAR(255)");
            }
            echo '<p class="success">Colonne "image_url" ajoutée avec succès à la table "livres".</p>';
        } catch (PDOException $e) {
            echo '<p class="error">Erreur lors de l\'ajout de la colonne "image_url": ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p class="success">La colonne "image_url" existe dans la table "livres".</p>';
    }
    
    // Vérifier et ajouter la colonne id_categorie si elle n'existe pas
    if (!columnExists($pdo, 'livres', 'id_categorie')) {
        echo '<p class="warning">La colonne "id_categorie" n\'existe pas dans la table "livres". Ajout de la colonne...</p>';
        
        try {
            if (strpos(DB_URL, 'pgsql') !== false) {
                // PostgreSQL
                $pdo->exec("ALTER TABLE livres ADD COLUMN id_categorie INTEGER");
            } else {
                // MySQL
                $pdo->exec("ALTER TABLE livres ADD COLUMN id_categorie INT");
            }
            echo '<p class="success">Colonne "id_categorie" ajoutée avec succès à la table "livres".</p>';
        } catch (PDOException $e) {
            echo '<p class="error">Erreur lors de l\'ajout de la colonne "id_categorie": ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p class="success">La colonne "id_categorie" existe dans la table "livres".</p>';
    }
}

// 2. Vérifier et corriger la table videos
echo '<h2>Table "videos"</h2>';

if (!tableExists($pdo, 'videos')) {
    echo '<p class="error">La table "videos" n\'existe pas. Création de la table...</p>';
    
    try {
        // Créer la table videos
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $pdo->exec("
                CREATE TABLE videos (
                    id SERIAL PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    description TEXT,
                    url_video VARCHAR(255) NOT NULL,
                    type_video VARCHAR(50) DEFAULT 'youtube',
                    duree VARCHAR(10),
                    miniature_url VARCHAR(255),
                    niveau VARCHAR(50) DEFAULT 'debutant',
                    prix DECIMAL(10, 2) DEFAULT NULL,
                    statut VARCHAR(50) DEFAULT 'brouillon',
                    id_categorie INTEGER,
                    id_createur INTEGER,
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            // MySQL
            $pdo->exec("
                CREATE TABLE videos (
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
        }
        echo '<p class="success">Table "videos" créée avec succès.</p>';
    } catch (PDOException $e) {
        echo '<p class="error">Erreur lors de la création de la table "videos": ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p class="success">La table "videos" existe.</p>';
    
    // Vérifier et ajouter la colonne id_createur si elle n'existe pas
    if (!columnExists($pdo, 'videos', 'id_createur')) {
        echo '<p class="warning">La colonne "id_createur" n\'existe pas dans la table "videos". Ajout de la colonne...</p>';
        
        try {
            if (strpos(DB_URL, 'pgsql') !== false) {
                // PostgreSQL
                $pdo->exec("ALTER TABLE videos ADD COLUMN id_createur INTEGER");
            } else {
                // MySQL
                $pdo->exec("ALTER TABLE videos ADD COLUMN id_createur INT");
            }
            echo '<p class="success">Colonne "id_createur" ajoutée avec succès à la table "videos".</p>';
        } catch (PDOException $e) {
            echo '<p class="error">Erreur lors de l\'ajout de la colonne "id_createur": ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p class="success">La colonne "id_createur" existe dans la table "videos".</p>';
    }
}

// 3. Vérifier et créer la table categories_videos si elle n'existe pas
echo '<h2>Table "categories_videos"</h2>';

if (!tableExists($pdo, 'categories_videos')) {
    echo '<p class="error">La table "categories_videos" n\'existe pas. Création de la table...</p>';
    
    try {
        // Créer la table categories_videos
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $pdo->exec("
                CREATE TABLE categories_videos (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    statut VARCHAR(50) DEFAULT 'actif',
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Insérer quelques catégories par défaut
            $pdo->exec("
                INSERT INTO categories_videos (nom, description, statut) VALUES
                ('Droit civil', 'Vidéos sur le droit civil', 'actif'),
                ('Droit pénal', 'Vidéos sur le droit pénal', 'actif'),
                ('Droit des affaires', 'Vidéos sur le droit des affaires', 'actif'),
                ('Droit constitutionnel', 'Vidéos sur le droit constitutionnel', 'actif')
            ");
        } else {
            // MySQL
            $pdo->exec("
                CREATE TABLE categories_videos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    statut VARCHAR(50) DEFAULT 'actif',
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Insérer quelques catégories par défaut
            $pdo->exec("
                INSERT INTO categories_videos (nom, description, statut) VALUES
                ('Droit civil', 'Vidéos sur le droit civil', 'actif'),
                ('Droit pénal', 'Vidéos sur le droit pénal', 'actif'),
                ('Droit des affaires', 'Vidéos sur le droit des affaires', 'actif'),
                ('Droit constitutionnel', 'Vidéos sur le droit constitutionnel', 'actif')
            ");
        }
        echo '<p class="success">Table "categories_videos" créée avec succès et catégories par défaut ajoutées.</p>';
    } catch (PDOException $e) {
        echo '<p class="error">Erreur lors de la création de la table "categories_videos": ' . $e->getMessage() . '</p>';
    }
} else {
    echo '<p class="success">La table "categories_videos" existe.</p>';
}

echo '<p><a href="../index.php">Retour à l\'accueil</a></p>';
echo '</body></html>';
?>
