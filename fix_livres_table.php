<?php
// Script pour corriger la structure de la table livres
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

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (SELECT 1 FROM information_schema.columns 
                    WHERE table_name = :table AND column_name = :column)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT column_name FROM information_schema.columns 
                    WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la colonne: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (SELECT 1 FROM information_schema.tables 
                    WHERE table_name = :table)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT table_name FROM information_schema.tables 
                    WHERE table_schema = DATABASE() AND table_name = :table";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return $stmt->rowCount() > 0;
        }
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
    <title>Correction de la table livres</title>
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
        <h1>Correction de la table livres</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

echo "<p class='success'>Connexion à la base de données établie avec succès.</p>";

// Vérifier si la table livres existe
if (!tableExists($pdo, 'livres')) {
    echo "<p class='error'>La table 'livres' n'existe pas. Création de la table...</p>";
    
    // Créer la table livres selon le schéma de init_db.php
    $sql_create_livres = "
    CREATE TABLE livres (
        id SERIAL PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        auteur VARCHAR(255) NOT NULL,
        description TEXT,
        annee_publication INT,
        editeur VARCHAR(255),
        isbn VARCHAR(20),
        id_categorie INT,
        image_couverture VARCHAR(255),
        fichier_pdf VARCHAR(255),
        date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        statut VARCHAR(20) DEFAULT 'actif'
    )";
    
    executeQuery($pdo, $sql_create_livres, "Création de la table livres");
} else {
    echo "<p class='success'>La table 'livres' existe.</p>";
    
    // Vérifier si la colonne id_categorie existe
    if (!columnExists($pdo, 'livres', 'id_categorie')) {
        echo "<p class='error'>La colonne 'id_categorie' n'existe pas dans la table 'livres'. Ajout de la colonne...</p>";
        
        // Ajouter la colonne id_categorie
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql_add_column = "ALTER TABLE livres ADD COLUMN id_categorie INT";
        } else {
            // MySQL
            $sql_add_column = "ALTER TABLE livres ADD COLUMN id_categorie INT";
        }
        
        executeQuery($pdo, $sql_add_column, "Ajout de la colonne id_categorie");
    } else {
        echo "<p class='success'>La colonne 'id_categorie' existe déjà dans la table 'livres'.</p>";
    }
}

// Vérifier la structure actuelle de la table livres
echo "<h2>Structure actuelle de la table livres :</h2>";
echo "<pre>";
try {
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $sql = "SELECT column_name, data_type, character_maximum_length 
                FROM information_schema.columns 
                WHERE table_name = 'livres'";
    } else {
        // MySQL
        $sql = "DESCRIBE livres";
    }
    
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);
} catch (PDOException $e) {
    echo "Erreur lors de l'affichage de la structure: " . $e->getMessage();
}
echo "</pre>";

// Vérifier si la table categories_livres existe
if (!tableExists($pdo, 'categories_livres')) {
    echo "<p class='error'>La table 'categories_livres' n'existe pas. Exécutez d'abord le script fix_tables.php.</p>";
} else {
    echo "<p class='success'>La table 'categories_livres' existe.</p>";
    
    // Afficher les catégories de livres disponibles
    echo "<h2>Catégories de livres disponibles :</h2>";
    echo "<pre>";
    try {
        $sql = "SELECT * FROM categories_livres";
        $stmt = $pdo->query($sql);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($categories);
    } catch (PDOException $e) {
        echo "Erreur lors de l'affichage des catégories: " . $e->getMessage();
    }
    echo "</pre>";
}

// Liens de retour
echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
echo "<p><a href='admin/init_db.php'>Initialiser la base de données</a></p>";
?>

    </div>
</body>
</html>
