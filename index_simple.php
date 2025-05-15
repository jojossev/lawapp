<?php
// Page d'accueil simplifiée pour tester le déploiement

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir l'URL de base
$base_url = getenv('APP_URL') ?: 'http://localhost';
$base_url = rtrim($base_url, '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LawApp - Accueil</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem;
            text-align: center;
        }
        h1 {
            margin: 0;
        }
        .content {
            background-color: white;
            padding: 2rem;
            margin-top: 2rem;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        footer {
            text-align: center;
            margin-top: 2rem;
            padding: 1rem;
            background-color: #2c3e50;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>LawApp</h1>
            <p>Votre plateforme d'apprentissage juridique</p>
        </div>
    </header>

    <div class="container">
        <div class="content">
            <h2>Bienvenue sur LawApp</h2>
            <p>Cette page est une version simplifiée pour tester le déploiement.</p>
            
            <h3>Informations de débogage</h3>
            <p>URL de base: <?php echo $base_url; ?></p>
            <p>Environnement: <?php echo getenv('ENVIRONMENT') ?: 'Non défini'; ?></p>
            
            <h3>Liens</h3>
            <ul>
                <li><a href="test.php">Test PHP Info</a></li>
                <li><a href="debug.php">Page de débogage</a></li>
                <li><a href="admin/init_db.php">Initialiser la base de données</a></li>
            </ul>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> LawApp. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
