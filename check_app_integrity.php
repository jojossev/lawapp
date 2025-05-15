<?php
// Script pour vérifier l'intégrité globale de l'application
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

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
        return false;
    }
}

// Fonction pour vérifier si un répertoire existe et est accessible en écriture
function checkDirectory($path) {
    if (!file_exists($path)) {
        return ['exists' => false, 'writable' => false];
    } else {
        return ['exists' => true, 'writable' => is_writable($path)];
    }
}

// Fonction pour vérifier si un fichier existe
function checkFile($path) {
    if (!file_exists($path)) {
        return ['exists' => false, 'readable' => false];
    } else {
        return ['exists' => true, 'readable' => is_readable($path)];
    }
}

// Fonction pour compter le nombre d'enregistrements dans une table
function countRecords($pdo, $table) {
    try {
        $sql = "SELECT COUNT(*) FROM $table";
        $stmt = $pdo->query($sql);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vérification de l'intégrité de l'application</title>
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
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-warning {
            background-color: #ff9800;
        }
        .btn-warning:hover {
            background-color: #e68a00;
        }
        .summary {
            background-color: #f9f9f9;
            border-left: 4px solid #4CAF50;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Vérification de l'intégrité de l'application</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

// Déterminer le type de base de données
$is_postgres = strpos(DB_URL, 'pgsql') !== false;
$db_type = $is_postgres ? 'PostgreSQL' : 'MySQL';

echo "<p class='success'>Connexion à la base de données établie avec succès. Type de base de données : $db_type</p>";

// 1. Vérification des tables de la base de données
echo "<h2>Vérification des tables de la base de données</h2>";

$tables = [
    'administrateurs' => 'Table des administrateurs',
    'utilisateurs' => 'Table des utilisateurs',
    'categories_livres' => 'Table des catégories de livres',
    'livres' => 'Table des livres',
    'categories_podcasts' => 'Table des catégories de podcasts',
    'podcasts' => 'Table des podcasts',
    'categories_cours' => 'Table des catégories de cours',
    'cours' => 'Table des cours',
    'lecons' => 'Table des leçons',
    'videos' => 'Table des vidéos',
    'inscriptions' => 'Table des inscriptions aux cours',
    'logs_activite' => 'Table des journaux d\'activité'
];

$missing_tables = [];
$empty_tables = [];

echo "<table>";
echo "<tr><th>Table</th><th>Description</th><th>Statut</th><th>Nombre d'enregistrements</th></tr>";

foreach ($tables as $table => $description) {
    $exists = tableExists($pdo, $table);
    $count = $exists ? countRecords($pdo, $table) : 0;
    
    echo "<tr>";
    echo "<td>" . $table . "</td>";
    echo "<td>" . $description . "</td>";
    
    if ($exists) {
        echo "<td><span class='success'>Existe</span></td>";
    } else {
        echo "<td><span class='error'>N'existe pas</span></td>";
        $missing_tables[] = $table;
    }
    
    echo "<td>" . $count . "</td>";
    
    if ($exists && $count == 0) {
        $empty_tables[] = $table;
    }
    
    echo "</tr>";
}

echo "</table>";

// 2. Vérification des répertoires d'upload
echo "<h2>Vérification des répertoires d'upload</h2>";

$directories = [
    'uploads' => __DIR__ . '/uploads',
    'uploads/cours_couvertures' => __DIR__ . '/uploads/cours_couvertures',
    'uploads/lecons' => __DIR__ . '/uploads/lecons',
    'uploads/podcasts/audio' => __DIR__ . '/uploads/podcasts/audio',
    'uploads/podcasts/images' => __DIR__ . '/uploads/podcasts/images',
    'uploads/livres' => __DIR__ . '/uploads/livres',
    'uploads/videos' => __DIR__ . '/uploads/videos',
    'uploads/utilisateurs' => __DIR__ . '/uploads/utilisateurs',
    'temp' => __DIR__ . '/temp'
];

$missing_dirs = [];
$not_writable_dirs = [];

echo "<table>";
echo "<tr><th>Répertoire</th><th>Statut</th><th>Permissions</th></tr>";

foreach ($directories as $name => $path) {
    $result = checkDirectory($path);
    
    echo "<tr>";
    echo "<td>" . $name . "</td>";
    
    if ($result['exists']) {
        echo "<td><span class='success'>Existe</span></td>";
    } else {
        echo "<td><span class='error'>N'existe pas</span></td>";
        $missing_dirs[] = $name;
    }
    
    if ($result['exists']) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = $result['writable'] ? "<span class='success'>Accessible en écriture</span>" : "<span class='error'>Non accessible en écriture</span>";
        echo "<td>" . $perms . " - " . $writable . "</td>";
        
        if (!$result['writable']) {
            $not_writable_dirs[] = $name;
        }
    } else {
        echo "<td>N/A</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// 3. Vérification des fichiers de configuration
echo "<h2>Vérification des fichiers de configuration</h2>";

$config_files = [
    'includes/config.php' => __DIR__ . '/includes/config.php',
    'includes/db_connect.php' => __DIR__ . '/includes/db_connect.php',
    '.htaccess' => __DIR__ . '/.htaccess'
];

$missing_files = [];

echo "<table>";
echo "<tr><th>Fichier</th><th>Statut</th></tr>";

foreach ($config_files as $name => $path) {
    $result = checkFile($path);
    
    echo "<tr>";
    echo "<td>" . $name . "</td>";
    
    if ($result['exists']) {
        echo "<td><span class='success'>Existe</span></td>";
    } else {
        echo "<td><span class='error'>N'existe pas</span></td>";
        $missing_files[] = $name;
    }
    
    echo "</tr>";
}

echo "</table>";

// 4. Vérification des variables d'environnement
echo "<h2>Vérification des variables d'environnement</h2>";

$env_vars = [
    'DATABASE_URL' => getenv('DATABASE_URL'),
    'APP_URL' => getenv('APP_URL'),
    'ENVIRONMENT' => getenv('ENVIRONMENT')
];

$missing_env_vars = [];

echo "<table>";
echo "<tr><th>Variable</th><th>Statut</th><th>Valeur</th></tr>";

foreach ($env_vars as $name => $value) {
    echo "<tr>";
    echo "<td>" . $name . "</td>";
    
    if (!empty($value)) {
        echo "<td><span class='success'>Définie</span></td>";
        
        // Masquer les informations sensibles
        if ($name == 'DATABASE_URL') {
            // Masquer le mot de passe dans l'URL de connexion
            $masked_value = preg_replace('/(:)([^:@]+)(@)/', ':***@', $value);
            echo "<td>" . $masked_value . "</td>";
        } else {
            echo "<td>" . $value . "</td>";
        }
    } else {
        echo "<td><span class='warning'>Non définie</span></td>";
        echo "<td>N/A</td>";
        $missing_env_vars[] = $name;
    }
    
    echo "</tr>";
}

echo "</table>";

// 5. Résumé des problèmes
echo "<h2>Résumé des problèmes</h2>";

echo "<div class='summary'>";

$has_problems = false;

if (!empty($missing_tables)) {
    $has_problems = true;
    echo "<h3>Tables manquantes :</h3>";
    echo "<ul>";
    foreach ($missing_tables as $table) {
        echo "<li>" . $table . "</li>";
    }
    echo "</ul>";
}

if (!empty($empty_tables)) {
    $has_problems = true;
    echo "<h3>Tables vides :</h3>";
    echo "<ul>";
    foreach ($empty_tables as $table) {
        echo "<li>" . $table . "</li>";
    }
    echo "</ul>";
}

if (!empty($missing_dirs)) {
    $has_problems = true;
    echo "<h3>Répertoires manquants :</h3>";
    echo "<ul>";
    foreach ($missing_dirs as $dir) {
        echo "<li>" . $dir . "</li>";
    }
    echo "</ul>";
}

if (!empty($not_writable_dirs)) {
    $has_problems = true;
    echo "<h3>Répertoires non accessibles en écriture :</h3>";
    echo "<ul>";
    foreach ($not_writable_dirs as $dir) {
        echo "<li>" . $dir . "</li>";
    }
    echo "</ul>";
}

if (!empty($missing_files)) {
    $has_problems = true;
    echo "<h3>Fichiers de configuration manquants :</h3>";
    echo "<ul>";
    foreach ($missing_files as $file) {
        echo "<li>" . $file . "</li>";
    }
    echo "</ul>";
}

if (!empty($missing_env_vars)) {
    $has_problems = true;
    echo "<h3>Variables d'environnement manquantes :</h3>";
    echo "<ul>";
    foreach ($missing_env_vars as $var) {
        echo "<li>" . $var . "</li>";
    }
    echo "</ul>";
}

if (!$has_problems) {
    echo "<p class='success'>Aucun problème détecté. L'application semble être correctement configurée.</p>";
} else {
    echo "<p class='warning'>Des problèmes ont été détectés. Utilisez le script <a href='fix_all_issues.php'>fix_all_issues.php</a> pour les corriger automatiquement.</p>";
}

echo "</div>";

// 6. Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='fix_all_issues.php' class='btn'>Corriger tous les problèmes</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
