<?php
// Script pour tester les sessions et les cookies
require_once __DIR__ . '/includes/config.php';

// Démarrer une session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Définir une variable de session pour le test
$_SESSION['test_session_value'] = 'Session test créée le ' . date('Y-m-d H:i:s');

// Définir un cookie pour le test
$cookie_name = "test_cookie";
$cookie_value = "Cookie test créé le " . date('Y-m-d H:i:s');
$cookie_expiry = time() + 3600; // 1 heure
setcookie($cookie_name, $cookie_value, $cookie_expiry, "/");

// Fonction pour vérifier si un répertoire est accessible en écriture
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

// Obtenir le chemin de stockage des sessions
$session_path = ini_get('session.save_path');
if (empty($session_path)) {
    $session_path = sys_get_temp_dir();
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test des sessions et cookies</title>
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
        <h1>Test des sessions et cookies</h1>
        
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
            <p>Valeur de session de test: <strong><?php echo isset($_SESSION['test_session_value']) ? htmlspecialchars($_SESSION['test_session_value']) : 'Non définie'; ?></strong></p>
            <p>Cookie de test: <strong><?php echo isset($_COOKIE[$cookie_name]) ? htmlspecialchars($_COOKIE[$cookie_name]) : 'Non défini (sera visible au prochain chargement)'; ?></strong></p>
        </div>
        
        <h2>Actions</h2>
        <p>
            <a href="test_sessions.php" class="btn">Recharger la page</a>
            <a href="test_sessions.php?clear=1" class="btn" style="background-color: #f44336;">Effacer la session</a>
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
            
            echo '<script>window.location.href = "test_sessions.php";</script>';
            exit;
        }
        ?>
    </div>
</body>
</html>
