<?php
// Script pour corriger la structure de la table cours et ses dépendances
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
    <title>Correction de la table cours et ses dépendances</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2 { color: #333; }
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
        <h1>Correction de la table cours et ses dépendances</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

echo "<p class='success'>Connexion à la base de données établie avec succès.</p>";

// 1. Vérifier si la table categories_cours existe
if (!tableExists($pdo, 'categories_cours')) {
    echo "<p class='warning'>La table 'categories_cours' n'existe pas. Création de la table...</p>";
    
    // Créer la table categories_cours
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_categories = "
        CREATE TABLE categories_cours (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(20) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_categories = "
        CREATE TABLE categories_cours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(20) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_categories, "Création de la table categories_cours");
    
    // Insérer des catégories de test
    $sql_insert_categories = "
    INSERT INTO categories_cours (nom, description) VALUES 
        ('Droit civil', 'Cours sur le droit civil'),
        ('Droit pénal', 'Cours sur le droit pénal'),
        ('Droit des affaires', 'Cours sur le droit des affaires'),
        ('Droit constitutionnel', 'Cours sur le droit constitutionnel'),
        ('Droit administratif', 'Cours sur le droit administratif')";
    
    executeQuery($pdo, $sql_insert_categories, "Insertion des catégories de cours de test");
} else {
    echo "<p class='success'>La table 'categories_cours' existe déjà.</p>";
}

// 2. Vérifier si la table cours existe
if (!tableExists($pdo, 'cours')) {
    echo "<p class='warning'>La table 'cours' n'existe pas. Création de la table...</p>";
    
    // Créer la table cours
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_cours = "
        CREATE TABLE cours (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            prix DECIMAL(10,2) NULL,
            image_url VARCHAR(255),
            niveau VARCHAR(50),
            duree VARCHAR(50),
            id_categorie INT,
            id_createur INT,
            note_moyenne DECIMAL(3,2) DEFAULT 0,
            statut VARCHAR(20) DEFAULT 'brouillon',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP NULL
        )";
    } else {
        // MySQL
        $sql_create_cours = "
        CREATE TABLE cours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            prix DECIMAL(10,2) NULL,
            image_url VARCHAR(255),
            niveau VARCHAR(50),
            duree VARCHAR(50),
            id_categorie INT,
            id_createur INT,
            note_moyenne DECIMAL(3,2) DEFAULT 0,
            statut VARCHAR(20) DEFAULT 'brouillon',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP NULL
        )";
    }
    
    executeQuery($pdo, $sql_create_cours, "Création de la table cours");
    
    // Insérer des cours de test
    $sql_insert_cours = "
    INSERT INTO cours (titre, description, prix, niveau, duree, id_categorie, statut) VALUES 
        ('Introduction au droit civil', 'Un cours complet sur les bases du droit civil.', 49.99, 'Débutant', '10 heures', 1, 'publié'),
        ('Droit pénal avancé', 'Approfondissez vos connaissances en droit pénal.', 79.99, 'Avancé', '15 heures', 2, 'publié'),
        ('Droit des affaires pour entrepreneurs', 'Tout ce que vous devez savoir pour votre entreprise.', 59.99, 'Intermédiaire', '12 heures', 3, 'publié')";
    
    executeQuery($pdo, $sql_insert_cours, "Insertion des cours de test");
} else {
    echo "<p class='success'>La table 'cours' existe déjà.</p>";
    
    // Vérifier les colonnes importantes
    $important_columns = ['id_categorie', 'id_createur', 'note_moyenne'];
    $column_definitions = [
        'id_categorie' => 'INT',
        'id_createur' => 'INT',
        'note_moyenne' => 'DECIMAL(3,2) DEFAULT 0'
    ];
    
    foreach ($important_columns as $column) {
        if (!columnExists($pdo, 'cours', $column)) {
            echo "<p class='warning'>La colonne '$column' n'existe pas dans la table 'cours'. Ajout de la colonne...</p>";
            
            $column_type = $column_definitions[$column];
            $sql_add_column = "ALTER TABLE cours ADD COLUMN $column $column_type";
            
            executeQuery($pdo, $sql_add_column, "Ajout de la colonne '$column'");
        } else {
            echo "<p class='success'>La colonne '$column' existe dans la table 'cours'.</p>";
        }
    }
}

// 3. Vérifier si la table modules existe
if (!tableExists($pdo, 'modules')) {
    echo "<p class='warning'>La table 'modules' n'existe pas. Création de la table...</p>";
    
    // Créer la table modules
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_modules = "
        CREATE TABLE modules (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            id_cours INT NOT NULL,
            ordre INT DEFAULT 1,
            duree VARCHAR(50),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_modules = "
        CREATE TABLE modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            id_cours INT NOT NULL,
            ordre INT DEFAULT 1,
            duree VARCHAR(50),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_modules, "Création de la table modules");
    
    // Insérer des modules de test
    $sql_insert_modules = "
    INSERT INTO modules (titre, description, id_cours, ordre, duree) VALUES 
        ('Les bases du droit civil', 'Introduction aux concepts fondamentaux', 1, 1, '2 heures'),
        ('Les contrats', 'Comprendre les différents types de contrats', 1, 2, '3 heures'),
        ('La responsabilité civile', 'Étude de la responsabilité civile', 1, 3, '2.5 heures')";
    
    executeQuery($pdo, $sql_insert_modules, "Insertion des modules de test");
} else {
    echo "<p class='success'>La table 'modules' existe déjà.</p>";
}

// 4. Vérifier si la table lecons existe
if (!tableExists($pdo, 'lecons')) {
    echo "<p class='warning'>La table 'lecons' n'existe pas. Création de la table...</p>";
    
    // Créer la table lecons
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_lecons = "
        CREATE TABLE lecons (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            contenu TEXT,
            id_module INT NOT NULL,
            ordre INT DEFAULT 1,
            duree VARCHAR(50),
            type VARCHAR(50) DEFAULT 'texte',
            video_url VARCHAR(255),
            fichier_pdf VARCHAR(255),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_lecons = "
        CREATE TABLE lecons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            contenu TEXT,
            id_module INT NOT NULL,
            ordre INT DEFAULT 1,
            duree VARCHAR(50),
            type VARCHAR(50) DEFAULT 'texte',
            video_url VARCHAR(255),
            fichier_pdf VARCHAR(255),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_lecons, "Création de la table lecons");
    
    // Insérer des leçons de test
    $sql_insert_lecons = "
    INSERT INTO lecons (titre, contenu, id_module, ordre, duree, type) VALUES 
        ('Qu''est-ce que le droit civil ?', 'Le droit civil est la branche du droit qui régit les rapports entre les personnes...', 1, 1, '30 minutes', 'texte'),
        ('Les sources du droit civil', 'Les sources du droit civil comprennent la loi, la jurisprudence...', 1, 2, '45 minutes', 'texte'),
        ('Les personnes physiques et morales', 'En droit civil, on distingue deux types de personnes...', 1, 3, '45 minutes', 'texte')";
    
    executeQuery($pdo, $sql_insert_lecons, "Insertion des leçons de test");
} else {
    echo "<p class='success'>La table 'lecons' existe déjà.</p>";
}

// Afficher la structure actuelle des tables
echo "<h2>Structure actuelle des tables</h2>";

$tables = ['categories_cours', 'cours', 'modules', 'lecons'];

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
echo "<li><a href='fix_livres_table.php'>Corriger la table livres</a></li>";
echo "<li><a href='fix_podcasts_table.php'>Corriger la table podcasts</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
