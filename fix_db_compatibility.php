<?php
// Script pour corriger les problèmes de compatibilité entre PostgreSQL et MySQL
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

// Fonction pour vérifier si une séquence existe (PostgreSQL uniquement)
function sequenceExists($pdo, $sequence) {
    try {
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.sequences 
                WHERE sequence_name = :sequence
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['sequence' => $sequence]);
            return (bool) $stmt->fetchColumn();
        } else {
            // MySQL n'utilise pas de séquences
            return true;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la séquence: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si un index existe
function indexExists($pdo, $table, $index) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM pg_indexes 
                WHERE tablename = :table AND indexname = :index
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'index' => $index]);
            return (bool) $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SHOW INDEX FROM $table WHERE Key_name = :index";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['index' => $index]);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de l'index: " . $e->getMessage() . "</p>";
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
    <title>Correction des problèmes de compatibilité entre PostgreSQL et MySQL</title>
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
        <h1>Correction des problèmes de compatibilité entre PostgreSQL et MySQL</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

// Déterminer le type de base de données
$is_postgres = strpos(DB_URL, 'pgsql') !== false;
$db_type = $is_postgres ? 'PostgreSQL' : 'MySQL';

echo "<p class='success'>Connexion à la base de données établie avec succès. Type de base de données : $db_type</p>";

// Liste des tables à vérifier
$tables = [
    'administrateurs', 'utilisateurs', 'categories_livres', 'categories_podcasts',
    'categories_cours', 'categories_videos', 'livres', 'podcasts', 'cours', 'videos',
    'modules', 'lecons', 'inscriptions', 'progression_utilisateurs', 'badges', 'user_badges',
    'user_favoris'
];

// Vérifier les séquences pour PostgreSQL
if ($is_postgres) {
    echo "<h2>Vérification des séquences PostgreSQL</h2>";
    
    foreach ($tables as $table) {
        if (tableExists($pdo, $table)) {
            $sequence = $table . "_id_seq";
            if (!sequenceExists($pdo, $sequence)) {
                echo "<p class='warning'>La séquence '$sequence' n'existe pas. Tentative de création...</p>";
                
                try {
                    // Vérifier si la colonne id existe dans la table
                    $sql = "SELECT column_name FROM information_schema.columns 
                            WHERE table_name = :table AND column_name = 'id'";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['table' => $table]);
                    $has_id = $stmt->rowCount() > 0;
                    
                    if ($has_id) {
                        // Créer la séquence
                        $sql_create_sequence = "CREATE SEQUENCE IF NOT EXISTS $sequence";
                        executeQuery($pdo, $sql_create_sequence, "Création de la séquence $sequence");
                        
                        // Lier la séquence à la colonne id
                        $sql_set_default = "ALTER TABLE $table ALTER COLUMN id SET DEFAULT nextval('$sequence')";
                        executeQuery($pdo, $sql_set_default, "Liaison de la séquence à la colonne id");
                        
                        // Mettre à jour la valeur de la séquence
                        $sql_update_sequence = "SELECT setval('$sequence', COALESCE((SELECT MAX(id) FROM $table), 1), false)";
                        $pdo->query($sql_update_sequence);
                        echo "<p class='success'>Séquence mise à jour avec la valeur maximale de id.</p>";
                    } else {
                        echo "<p class='warning'>La table '$table' n'a pas de colonne 'id'. Impossible de créer une séquence.</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>Erreur lors de la création de la séquence: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p class='success'>La séquence '$sequence' existe déjà.</p>";
                
                // Vérifier si la séquence est correctement liée à la colonne id
                try {
                    $sql = "SELECT column_default FROM information_schema.columns 
                            WHERE table_name = :table AND column_name = 'id'";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['table' => $table]);
                    $default_value = $stmt->fetchColumn();
                    
                    if ($default_value !== "nextval('$sequence'::regclass)") {
                        echo "<p class='warning'>La séquence n'est pas correctement liée à la colonne id. Tentative de correction...</p>";
                        
                        $sql_set_default = "ALTER TABLE $table ALTER COLUMN id SET DEFAULT nextval('$sequence')";
                        executeQuery($pdo, $sql_set_default, "Liaison de la séquence à la colonne id");
                    }
                    
                    // Mettre à jour la valeur de la séquence
                    $sql_update_sequence = "SELECT setval('$sequence', COALESCE((SELECT MAX(id) FROM $table), 1), false)";
                    $pdo->query($sql_update_sequence);
                    echo "<p class='success'>Séquence mise à jour avec la valeur maximale de id.</p>";
                } catch (PDOException $e) {
                    echo "<p class='error'>Erreur lors de la vérification de la liaison de la séquence: " . $e->getMessage() . "</p>";
                }
            }
        }
    }
}

// Vérifier les index pour les deux types de bases de données
echo "<h2>Vérification des index</h2>";

$indexes = [
    ['table' => 'utilisateurs', 'index' => 'idx_utilisateurs_email', 'column' => 'email'],
    ['table' => 'administrateurs', 'index' => 'idx_administrateurs_email', 'column' => 'email'],
    ['table' => 'livres', 'index' => 'idx_livres_categorie', 'column' => 'id_categorie'],
    ['table' => 'podcasts', 'index' => 'idx_podcasts_categorie', 'column' => 'id_categorie'],
    ['table' => 'cours', 'index' => 'idx_cours_categorie', 'column' => 'id_categorie'],
    ['table' => 'videos', 'index' => 'idx_videos_categorie', 'column' => 'id_categorie'],
    ['table' => 'modules', 'index' => 'idx_modules_cours', 'column' => 'id_cours'],
    ['table' => 'lecons', 'index' => 'idx_lecons_module', 'column' => 'id_module'],
    ['table' => 'inscriptions', 'index' => 'idx_inscriptions_user', 'column' => 'id_utilisateur'],
    ['table' => 'inscriptions', 'index' => 'idx_inscriptions_cours', 'column' => 'id_cours'],
    ['table' => 'progression_utilisateurs', 'index' => 'idx_progression_user', 'column' => 'id_utilisateur'],
    ['table' => 'progression_utilisateurs', 'index' => 'idx_progression_cours', 'column' => 'id_cours']
];

foreach ($indexes as $index_info) {
    $table = $index_info['table'];
    $index = $index_info['index'];
    $column = $index_info['column'];
    
    if (tableExists($pdo, $table)) {
        if (!indexExists($pdo, $table, $index)) {
            echo "<p class='warning'>L'index '$index' n'existe pas sur la table '$table'. Tentative de création...</p>";
            
            try {
                $sql_create_index = "CREATE INDEX $index ON $table ($column)";
                executeQuery($pdo, $sql_create_index, "Création de l'index $index");
            } catch (PDOException $e) {
                echo "<p class='error'>Erreur lors de la création de l'index: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='success'>L'index '$index' existe déjà sur la table '$table'.</p>";
        }
    } else {
        echo "<p class='warning'>La table '$table' n'existe pas. Impossible de vérifier l'index.</p>";
    }
}

// Vérifier les types de données pour PostgreSQL
if ($is_postgres) {
    echo "<h2>Vérification des types de données PostgreSQL</h2>";
    
    // Liste des colonnes à vérifier
    $columns = [
        ['table' => 'utilisateurs', 'column' => 'date_inscription', 'type' => 'timestamp'],
        ['table' => 'utilisateurs', 'column' => 'derniere_connexion', 'type' => 'timestamp'],
        ['table' => 'administrateurs', 'column' => 'date_creation', 'type' => 'timestamp'],
        ['table' => 'livres', 'column' => 'date_publication', 'type' => 'date'],
        ['table' => 'podcasts', 'column' => 'date_publication', 'type' => 'date'],
        ['table' => 'cours', 'column' => 'date_creation', 'type' => 'timestamp'],
        ['table' => 'videos', 'column' => 'date_publication', 'type' => 'date']
    ];
    
    foreach ($columns as $column_info) {
        $table = $column_info['table'];
        $column = $column_info['column'];
        $type = $column_info['type'];
        
        if (tableExists($pdo, $table)) {
            try {
                $sql = "SELECT data_type FROM information_schema.columns 
                        WHERE table_name = :table AND column_name = :column";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['table' => $table, 'column' => $column]);
                $current_type = $stmt->fetchColumn();
                
                if ($current_type && $current_type !== $type) {
                    echo "<p class='warning'>La colonne '$column' de la table '$table' a le type '$current_type' au lieu de '$type'. Tentative de correction...</p>";
                    
                    $sql_alter_type = "ALTER TABLE $table ALTER COLUMN $column TYPE $type";
                    executeQuery($pdo, $sql_alter_type, "Modification du type de la colonne $column");
                } elseif ($current_type) {
                    echo "<p class='success'>La colonne '$column' de la table '$table' a le bon type '$type'.</p>";
                } else {
                    echo "<p class='warning'>La colonne '$column' n'existe pas dans la table '$table'.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='error'>Erreur lors de la vérification du type de la colonne: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='warning'>La table '$table' n'existe pas. Impossible de vérifier la colonne.</p>";
        }
    }
}

// Vérifier les contraintes d'unicité
echo "<h2>Vérification des contraintes d'unicité</h2>";

$unique_constraints = [
    ['table' => 'utilisateurs', 'column' => 'email', 'constraint' => 'utilisateurs_email_key'],
    ['table' => 'administrateurs', 'column' => 'email', 'constraint' => 'administrateurs_email_key']
];

foreach ($unique_constraints as $constraint_info) {
    $table = $constraint_info['table'];
    $column = $constraint_info['column'];
    $constraint = $constraint_info['constraint'];
    
    if (tableExists($pdo, $table)) {
        try {
            // Vérifier si la contrainte existe
            if ($is_postgres) {
                $sql = "SELECT EXISTS (
                    SELECT 1 FROM information_schema.table_constraints 
                    WHERE table_name = :table AND constraint_name = :constraint
                )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['table' => $table, 'constraint' => $constraint]);
                $constraint_exists = (bool) $stmt->fetchColumn();
            } else {
                // Pour MySQL, vérifier si la colonne a une contrainte d'unicité
                $sql = "SHOW INDEXES FROM $table WHERE Column_name = :column AND Non_unique = 0";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['column' => $column]);
                $constraint_exists = $stmt->rowCount() > 0;
            }
            
            if (!$constraint_exists) {
                echo "<p class='warning'>La contrainte d'unicité sur la colonne '$column' de la table '$table' n'existe pas. Tentative de création...</p>";
                
                if ($is_postgres) {
                    $sql_add_constraint = "ALTER TABLE $table ADD CONSTRAINT $constraint UNIQUE ($column)";
                } else {
                    $sql_add_constraint = "ALTER TABLE $table ADD UNIQUE INDEX $constraint ($column)";
                }
                
                executeQuery($pdo, $sql_add_constraint, "Ajout de la contrainte d'unicité");
            } else {
                echo "<p class='success'>La contrainte d'unicité sur la colonne '$column' de la table '$table' existe déjà.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de la vérification de la contrainte d'unicité: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='warning'>La table '$table' n'existe pas. Impossible de vérifier la contrainte d'unicité.</p>";
    }
}

// Liens de retour
echo "<h2>Liens utiles :</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='fix_all_tables.php'>Corriger toutes les tables</a></li>";
echo "<li><a href='fix_foreign_keys.php'>Corriger les clés étrangères</a></li>";
echo "<li><a href='fix_users_tables.php'>Corriger les tables utilisateurs</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
