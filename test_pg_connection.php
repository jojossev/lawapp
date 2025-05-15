<?php
// Script pour tester la connexion à la base de données PostgreSQL sur Render
$page_title = "Test de connexion PostgreSQL";
$extra_css = "<style>
    .container { max-width: 800px; margin: 0 auto; padding: 20px; }
    .card { background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
    .btn { display: inline-block; background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin: 5px; }
</style>";

// Désactiver l'affichage des erreurs pour éviter de révéler des informations sensibles
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Fonction pour masquer les informations sensibles dans les messages d'erreur
function sanitizeErrorMessage($message) {
    // Masquer les mots de passe dans les chaînes de connexion
    $message = preg_replace('/password=([^;& ]+)/', 'password=***', $message);
    $message = preg_replace('/pwd=([^;& ]+)/', 'pwd=***', $message);
    $message = preg_replace('/:([^:@]+)@/', ':***@', $message);
    
    return $message;
}

// Fonction pour tester la connexion à PostgreSQL
function testPgConnection($database_url) {
    try {
        // Extraire les informations de connexion de l'URL
        $url = parse_url($database_url);
        $host = $url['host'] ?? 'localhost';
        $port = $url['port'] ?? '5432';
        $dbname = ltrim($url['path'] ?? '', '/');
        $user = $url['user'] ?? '';
        $password = $url['pass'] ?? '';
        
        // Construire le DSN pour PostgreSQL
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        
        // Tenter de se connecter
        $start_time = microtime(true);
        $pdo = new PDO($dsn, $user, $password);
        $end_time = microtime(true);
        $connection_time = round(($end_time - $start_time) * 1000, 2); // en millisecondes
        
        // Configurer PDO pour qu'il lance des exceptions en cas d'erreur
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Vérifier la version de PostgreSQL
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetchColumn();
        
        // Tester une requête simple
        $stmt = $pdo->query("SELECT current_timestamp");
        $timestamp = $stmt->fetchColumn();
        
        // Récupérer la liste des tables
        $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return [
            'success' => true,
            'message' => "Connexion à PostgreSQL établie avec succès",
            'version' => $version,
            'timestamp' => $timestamp,
            'tables' => $tables,
            'connection_time' => $connection_time
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => "Erreur de connexion à PostgreSQL : " . sanitizeErrorMessage($e->getMessage()),
            'code' => $e->getCode()
        ];
    }
}

// Récupérer l'URL de la base de données depuis les variables d'environnement
$database_url = getenv('DATABASE_URL');

// Si DATABASE_URL n'est pas défini, utiliser une valeur par défaut pour les tests
if (empty($database_url)) {
    $database_url = "postgresql://postgres:password@localhost:5432/lawapp";
}

// Tester la connexion
$result = testPgConnection($database_url);

// Début du HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <?php echo $extra_css; ?>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Test de connexion à PostgreSQL</h1>
            
            <h2>Informations sur l'environnement</h2>
            <ul>
                <li><strong>PHP Version:</strong> <?php echo phpversion(); ?></li>
                <li><strong>Extensions PDO:</strong> <?php echo implode(', ', PDO::getAvailableDrivers()); ?></li>
                <li><strong>Environnement:</strong> <?php echo getenv('ENVIRONMENT') ?: 'Non défini'; ?></li>
                <li><strong>Serveur:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Non défini'; ?></li>
                <li><strong>URL de l'application:</strong> <?php echo getenv('APP_URL') ?: 'Non défini'; ?></li>
                <li><strong>URL de la base de données:</strong> <?php 
                    $masked_url = preg_replace('/:([^:@]+)@/', ':***@', $database_url);
                    echo $masked_url; 
                ?></li>
            </ul>
            
            <h2>Résultat du test de connexion</h2>
            <?php if ($result['success']): ?>
                <p class="success"><?php echo $result['message']; ?> en <?php echo $result['connection_time']; ?> ms</p>
                <h3>Version de PostgreSQL</h3>
                <pre><?php echo $result['version']; ?></pre>
                
                <h3>Horodatage du serveur</h3>
                <pre><?php echo $result['timestamp']; ?></pre>
                
                <h3>Tables disponibles (<?php echo count($result['tables']); ?>)</h3>
                <?php if (empty($result['tables'])): ?>
                    <p class="warning">Aucune table n'a été trouvée dans la base de données.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($result['tables'] as $table): ?>
                            <li><?php echo $table; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <h3>Test de requête simple</h3>
                <?php
                try {
                    $pdo = new PDO($dsn, $user, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Tester une requête sur la table administrateurs si elle existe
                    if (in_array('administrateurs', $result['tables'])) {
                        $stmt = $pdo->query("SELECT COUNT(*) FROM administrateurs");
                        $count = $stmt->fetchColumn();
                        echo "<p>Nombre d'administrateurs : $count</p>";
                    } else {
                        echo "<p class='warning'>La table 'administrateurs' n'existe pas.</p>";
                    }
                    
                    // Tester une requête sur la table utilisateurs si elle existe
                    if (in_array('utilisateurs', $result['tables'])) {
                        $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
                        $count = $stmt->fetchColumn();
                        echo "<p>Nombre d'utilisateurs : $count</p>";
                    } else {
                        echo "<p class='warning'>La table 'utilisateurs' n'existe pas.</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>Erreur lors de l'exécution des requêtes : " . sanitizeErrorMessage($e->getMessage()) . "</p>";
                }
                ?>
            <?php else: ?>
                <p class="error"><?php echo $result['message']; ?></p>
                <p>Code d'erreur : <?php echo $result['code']; ?></p>
                
                <h3>Suggestions de résolution</h3>
                <ul>
                    <li>Vérifiez que l'extension PDO_PGSQL est activée dans PHP</li>
                    <li>Vérifiez que les informations de connexion sont correctes (hôte, port, nom de la base de données, utilisateur, mot de passe)</li>
                    <li>Vérifiez que la base de données existe et est accessible depuis ce serveur</li>
                    <li>Vérifiez les règles de pare-feu qui pourraient bloquer la connexion</li>
                    <li>Vérifiez que PostgreSQL est en cours d'exécution</li>
                </ul>
            <?php endif; ?>
            
            <h2>Actions</h2>
            <p>
                <a href="index.php" class="btn">Retour à l'accueil</a>
                <a href="fix_all_issues.php" class="btn">Corriger tous les problèmes</a>
                <a href="check_app_integrity.php" class="btn">Vérifier l'intégrité de l'application</a>
            </p>
        </div>
    </div>
</body>
</html>
