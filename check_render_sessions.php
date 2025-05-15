<?php
// Script pour vérifier l'état des sessions sur Render
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers de configuration
require_once 'includes/config.php';

// Démarrer une session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Définir une variable de session pour le test
$_SESSION['render_test'] = 'Test session Render - ' . date('Y-m-d H:i:s');

// Définir un cookie pour le test
$cookie_name = "render_test_cookie";
$cookie_value = "Test cookie Render - " . date('Y-m-d H:i:s');
$cookie_expiry = time() + 3600; // 1 heure
setcookie($cookie_name, $cookie_value, $cookie_expiry, "/");

// Obtenir le chemin de stockage des sessions
$session_path = ini_get('session.save_path');
if (empty($session_path)) {
    $session_path = sys_get_temp_dir();
}

// Vérifier si le répertoire de session existe et est accessible en écriture
function checkDirectoryWritable($path) {
    if (is_dir($path)) {
        if (is_writable($path)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// Vérifier les permissions du fichier de session
function checkSessionFilePermissions() {
    $session_id = session_id();
    if (empty($session_id)) {
        return "Aucun ID de session";
    }
    
    $session_path = ini_get('session.save_path');
    if (empty($session_path)) {
        $session_path = sys_get_temp_dir();
    }
    
    $session_file = $session_path . '/sess_' . $session_id;
    
    if (file_exists($session_file)) {
        $perms = fileperms($session_file);
        $perms_string = substr(sprintf('%o', $perms), -4);
        return "Fichier trouvé, permissions: " . $perms_string;
    } else {
        return "Fichier de session non trouvé";
    }
}

// Vérifier les variables d'environnement liées à Render
function checkRenderEnvironment() {
    $render_vars = array(
        'RENDER_EXTERNAL_URL',
        'RENDER_INTERNAL_URL',
        'RENDER_SERVICE_ID',
        'RENDER_SERVICE_NAME',
        'RENDER_GIT_BRANCH',
        'RENDER_GIT_COMMIT',
        'RENDER_INSTANCE_ID'
    );
    
    $results = array();
    
    foreach ($render_vars as $var) {
        $results[$var] = getenv($var) ?: 'Non défini';
    }
    
    return $results;
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vérification des sessions sur Render</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { background-color: #f0f0f0; padding: 10px; border-radius: 5px; margin: 10px 0; }
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
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Vérification des sessions sur Render</h1>
        
        <h2>Informations sur l'environnement Render</h2>
        <?php $render_env = checkRenderEnvironment(); ?>
        <table>
            <tr>
                <th>Variable</th>
                <th>Valeur</th>
            </tr>
            <?php foreach ($render_env as $var => $value): ?>
            <tr>
                <td><?php echo htmlspecialchars($var); ?></td>
                <td><?php echo htmlspecialchars($value); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Informations sur la session</h2>
        <table>
            <tr>
                <th>Paramètre</th>
                <th>Valeur</th>
                <th>Statut</th>
            </tr>
            <tr>
                <td>ID de session</td>
                <td><?php echo session_id(); ?></td>
                <td>
                    <?php if (session_id()): ?>
                        <span class="success">OK</span>
                    <?php else: ?>
                        <span class="error">Erreur: Aucun ID de session</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Chemin de stockage des sessions</td>
                <td><?php echo $session_path; ?></td>
                <td>
                    <?php if (checkDirectoryWritable($session_path)): ?>
                        <span class="success">OK - Accessible en écriture</span>
                    <?php else: ?>
                        <span class="error">Erreur: Répertoire non accessible en écriture</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Fichier de session</td>
                <td colspan="2"><?php echo checkSessionFilePermissions(); ?></td>
            </tr>
            <tr>
                <td>Durée de vie de la session</td>
                <td><?php echo ini_get('session.gc_maxlifetime'); ?> secondes</td>
                <td>
                    <?php 
                    $lifetime = ini_get('session.gc_maxlifetime');
                    if ($lifetime < 60) {
                        echo '<span class="error">Trop court</span>';
                    } elseif ($lifetime > 86400) {
                        echo '<span class="warning">Très long</span>';
                    } else {
                        echo '<span class="success">OK</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Cookies de session</td>
                <td><?php echo ini_get('session.use_cookies') ? 'Activé' : 'Désactivé'; ?></td>
                <td>
                    <?php if (ini_get('session.use_cookies')): ?>
                        <span class="success">OK</span>
                    <?php else: ?>
                        <span class="error">Erreur: Cookies de session désactivés</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Cookie SameSite</td>
                <td><?php echo ini_get('session.cookie_samesite') ?: 'Non défini'; ?></td>
                <td>
                    <?php 
                    $samesite = ini_get('session.cookie_samesite');
                    if (empty($samesite)) {
                        echo '<span class="warning">Non défini</span>';
                    } elseif ($samesite === 'Lax' || $samesite === 'Strict') {
                        echo '<span class="success">OK</span>';
                    } else {
                        echo '<span class="warning">Valeur non recommandée</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Cookie Secure</td>
                <td><?php echo ini_get('session.cookie_secure') ? 'Activé' : 'Désactivé'; ?></td>
                <td>
                    <?php 
                    $secure = ini_get('session.cookie_secure');
                    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
                    
                    if ($https && !$secure) {
                        echo '<span class="error">HTTPS détecté mais cookies non sécurisés</span>';
                    } elseif (!$https && $secure) {
                        echo '<span class="warning">Cookies sécurisés mais pas de HTTPS</span>';
                    } elseif ($https && $secure) {
                        echo '<span class="success">OK</span>';
                    } else {
                        echo '<span class="warning">Non sécurisé</span>';
                    }
                    ?>
                </td>
            </tr>
        </table>
        
        <h2>Variables de session</h2>
        <?php if (!empty($_SESSION)): ?>
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Valeur</th>
                </tr>
                <?php foreach ($_SESSION as $key => $value): ?>
                <tr>
                    <td><?php echo htmlspecialchars($key); ?></td>
                    <td><?php echo htmlspecialchars(is_array($value) ? 'Array' : $value); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="warning">Aucune variable de session n'est définie.</p>
        <?php endif; ?>
        
        <h2>Cookies</h2>
        <?php if (!empty($_COOKIE)): ?>
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Valeur</th>
                </tr>
                <?php foreach ($_COOKIE as $key => $value): ?>
                <tr>
                    <td><?php echo htmlspecialchars($key); ?></td>
                    <td><?php echo htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <p class="info">Note: Le cookie de test que nous venons de créer ne sera visible qu'au prochain chargement de la page.</p>
        <?php else: ?>
            <p class="warning">Aucun cookie n'est défini. Rechargez la page pour voir le cookie de test.</p>
        <?php endif; ?>
        
        <h2>Test de persistance de session</h2>
        <p>Rechargez cette page pour vérifier si les données de session persistent.</p>
        
        <div class="info">
            <p>Valeur de session de test: <strong><?php echo isset($_SESSION['render_test']) ? htmlspecialchars($_SESSION['render_test']) : 'Non définie'; ?></strong></p>
            <p>Cookie de test: <strong><?php echo isset($_COOKIE[$cookie_name]) ? htmlspecialchars($_COOKIE[$cookie_name]) : 'Non défini (sera visible au prochain chargement)'; ?></strong></p>
        </div>
        
        <h2>Informations sur le serveur</h2>
        <table>
            <tr>
                <th>Paramètre</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Système d'exploitation</td>
                <td><?php echo PHP_OS; ?></td>
            </tr>
            <tr>
                <td>Version PHP</td>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <td>Serveur Web</td>
                <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Non disponible'; ?></td>
            </tr>
            <tr>
                <td>Protocole</td>
                <td><?php echo isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS' : 'HTTP'; ?></td>
            </tr>
            <tr>
                <td>Hôte</td>
                <td><?php echo $_SERVER['HTTP_HOST'] ?? 'Non disponible'; ?></td>
            </tr>
            <tr>
                <td>Adresse IP du serveur</td>
                <td><?php echo $_SERVER['SERVER_ADDR'] ?? 'Non disponible'; ?></td>
            </tr>
            <tr>
                <td>Répertoire temporaire</td>
                <td><?php echo sys_get_temp_dir(); ?></td>
            </tr>
        </table>
        
        <h2>Actions</h2>
        <p>
            <a href="check_render_sessions.php" class="btn">Recharger la page</a>
            <a href="check_render_sessions.php?clear=1" class="btn" style="background-color: #f44336;">Effacer la session</a>
            <a href="index.php" class="btn" style="background-color: #2196F3;">Retour à l'accueil</a>
        </p>
        
        <?php
        // Effacer la session si demandé
        if (isset($_GET['clear'])) {
            // Détruire toutes les données de session
            $_SESSION = array();
            
            // Détruire le cookie de session si utilisé
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']);
            }
            
            // Détruire la session
            session_destroy();
            
            echo '<script>window.location.href = "check_render_sessions.php";</script>';
            exit;
        }
        ?>
    </div>
</body>
</html>
