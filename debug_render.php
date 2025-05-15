<?php
// Script pour déboguer les problèmes de déploiement sur Render
// Ce script n'utilise pas la connexion à la base de données pour éviter les erreurs

// Fonction pour vérifier si un fichier ou un dossier existe et est accessible
function checkPath($path, $type = 'file') {
    $result = [
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'writable' => is_writable($path),
        'type' => $type,
        'path' => $path
    ];
    
    if ($type == 'directory' && $result['exists']) {
        $result['is_dir'] = is_dir($path);
        if ($result['is_dir'] && $result['readable']) {
            try {
                $result['contents'] = scandir($path);
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
        }
    }
    
    return $result;
}

// Chemins importants à vérifier
$paths = [
    'root' => [
        'path' => __DIR__,
        'type' => 'directory'
    ],
    'admin' => [
        'path' => __DIR__ . '/admin',
        'type' => 'directory'
    ],
    'includes' => [
        'path' => __DIR__ . '/includes',
        'type' => 'directory'
    ],
    'config' => [
        'path' => __DIR__ . '/includes/config.php',
        'type' => 'file'
    ],
    'db_connect' => [
        'path' => __DIR__ . '/includes/db_connect.php',
        'type' => 'file'
    ],
    'admin_login' => [
        'path' => __DIR__ . '/admin/admin_login.php',
        'type' => 'file'
    ],
    'init_db' => [
        'path' => __DIR__ . '/admin/init_db.php',
        'type' => 'file'
    ]
];

// Vérifier les chemins
$results = [];
foreach ($paths as $key => $info) {
    $results[$key] = checkPath($info['path'], $info['type']);
}

// Vérifier les variables d'environnement
$env_vars = [
    'DATABASE_URL',
    'APP_URL',
    'ENVIRONMENT',
    'RENDER_EXTERNAL_URL',
    'RENDER_SERVICE_ID',
    'RENDER_SERVICE_NAME',
    'RENDER_SERVICE_TYPE',
    'PORT'
];

$env_results = [];
foreach ($env_vars as $var) {
    $env_results[$var] = [
        'defined' => getenv($var) !== false,
        'value' => getenv($var) !== false ? (strpos($var, 'DATABASE') !== false ? 'HIDDEN' : getenv($var)) : null
    ];
}

// Vérifier les extensions PHP
$required_extensions = [
    'pdo',
    'pdo_pgsql',
    'pdo_mysql',
    'json',
    'mbstring',
    'openssl'
];

$extensions_results = [];
$loaded_extensions = get_loaded_extensions();
foreach ($required_extensions as $ext) {
    $extensions_results[$ext] = in_array($ext, $loaded_extensions);
}

// Vérifier les permissions
$permissions = [
    'document_root' => [
        'path' => $_SERVER['DOCUMENT_ROOT'],
        'perms' => substr(sprintf('%o', fileperms($_SERVER['DOCUMENT_ROOT'])), -4)
    ],
    'current_dir' => [
        'path' => __DIR__,
        'perms' => substr(sprintf('%o', fileperms(__DIR__)), -4)
    ]
];

if (is_dir(__DIR__ . '/admin')) {
    $permissions['admin_dir'] = [
        'path' => __DIR__ . '/admin',
        'perms' => substr(sprintf('%o', fileperms(__DIR__ . '/admin')), -4)
    ];
}

// Vérifier la configuration PHP
$php_config = [
    'display_errors' => ini_get('display_errors'),
    'error_reporting' => ini_get('error_reporting'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'allow_url_fopen' => ini_get('allow_url_fopen')
];

// Afficher les résultats
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Débogage Render - LawApp</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        .status-ok { background-color: #d4edda; }
        .status-error { background-color: #f8d7da; }
        .status-warning { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Débogage Render - LawApp</h1>
        <p>Ce script affiche des informations de débogage pour aider à résoudre les problèmes de déploiement sur Render.</p>
        
        <h2>Informations sur le serveur</h2>
        <table>
            <tr>
                <th>Paramètre</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td>Server Software</td>
                <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
            </tr>
            <tr>
                <td>Document Root</td>
                <td><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td>
            </tr>
            <tr>
                <td>Script Filename</td>
                <td><?php echo $_SERVER['SCRIPT_FILENAME']; ?></td>
            </tr>
            <tr>
                <td>Request URI</td>
                <td><?php echo $_SERVER['REQUEST_URI']; ?></td>
            </tr>
            <tr>
                <td>Server Name</td>
                <td><?php echo $_SERVER['SERVER_NAME']; ?></td>
            </tr>
            <tr>
                <td>Server Port</td>
                <td><?php echo $_SERVER['SERVER_PORT']; ?></td>
            </tr>
            <tr>
                <td>HTTPS</td>
                <td><?php echo isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off'; ?></td>
            </tr>
            <tr>
                <td>Remote Address</td>
                <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
            </tr>
        </table>
        
        <h2>Vérification des chemins</h2>
        <table>
            <tr>
                <th>Chemin</th>
                <th>Type</th>
                <th>Existe</th>
                <th>Lisible</th>
                <th>Inscriptible</th>
            </tr>
            <?php foreach ($results as $key => $result): ?>
            <tr class="<?php echo $result['exists'] && $result['readable'] ? 'status-ok' : 'status-error'; ?>">
                <td><?php echo $result['path']; ?></td>
                <td><?php echo $result['type']; ?></td>
                <td><?php echo $result['exists'] ? '✓' : '✗'; ?></td>
                <td><?php echo $result['readable'] ? '✓' : '✗'; ?></td>
                <td><?php echo $result['writable'] ? '✓' : '✗'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Contenu des dossiers</h2>
        <?php foreach ($results as $key => $result): ?>
            <?php if ($result['type'] == 'directory' && $result['exists'] && $result['readable'] && isset($result['contents'])): ?>
            <h3>Dossier : <?php echo $result['path']; ?></h3>
            <ul>
                <?php foreach ($result['contents'] as $item): ?>
                    <?php if ($item != '.' && $item != '..'): ?>
                    <li>
                        <?php echo $item; ?>
                        <?php 
                        $fullPath = $result['path'] . '/' . $item;
                        if (is_dir($fullPath)) echo ' (dossier)';
                        ?>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <h2>Variables d'environnement</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Définie</th>
                <th>Valeur</th>
            </tr>
            <?php foreach ($env_results as $var => $result): ?>
            <tr class="<?php echo $result['defined'] ? 'status-ok' : 'status-warning'; ?>">
                <td><?php echo $var; ?></td>
                <td><?php echo $result['defined'] ? '✓' : '✗'; ?></td>
                <td><?php echo $result['value']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Extensions PHP</h2>
        <table>
            <tr>
                <th>Extension</th>
                <th>Chargée</th>
            </tr>
            <?php foreach ($extensions_results as $ext => $loaded): ?>
            <tr class="<?php echo $loaded ? 'status-ok' : 'status-error'; ?>">
                <td><?php echo $ext; ?></td>
                <td><?php echo $loaded ? '✓' : '✗'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Permissions</h2>
        <table>
            <tr>
                <th>Chemin</th>
                <th>Permissions</th>
            </tr>
            <?php foreach ($permissions as $key => $info): ?>
            <tr>
                <td><?php echo $info['path']; ?></td>
                <td><?php echo $info['perms']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Configuration PHP</h2>
        <table>
            <tr>
                <th>Paramètre</th>
                <th>Valeur</th>
            </tr>
            <?php foreach ($php_config as $param => $value): ?>
            <tr>
                <td><?php echo $param; ?></td>
                <td><?php echo $value; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Liens de test</h2>
        <ul>
            <li><a href="index.php">Page d'accueil</a></li>
            <li><a href="test_render.php">Test de Render</a></li>
            <li><a href="check_admin_path.php">Vérification du chemin admin</a></li>
            <li><a href="fix_livres_table.php">Correction de la table livres</a></li>
            <li><a href="fix_podcasts_table.php">Correction de la table podcasts</a></li>
            <li><a href="fix_admin_table.php">Correction de la table administrateurs</a></li>
            <li><a href="admin/admin_login.php">Page de connexion admin</a></li>
        </ul>
    </div>
</body>
</html>
