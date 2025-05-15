<?php
// Script pour corriger la structure de la table podcasts
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
    <title>Correction de la table podcasts</title>
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
        <h1>Correction de la table podcasts</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

echo "<p class='success'>Connexion à la base de données établie avec succès.</p>";

// Vérifier si la table categories_podcasts existe
if (!tableExists($pdo, 'categories_podcasts')) {
    echo "<p class='error'>La table 'categories_podcasts' n'existe pas. Création de la table...</p>";
    
    // Créer la table categories_podcasts
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_categories = "
        CREATE TABLE categories_podcasts (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(20) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_categories = "
        CREATE TABLE categories_podcasts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(20) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_categories, "Création de la table categories_podcasts");
    
    // Insérer des catégories de test
    $sql_insert_categories = "
    INSERT INTO categories_podcasts (nom, description, statut) VALUES 
    ('Actualités Juridiques', 'Les dernières nouvelles du monde juridique', 'actif'),
    ('Cas Pratiques', 'Analyse de cas juridiques concrets', 'actif'),
    ('Interviews d''Experts', 'Entretiens avec des professionnels du droit', 'actif'),
    ('Conseils Juridiques', 'Conseils pratiques sur des questions juridiques', 'actif'),
    ('Débats Juridiques', 'Discussions sur des sujets juridiques controversés', 'actif')";
    
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_insert_categories .= " ON CONFLICT (id) DO NOTHING";
    }
    
    executeQuery($pdo, $sql_insert_categories, "Insertion des catégories de podcasts de test");
} else {
    echo "<p class='success'>La table 'categories_podcasts' existe.</p>";
}

// Vérifier si la table podcasts existe
if (!tableExists($pdo, 'podcasts')) {
    echo "<p class='error'>La table 'podcasts' n'existe pas. Création de la table...</p>";
    
    // Créer la table podcasts
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_create_podcasts = "
        CREATE TABLE podcasts (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            duree INT, -- en minutes
            audio_url VARCHAR(255),
            image_url VARCHAR(255),
            id_categorie INT,
            id_createur INT,
            nombre_ecoutes INT DEFAULT 0,
            statut VARCHAR(20) DEFAULT 'publie',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_categorie) REFERENCES categories_podcasts(id) ON DELETE SET NULL
        )";
    } else {
        // MySQL
        $sql_create_podcasts = "
        CREATE TABLE podcasts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            duree INT, -- en minutes
            audio_url VARCHAR(255),
            image_url VARCHAR(255),
            id_categorie INT,
            id_createur INT,
            nombre_ecoutes INT DEFAULT 0,
            statut ENUM('brouillon', 'publie', 'archive') DEFAULT 'publie',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_mise_a_jour TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_categorie) REFERENCES categories_podcasts(id) ON DELETE SET NULL
        )";
    }
    
    executeQuery($pdo, $sql_create_podcasts, "Création de la table podcasts");
    
    // Insérer des podcasts de test
    $sql_insert_podcasts = "
    INSERT INTO podcasts (titre, description, duree, audio_url, image_url, id_categorie, statut) VALUES 
    ('Introduction au Droit Civil', 'Un podcast expliquant les bases du droit civil.', 45, '/LawApp/assets/audio/intro-droit-civil.mp3', '/LawApp/assets/images/podcasts/droit-civil.jpg', 1, 'publie'),
    ('Cas Pratique: Litige Commercial', 'Analyse d''un cas pratique de litige commercial.', 30, '/LawApp/assets/audio/cas-litige-commercial.mp3', '/LawApp/assets/images/podcasts/litige-commercial.jpg', 2, 'publie'),
    ('Interview avec Me Dupont', 'Entretien avec un avocat spécialisé en droit des affaires.', 60, '/LawApp/assets/audio/interview-dupont.mp3', '/LawApp/assets/images/podcasts/interview.jpg', 3, 'publie')";
    
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql_insert_podcasts .= " ON CONFLICT (id) DO NOTHING";
    }
    
    executeQuery($pdo, $sql_insert_podcasts, "Insertion des podcasts de test");
} else {
    echo "<p class='success'>La table 'podcasts' existe.</p>";
    
    // Afficher la structure de la table podcasts
    echo "<h2>Structure actuelle de la table podcasts :</h2>";
    echo "<pre>";
    try {
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "SELECT column_name, data_type, character_maximum_length 
                    FROM information_schema.columns 
                    WHERE table_name = 'podcasts'";
        } else {
            // MySQL
            $sql = "DESCRIBE podcasts";
        }
        
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($columns);
    } catch (PDOException $e) {
        echo "Erreur lors de l'affichage de la structure: " . $e->getMessage();
    }
    echo "</pre>";
    
    // Afficher les podcasts existants
    echo "<h2>Podcasts existants :</h2>";
    echo "<pre>";
    try {
        $sql = "SELECT * FROM podcasts";
        $stmt = $pdo->query($sql);
        $podcasts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($podcasts);
    } catch (PDOException $e) {
        echo "Erreur lors de l'affichage des podcasts: " . $e->getMessage();
    }
    echo "</pre>";
}

// Afficher les catégories de podcasts
echo "<h2>Catégories de podcasts :</h2>";
echo "<pre>";
try {
    $sql = "SELECT * FROM categories_podcasts";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($categories);
} catch (PDOException $e) {
    echo "Erreur lors de l'affichage des catégories: " . $e->getMessage();
}
echo "</pre>";

// Liens de retour
echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
echo "<p><a href='admin/init_db.php'>Initialiser la base de données</a></p>";
echo "<p><a href='fix_livres_table.php'>Corriger la table livres</a></p>";
?>

    </div>
</body>
</html>
