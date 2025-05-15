<?php
// Script pour corriger les tables PostgreSQL
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fonction pour afficher les messages
function showMessage($message, $type = 'info') {
    $class = ($type == 'success') ? 'success' : (($type == 'error') ? 'error' : 'info');
    echo "<p class='$class'>$message</p>";
    if (ob_get_level() > 0) {
        ob_flush();
        flush();
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

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        $sql = "SELECT EXISTS (
            SELECT 1 FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = :table 
            AND column_name = :column
        )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['table' => $table, 'column' => $column]);
        return (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        showMessage("Erreur lors de la vérification de la colonne: " . $e->getMessage(), 'error');
        return false;
    }
}

// Connexion à la base de données
$database_url = getenv('DATABASE_URL');
if (empty($database_url)) {
    showMessage("Variable d'environnement DATABASE_URL non définie.", 'error');
    exit;
}

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
    
    // Afficher les informations de connexion (uniquement pour le débogage)
    showMessage("Tentative de connexion à : Host=$host, Port=$port, DBName=$dbname, User=$user", 'info');
    
    // Tenter de se connecter
    $pdo = new PDO($dsn, $user, $password);
    
    // Configurer PDO pour qu'il lance des exceptions en cas d'erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    showMessage("Connexion à la base de données PostgreSQL établie avec succès.", 'success');
} catch (PDOException $e) {
    showMessage("Erreur de connexion à PostgreSQL : " . $e->getMessage(), 'error');
    exit;
}

// Début du HTML
// Activer la mise en tampon de sortie au début du script
ob_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction des tables PostgreSQL</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        .progress-container { 
            width: 100%; 
            background-color: #ddd; 
            margin: 10px 0; 
            border-radius: 5px;
        }
        .progress-bar { 
            height: 30px; 
            background-color: #4CAF50; 
            text-align: center; 
            line-height: 30px; 
            color: white; 
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Correction des tables PostgreSQL</h1>
        
        <h2>1. Création des tables principales</h2>
        
        <?php
        // Tableau des tables à créer
        $tables = [
            'administrateurs' => "
                CREATE TABLE IF NOT EXISTS administrateurs (
                    id SERIAL PRIMARY KEY,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    mot_de_passe VARCHAR(255) NOT NULL,
                    prenom VARCHAR(50),
                    nom VARCHAR(50),
                    statut VARCHAR(20) DEFAULT 'actif',
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'utilisateurs' => "
                CREATE TABLE IF NOT EXISTS utilisateurs (
                    id SERIAL PRIMARY KEY,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    mot_de_passe VARCHAR(255) NOT NULL,
                    prenom VARCHAR(50),
                    nom VARCHAR(50),
                    date_naissance DATE,
                    adresse TEXT,
                    telephone VARCHAR(20),
                    bio TEXT,
                    photo_profil VARCHAR(255),
                    statut VARCHAR(20) DEFAULT 'actif',
                    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    derniere_connexion TIMESTAMP
                )
            ",
            'categories_livres' => "
                CREATE TABLE IF NOT EXISTS categories_livres (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    image VARCHAR(255)
                )
            ",
            'categories_podcasts' => "
                CREATE TABLE IF NOT EXISTS categories_podcasts (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    image VARCHAR(255)
                )
            ",
            'categories_cours' => "
                CREATE TABLE IF NOT EXISTS categories_cours (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    image VARCHAR(255)
                )
            ",
            'categories_videos' => "
                CREATE TABLE IF NOT EXISTS categories_videos (
                    id SERIAL PRIMARY KEY,
                    nom VARCHAR(100) NOT NULL,
                    description TEXT,
                    image VARCHAR(255)
                )
            ",
            'livres' => "
                CREATE TABLE IF NOT EXISTS livres (
                    id SERIAL PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    auteur VARCHAR(100),
                    description TEXT,
                    prix DECIMAL(10, 2),
                    couverture VARCHAR(255),
                    fichier_pdf VARCHAR(255),
                    date_publication DATE,
                    id_categorie INT,
                    statut VARCHAR(20) DEFAULT 'actif',
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'podcasts' => "
                CREATE TABLE IF NOT EXISTS podcasts (
                    id SERIAL PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    description TEXT,
                    duree INT,
                    fichier_audio VARCHAR(255),
                    image VARCHAR(255),
                    date_publication DATE,
                    id_categorie INT,
                    statut VARCHAR(20) DEFAULT 'actif',
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'cours' => "
                CREATE TABLE IF NOT EXISTS cours (
                    id SERIAL PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    description TEXT,
                    prix DECIMAL(10, 2),
                    niveau VARCHAR(50),
                    duree_estimee INT,
                    image_couverture VARCHAR(255),
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    statut VARCHAR(20) DEFAULT 'actif',
                    id_categorie INT,
                    id_createur INT,
                    note_moyenne DECIMAL(3, 2)
                )
            ",
            'modules' => "
                CREATE TABLE IF NOT EXISTS modules (
                    id SERIAL PRIMARY KEY,
                    id_cours INT NOT NULL,
                    titre VARCHAR(255) NOT NULL,
                    description TEXT,
                    ordre INT,
                    statut VARCHAR(20) DEFAULT 'actif',
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'lecons' => "
                CREATE TABLE IF NOT EXISTS lecons (
                    id SERIAL PRIMARY KEY,
                    id_module INT NOT NULL,
                    titre VARCHAR(255) NOT NULL,
                    contenu TEXT,
                    duree INT,
                    fichier VARCHAR(255),
                    ordre INT,
                    statut VARCHAR(20) DEFAULT 'actif',
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'inscriptions' => "
                CREATE TABLE IF NOT EXISTS inscriptions (
                    id SERIAL PRIMARY KEY,
                    id_utilisateur INT NOT NULL,
                    id_cours INT NOT NULL,
                    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    statut VARCHAR(20) DEFAULT 'actif',
                    progression INT DEFAULT 0,
                    date_completion TIMESTAMP,
                    note INT,
                    commentaire TEXT
                )
            ",
            'progression_utilisateurs' => "
                CREATE TABLE IF NOT EXISTS progression_utilisateurs (
                    id SERIAL PRIMARY KEY,
                    id_utilisateur INT NOT NULL,
                    id_lecon INT NOT NULL,
                    statut VARCHAR(20) DEFAULT 'non_commencé',
                    progression INT DEFAULT 0,
                    date_debut TIMESTAMP,
                    date_fin TIMESTAMP
                )
            "
        ];
        
        // Créer les tables
        $total = count($tables);
        $count = 0;
        
        echo "<div class='progress-container'>";
        echo "<div class='progress-bar' style='width: 0%'>0%</div>";
        echo "</div>";
        
        foreach ($tables as $table => $sql) {
            $count++;
            $percent = round(($count / $total) * 100);
            
            echo "<script>document.querySelector('.progress-bar').style.width = '$percent%';</script>";
            echo "<script>document.querySelector('.progress-bar').textContent = '$percent%';</script>";
            ob_flush();
            flush();
            
            echo "<h3>Table: $table</h3>";
            
            if (tableExists($pdo, $table)) {
                showMessage("La table '$table' existe déjà.", 'success');
                
                // Vérifier et ajouter les colonnes manquantes pour certaines tables
                if ($table == 'cours') {
                    $columns = ['id_categorie', 'id_createur', 'note_moyenne'];
                    foreach ($columns as $column) {
                        if (!columnExists($pdo, $table, $column)) {
                            try {
                                $type = ($column == 'note_moyenne') ? 'DECIMAL(3, 2)' : 'INT';
                                $pdo->exec("ALTER TABLE $table ADD COLUMN $column $type");
                                showMessage("Colonne '$column' ajoutée à la table '$table'.", 'success');
                            } catch (PDOException $e) {
                                showMessage("Erreur lors de l'ajout de la colonne '$column': " . $e->getMessage(), 'error');
                            }
                        }
                    }
                }
                
                if ($table == 'livres' && !columnExists($pdo, $table, 'id_categorie')) {
                    try {
                        $pdo->exec("ALTER TABLE $table ADD COLUMN id_categorie INT");
                        showMessage("Colonne 'id_categorie' ajoutée à la table '$table'.", 'success');
                    } catch (PDOException $e) {
                        showMessage("Erreur lors de l'ajout de la colonne 'id_categorie': " . $e->getMessage(), 'error');
                    }
                }
            } else {
                try {
                    $pdo->exec($sql);
                    showMessage("Création de la table '$table' réussie.", 'success');
                    
                    // Insérer des données de test pour certaines tables
                    if ($table == 'administrateurs') {
                        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
                        $insert_sql = "INSERT INTO administrateurs (email, mot_de_passe, prenom, nom, statut) 
                                      VALUES ('admin@example.com', :password, 'Admin', 'System', 'actif')";
                        $stmt = $pdo->prepare($insert_sql);
                        $stmt->execute(['password' => $password_hash]);
                        showMessage("Administrateur par défaut créé.", 'success');
                    }
                    
                    if ($table == 'categories_cours') {
                        $insert_sql = "INSERT INTO categories_cours (nom, description) VALUES 
                                      ('Droit civil', 'Cours de droit civil'),
                                      ('Droit pénal', 'Cours de droit pénal'),
                                      ('Droit des affaires', 'Cours de droit des affaires')";
                        $pdo->exec($insert_sql);
                        showMessage("Catégories de cours de test insérées.", 'success');
                    }
                    
                    if ($table == 'categories_livres') {
                        $insert_sql = "INSERT INTO categories_livres (nom, description) VALUES 
                                      ('Codes juridiques', 'Recueils de lois et codes'),
                                      ('Manuels', 'Manuels de droit'),
                                      ('Essais', 'Essais juridiques')";
                        $pdo->exec($insert_sql);
                        showMessage("Catégories de livres de test insérées.", 'success');
                    }
                } catch (PDOException $e) {
                    showMessage("Erreur lors de la création de la table '$table': " . $e->getMessage(), 'error');
                }
            }
        }
        
        echo "<script>document.querySelector('.progress-bar').style.width = '100%';</script>";
        echo "<script>document.querySelector('.progress-bar').textContent = '100%';</script>";
        
        // Afficher les tables existantes
        try {
            $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
            $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<h2>Tables existantes dans la base de données</h2>";
            echo "<ul>";
            foreach ($existing_tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        } catch (PDOException $e) {
            showMessage("Erreur lors de l'affichage des tables: " . $e->getMessage(), 'error');
        }
        ?>
        
        <h2>Résumé et recommandations</h2>
        <p>Le processus de correction des tables PostgreSQL est terminé. Voici les recommandations :</p>
        <ul>
            <li>Redémarrez votre application sur Render en allant dans le tableau de bord et en cliquant sur 'Manual Deploy' > 'Clear Build Cache & Deploy'.</li>
            <li>Si l'erreur persiste, vérifiez les logs de l'application dans le tableau de bord Render.</li>
            <li>Assurez-vous que les variables d'environnement sont correctement configurées, notamment DATABASE_URL.</li>
        </ul>
        
        <p><a href="index.php" style="display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Retour à l'accueil</a></p>
    </div>
</body>
</html>
