<?php
// Script pour vérifier et corriger les relations entre les tables (clés étrangères)
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

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
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.tables 
                WHERE table_name = :table
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return (bool) $stmt->fetchColumn();
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

// Fonction pour vérifier si une contrainte de clé étrangère existe
function foreignKeyExists($pdo, $table, $column, $ref_table) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.table_constraints tc
                JOIN information_schema.constraint_column_usage ccu ON tc.constraint_name = ccu.constraint_name
                WHERE tc.constraint_type = 'FOREIGN KEY' 
                AND tc.table_name = :table 
                AND ccu.column_name = :column
                AND ccu.table_name = :ref_table
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column, 'ref_table' => $ref_table]);
            return (bool) $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT COUNT(*) FROM information_schema.key_column_usage
                    WHERE table_schema = DATABASE()
                    AND table_name = :table
                    AND column_name = :column
                    AND referenced_table_name = :ref_table";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column, 'ref_table' => $ref_table]);
            return $stmt->fetchColumn() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la clé étrangère: " . $e->getMessage() . "</p>";
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
    <title>Vérification et correction des clés étrangères</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Vérification et correction des clés étrangères</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

echo "<p class='success'>Connexion à la base de données établie avec succès.</p>";

// Définition des relations entre les tables
$foreign_keys = [
    // Livres
    ['table' => 'livres', 'column' => 'id_categorie', 'ref_table' => 'categories_livres', 'ref_column' => 'id'],
    
    // Podcasts
    ['table' => 'podcasts', 'column' => 'id_categorie', 'ref_table' => 'categories_podcasts', 'ref_column' => 'id'],
    
    // Cours
    ['table' => 'cours', 'column' => 'id_categorie', 'ref_table' => 'categories_cours', 'ref_column' => 'id'],
    ['table' => 'cours', 'column' => 'id_createur', 'ref_table' => 'utilisateurs', 'ref_column' => 'id'],
    
    // Modules
    ['table' => 'modules', 'column' => 'id_cours', 'ref_table' => 'cours', 'ref_column' => 'id'],
    
    // Leçons
    ['table' => 'lecons', 'column' => 'id_module', 'ref_table' => 'modules', 'ref_column' => 'id'],
    
    // Vidéos
    ['table' => 'videos', 'column' => 'id_categorie', 'ref_table' => 'categories_videos', 'ref_column' => 'id'],
    
    // Inscriptions
    ['table' => 'inscriptions', 'column' => 'id_utilisateur', 'ref_table' => 'utilisateurs', 'ref_column' => 'id'],
    ['table' => 'inscriptions', 'column' => 'id_cours', 'ref_table' => 'cours', 'ref_column' => 'id'],
    
    // Progression utilisateurs
    ['table' => 'progression_utilisateurs', 'column' => 'id_utilisateur', 'ref_table' => 'utilisateurs', 'ref_column' => 'id'],
    ['table' => 'progression_utilisateurs', 'column' => 'id_cours', 'ref_table' => 'cours', 'ref_column' => 'id'],
    ['table' => 'progression_utilisateurs', 'column' => 'id_module', 'ref_table' => 'modules', 'ref_column' => 'id'],
    ['table' => 'progression_utilisateurs', 'column' => 'id_lecon', 'ref_table' => 'lecons', 'ref_column' => 'id'],
    
    // User badges
    ['table' => 'user_badges', 'column' => 'id_utilisateur', 'ref_table' => 'utilisateurs', 'ref_column' => 'id'],
    ['table' => 'user_badges', 'column' => 'id_badge', 'ref_table' => 'badges', 'ref_column' => 'id'],
    
    // Livre catégories
    ['table' => 'livre_categories', 'column' => 'id_livre', 'ref_table' => 'livres', 'ref_column' => 'id'],
    ['table' => 'livre_categories', 'column' => 'id_categorie', 'ref_table' => 'categories_livres', 'ref_column' => 'id'],
    
    // Avis livres
    ['table' => 'avis_livres', 'column' => 'id_livre', 'ref_table' => 'livres', 'ref_column' => 'id'],
    ['table' => 'avis_livres', 'column' => 'id_utilisateur', 'ref_table' => 'utilisateurs', 'ref_column' => 'id']
];

// Vérifier et corriger les clés étrangères
echo "<h2>Vérification des clés étrangères</h2>";

foreach ($foreign_keys as $fk) {
    $table = $fk['table'];
    $column = $fk['column'];
    $ref_table = $fk['ref_table'];
    $ref_column = $fk['ref_column'];
    
    echo "<h3>Relation : $table.$column -> $ref_table.$ref_column</h3>";
    
    // Vérifier si les tables existent
    if (!tableExists($pdo, $table)) {
        echo "<p class='warning'>La table '$table' n'existe pas. Impossible de vérifier la clé étrangère.</p>";
        continue;
    }
    
    if (!tableExists($pdo, $ref_table)) {
        echo "<p class='warning'>La table de référence '$ref_table' n'existe pas. Impossible de vérifier la clé étrangère.</p>";
        continue;
    }
    
    // Vérifier si la clé étrangère existe
    if (!foreignKeyExists($pdo, $table, $column, $ref_table)) {
        echo "<p class='warning'>La clé étrangère n'existe pas. Tentative d'ajout...</p>";
        
        // Générer un nom unique pour la contrainte
        $constraint_name = "fk_" . $table . "_" . $column . "_" . $ref_table;
        
        // Ajouter la clé étrangère
        try {
            // Pour PostgreSQL
            if (strpos(DB_URL, 'pgsql') !== false) {
                $sql = "ALTER TABLE $table ADD CONSTRAINT $constraint_name 
                        FOREIGN KEY ($column) REFERENCES $ref_table($ref_column) ON DELETE SET NULL";
            } 
            // Pour MySQL
            else {
                $sql = "ALTER TABLE $table ADD CONSTRAINT $constraint_name 
                        FOREIGN KEY ($column) REFERENCES $ref_table($ref_column) ON DELETE SET NULL";
            }
            
            executeQuery($pdo, $sql, "Ajout de la clé étrangère");
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'ajout de la clé étrangère: " . $e->getMessage() . "</p>";
            
            // Si l'erreur est due à des données invalides, afficher les données problématiques
            echo "<p class='warning'>Vérification des données invalides...</p>";
            try {
                $sql = "SELECT $table.id, $table.$column FROM $table 
                        LEFT JOIN $ref_table ON $table.$column = $ref_table.$ref_column
                        WHERE $table.$column IS NOT NULL AND $ref_table.$ref_column IS NULL";
                $stmt = $pdo->query($sql);
                $invalid_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($invalid_data) > 0) {
                    echo "<p class='error'>Données invalides trouvées :</p>";
                    echo "<pre>";
                    print_r($invalid_data);
                    echo "</pre>";
                    
                    echo "<p class='warning'>Tentative de correction des données invalides...</p>";
                    
                    // Mettre à NULL les valeurs invalides
                    $sql = "UPDATE $table SET $column = NULL WHERE $column NOT IN (SELECT $ref_column FROM $ref_table)";
                    executeQuery($pdo, $sql, "Correction des données invalides");
                    
                    // Réessayer d'ajouter la clé étrangère
                    echo "<p class='warning'>Nouvelle tentative d'ajout de la clé étrangère...</p>";
                    if (strpos(DB_URL, 'pgsql') !== false) {
                        $sql = "ALTER TABLE $table ADD CONSTRAINT $constraint_name 
                                FOREIGN KEY ($column) REFERENCES $ref_table($ref_column) ON DELETE SET NULL";
                    } else {
                        $sql = "ALTER TABLE $table ADD CONSTRAINT $constraint_name 
                                FOREIGN KEY ($column) REFERENCES $ref_table($ref_column) ON DELETE SET NULL";
                    }
                    
                    executeQuery($pdo, $sql, "Ajout de la clé étrangère (seconde tentative)");
                } else {
                    echo "<p class='success'>Aucune donnée invalide trouvée. Le problème est ailleurs.</p>";
                }
            } catch (PDOException $e2) {
                echo "<p class='error'>Erreur lors de la vérification des données invalides: " . $e2->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p class='success'>La clé étrangère existe déjà.</p>";
    }
}

// Liens de retour
echo "<h2>Liens utiles :</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='fix_all_tables.php'>Corriger toutes les tables</a></li>";
echo "<li><a href='fix_admin_table.php'>Corriger la table administrateurs</a></li>";
echo "<li><a href='fix_livres_table.php'>Corriger la table livres</a></li>";
echo "<li><a href='fix_podcasts_table.php'>Corriger la table podcasts</a></li>";
echo "<li><a href='fix_cours_table.php'>Corriger la table cours</a></li>";
echo "<li><a href='fix_videos_table.php'>Corriger la table videos</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
