<?php
// Script simple pour tester si le serveur Render fonctionne correctement
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Render</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test de Render</h1>
        
        <p>Si vous voyez cette page, cela signifie que le serveur PHP de Render fonctionne correctement.</p>
        
        <h2>Informations sur le serveur :</h2>
        <pre>
PHP Version : <?php echo phpversion(); ?>

Server Software : <?php echo $_SERVER['SERVER_SOFTWARE']; ?>

Document Root : <?php echo $_SERVER['DOCUMENT_ROOT']; ?>

Script Filename : <?php echo $_SERVER['SCRIPT_FILENAME']; ?>

Request URI : <?php echo $_SERVER['REQUEST_URI']; ?>

Server Name : <?php echo $_SERVER['SERVER_NAME']; ?>

Server Port : <?php echo $_SERVER['SERVER_PORT']; ?>

HTTPS : <?php echo isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off'; ?>

Remote Address : <?php echo $_SERVER['REMOTE_ADDR']; ?>
        </pre>
        
        <h2>Variables d'environnement :</h2>
        <pre>
DATABASE_URL : <?php echo getenv('DATABASE_URL') ? 'Défini' : 'Non défini'; ?>

APP_URL : <?php echo getenv('APP_URL') ? getenv('APP_URL') : 'Non défini'; ?>

ENVIRONMENT : <?php echo getenv('ENVIRONMENT') ? getenv('ENVIRONMENT') : 'Non défini'; ?>
        </pre>
        
        <h2>Extensions PHP chargées :</h2>
        <pre>
<?php 
$extensions = get_loaded_extensions();
sort($extensions);
echo implode(", ", $extensions);
?>
        </pre>
        
        <h2>Liens de test :</h2>
        <ul>
            <li><a href="index.php">Page d'accueil</a></li>
            <li><a href="check_admin_path.php">Vérification du chemin admin</a></li>
            <li><a href="fix_livres_table.php">Correction de la table livres</a></li>
            <li><a href="fix_podcasts_table.php">Correction de la table podcasts</a></li>
            <li><a href="fix_admin_table.php">Correction de la table administrateurs</a></li>
            <li><a href="admin/admin_login.php">Page de connexion admin</a></li>
        </ul>
    </div>
</body>
</html>
