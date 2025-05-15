<?php
// Script pour tester les redirections sur Render
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers de configuration
require_once 'includes/config.php';

// Fonction pour tester une redirection
function testRedirection($url, $method = 'GET', $data = []) {
    $ch = curl_init();
    
    // Configuration de cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Ne pas suivre les redirections
    
    // Si c'est une requête POST
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    // Analyser la réponse
    $header_size = $info['header_size'];
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    
    // Vérifier si c'est une redirection
    $is_redirect = ($info['http_code'] >= 300 && $info['http_code'] < 400);
    
    // Extraire l'URL de redirection si c'est une redirection
    $redirect_url = '';
    if ($is_redirect) {
        preg_match('/Location: (.*?)\r\n/i', $header, $matches);
        $redirect_url = isset($matches[1]) ? $matches[1] : '';
    }
    
    return [
        'url' => $url,
        'method' => $method,
        'http_code' => $info['http_code'],
        'is_redirect' => $is_redirect,
        'redirect_url' => $redirect_url,
        'total_time' => $info['total_time'],
        'error' => $error,
        'header' => $header,
        'body' => $body
    ];
}

// Liste des URLs à tester
$urls_to_test = [
    // Pages principales
    ['url' => 'index.php', 'description' => 'Page d\'accueil'],
    ['url' => 'login.php', 'description' => 'Page de connexion'],
    ['url' => 'register.php', 'description' => 'Page d\'inscription'],
    ['url' => 'profile.php', 'description' => 'Page de profil (devrait rediriger si non connecté)'],
    ['url' => 'admin/index.php', 'description' => 'Page d\'administration (devrait rediriger si non connecté)'],
    
    // Pages avec redirections connues
    ['url' => 'logout.php', 'description' => 'Déconnexion (devrait rediriger)'],
    ['url' => 'admin/admin_logout.php', 'description' => 'Déconnexion admin (devrait rediriger)']
];

// Construire l'URL de base
$base_url = isset($_SERVER['HTTP_HOST']) ? 
    ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'])) : 
    'http://localhost';

// Si le chemin se termine par le nom du script, on remonte d'un niveau
if (substr($base_url, -strlen(basename($_SERVER['PHP_SELF']))) === basename($_SERVER['PHP_SELF'])) {
    $base_url = dirname($base_url);
}

// S'assurer que l'URL se termine par un slash
if (substr($base_url, -1) !== '/') {
    $base_url .= '/';
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test des redirections</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
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
        pre {
            background-color: #f8f8f8;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
            max-height: 200px;
            font-size: 12px;
        }
        .details-toggle {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
        .details {
            display: none;
            margin-top: 10px;
        }
    </style>
    <script>
        function toggleDetails(id) {
            var details = document.getElementById(id);
            if (details.style.display === "none" || details.style.display === "") {
                details.style.display = "block";
            } else {
                details.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Test des redirections</h1>
        
        <h2>Informations sur l'environnement</h2>
        <ul>
            <li><strong>PHP Version:</strong> <?php echo phpversion(); ?></li>
            <li><strong>Environnement:</strong> <?php echo getenv('ENVIRONMENT') ?: 'Non défini'; ?></li>
            <li><strong>Serveur:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Non défini'; ?></li>
            <li><strong>URL de l'application:</strong> <?php echo getenv('APP_URL') ?: 'Non défini'; ?></li>
            <li><strong>URL de base pour les tests:</strong> <?php echo $base_url; ?></li>
        </ul>
        
        <h2>Résultats des tests de redirection</h2>
        <table>
            <tr>
                <th>URL</th>
                <th>Description</th>
                <th>Code HTTP</th>
                <th>Redirection</th>
                <th>URL de redirection</th>
                <th>Temps (s)</th>
                <th>Détails</th>
            </tr>
            <?php foreach ($urls_to_test as $index => $url_info): ?>
                <?php 
                    $full_url = $base_url . $url_info['url'];
                    $result = testRedirection($full_url);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($url_info['url']); ?></td>
                    <td><?php echo htmlspecialchars($url_info['description']); ?></td>
                    <td><?php echo $result['http_code']; ?></td>
                    <td>
                        <?php if ($result['is_redirect']): ?>
                            <span class="success">Oui</span>
                        <?php else: ?>
                            <span class="warning">Non</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($result['redirect_url']); ?></td>
                    <td><?php echo round($result['total_time'], 3); ?></td>
                    <td>
                        <span class="details-toggle" onclick="toggleDetails('details-<?php echo $index; ?>')">Voir détails</span>
                        <div id="details-<?php echo $index; ?>" class="details">
                            <h4>En-têtes HTTP</h4>
                            <pre><?php echo htmlspecialchars($result['header']); ?></pre>
                            
                            <?php if ($result['error']): ?>
                                <h4>Erreur</h4>
                                <pre><?php echo htmlspecialchars($result['error']); ?></pre>
                            <?php endif; ?>
                            
                            <h4>Contenu (partiel)</h4>
                            <pre><?php echo htmlspecialchars(substr($result['body'], 0, 500)) . (strlen($result['body']) > 500 ? '...' : ''); ?></pre>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Recommandations pour les redirections</h2>
        <div class="info">
            <p>Pour éviter les problèmes de "headers already sent", utilisez des redirections JavaScript au lieu de la fonction PHP <code>header()</code> :</p>
            <pre>
// Au lieu de :
header('Location: page.php');
exit;

// Utilisez :
echo "&lt;script>window.location.href = 'page.php';&lt;/script>";
die();
            </pre>
            
            <p>Pour plus d'informations sur la correction des redirections, consultez le script <a href="fix_redirections.php">fix_redirections.php</a>.</p>
        </div>
        
        <p>
            <a href="index.php" class="btn">Retour à l'accueil</a>
        </p>
    </div>
</body>
</html>
