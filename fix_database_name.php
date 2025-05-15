<?php
// Script pour corriger le problème de nom de base de données PostgreSQL
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

// Fonction pour créer le fichier de configuration de la base de données
function createDbConfigFile($dbname) {
    $configFile = __DIR__ . '/includes/db_config.php';
    $content = "<?php\n";
    $content .= "// Fichier de configuration de la base de données généré automatiquement\n";
    $content .= "// Ce fichier est utilisé pour corriger le problème de nom de base de données sur Render\n\n";
    $content .= "// Si la variable d'environnement DATABASE_URL est définie, on l'utilise\n";
    $content .= "// sinon on utilise les constantes définies ci-dessous\n";
    $content .= "if (!defined('FIXED_DB_NAME')) {\n";
    $content .= "    define('FIXED_DB_NAME', '$dbname');\n";
    $content .= "}\n";
    $content .= "?>";
    
    if (file_put_contents($configFile, $content)) {
        showMessage("Fichier de configuration de la base de données créé avec succès.", 'success');
        return true;
    } else {
        showMessage("Impossible de créer le fichier de configuration de la base de données.", 'error');
        return false;
    }
}

// Fonction pour modifier le fichier db_connect.php
function updateDbConnectFile() {
    $dbConnectFile = __DIR__ . '/includes/db_connect.php';
    
    if (!file_exists($dbConnectFile)) {
        showMessage("Le fichier db_connect.php n'existe pas.", 'error');
        return false;
    }
    
    $content = file_get_contents($dbConnectFile);
    
    // Vérifier si le fichier a déjà été modifié
    if (strpos($content, 'FIXED_DB_NAME') !== false) {
        showMessage("Le fichier db_connect.php a déjà été modifié.", 'info');
        return true;
    }
    
    // Inclure le fichier de configuration
    $includeConfig = "require_once __DIR__ . '/db_config.php';";
    if (strpos($content, $includeConfig) === false) {
        $content = str_replace("<?php", "<?php\n" . $includeConfig, $content);
    }
    
    // Modifier la partie qui extrait le nom de la base de données
    $originalCode = '$dbname = ltrim($url[\'path\'] ?? \'\', \'/\');';
    $newCode = '$dbname = defined(\'FIXED_DB_NAME\') ? FIXED_DB_NAME : ltrim($url[\'path\'] ?? \'\', \'/\');';
    $content = str_replace($originalCode, $newCode, $content);
    
    if (file_put_contents($dbConnectFile, $content)) {
        showMessage("Fichier db_connect.php modifié avec succès.", 'success');
        return true;
    } else {
        showMessage("Impossible de modifier le fichier db_connect.php.", 'error');
        return false;
    }
}

// Récupérer l'URL de la base de données
$database_url = getenv('DATABASE_URL');
if (empty($database_url)) {
    showMessage("Variable d'environnement DATABASE_URL non définie.", 'error');
    showMessage("Ce script doit être exécuté sur Render où la variable DATABASE_URL est définie.", 'info');
    showMessage("Si vous êtes en développement local, ce script n'est pas nécessaire.", 'info');
} else {
    showMessage("DATABASE_URL trouvée: " . (strlen($database_url) > 30 ? substr($database_url, 0, 30) . '...' : $database_url), 'success');
    
    // Analyser l'URL de la base de données
    $url = parse_url($database_url);
    $dbname = ltrim($url['path'] ?? '', '/');
    
    showMessage("Nom de la base de données extrait: $dbname", 'info');
    
    // Corriger le nom de la base de données si nécessaire
    if (substr($dbname, -1) === '_') {
        $fixedDbName = substr($dbname, 0, -1);
        showMessage("Correction du nom de la base de données: $dbname -> $fixedDbName", 'success');
        
        // Créer le fichier de configuration
        if (createDbConfigFile($fixedDbName)) {
            // Modifier le fichier db_connect.php
            if (updateDbConnectFile()) {
                showMessage("Configuration terminée. La connexion à la base de données devrait maintenant fonctionner correctement.", 'success');
                showMessage("Nom de la base de données corrigé: $fixedDbName", 'success');
            }
        }
    } else {
        showMessage("Le nom de la base de données ne se termine pas par un underscore, aucune correction nécessaire.", 'info');
        
        // Créer quand même le fichier de configuration pour être sûr
        if (createDbConfigFile($dbname)) {
            // Modifier le fichier db_connect.php
            if (updateDbConnectFile()) {
                showMessage("Configuration terminée. La connexion à la base de données devrait maintenant fonctionner correctement.", 'success');
                showMessage("Nom de la base de données utilisé: $dbname", 'success');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction du nom de la base de données</title>
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
        <h1>Correction du nom de la base de données PostgreSQL</h1>
        
        <div class="next-steps">
            <h2>Prochaines étapes</h2>
            <p>Une fois la correction appliquée, vous devez :</p>
            <ol>
                <li>Redémarrer l'application sur Render (Manual Deploy > Clear Build Cache & Deploy)</li>
                <li>Exécuter le script <a href="fix_postgres_tables.php">fix_postgres_tables.php</a> pour créer les tables nécessaires</li>
                <li>Vérifier que l'application fonctionne correctement</li>
            </ol>
        </div>
    </div>
</body>
</html>
