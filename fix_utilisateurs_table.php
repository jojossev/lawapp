<?php
// Script pour créer la table utilisateurs si elle n'existe pas
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Activer la mise en tampon de sortie au début du script
ob_start();

// Fonction pour afficher les messages
function showMessage($message, $type = 'info') {
    $class = ($type == 'success') ? 'success' : (($type == 'error') ? 'error' : 'info');
    echo "<div style='margin: 10px; padding: 10px; border-radius: 5px; background-color: " . 
        ($type == 'success' ? '#d4edda' : ($type == 'error' ? '#f8d7da' : '#d1ecf1')) . 
        "; color: " . 
        ($type == 'success' ? '#155724' : ($type == 'error' ? '#721c24' : '#0c5460')) . 
        ";'><strong>" . 
        ($type == 'success' ? 'Succès' : ($type == 'error' ? 'Erreur' : 'Info')) . 
        ":</strong> $message</div>";
    
    // Vider le tampon de sortie seulement s'il est actif
    if (ob_get_level() > 0) {
        @ob_flush();
        @flush();
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        $sql = "SELECT EXISTS (
            SELECT 1 FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = :table
        )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['table' => $table]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        showMessage("Erreur lors de la vérification de la table: " . $e->getMessage(), 'error');
        return false;
    }
}

// Connexion à la base de données
$database_url = getenv('DATABASE_URL');
if (empty($database_url)) {
    // En développement local, utiliser les constantes de configuration
    require_once 'includes/config.php';
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=lawapp", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbType = 'mysql';
        showMessage("Connexion à la base de données MySQL établie avec succès.", 'success');
    } catch (PDOException $e) {
        showMessage("Erreur de connexion à MySQL : " . $e->getMessage(), 'error');
        exit;
    }
} else {
    // En production, utiliser l'URL de connexion
    try {
        // Extraire les informations de connexion de l'URL
        $url = parse_url($database_url);
        $host = $url['host'] ?? 'localhost';
        $port = $url['port'] ?? '5432';
        $dbname = ltrim($url['path'] ?? '', '/');
        
        // Correction pour le problème de nom de base de données avec underscore à la fin
        if (substr($dbname, -1) === '_') {
            $dbname = substr($dbname, 0, -1);
        }
        
        $user = $url['user'] ?? '';
        $password = $url['pass'] ?? '';
        
        // Construire le DSN pour PostgreSQL
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        
        // Tenter de se connecter
        $pdo = new PDO($dsn, $user, $password);
        
        // Configurer PDO pour qu'il lance des exceptions en cas d'erreur
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbType = 'pgsql';
        
        showMessage("Connexion à la base de données PostgreSQL établie avec succès.", 'success');
    } catch (PDOException $e) {
        showMessage("Erreur de connexion à PostgreSQL : " . $e->getMessage(), 'error');
        exit;
    }
}

// Vérifier et créer la table utilisateurs si nécessaire
showMessage("Vérification de la table 'utilisateurs'...", 'info');
if (!tableExists($pdo, 'utilisateurs')) {
    showMessage("La table 'utilisateurs' n'existe pas. Création en cours...", 'info');
    try {
        if ($dbType == 'pgsql') {
            $sql = "CREATE TABLE utilisateurs (
                id SERIAL PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                mot_de_passe VARCHAR(255) NOT NULL,
                date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                role VARCHAR(20) DEFAULT 'utilisateur',
                statut VARCHAR(20) DEFAULT 'actif',
                derniere_connexion TIMESTAMP NULL
            )";
        } else {
            $sql = "CREATE TABLE utilisateurs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                mot_de_passe VARCHAR(255) NOT NULL,
                date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                role VARCHAR(20) DEFAULT 'utilisateur',
                statut VARCHAR(20) DEFAULT 'actif',
                derniere_connexion TIMESTAMP NULL
            )";
        }
        $pdo->exec($sql);
        showMessage("Table 'utilisateurs' créée avec succès.", 'success');
        
        // Insérer un utilisateur par défaut
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES 
            ('Admin', 'Super', 'admin@lawapp.com', :password, 'admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['password' => $password_hash]);
        showMessage("Utilisateur par défaut créé avec succès (email: admin@lawapp.com, mot de passe: admin123).", 'success');
    } catch (PDOException $e) {
        showMessage("Erreur lors de la création de la table 'utilisateurs': " . $e->getMessage(), 'error');
    }
} else {
    showMessage("La table 'utilisateurs' existe déjà.", 'success');
    
    // Vérifier s'il y a au moins un utilisateur
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs");
        $count = $stmt->fetchColumn();
        if ($count == 0) {
            showMessage("Aucun utilisateur trouvé. Création d'un utilisateur par défaut...", 'info');
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES 
                ('Admin', 'Super', 'admin@lawapp.com', :password, 'admin')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['password' => $password_hash]);
            showMessage("Utilisateur par défaut créé avec succès (email: admin@lawapp.com, mot de passe: admin123).", 'success');
        } else {
            showMessage("$count utilisateur(s) trouvé(s) dans la base de données.", 'success');
        }
    } catch (PDOException $e) {
        showMessage("Erreur lors de la vérification des utilisateurs: " . $e->getMessage(), 'error');
    }
}

// Conclusion
showMessage("Vérification et correction de la table 'utilisateurs' terminée.", 'success');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction de la table utilisateurs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        h1 {
            color: #343a40;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .next-steps {
            margin-top: 30px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_scripts.php" class="back-link">&larr; Retour aux scripts d'administration</a>
        <h1>Correction de la table utilisateurs</h1>
        
        <div class="next-steps">
            <h2>Prochaines étapes</h2>
            <p>Une fois la table utilisateurs créée, vous pouvez :</p>
            <ol>
                <li>Vous connecter avec l'utilisateur par défaut (admin@lawapp.com / admin123)</li>
                <li>Créer d'autres utilisateurs si nécessaire</li>
                <li>Vérifier que les vidéos s'affichent correctement</li>
            </ol>
            <p><a href="index.php" class="back-link">Retourner à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
