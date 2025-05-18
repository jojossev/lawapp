<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Style CSS pour une meilleure présentation
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de l\'intégrité de l\'application</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-ok { background-color: #e8f5e9; }
        .status-warning { background-color: #fff8e1; }
        .status-error { background-color: #ffebee; }
    </style>
</head>
<body>
    <h1>Vérification de l\'intégrité de l\'application</h1>';

require_once __DIR__ . '/../includes/config.php';

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
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = :dbname AND table_name = :table
            ");
            // Extraire dynamiquement le nom de la base de données
            $database_url = defined('DATABASE_URL') ? DATABASE_URL : getenv('DATABASE_URL');
            if (empty($database_url)) {
                // Fallback si aucune URL n'est définie
                $db_name = 'lawapp_';
            } else {
                $db_name = parse_url($database_url, PHP_URL_PATH);
                $db_name = ltrim($db_name, '/');
            }
            
            $stmt->execute([':dbname' => $db_name, ':table' => $table]);
        }
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return false;
    }
}

// Fonction pour vérifier si un répertoire existe et est accessible
function checkDirectory($path) {
    return is_dir($path) && is_readable($path);
}

// Fonction pour vérifier si un fichier existe et est accessible
function checkFile($path) {
    return file_exists($path) && is_readable($path);
}

// Fonction pour vérifier si une variable d'environnement est définie
function checkEnvVar($name) {
    return isset($_ENV[$name]) || isset($_SERVER[$name]) || getenv($name) !== false;
}

try {
    // 1. Vérification de la connexion à la base de données
    echo "<h2>1. Connexion à la base de données</h2>";
    
    try {
        $pdo->query("SELECT 1");
        echo "<p class='success'>✓ Connexion à la base de données établie avec succès.</p>";
        
        // Afficher les informations de connexion
        echo "<p>Type de base de données: <strong>" . (strpos(DB_URL, 'pgsql') !== false ? 'PostgreSQL' : 'MySQL') . "</strong></p>";
        // Vérifier l'existence de DATABASE_URL
        $database_url = defined('DATABASE_URL') ? DATABASE_URL : getenv('DATABASE_URL');
        
        if (empty($database_url)) {
            echo "<p class='error'>✗ Variable DATABASE_URL non définie.</p>";
            $db_name = 'Inconnu';
        } else {
            // Extraire le nom de la base de données de l'URL
            $db_name = parse_url($database_url, PHP_URL_PATH);
            $db_name = ltrim($db_name, '/');
        }
        
        echo "<p>Nom de la base de données: <strong>" . htmlspecialchars($db_name) . "</strong></p>";
        
        // Vérifier la version de la base de données
        $version = $pdo->query("SELECT version()")->fetchColumn();
        echo "<p>Version: <strong>" . htmlspecialchars($version) . "</strong></p>";
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
    }
    
    // 2. Vérification des tables essentielles
    echo "<h2>2. Tables essentielles</h2>";
    
    $essential_tables = [
        'utilisateurs' => 'Table des utilisateurs pour l\'authentification',
        'administrateurs' => 'Table des administrateurs pour l\'accès au tableau de bord',
        'cours' => 'Table des cours disponibles',
        'categories_cours' => 'Table des catégories de cours',
        'lecons' => 'Table des leçons associées aux cours',
        'quiz' => 'Table des quiz associés aux cours',
        'questions' => 'Table des questions des quiz',
        'reponses' => 'Table des réponses aux questions',
        'inscriptions' => 'Table des inscriptions des utilisateurs aux cours',
        'videos' => 'Table des vidéos disponibles',
        'livres' => 'Table des livres disponibles',
        'categories_livres' => 'Table des catégories de livres',
        'podcasts' => 'Table des podcasts disponibles',
        'categories_podcasts' => 'Table des catégories de podcasts'
    ];
    
    echo "<table>";
    echo "<tr><th>Table</th><th>Description</th><th>Statut</th></tr>";
    
    $missing_tables = [];
    
    foreach ($essential_tables as $table => $description) {
        $exists = tableExists($pdo, $table);
        $class = $exists ? 'status-ok' : 'status-error';
        $status = $exists ? '✓ Présente' : '✗ Manquante';
        
        echo "<tr class='" . $class . "'>";
        echo "<td>" . htmlspecialchars($table) . "</td>";
        echo "<td>" . htmlspecialchars($description) . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
        
        if (!$exists) {
            $missing_tables[] = $table;
        }
    }
    
    echo "</table>";
    
    if (count($missing_tables) > 0) {
        echo "<p class='warning'>Tables manquantes: " . implode(', ', $missing_tables) . "</p>";
        echo "<p>Utilisez les scripts de correction pour créer les tables manquantes:</p>";
        echo "<ul>";
        
        foreach ($missing_tables as $table) {
            switch ($table) {
                case 'utilisateurs':
                    echo "<li><a href='fix_utilisateurs_table.php'>fix_utilisateurs_table.php</a></li>";
                    break;
                case 'administrateurs':
                    echo "<li><a href='fix_admin_table.php'>fix_admin_table.php</a></li>";
                    break;
                case 'cours':
                case 'categories_cours':
                    echo "<li><a href='fix_cours_table.php'>fix_cours_table.php</a></li>";
                    break;
                case 'lecons':
                case 'quiz':
                case 'questions':
                case 'reponses':
                    echo "<li><a href='fix_lecons_quiz_tables.php'>fix_lecons_quiz_tables.php</a></li>";
                    break;
                case 'inscriptions':
                    echo "<li><a href='fix_inscriptions_table.php'>fix_inscriptions_table.php</a></li>";
                    break;
                case 'videos':
                    echo "<li><a href='fix_videos_structure.php'>fix_videos_structure.php</a></li>";
                    break;
                case 'livres':
                case 'categories_livres':
                    echo "<li><a href='fix_livres_table.php'>fix_livres_table.php</a></li>";
                    break;
                case 'podcasts':
                case 'categories_podcasts':
                    echo "<li><a href='fix_podcasts_table.php'>fix_podcasts_table.php</a></li>";
                    break;
            }
        }
        
        echo "</ul>";
        echo "<p><a href='fix_all_issues.php'>Exécuter tous les scripts de correction</a></p>";
    } else {
        echo "<p class='success'>Toutes les tables essentielles sont présentes.</p>";
    }
    
    // 3. Vérification des répertoires essentiels
    echo "<h2>3. Répertoires essentiels</h2>";
    
    $essential_dirs = [
        __DIR__ . '/..' => 'Répertoire racine de l\'application',
        __DIR__ . '/../includes' => 'Répertoire des fichiers d\'inclusion',
        __DIR__ . '/../admin' => 'Répertoire d\'administration',
        __DIR__ . '/../assets' => 'Répertoire des ressources statiques',
        __DIR__ . '/../assets/img' => 'Répertoire des images',
        __DIR__ . '/../assets/css' => 'Répertoire des feuilles de style',
        __DIR__ . '/../assets/js' => 'Répertoire des scripts JavaScript',
        __DIR__ . '/../uploads' => 'Répertoire des fichiers téléchargés'
    ];
    
    echo "<table>";
    echo "<tr><th>Répertoire</th><th>Description</th><th>Statut</th></tr>";
    
    foreach ($essential_dirs as $dir => $description) {
        $exists = checkDirectory($dir);
        $class = $exists ? 'status-ok' : 'status-error';
        $status = $exists ? '✓ Accessible' : '✗ Inaccessible';
        
        echo "<tr class='" . $class . "'>";
        echo "<td>" . htmlspecialchars($dir) . "</td>";
        echo "<td>" . htmlspecialchars($description) . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // 4. Vérification des fichiers essentiels
    echo "<h2>4. Fichiers essentiels</h2>";
    
    $essential_files = [
        __DIR__ . '/../index.php' => 'Page d\'accueil',
        __DIR__ . '/../includes/config.php' => 'Configuration de l\'application',
        __DIR__ . '/../includes/db_connect.php' => 'Connexion à la base de données',
        __DIR__ . '/../includes/functions.php' => 'Fonctions utilitaires',
        __DIR__ . '/../includes/header.php' => 'En-tête des pages',
        __DIR__ . '/../includes/footer.php' => 'Pied de page',
        __DIR__ . '/../login.php' => 'Page de connexion',
        __DIR__ . '/../register.php' => 'Page d\'inscription',
        __DIR__ . '/../admin/index.php' => 'Tableau de bord d\'administration',
        __DIR__ . '/../admin/admin_login.php' => 'Page de connexion administrateur'
    ];
    
    echo "<table>";
    echo "<tr><th>Fichier</th><th>Description</th><th>Statut</th></tr>";
    
    foreach ($essential_files as $file => $description) {
        $exists = checkFile($file);
        $class = $exists ? 'status-ok' : 'status-error';
        $status = $exists ? '✓ Présent' : '✗ Manquant';
        
        echo "<tr class='" . $class . "'>";
        echo "<td>" . htmlspecialchars($file) . "</td>";
        echo "<td>" . htmlspecialchars($description) . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // 5. Vérification des variables d'environnement
    echo "<h2>5. Variables d'environnement</h2>";
    
    $env_vars = [
        'ENVIRONMENT' => 'Environnement (development, production)',
        'DATABASE_URL' => 'URL de connexion à la base de données',
        'APP_URL' => 'URL de base de l\'application',
        'RENDER_EXTERNAL_URL' => 'URL externe sur Render'
    ];
    
    echo "<table>";
    echo "<tr><th>Variable</th><th>Description</th><th>Statut</th></tr>";
    
    foreach ($env_vars as $var => $description) {
        $exists = checkEnvVar($var);
        $class = $exists ? 'status-ok' : 'status-warning';
        $status = $exists ? '✓ Définie' : '✗ Non définie';
        
        echo "<tr class='" . $class . "'>";
        echo "<td>" . htmlspecialchars($var) . "</td>";
        echo "<td>" . htmlspecialchars($description) . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // 6. Vérification des extensions PHP
    echo "<h2>6. Extensions PHP requises</h2>";
    
    $required_extensions = [
        'pdo' => 'Extension PDO pour l\'accès aux bases de données',
        'pdo_mysql' => 'Driver PDO pour MySQL',
        'pdo_pgsql' => 'Driver PDO pour PostgreSQL',
        'mbstring' => 'Extension pour le traitement des chaînes multi-octets',
        'json' => 'Extension pour le traitement JSON',
        'session' => 'Extension pour la gestion des sessions',
        'fileinfo' => 'Extension pour la détection des types MIME'
    ];
    
    echo "<table>";
    echo "<tr><th>Extension</th><th>Description</th><th>Statut</th></tr>";
    
    foreach ($required_extensions as $ext => $description) {
        $loaded = extension_loaded($ext);
        $class = $loaded ? 'status-ok' : 'status-error';
        $status = $loaded ? '✓ Chargée' : '✗ Non chargée';
        
        echo "<tr class='" . $class . "'>";
        echo "<td>" . htmlspecialchars($ext) . "</td>";
        echo "<td>" . htmlspecialchars($description) . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // 7. Résumé
    echo "<h2>7. Résumé</h2>";
    
    $issues = [];
    
    if (count($missing_tables) > 0) {
        $issues[] = count($missing_tables) . " tables manquantes";
    }
    
    $missing_dirs = array_filter($essential_dirs, function($dir) {
        return !checkDirectory($dir);
    }, ARRAY_FILTER_USE_KEY);
    
    if (count($missing_dirs) > 0) {
        $issues[] = count($missing_dirs) . " répertoires inaccessibles";
    }
    
    $missing_files = array_filter($essential_files, function($file) {
        return !checkFile($file);
    }, ARRAY_FILTER_USE_KEY);
    
    if (count($missing_files) > 0) {
        $issues[] = count($missing_files) . " fichiers manquants";
    }
    
    $missing_env_vars = array_filter($env_vars, function($var) {
        return !checkEnvVar($var);
    }, ARRAY_FILTER_USE_KEY);
    
    if (count($missing_env_vars) > 0) {
        $issues[] = count($missing_env_vars) . " variables d'environnement non définies";
    }
    
    $missing_extensions = array_filter($required_extensions, function($ext) {
        return !extension_loaded($ext);
    }, ARRAY_FILTER_USE_KEY);
    
    if (count($missing_extensions) > 0) {
        $issues[] = count($missing_extensions) . " extensions PHP manquantes";
    }
    
    if (count($issues) > 0) {
        echo "<p class='warning'>L'application présente les problèmes suivants: " . implode(', ', $issues) . ".</p>";
        echo "<p>Utilisez les scripts de correction appropriés pour résoudre ces problèmes.</p>";
    } else {
        echo "<p class='success'>L'application est en bon état et prête à être utilisée.</p>";
    }
    
    echo "<h2>Actions disponibles</h2>";
    echo "<ul>";
    echo "<li><a href='fix_all_issues.php'>Exécuter tous les scripts de correction</a></li>";
    echo "<li><a href='test_db_connection.php'>Tester la connexion à la base de données</a></li>";
    echo "<li><a href='check_and_fix_database.php'>Vérifier et corriger la base de données</a></li>";
    echo "<li><a href='../index.php'>Retour à l'accueil</a></li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
