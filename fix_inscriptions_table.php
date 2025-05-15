<?php
// Script pour vérifier et corriger la table des inscriptions aux cours
require_once __DIR__ . '/includes/config.php';

// Fonction pour exécuter une requête SQL et gérer les erreurs
function executeQuery($pdo, $sql, $description, $params = []) {
    try {
        if (empty($params)) {
            $pdo->exec($sql);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
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
            return $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT COUNT(*) FROM information_schema.tables 
                    WHERE table_schema = DATABASE() AND table_name = :table";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return $stmt->fetchColumn() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.columns 
                WHERE table_name = :table AND column_name = :column
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT COUNT(*) FROM information_schema.columns 
                    WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return $stmt->fetchColumn() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la colonne: " . $e->getMessage() . "</p>";
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
    <title>Vérification et correction de la table des inscriptions</title>
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
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Vérification et correction de la table des inscriptions</h1>
        
        <p>Ce script vérifie et corrige la structure de la table des inscriptions aux cours.</p>
        
<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

echo "<p class='success'>Connexion à la base de données établie avec succès.</p>";

// Vérifier si les tables nécessaires existent
$tables_required = ['utilisateurs', 'cours'];
$tables_missing = [];

foreach ($tables_required as $table) {
    if (!tableExists($pdo, $table)) {
        $tables_missing[] = $table;
    }
}

if (!empty($tables_missing)) {
    echo "<p class='error'>Les tables suivantes sont requises mais n'existent pas : " . implode(', ', $tables_missing) . "</p>";
    echo "<p>Veuillez d'abord créer ces tables avant de continuer.</p>";
    
    echo "<ul>";
    foreach ($tables_missing as $table) {
        echo "<li><a href='fix_{$table}_table.php' class='btn'>Créer la table {$table}</a></li>";
    }
    echo "</ul>";
} else {
    echo "<p class='success'>Toutes les tables requises existent.</p>";
    
    // Vérifier si la table inscriptions existe
    if (!tableExists($pdo, 'inscriptions')) {
        echo "<p class='warning'>La table 'inscriptions' n'existe pas. Création de la table...</p>";
        
        // Créer la table inscriptions
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql_create_inscriptions = "
            CREATE TABLE inscriptions (
                id SERIAL PRIMARY KEY,
                id_utilisateur INTEGER NOT NULL,
                id_cours INTEGER NOT NULL,
                date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                progres INTEGER DEFAULT 0,
                statut VARCHAR(20) DEFAULT 'actif',
                derniere_activite TIMESTAMP,
                CONSTRAINT fk_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
                CONSTRAINT fk_cours FOREIGN KEY (id_cours) REFERENCES cours(id) ON DELETE CASCADE,
                CONSTRAINT unique_inscription UNIQUE (id_utilisateur, id_cours)
            )";
        } else {
            // MySQL
            $sql_create_inscriptions = "
            CREATE TABLE inscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_utilisateur INT NOT NULL,
                id_cours INT NOT NULL,
                date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                progres INT DEFAULT 0,
                statut ENUM('actif', 'inactif', 'terminé') DEFAULT 'actif',
                derniere_activite DATETIME,
                CONSTRAINT fk_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
                CONSTRAINT fk_cours FOREIGN KEY (id_cours) REFERENCES cours(id) ON DELETE CASCADE,
                CONSTRAINT unique_inscription UNIQUE (id_utilisateur, id_cours)
            )";
        }
        
        executeQuery($pdo, $sql_create_inscriptions, "Création de la table inscriptions");
        
        // Insérer des données de test
        echo "<h2>Insertion de données de test</h2>";
        
        // Récupérer quelques utilisateurs
        try {
            $stmt = $pdo->query("SELECT id FROM utilisateurs LIMIT 3");
            $utilisateurs = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Récupérer quelques cours
            $stmt = $pdo->query("SELECT id FROM cours LIMIT 3");
            $cours = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($utilisateurs) && !empty($cours)) {
                foreach ($utilisateurs as $id_utilisateur) {
                    foreach ($cours as $id_cours) {
                        $progres = rand(0, 100);
                        $statut = $progres == 100 ? 'terminé' : 'actif';
                        
                        $sql_insert = "
                        INSERT INTO inscriptions (id_utilisateur, id_cours, progres, statut) 
                        VALUES (:id_utilisateur, :id_cours, :progres, :statut)";
                        
                        try {
                            $stmt = $pdo->prepare($sql_insert);
                            $stmt->execute([
                                'id_utilisateur' => $id_utilisateur,
                                'id_cours' => $id_cours,
                                'progres' => $progres,
                                'statut' => $statut
                            ]);
                            echo "<p class='success'>✓ Inscription ajoutée pour l'utilisateur $id_utilisateur au cours $id_cours</p>";
                        } catch (PDOException $e) {
                            // Ignorer les erreurs de duplicata
                            if (strpos($e->getMessage(), 'unique') === false && strpos($e->getMessage(), 'Duplicate') === false) {
                                echo "<p class='error'>✗ Erreur lors de l'ajout de l'inscription: " . $e->getMessage() . "</p>";
                            }
                        }
                    }
                }
            } else {
                echo "<p class='warning'>Impossible d'insérer des données de test : pas assez d'utilisateurs ou de cours.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>✗ Erreur lors de la récupération des utilisateurs ou des cours: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='success'>La table 'inscriptions' existe déjà.</p>";
        
        // Vérifier la structure de la table
        echo "<h2>Vérification de la structure de la table</h2>";
        
        // Colonnes requises
        $required_columns = [
            'id', 'id_utilisateur', 'id_cours', 'date_inscription', 
            'progres', 'statut', 'derniere_activite'
        ];
        
        $missing_columns = [];
        foreach ($required_columns as $column) {
            if (!columnExists($pdo, 'inscriptions', $column)) {
                $missing_columns[] = $column;
            }
        }
        
        if (!empty($missing_columns)) {
            echo "<p class='warning'>Les colonnes suivantes sont manquantes dans la table 'inscriptions' : " . implode(', ', $missing_columns) . "</p>";
            
            // Ajouter les colonnes manquantes
            foreach ($missing_columns as $column) {
                $sql_add_column = "";
                
                switch ($column) {
                    case 'progres':
                        $sql_add_column = "ALTER TABLE inscriptions ADD COLUMN progres " . 
                            (strpos(DB_URL, 'pgsql') !== false ? "INTEGER DEFAULT 0" : "INT DEFAULT 0");
                        break;
                    case 'statut':
                        $sql_add_column = "ALTER TABLE inscriptions ADD COLUMN statut " . 
                            (strpos(DB_URL, 'pgsql') !== false ? "VARCHAR(20) DEFAULT 'actif'" : "ENUM('actif', 'inactif', 'terminé') DEFAULT 'actif'");
                        break;
                    case 'derniere_activite':
                        $sql_add_column = "ALTER TABLE inscriptions ADD COLUMN derniere_activite " . 
                            (strpos(DB_URL, 'pgsql') !== false ? "TIMESTAMP" : "DATETIME");
                        break;
                    case 'date_inscription':
                        $sql_add_column = "ALTER TABLE inscriptions ADD COLUMN date_inscription " . 
                            (strpos(DB_URL, 'pgsql') !== false ? "TIMESTAMP DEFAULT CURRENT_TIMESTAMP" : "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
                        break;
                    // Les autres colonnes ne devraient pas être manquantes car elles font partie de la clé primaire ou des clés étrangères
                }
                
                if (!empty($sql_add_column)) {
                    executeQuery($pdo, $sql_add_column, "Ajout de la colonne '$column'");
                }
            }
        } else {
            echo "<p class='success'>La structure de la table 'inscriptions' est correcte.</p>";
        }
        
        // Afficher les inscriptions existantes
        echo "<h2>Inscriptions existantes</h2>";
        
        try {
            $sql = "
            SELECT i.id, u.email as utilisateur, c.titre as cours, i.date_inscription, i.progres, i.statut
            FROM inscriptions i
            JOIN utilisateurs u ON i.id_utilisateur = u.id
            JOIN cours c ON i.id_cours = c.id
            ORDER BY i.id DESC
            LIMIT 10";
            
            $stmt = $pdo->query($sql);
            $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($inscriptions) > 0) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Utilisateur</th><th>Cours</th><th>Date d'inscription</th><th>Progrès</th><th>Statut</th></tr>";
                
                foreach ($inscriptions as $inscription) {
                    echo "<tr>";
                    echo "<td>" . $inscription['id'] . "</td>";
                    echo "<td>" . $inscription['utilisateur'] . "</td>";
                    echo "<td>" . $inscription['cours'] . "</td>";
                    echo "<td>" . $inscription['date_inscription'] . "</td>";
                    echo "<td>" . $inscription['progres'] . "%</td>";
                    echo "<td>" . $inscription['statut'] . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p class='warning'>Aucune inscription trouvée.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>✗ Erreur lors de l'affichage des inscriptions: " . $e->getMessage() . "</p>";
        }
    }
}

// Liens de retour
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='test_inscriptions.php'>Tester la table des inscriptions</a></li>";
echo "<li><a href='fix_cours_table.php'>Vérifier la table des cours</a></li>";
echo "<li><a href='fix_all_issues.php'>Corriger tous les problèmes</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
