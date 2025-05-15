<?php
// Script pour vérifier et corriger les problèmes de fichiers et de permissions
require_once __DIR__ . '/includes/config.php';

// Fonction pour vérifier si un répertoire existe et est accessible en écriture
function checkDirectory($path, $create = true) {
    if (!file_exists($path)) {
        if ($create) {
            try {
                mkdir($path, 0755, true);
                return ['exists' => true, 'writable' => true, 'created' => true];
            } catch (Exception $e) {
                return ['exists' => false, 'writable' => false, 'error' => $e->getMessage()];
            }
        } else {
            return ['exists' => false, 'writable' => false];
        }
    } else {
        return ['exists' => true, 'writable' => is_writable($path), 'created' => false];
    }
}

// Fonction pour obtenir la taille d'un répertoire
function getDirSize($path) {
    $size = 0;
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            if (is_dir($path . "/" . $file)) {
                $size += getDirSize($path . "/" . $file);
            } else {
                $size += filesize($path . "/" . $file);
            }
        }
    }
    
    return $size;
}

// Fonction pour formater la taille en unités lisibles
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vérification des fichiers et permissions</title>
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
    </style>
</head>
<body>
    <div class='container'>
        <h1>Vérification des fichiers et permissions</h1>

<?php
// 1. Vérification des répertoires d'upload
echo "<h2>Vérification des répertoires d'upload</h2>";

// Liste des répertoires à vérifier
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

echo "<table>";
echo "<tr><th>Répertoire</th><th>Statut</th><th>Permissions</th><th>Taille</th><th>Action</th></tr>";

foreach ($directories as $name => $path) {
    $result = checkDirectory($path);
    
    echo "<tr>";
    echo "<td>" . $name . "</td>";
    
    // Statut
    if ($result['exists']) {
        if ($result['created']) {
            echo "<td><span class='warning'>Créé</span></td>";
        } else {
            echo "<td><span class='success'>Existe</span></td>";
        }
    } else {
        echo "<td><span class='error'>N'existe pas</span></td>";
    }
    
    // Permissions
    if ($result['exists']) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = $result['writable'] ? "<span class='success'>Accessible en écriture</span>" : "<span class='error'>Non accessible en écriture</span>";
        echo "<td>" . $perms . " - " . $writable . "</td>";
    } else {
        echo "<td><span class='error'>N/A</span></td>";
    }
    
    // Taille
    if ($result['exists']) {
        $size = getDirSize($path);
        echo "<td>" . formatSize($size) . "</td>";
    } else {
        echo "<td>N/A</td>";
    }
    
    // Action
    if ($result['exists'] && !$result['writable']) {
        echo "<td><a href='?fix_permissions=" . urlencode($path) . "' class='btn btn-warning'>Corriger permissions</a></td>";
    } else if (!$result['exists']) {
        echo "<td><a href='?create_dir=" . urlencode($path) . "' class='btn'>Créer répertoire</a></td>";
    } else {
        echo "<td>Aucune action requise</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// Traitement des actions
if (isset($_GET['fix_permissions']) && !empty($_GET['fix_permissions'])) {
    $path = $_GET['fix_permissions'];
    
    echo "<h3>Tentative de correction des permissions pour " . htmlspecialchars($path) . "</h3>";
    
    if (file_exists($path)) {
        if (chmod($path, 0755)) {
            echo "<p class='success'>Permissions corrigées avec succès.</p>";
        } else {
            echo "<p class='error'>Échec de la correction des permissions. Vérifiez que le serveur web a les droits nécessaires.</p>";
        }
    } else {
        echo "<p class='error'>Le répertoire n'existe pas.</p>";
    }
}

if (isset($_GET['create_dir']) && !empty($_GET['create_dir'])) {
    $path = $_GET['create_dir'];
    
    echo "<h3>Tentative de création du répertoire " . htmlspecialchars($path) . "</h3>";
    
    $result = checkDirectory($path, true);
    
    if ($result['exists'] && $result['created']) {
        echo "<p class='success'>Répertoire créé avec succès.</p>";
    } else if ($result['exists'] && !$result['created']) {
        echo "<p class='warning'>Le répertoire existe déjà.</p>";
    } else {
        echo "<p class='error'>Échec de la création du répertoire: " . $result['error'] . "</p>";
    }
}

// 2. Vérification des fichiers de configuration
echo "<h2>Vérification des fichiers de configuration</h2>";

$config_files = [
    'includes/config.php' => __DIR__ . '/includes/config.php',
    'includes/db_connect.php' => __DIR__ . '/includes/db_connect.php',
    '.htaccess' => __DIR__ . '/.htaccess'
];

echo "<table>";
echo "<tr><th>Fichier</th><th>Statut</th><th>Permissions</th><th>Taille</th><th>Dernière modification</th></tr>";

foreach ($config_files as $name => $path) {
    echo "<tr>";
    echo "<td>" . $name . "</td>";
    
    // Statut
    if (file_exists($path)) {
        echo "<td><span class='success'>Existe</span></td>";
        
        // Permissions
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path) ? "<span class='success'>Accessible en écriture</span>" : "<span class='error'>Non accessible en écriture</span>";
        echo "<td>" . $perms . " - " . $writable . "</td>";
        
        // Taille
        $size = filesize($path);
        echo "<td>" . formatSize($size) . "</td>";
        
        // Dernière modification
        $last_modified = filemtime($path);
        echo "<td>" . date("Y-m-d H:i:s", $last_modified) . "</td>";
    } else {
        echo "<td><span class='error'>N'existe pas</span></td>";
        echo "<td>N/A</td>";
        echo "<td>N/A</td>";
        echo "<td>N/A</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// 3. Vérification du fichier .htaccess
echo "<h2>Vérification du fichier .htaccess</h2>";

$htaccess_path = __DIR__ . '/.htaccess';

if (file_exists($htaccess_path)) {
    echo "<p class='success'>Le fichier .htaccess existe.</p>";
    
    $htaccess_content = file_get_contents($htaccess_path);
    
    // Vérifier si le fichier .htaccess contient des règles de sécurité de base
    $security_checks = [
        'Directory listing' => strpos($htaccess_content, 'Options -Indexes') !== false,
        'PHP errors' => strpos($htaccess_content, 'php_flag display_errors off') !== false,
        'File access' => strpos($htaccess_content, '<Files') !== false,
        'MIME types' => strpos($htaccess_content, 'AddType') !== false,
        'XSS protection' => strpos($htaccess_content, 'X-XSS-Protection') !== false,
        'Frame options' => strpos($htaccess_content, 'X-Frame-Options') !== false,
        'Content type' => strpos($htaccess_content, 'X-Content-Type-Options') !== false
    ];
    
    echo "<table>";
    echo "<tr><th>Règle de sécurité</th><th>Statut</th></tr>";
    
    foreach ($security_checks as $rule => $exists) {
        echo "<tr>";
        echo "<td>" . $rule . "</td>";
        echo "<td>" . ($exists ? "<span class='success'>Présente</span>" : "<span class='warning'>Absente</span>") . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Proposer un fichier .htaccess amélioré si des règles sont manquantes
    if (in_array(false, $security_checks)) {
        echo "<h3>Proposition de fichier .htaccess amélioré</h3>";
        
        echo "<p>Certaines règles de sécurité sont absentes du fichier .htaccess actuel. Voici une proposition de fichier .htaccess amélioré :</p>";
        
        echo "<pre>";
        echo "# Désactiver l'affichage du contenu des répertoires\n";
        echo "Options -Indexes\n\n";
        echo "# Protéger les fichiers sensibles\n";
        echo "&lt;Files ~ \"^\\.(htaccess|htpasswd|ini|log|yml|yaml|conf)$\"&gt;\n";
        echo "    Order Allow,Deny\n";
        echo "    Deny from all\n";
        echo "&lt;/Files&gt;\n\n";
        echo "# Masquer les informations sur le serveur\n";
        echo "ServerSignature Off\n\n";
        echo "# Définir le jeu de caractères par défaut\n";
        echo "AddDefaultCharset UTF-8\n\n";
        echo "# Définir les types MIME pour plus de sécurité\n";
        echo "AddType application/javascript .js\n";
        echo "AddType text/css .css\n\n";
        echo "# Protection contre les attaques XSS et autres\n";
        echo "&lt;IfModule mod_headers.c&gt;\n";
        echo "    Header set X-XSS-Protection \"1; mode=block\"\n";
        echo "    Header set X-Frame-Options \"SAMEORIGIN\"\n";
        echo "    Header set X-Content-Type-Options \"nosniff\"\n";
        echo "    Header set Referrer-Policy \"strict-origin-when-cross-origin\"\n";
        echo "    # Pour activer HSTS (HTTP Strict Transport Security) en production\n";
        echo "    # Header set Strict-Transport-Security \"max-age=31536000; includeSubDomains; preload\"\n";
        echo "&lt;/IfModule&gt;\n\n";
        echo "# Désactiver l'affichage des erreurs PHP\n";
        echo "&lt;IfModule mod_php7.c&gt;\n";
        echo "    php_flag display_errors off\n";
        echo "    php_value error_reporting 0\n";
        echo "&lt;/IfModule&gt;\n\n";
        echo "# Redirection vers HTTPS (à activer en production)\n";
        echo "# &lt;IfModule mod_rewrite.c&gt;\n";
        echo "#    RewriteEngine On\n";
        echo "#    RewriteCond %{HTTPS} off\n";
        echo "#    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n";
        echo "# &lt;/IfModule&gt;\n";
        echo "</pre>";
        
        echo "<p><a href='?create_htaccess=1' class='btn btn-warning'>Créer/Mettre à jour le fichier .htaccess</a></p>";
    }
} else {
    echo "<p class='warning'>Le fichier .htaccess n'existe pas.</p>";
    echo "<p><a href='?create_htaccess=1' class='btn'>Créer un fichier .htaccess</a></p>";
}

// Traitement de la création/mise à jour du fichier .htaccess
if (isset($_GET['create_htaccess'])) {
    echo "<h3>Création/Mise à jour du fichier .htaccess</h3>";
    
    $htaccess_content = "# Désactiver l'affichage du contenu des répertoires\n";
    $htaccess_content .= "Options -Indexes\n\n";
    $htaccess_content .= "# Protéger les fichiers sensibles\n";
    $htaccess_content .= "<Files ~ \"^\\.(htaccess|htpasswd|ini|log|yml|yaml|conf)$\">\n";
    $htaccess_content .= "    Order Allow,Deny\n";
    $htaccess_content .= "    Deny from all\n";
    $htaccess_content .= "</Files>\n\n";
    $htaccess_content .= "# Masquer les informations sur le serveur\n";
    $htaccess_content .= "ServerSignature Off\n\n";
    $htaccess_content .= "# Définir le jeu de caractères par défaut\n";
    $htaccess_content .= "AddDefaultCharset UTF-8\n\n";
    $htaccess_content .= "# Définir les types MIME pour plus de sécurité\n";
    $htaccess_content .= "AddType application/javascript .js\n";
    $htaccess_content .= "AddType text/css .css\n\n";
    $htaccess_content .= "# Protection contre les attaques XSS et autres\n";
    $htaccess_content .= "<IfModule mod_headers.c>\n";
    $htaccess_content .= "    Header set X-XSS-Protection \"1; mode=block\"\n";
    $htaccess_content .= "    Header set X-Frame-Options \"SAMEORIGIN\"\n";
    $htaccess_content .= "    Header set X-Content-Type-Options \"nosniff\"\n";
    $htaccess_content .= "    Header set Referrer-Policy \"strict-origin-when-cross-origin\"\n";
    $htaccess_content .= "    # Pour activer HSTS (HTTP Strict Transport Security) en production\n";
    $htaccess_content .= "    # Header set Strict-Transport-Security \"max-age=31536000; includeSubDomains; preload\"\n";
    $htaccess_content .= "</IfModule>\n\n";
    $htaccess_content .= "# Désactiver l'affichage des erreurs PHP\n";
    $htaccess_content .= "<IfModule mod_php7.c>\n";
    $htaccess_content .= "    php_flag display_errors off\n";
    $htaccess_content .= "    php_value error_reporting 0\n";
    $htaccess_content .= "</IfModule>\n\n";
    $htaccess_content .= "# Redirection vers HTTPS (à activer en production)\n";
    $htaccess_content .= "# <IfModule mod_rewrite.c>\n";
    $htaccess_content .= "#    RewriteEngine On\n";
    $htaccess_content .= "#    RewriteCond %{HTTPS} off\n";
    $htaccess_content .= "#    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n";
    $htaccess_content .= "# </IfModule>\n";
    
    if (file_put_contents($htaccess_path, $htaccess_content)) {
        echo "<p class='success'>Fichier .htaccess créé/mis à jour avec succès.</p>";
    } else {
        echo "<p class='error'>Échec de la création/mise à jour du fichier .htaccess. Vérifiez les permissions.</p>";
    }
}

// 4. Vérification des fichiers temporaires
echo "<h2>Vérification des fichiers temporaires</h2>";

$temp_dir = __DIR__ . '/temp';

if (!file_exists($temp_dir)) {
    echo "<p class='warning'>Le répertoire temp n'existe pas. <a href='?create_dir=" . urlencode($temp_dir) . "' class='btn'>Créer le répertoire</a></p>";
} else {
    echo "<p class='success'>Le répertoire temp existe.</p>";
    
    // Vérifier s'il y a des fichiers temporaires anciens
    $old_files = [];
    $now = time();
    $max_age = 86400; // 24 heures
    
    $files = scandir($temp_dir);
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            $file_path = $temp_dir . '/' . $file;
            $file_age = $now - filemtime($file_path);
            
            if ($file_age > $max_age) {
                $old_files[] = [
                    'name' => $file,
                    'path' => $file_path,
                    'age' => $file_age,
                    'size' => filesize($file_path)
                ];
            }
        }
    }
    
    if (count($old_files) > 0) {
        echo "<p class='warning'>Des fichiers temporaires anciens (> 24h) ont été trouvés :</p>";
        
        echo "<table>";
        echo "<tr><th>Fichier</th><th>Âge</th><th>Taille</th><th>Action</th></tr>";
        
        foreach ($old_files as $file) {
            echo "<tr>";
            echo "<td>" . $file['name'] . "</td>";
            echo "<td>" . floor($file['age'] / 3600) . " heures</td>";
            echo "<td>" . formatSize($file['size']) . "</td>";
            echo "<td><a href='?delete_file=" . urlencode($file['path']) . "' class='btn btn-warning'>Supprimer</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p><a href='?clean_temp=1' class='btn btn-warning'>Nettoyer tous les fichiers temporaires anciens</a></p>";
    } else {
        echo "<p class='success'>Aucun fichier temporaire ancien trouvé.</p>";
    }
}

// Traitement de la suppression de fichiers temporaires
if (isset($_GET['delete_file']) && !empty($_GET['delete_file'])) {
    $file_path = $_GET['delete_file'];
    
    echo "<h3>Suppression du fichier " . htmlspecialchars(basename($file_path)) . "</h3>";
    
    if (file_exists($file_path) && is_file($file_path)) {
        if (unlink($file_path)) {
            echo "<p class='success'>Fichier supprimé avec succès.</p>";
        } else {
            echo "<p class='error'>Échec de la suppression du fichier. Vérifiez les permissions.</p>";
        }
    } else {
        echo "<p class='error'>Le fichier n'existe pas ou n'est pas un fichier régulier.</p>";
    }
}

if (isset($_GET['clean_temp'])) {
    echo "<h3>Nettoyage des fichiers temporaires anciens</h3>";
    
    $temp_dir = __DIR__ . '/temp';
    $now = time();
    $max_age = 86400; // 24 heures
    $deleted_count = 0;
    $error_count = 0;
    
    if (file_exists($temp_dir) && is_dir($temp_dir)) {
        $files = scandir($temp_dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $file_path = $temp_dir . '/' . $file;
                $file_age = $now - filemtime($file_path);
                
                if ($file_age > $max_age) {
                    if (unlink($file_path)) {
                        $deleted_count++;
                    } else {
                        $error_count++;
                    }
                }
            }
        }
        
        echo "<p>" . $deleted_count . " fichier(s) supprimé(s) avec succès.</p>";
        
        if ($error_count > 0) {
            echo "<p class='error'>" . $error_count . " fichier(s) n'ont pas pu être supprimés.</p>";
        }
    } else {
        echo "<p class='error'>Le répertoire temp n'existe pas.</p>";
    }
}

// 5. Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='fix_db_security.php'>Sécurité de la base de données</a></li>";
echo "<li><a href='fix_session_cookies.php'>Vérifier sessions/cookies</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
