<?php
// Script pour corriger la structure de la table administrateurs
require_once __DIR__ . '/includes/config.php';

// Fonction pour exécuter une requête SQL et gérer les erreurs
function executeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "<p style='color:green'>✓ $description : Succès</p>";
        return true;
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ $description: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT table_name FROM information_schema.tables 
                    WHERE table_name = :table";
        } 
        // Pour MySQL
        else {
            $sql = "SELECT table_name FROM information_schema.tables 
                    WHERE table_schema = DATABASE() AND table_name = :table";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['table' => $table]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Correction de la table administrateurs</title>
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
    <div class='container'>
        <h1>Correction de la table administrateurs</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

echo "<p class='success'>Connexion à la base de données établie avec succès.</p>";

// Vérifier si la table administrateurs existe
if (!tableExists($pdo, 'administrateurs')) {
    echo "<p class='error'>La table 'administrateurs' n'existe pas. Création de la table...</p>";
    
    // Créer la table administrateurs
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_admin = "
        CREATE TABLE administrateurs (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            nom VARCHAR(100) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'admin',
            statut VARCHAR(20) NOT NULL DEFAULT 'actif',
            derniere_connexion TIMESTAMP,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_admin = "
        CREATE TABLE administrateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            nom VARCHAR(100) NOT NULL,
            role ENUM('admin', 'editeur') NOT NULL DEFAULT 'admin',
            statut ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif',
            derniere_connexion DATETIME,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_admin, "Création de la table administrateurs");
    
    // Insérer l'administrateur par défaut
    $sql_insert_admin = "
    INSERT INTO administrateurs (email, mot_de_passe, prenom, nom, role, statut) 
    VALUES ('admin@lawapp.com', 'admin', 'Admin', 'Principal', 'admin', 'actif')";
    
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_insert_admin .= " ON CONFLICT (email) DO NOTHING";
    }
    
    executeQuery($pdo, $sql_insert_admin, "Insertion de l'administrateur par défaut");
    
    echo "<div class='success'>
        <p>Administrateur par défaut créé :</p>
        <p>Email : admin@lawapp.com</p>
        <p>Mot de passe : admin</p>
    </div>";
} else {
    echo "<p class='success'>La table 'administrateurs' existe.</p>";
    
    // Afficher la structure de la table administrateurs
    echo "<h2>Structure actuelle de la table administrateurs :</h2>";
    echo "<pre>";
    try {
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "SELECT column_name, data_type, character_maximum_length 
                    FROM information_schema.columns 
                    WHERE table_name = 'administrateurs'";
        } else {
            // MySQL
            $sql = "DESCRIBE administrateurs";
        }
        
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($columns);
    } catch (PDOException $e) {
        echo "Erreur lors de l'affichage de la structure: " . $e->getMessage();
    }
    echo "</pre>";
    
    // Afficher les administrateurs existants
    echo "<h2>Administrateurs existants :</h2>";
    echo "<pre>";
    try {
        $sql = "SELECT id, email, prenom, nom, role, statut, derniere_connexion, date_creation, date_modification FROM administrateurs";
        $stmt = $pdo->query($sql);
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($admins);
    } catch (PDOException $e) {
        echo "Erreur lors de l'affichage des administrateurs: " . $e->getMessage();
    }
    echo "</pre>";
    
    // Vérifier si l'administrateur par défaut existe
    try {
        $stmt = $pdo->prepare("SELECT id FROM administrateurs WHERE email = 'admin@lawapp.com'");
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            echo "<p class='error'>L'administrateur par défaut n'existe pas. Ajout de l'administrateur par défaut...</p>";
            
            // Insérer l'administrateur par défaut
            $sql_insert_admin = "
            INSERT INTO administrateurs (email, mot_de_passe, prenom, nom, role, statut) 
            VALUES ('admin@lawapp.com', 'admin', 'Admin', 'Principal', 'admin', 'actif')";
            
            if (strpos(DB_URL, 'pgsql') !== false) {
                // PostgreSQL
                $sql_insert_admin .= " ON CONFLICT (email) DO NOTHING";
            }
            
            executeQuery($pdo, $sql_insert_admin, "Insertion de l'administrateur par défaut");
            
            echo "<div class='success'>
                <p>Administrateur par défaut créé :</p>
                <p>Email : admin@lawapp.com</p>
                <p>Mot de passe : admin</p>
            </div>";
        } else {
            echo "<p class='success'>L'administrateur par défaut existe déjà.</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification de l'administrateur par défaut: " . $e->getMessage() . "</p>";
    }
}

// Liens de retour
echo "<h2>Liens utiles :</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='admin/admin_login.php'>Page de connexion administrateur</a></li>";
echo "<li><a href='fix_livres_table.php'>Corriger la table livres</a></li>";
echo "<li><a href='fix_podcasts_table.php'>Corriger la table podcasts</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
