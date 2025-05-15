<?php
// Script pour vérifier et corriger les problèmes de session et de cookies
require_once __DIR__ . '/includes/config.php';

// Fonction pour vérifier si un fichier existe et est accessible en écriture
function checkFileWritable($path) {
    if (file_exists($path)) {
        if (is_writable($path)) {
            return true;
        } else {
            return false;
        }
    } else {
        // Le fichier n'existe pas, vérifions si le répertoire parent est accessible en écriture
        $dir = dirname($path);
        if (is_dir($dir) && is_writable($dir)) {
            return true;
        } else {
            return false;
        }
    }
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vérification des sessions et cookies</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Vérification des sessions et cookies</h1>

<?php
// 1. Vérification de la configuration PHP pour les sessions
echo "<h2>Configuration PHP pour les sessions</h2>";

echo "<table>";
echo "<tr><th>Paramètre</th><th>Valeur</th><th>Recommandation</th><th>Statut</th></tr>";

// session.save_path
$session_save_path = ini_get('session.save_path');
$session_save_path_writable = !empty($session_save_path) && is_dir($session_save_path) && is_writable($session_save_path);
echo "<tr>";
echo "<td>session.save_path</td>";
echo "<td>" . (empty($session_save_path) ? "<em>Non défini</em>" : $session_save_path) . "</td>";
echo "<td>Doit être défini et accessible en écriture</td>";
echo "<td>" . ($session_save_path_writable ? "<span class='success'>OK</span>" : "<span class='error'>Problème</span>") . "</td>";
echo "</tr>";

// session.use_cookies
$session_use_cookies = ini_get('session.use_cookies');
echo "<tr>";
echo "<td>session.use_cookies</td>";
echo "<td>" . ($session_use_cookies ? "Activé" : "Désactivé") . "</td>";
echo "<td>Doit être activé</td>";
echo "<td>" . ($session_use_cookies ? "<span class='success'>OK</span>" : "<span class='error'>Problème</span>") . "</td>";
echo "</tr>";

// session.use_only_cookies
$session_use_only_cookies = ini_get('session.use_only_cookies');
echo "<tr>";
echo "<td>session.use_only_cookies</td>";
echo "<td>" . ($session_use_only_cookies ? "Activé" : "Désactivé") . "</td>";
echo "<td>Doit être activé pour la sécurité</td>";
echo "<td>" . ($session_use_only_cookies ? "<span class='success'>OK</span>" : "<span class='error'>Problème</span>") . "</td>";
echo "</tr>";

// session.cookie_secure
$session_cookie_secure = ini_get('session.cookie_secure');
$is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
echo "<tr>";
echo "<td>session.cookie_secure</td>";
echo "<td>" . ($session_cookie_secure ? "Activé" : "Désactivé") . "</td>";
echo "<td>Doit être activé en production avec HTTPS</td>";
echo "<td>";
if ($is_https && !$session_cookie_secure) {
    echo "<span class='error'>Problème - HTTPS détecté mais cookies non sécurisés</span>";
} elseif (!$is_https && $session_cookie_secure) {
    echo "<span class='warning'>Attention - Cookies sécurisés mais pas de HTTPS</span>";
} elseif ($is_https && $session_cookie_secure) {
    echo "<span class='success'>OK</span>";
} else {
    echo "<span class='warning'>Non sécurisé - Activez HTTPS en production</span>";
}
echo "</td>";
echo "</tr>";

// session.cookie_httponly
$session_cookie_httponly = ini_get('session.cookie_httponly');
echo "<tr>";
echo "<td>session.cookie_httponly</td>";
echo "<td>" . ($session_cookie_httponly ? "Activé" : "Désactivé") . "</td>";
echo "<td>Doit être activé pour la sécurité</td>";
echo "<td>" . ($session_cookie_httponly ? "<span class='success'>OK</span>" : "<span class='error'>Problème</span>") . "</td>";
echo "</tr>";

// session.cookie_samesite
$session_cookie_samesite = ini_get('session.cookie_samesite');
echo "<tr>";
echo "<td>session.cookie_samesite</td>";
echo "<td>" . (empty($session_cookie_samesite) ? "<em>Non défini</em>" : $session_cookie_samesite) . "</td>";
echo "<td>Recommandé: 'Lax' ou 'Strict'</td>";
echo "<td>";
if (empty($session_cookie_samesite)) {
    echo "<span class='warning'>Non défini</span>";
} elseif ($session_cookie_samesite === 'Lax' || $session_cookie_samesite === 'Strict') {
    echo "<span class='success'>OK</span>";
} else {
    echo "<span class='warning'>Valeur non recommandée</span>";
}
echo "</td>";
echo "</tr>";

// session.gc_maxlifetime
$session_gc_maxlifetime = ini_get('session.gc_maxlifetime');
echo "<tr>";
echo "<td>session.gc_maxlifetime</td>";
echo "<td>" . $session_gc_maxlifetime . " secondes</td>";
echo "<td>Recommandé: 1440-86400 secondes (24min-24h)</td>";
echo "<td>";
if ($session_gc_maxlifetime < 1440) {
    echo "<span class='warning'>Trop court</span>";
} elseif ($session_gc_maxlifetime > 86400) {
    echo "<span class='warning'>Trop long</span>";
} else {
    echo "<span class='success'>OK</span>";
}
echo "</td>";
echo "</tr>";

echo "</table>";

// 2. Test de création de session
echo "<h2>Test de création de session</h2>";

// Démarrer une session de test
if (!isset($_SESSION)) {
    session_start();
}

$_SESSION['test_session'] = 'Test de session ' . time();

echo "<p>Tentative de création d'une session de test...</p>";

if (isset($_SESSION['test_session'])) {
    echo "<p class='success'>Session créée avec succès. Valeur: " . $_SESSION['test_session'] . "</p>";
} else {
    echo "<p class='error'>Échec de la création de session.</p>";
}

// 3. Test de création de cookie
echo "<h2>Test de création de cookie</h2>";

$cookie_name = "test_cookie";
$cookie_value = "Test de cookie " . time();
$cookie_expiry = time() + 3600; // 1 heure

// Tentative de création d'un cookie
setcookie($cookie_name, $cookie_value, $cookie_expiry, "/");

echo "<p>Tentative de création d'un cookie de test...</p>";
echo "<p>Note: Le cookie ne sera visible qu'au prochain chargement de la page.</p>";

// Vérifier si des cookies sont déjà définis
if (!empty($_COOKIE)) {
    echo "<p class='success'>Des cookies sont déjà définis sur ce domaine.</p>";
    echo "<p>Cookies existants:</p>";
    echo "<ul>";
    foreach ($_COOKIE as $name => $value) {
        echo "<li>" . htmlspecialchars($name) . " = " . htmlspecialchars(substr($value, 0, 30)) . (strlen($value) > 30 ? "..." : "") . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='warning'>Aucun cookie n'est actuellement défini. Rechargez la page pour voir si le cookie de test a été créé.</p>";
}

// 4. Recommandations pour la gestion des sessions
echo "<h2>Recommandations pour la gestion des sessions</h2>";

echo "<h3>Bonnes pratiques pour la configuration des sessions</h3>";
echo "<ul>";
echo "<li>Utilisez <code>session_regenerate_id(true)</code> après la connexion pour éviter les attaques de fixation de session.</li>";
echo "<li>Définissez un délai d'expiration de session approprié (1-24 heures).</li>";
echo "<li>Activez les cookies sécurisés en production (HTTPS).</li>";
echo "<li>Utilisez l'attribut HttpOnly pour les cookies de session.</li>";
echo "<li>Définissez l'attribut SameSite à 'Lax' ou 'Strict' pour les cookies.</li>";
echo "</ul>";

echo "<h3>Exemple de code pour une gestion sécurisée des sessions</h3>";
echo "<pre>";
echo "<?php\n";
echo "// Configuration recommandée pour les sessions\n";
echo "ini_set('session.cookie_httponly', 1);\n";
echo "ini_set('session.use_only_cookies', 1);\n";
echo "if (isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] === 'on') {\n";
echo "    ini_set('session.cookie_secure', 1);\n";
echo "}\n";
echo "ini_set('session.cookie_samesite', 'Lax');\n";
echo "ini_set('session.gc_maxlifetime', 3600); // 1 heure\n\n";
echo "// Démarrer la session\n";
echo "session_start();\n\n";
echo "// Après une connexion réussie\n";
echo "function login_user(\$user_id) {\n";
echo "    // Régénérer l'ID de session pour éviter la fixation de session\n";
echo "    session_regenerate_id(true);\n";
echo "    \$_SESSION['user_id'] = \$user_id;\n";
echo "    \$_SESSION['last_activity'] = time();\n";
echo "    \$_SESSION['ip_address'] = \$_SERVER['REMOTE_ADDR'];\n";
echo "    \$_SESSION['user_agent'] = \$_SERVER['HTTP_USER_AGENT'];\n";
echo "}\n\n";
echo "// Vérification de la session à chaque requête\n";
echo "function verify_session() {\n";
echo "    // Vérifier si la session existe\n";
echo "    if (!isset(\$_SESSION['user_id'])) {\n";
echo "        return false;\n";
echo "    }\n\n";
echo "    // Vérifier l'expiration de la session\n";
echo "    \$max_lifetime = 3600; // 1 heure\n";
echo "    if (time() - \$_SESSION['last_activity'] > \$max_lifetime) {\n";
echo "        session_unset();\n";
echo "        session_destroy();\n";
echo "        return false;\n";
echo "    }\n\n";
echo "    // Vérifier que l'IP et le User-Agent n'ont pas changé\n";
echo "    if (\$_SESSION['ip_address'] !== \$_SERVER['REMOTE_ADDR'] || \n";
echo "        \$_SESSION['user_agent'] !== \$_SERVER['HTTP_USER_AGENT']) {\n";
echo "        session_unset();\n";
echo "        session_destroy();\n";
echo "        return false;\n";
echo "    }\n\n";
echo "    // Mettre à jour le timestamp de dernière activité\n";
echo "    \$_SESSION['last_activity'] = time();\n";
echo "    return true;\n";
echo "}\n\n";
echo "// Déconnexion sécurisée\n";
echo "function logout_user() {\n";
echo "    // Détruire toutes les données de session\n";
echo "    \$_SESSION = array();\n\n";
echo "    // Détruire le cookie de session si utilisé\n";
echo "    if (ini_get('session.use_cookies')) {\n";
echo "        \$params = session_get_cookie_params();\n";
echo "        setcookie(session_name(), '', time() - 42000,\n";
echo "            \$params['path'], \$params['domain'],\n";
echo "            \$params['secure'], \$params['httponly']);\n";
echo "    }\n\n";
echo "    // Détruire la session\n";
echo "    session_destroy();\n";
echo "}\n";
echo "?>";
echo "</pre>";

// 5. Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='fix_db_security.php'>Sécurité de la base de données</a></li>";
echo "<li><a href='fix_db_performance.php'>Optimisation des performances</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
