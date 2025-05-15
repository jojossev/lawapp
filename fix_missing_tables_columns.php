<?php
// Script pour corriger les tables et colonnes manquantes dans la base de données
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Activer la mise en tampon de sortie au début du script
ob_start();

// Fonction pour afficher les messages
function showMessage($message, $type = 'info') {
    $class = ($type == 'success') ? 'success' : (($type == 'error') ? 'error' : 'info');
    echo "<div style='margin: 10px; padding: 10px; border-radius: 5px; background-color: " . 
        ($type == 'success' ? '#d4edda' : ($type == 'error' ? '#f8d7da' : '#d1ecf1')) . 
        "; color: " . 
        ($type == 'success' ? '#155724' : ($type == 'error' ? '#721c24' : '#0c5460')) . 
        ";'><strong>" . 
        ($type == 'success' ? 'Succès' : ($type == 'error' ? 'Erreur' : 'Info')) . 
        ":</strong> $message</div>";
    
    // Vider le tampon de sortie seulement s'il est actif
    if (ob_get_level() > 0) {
        @ob_flush();
        @flush();
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        $sql = "SELECT EXISTS (
            SELECT 1 FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = :table
        )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['table' => $table]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        showMessage("Erreur lors de la vérification de la table: " . $e->getMessage(), 'error');
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        $sql = "SELECT EXISTS (
            SELECT 1 FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = :table 
            AND column_name = :column
        )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['table' => $table, 'column' => $column]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        showMessage("Erreur lors de la vérification de la colonne: " . $e->getMessage(), 'error');
        return false;
    }
}

// Connexion à la base de données
$database_url = getenv('DATABASE_URL');
if (empty($database_url)) {
    // En développement local, utiliser les constantes de configuration
    require_once 'includes/config.php';
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=lawapp", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbType = 'mysql';
        showMessage("Connexion à la base de données MySQL établie avec succès.", 'success');
    } catch (PDOException $e) {
        showMessage("Erreur de connexion à MySQL : " . $e->getMessage(), 'error');
        exit;
    }
} else {
    // En production, utiliser l'URL de connexion
    try {
        // Extraire les informations de connexion de l'URL
        $url = parse_url($database_url);
        $host = $url['host'] ?? 'localhost';
        $port = $url['port'] ?? '5432';
        $dbname = ltrim($url['path'] ?? '', '/');
        
        // Correction pour le problème de nom de base de données avec underscore à la fin
        if (substr($dbname, -1) === '_') {
            $dbname = substr($dbname, 0, -1);
        }
        
        $user = $url['user'] ?? '';
        $password = $url['pass'] ?? '';
        
        // Construire le DSN pour PostgreSQL
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        
        // Tenter de se connecter
        $pdo = new PDO($dsn, $user, $password);
        
        // Configurer PDO pour qu'il lance des exceptions en cas d'erreur
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbType = 'pgsql';
        
        showMessage("Connexion à la base de données PostgreSQL établie avec succès.", 'success');
    } catch (PDOException $e) {
        showMessage("Erreur de connexion à PostgreSQL : " . $e->getMessage(), 'error');
        exit;
    }
}

// Vérifier et corriger la table livres
showMessage("Vérification de la table 'livres'...", 'info');
if (tableExists($pdo, 'livres')) {
    showMessage("La table 'livres' existe.", 'success');
    
    // Vérifier si la colonne date_creation existe
    if (!columnExists($pdo, 'livres', 'date_creation')) {
        showMessage("La colonne 'date_creation' n'existe pas dans la table 'livres'. Ajout en cours...", 'info');
        try {
            if ($dbType == 'pgsql') {
                $sql = "ALTER TABLE livres ADD COLUMN date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            } else {
                $sql = "ALTER TABLE livres ADD COLUMN date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            }
            $pdo->exec($sql);
            showMessage("Colonne 'date_creation' ajoutée avec succès à la table 'livres'.", 'success');
        } catch (PDOException $e) {
            showMessage("Erreur lors de l'ajout de la colonne 'date_creation': " . $e->getMessage(), 'error');
        }
    } else {
        showMessage("La colonne 'date_creation' existe déjà dans la table 'livres'.", 'success');
    }
} else {
    showMessage("La table 'livres' n'existe pas. Création en cours...", 'info');
    try {
        if ($dbType == 'pgsql') {
            $sql = "CREATE TABLE livres (
                id SERIAL PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                auteur VARCHAR(255) NOT NULL,
                description TEXT,
                url_image VARCHAR(255),
                url_telechargement VARCHAR(255),
                id_categorie INT,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE livres (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                auteur VARCHAR(255) NOT NULL,
                description TEXT,
                url_image VARCHAR(255),
                url_telechargement VARCHAR(255),
                id_categorie INT,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $pdo->exec($sql);
        showMessage("Table 'livres' créée avec succès.", 'success');
    } catch (PDOException $e) {
        showMessage("Erreur lors de la création de la table 'livres': " . $e->getMessage(), 'error');
    }
}

// Vérifier et corriger la table videos
showMessage("Vérification de la table 'videos'...", 'info');
if (!tableExists($pdo, 'videos')) {
    showMessage("La table 'videos' n'existe pas. Création en cours...", 'info');
    try {
        if ($dbType == 'pgsql') {
            $sql = "CREATE TABLE videos (
                id SERIAL PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                description TEXT,
                url_video VARCHAR(255) NOT NULL,
                id_categorie INT,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE videos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                description TEXT,
                url_video VARCHAR(255) NOT NULL,
                id_categorie INT,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $pdo->exec($sql);
        showMessage("Table 'videos' créée avec succès.", 'success');
        
        // Insérer quelques données de test
        $sql = "INSERT INTO videos (titre, description, url_video, id_categorie) VALUES 
            ('Introduction au droit civil', 'Une présentation des principes fondamentaux du droit civil', 'https://www.youtube.com/watch?v=example1', 1),
            ('Le droit des contrats', 'Comprendre les bases du droit des contrats', 'https://www.youtube.com/watch?v=example2', 1),
            ('Droit pénal : les infractions', 'Classification des infractions en droit pénal', 'https://www.youtube.com/watch?v=example3', 2)";
        $pdo->exec($sql);
        showMessage("Données de test insérées dans la table 'videos'.", 'success');
    } catch (PDOException $e) {
        showMessage("Erreur lors de la création de la table 'videos': " . $e->getMessage(), 'error');
    }
} else {
    showMessage("La table 'videos' existe déjà.", 'success');
}

// Vérifier et corriger la table categories_videos
showMessage("Vérification de la table 'categories_videos'...", 'info');
if (!tableExists($pdo, 'categories_videos')) {
    showMessage("La table 'categories_videos' n'existe pas. Création en cours...", 'info');
    try {
        if ($dbType == 'pgsql') {
            $sql = "CREATE TABLE categories_videos (
                id SERIAL PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                description TEXT,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE categories_videos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                description TEXT,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $pdo->exec($sql);
        showMessage("Table 'categories_videos' créée avec succès.", 'success');
        
        // Insérer quelques données de test
        $sql = "INSERT INTO categories_videos (nom, description) VALUES 
            ('Droit civil', 'Vidéos sur le droit civil'),
            ('Droit pénal', 'Vidéos sur le droit pénal'),
            ('Droit des affaires', 'Vidéos sur le droit des affaires')";
        $pdo->exec($sql);
        showMessage("Données de test insérées dans la table 'categories_videos'.", 'success');
    } catch (PDOException $e) {
        showMessage("Erreur lors de la création de la table 'categories_videos': " . $e->getMessage(), 'error');
    }
} else {
    showMessage("La table 'categories_videos' existe déjà.", 'success');
}

// Vérifier et corriger la table categories_livres
showMessage("Vérification de la table 'categories_livres'...", 'info');
if (!tableExists($pdo, 'categories_livres')) {
    showMessage("La table 'categories_livres' n'existe pas. Création en cours...", 'info');
    try {
        if ($dbType == 'pgsql') {
            $sql = "CREATE TABLE categories_livres (
                id SERIAL PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                description TEXT,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE categories_livres (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                description TEXT,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        $pdo->exec($sql);
        showMessage("Table 'categories_livres' créée avec succès.", 'success');
        
        // Insérer quelques données de test
        $sql = "INSERT INTO categories_livres (nom, description) VALUES 
            ('Droit civil', 'Livres sur le droit civil'),
            ('Droit pénal', 'Livres sur le droit pénal'),
            ('Droit des affaires', 'Livres sur le droit des affaires')";
        $pdo->exec($sql);
        showMessage("Données de test insérées dans la table 'categories_livres'.", 'success');
    } catch (PDOException $e) {
        showMessage("Erreur lors de la création de la table 'categories_livres': " . $e->getMessage(), 'error');
    }
} else {
    showMessage("La table 'categories_livres' existe déjà.", 'success');
}

// Vérifier les requêtes SQL problématiques
showMessage("Vérification des requêtes SQL problématiques...", 'info');

// Vérifier le fichier livres.php
$livresFile = __DIR__ . '/livres.php';
if (file_exists($livresFile)) {
    showMessage("Vérification du fichier 'livres.php'...", 'info');
    $content = file_get_contents($livresFile);
    
    // Rechercher la requête problématique
    if (strpos($content, 'l.date_creation') !== false) {
        showMessage("Requête problématique trouvée dans 'livres.php'. Correction en cours...", 'info');
        
        // Remplacer l.date_creation par cl.date_creation
        $content = str_replace('l.date_creation', 'cl.date_creation', $content);
        
        if (file_put_contents($livresFile, $content)) {
            showMessage("Fichier 'livres.php' corrigé avec succès.", 'success');
        } else {
            showMessage("Impossible de modifier le fichier 'livres.php'.", 'error');
        }
    } else {
        showMessage("Aucune requête problématique trouvée dans 'livres.php'.", 'success');
    }
} else {
    showMessage("Le fichier 'livres.php' n'existe pas.", 'info');
}

// Conclusion
showMessage("Correction des tables et colonnes terminée.", 'success');
showMessage("Vous pouvez maintenant accéder aux pages de l'application sans erreurs.", 'success');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction des tables et colonnes manquantes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        h1 {
            color: #343a40;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .next-steps {
            margin-top: 30px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_scripts.php" class="back-link">&larr; Retour aux scripts d'administration</a>
        <h1>Correction des tables et colonnes manquantes</h1>
        
        <div class="next-steps">
            <h2>Prochaines étapes</h2>
            <p>Une fois les corrections appliquées, vous devez :</p>
            <ol>
                <li>Vérifier que les tables ont été créées correctement</li>
                <li>Tester les pages qui généraient des erreurs</li>
                <li>Si nécessaire, exécuter d'autres scripts de correction spécifiques</li>
            </ol>
            <p><a href="index.php" class="back-link">Retourner à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
