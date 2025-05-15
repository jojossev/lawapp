<?php
// Script pour vérifier si le dossier admin existe et est accessible
echo "<h1>Vérification du dossier admin</h1>";

// Chemin absolu vers le dossier admin
$admin_path = __DIR__ . '/admin';

// Vérifier si le dossier existe
if (is_dir($admin_path)) {
    echo "<p style='color:green'>✓ Le dossier admin existe sur le serveur.</p>";
    
    // Vérifier si le dossier est lisible
    if (is_readable($admin_path)) {
        echo "<p style='color:green'>✓ Le dossier admin est lisible.</p>";
    } else {
        echo "<p style='color:red'>✗ Le dossier admin n'est pas lisible.</p>";
    }
    
    // Lister les fichiers dans le dossier admin
    echo "<h2>Fichiers dans le dossier admin :</h2>";
    echo "<ul>";
    $files = scandir($admin_path);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>" . htmlspecialchars($file) . " - ";
            if (is_readable($admin_path . '/' . $file)) {
                echo "<span style='color:green'>lisible</span>";
            } else {
                echo "<span style='color:red'>non lisible</span>";
            }
            echo "</li>";
        }
    }
    echo "</ul>";
    
    // Vérifier spécifiquement admin_login.php
    $admin_login_file = $admin_path . '/admin_login.php';
    if (file_exists($admin_login_file)) {
        echo "<p style='color:green'>✓ Le fichier admin_login.php existe.</p>";
        if (is_readable($admin_login_file)) {
            echo "<p style='color:green'>✓ Le fichier admin_login.php est lisible.</p>";
        } else {
            echo "<p style='color:red'>✗ Le fichier admin_login.php n'est pas lisible.</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Le fichier admin_login.php n'existe pas.</p>";
    }
} else {
    echo "<p style='color:red'>✗ Le dossier admin n'existe pas sur le serveur.</p>";
}

// Vérifier les permissions
echo "<h2>Permissions :</h2>";
echo "<pre>";
if (function_exists('posix_getpwuid')) {
    $owner = posix_getpwuid(fileowner(__DIR__));
    echo "Propriétaire du dossier racine : " . $owner['name'] . "\n";
    
    if (is_dir($admin_path)) {
        $admin_owner = posix_getpwuid(fileowner($admin_path));
        echo "Propriétaire du dossier admin : " . $admin_owner['name'] . "\n";
    }
} else {
    echo "Fonction posix_getpwuid non disponible.\n";
}

echo "Permissions du dossier racine : " . substr(sprintf('%o', fileperms(__DIR__)), -4) . "\n";
if (is_dir($admin_path)) {
    echo "Permissions du dossier admin : " . substr(sprintf('%o', fileperms($admin_path)), -4) . "\n";
}
echo "</pre>";

// Informations sur le serveur
echo "<h2>Informations sur le serveur :</h2>";
echo "<pre>";
echo "PHP Version : " . phpversion() . "\n";
echo "Server Software : " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root : " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Filename : " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "</pre>";

// Vérifier la configuration du serveur web
echo "<h2>Configuration du serveur web :</h2>";
if (function_exists('apache_get_modules')) {
    echo "<p>Modules Apache chargés :</p>";
    echo "<ul>";
    $modules = apache_get_modules();
    foreach ($modules as $module) {
        echo "<li>" . htmlspecialchars($module) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Fonction apache_get_modules non disponible.</p>";
}

// Vérifier les redirections
echo "<h2>Test de redirection :</h2>";
echo "<p>Cliquez sur les liens suivants pour tester les redirections :</p>";
echo "<ul>";
echo "<li><a href='admin/' target='_blank'>Lien vers admin/</a></li>";
echo "<li><a href='admin/index.php' target='_blank'>Lien vers admin/index.php</a></li>";
echo "<li><a href='admin/admin_login.php' target='_blank'>Lien vers admin/admin_login.php</a></li>";
echo "</ul>";
?>
