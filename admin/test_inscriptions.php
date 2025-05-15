<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Style CSS pour une meilleure présentation
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test des inscriptions aux cours</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Test des inscriptions aux cours</h1>';

require_once __DIR__ . '/../includes/config.php';

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $stmt = $pdo->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_name = :table
                )
            ");
            $stmt->execute([':table' => $table]);
        } else {
            // MySQL
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.tables 
                WHERE table_schema = :dbname AND table_name = :table
            ");
            $stmt->execute([':dbname' => DB_NAME, ':table' => $table]);
        }
        return (bool)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return false;
    }
}

// Fonction pour obtenir la structure d'une table
function getTableStructure($pdo, $table) {
    try {
        // PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $query = "
                SELECT column_name, data_type, character_maximum_length
                FROM information_schema.columns
                WHERE table_name = :table
                ORDER BY ordinal_position
            ";
        } else {
            // MySQL
            $query = "
                SELECT column_name, data_type, character_maximum_length
                FROM information_schema.columns
                WHERE table_schema = :dbname AND table_name = :table
                ORDER BY ordinal_position
            ";
        }
        
        $stmt = $pdo->prepare($query);
        
        if (strpos(DB_URL, 'pgsql') !== false) {
            $stmt->execute([':table' => $table]);
        } else {
            $stmt->execute([':dbname' => DB_NAME, ':table' => $table]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

try {
    // 1. Vérifier si la table inscriptions existe
    echo "<h2>1. Vérification de la table inscriptions</h2>";
    
    if (tableExists($pdo, 'inscriptions')) {
        echo "<p class='success'>✓ La table inscriptions existe.</p>";
        
        // Afficher la structure de la table
        $structure = getTableStructure($pdo, 'inscriptions');
        
        echo "<h3>Structure de la table inscriptions</h3>";
        echo "<table>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Longueur</th></tr>";
        
        foreach ($structure as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['column_name']) . "</td>";
            echo "<td>" . htmlspecialchars($column['data_type']) . "</td>";
            echo "<td>" . (isset($column['character_maximum_length']) ? htmlspecialchars($column['character_maximum_length']) : '-') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Vérifier si les tables liées existent
        $utilisateurs_exists = tableExists($pdo, 'utilisateurs');
        $cours_exists = tableExists($pdo, 'cours');
        
        if (!$utilisateurs_exists) {
            echo "<p class='warning'>⚠ La table utilisateurs n'existe pas. Les relations ne pourront pas être testées.</p>";
            echo "<p>Utilisez le script <a href='fix_utilisateurs_table.php'>fix_utilisateurs_table.php</a> pour créer cette table.</p>";
        }
        
        if (!$cours_exists) {
            echo "<p class='warning'>⚠ La table cours n'existe pas. Les relations ne pourront pas être testées.</p>";
            echo "<p>Utilisez le script <a href='fix_cours_table.php'>fix_cours_table.php</a> pour créer cette table.</p>";
        }
        
        // 2. Afficher les données des inscriptions
        echo "<h2>2. Données des inscriptions</h2>";
        
        try {
            // Requête SQL pour récupérer les inscriptions avec les informations des utilisateurs et des cours
            if ($utilisateurs_exists && $cours_exists) {
                $query = "
                    SELECT 
                        i.id, 
                        i.id_utilisateur, 
                        i.id_cours, 
                        i.date_inscription, 
                        i.progres, 
                        i.statut,
                        u.nom AS nom_utilisateur, 
                        u.prenom AS prenom_utilisateur, 
                        u.email AS email_utilisateur,
                        c.titre AS titre_cours, 
                        c.description AS description_cours
                    FROM 
                        inscriptions i
                    LEFT JOIN 
                        utilisateurs u ON i.id_utilisateur = u.id
                    LEFT JOIN 
                        cours c ON i.id_cours = c.id
                    ORDER BY 
                        i.id
                ";
            } else {
                $query = "
                    SELECT 
                        id, 
                        id_utilisateur, 
                        id_cours, 
                        date_inscription, 
                        progres, 
                        statut
                    FROM 
                        inscriptions
                    ORDER BY 
                        id
                ";
            }
            
            $stmt = $pdo->query($query);
            $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($inscriptions) > 0) {
                echo "<p class='success'>✓ " . count($inscriptions) . " inscription(s) trouvée(s).</p>";
                
                echo "<table>";
                echo "<tr>";
                echo "<th>ID</th>";
                echo "<th>Utilisateur</th>";
                echo "<th>Cours</th>";
                echo "<th>Date d'inscription</th>";
                echo "<th>Progrès</th>";
                echo "<th>Statut</th>";
                echo "</tr>";
                
                foreach ($inscriptions as $inscription) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($inscription['id']) . "</td>";
                    
                    if (isset($inscription['nom_utilisateur'])) {
                        echo "<td>" . htmlspecialchars($inscription['prenom_utilisateur'] . ' ' . $inscription['nom_utilisateur']) . 
                             "<br><small>" . htmlspecialchars($inscription['email_utilisateur']) . "</small>" .
                             "<br><small>ID: " . htmlspecialchars($inscription['id_utilisateur']) . "</small></td>";
                    } else {
                        echo "<td>ID: " . htmlspecialchars($inscription['id_utilisateur']) . "</td>";
                    }
                    
                    if (isset($inscription['titre_cours'])) {
                        echo "<td>" . htmlspecialchars($inscription['titre_cours']) . 
                             "<br><small>" . htmlspecialchars(substr($inscription['description_cours'], 0, 50)) . "...</small>" .
                             "<br><small>ID: " . htmlspecialchars($inscription['id_cours']) . "</small></td>";
                    } else {
                        echo "<td>ID: " . htmlspecialchars($inscription['id_cours']) . "</td>";
                    }
                    
                    echo "<td>" . htmlspecialchars($inscription['date_inscription']) . "</td>";
                    echo "<td>" . htmlspecialchars($inscription['progres']) . "%</td>";
                    echo "<td>" . htmlspecialchars($inscription['statut']) . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠ Aucune inscription trouvée.</p>";
                echo "<p>Utilisez le script <a href='fix_inscriptions_table.php'>fix_inscriptions_table.php</a> pour créer des inscriptions de test.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>✗ Erreur lors de la récupération des inscriptions: " . $e->getMessage() . "</p>";
        }
        
        // 3. Vérifier les relations
        if ($utilisateurs_exists && $cours_exists) {
            echo "<h2>3. Vérification des relations</h2>";
            
            try {
                // Vérifier les utilisateurs inexistants
                $query = "
                    SELECT 
                        i.id, 
                        i.id_utilisateur
                    FROM 
                        inscriptions i
                    LEFT JOIN 
                        utilisateurs u ON i.id_utilisateur = u.id
                    WHERE 
                        u.id IS NULL
                ";
                
                $stmt = $pdo->query($query);
                $invalid_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($invalid_users) > 0) {
                    echo "<p class='error'>✗ " . count($invalid_users) . " inscription(s) avec des utilisateurs inexistants:</p>";
                    
                    echo "<table>";
                    echo "<tr><th>ID Inscription</th><th>ID Utilisateur</th></tr>";
                    
                    foreach ($invalid_users as $invalid) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($invalid['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($invalid['id_utilisateur']) . "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                } else {
                    echo "<p class='success'>✓ Toutes les inscriptions ont des utilisateurs valides.</p>";
                }
                
                // Vérifier les cours inexistants
                $query = "
                    SELECT 
                        i.id, 
                        i.id_cours
                    FROM 
                        inscriptions i
                    LEFT JOIN 
                        cours c ON i.id_cours = c.id
                    WHERE 
                        c.id IS NULL
                ";
                
                $stmt = $pdo->query($query);
                $invalid_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($invalid_courses) > 0) {
                    echo "<p class='error'>✗ " . count($invalid_courses) . " inscription(s) avec des cours inexistants:</p>";
                    
                    echo "<table>";
                    echo "<tr><th>ID Inscription</th><th>ID Cours</th></tr>";
                    
                    foreach ($invalid_courses as $invalid) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($invalid['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($invalid['id_cours']) . "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                } else {
                    echo "<p class='success'>✓ Toutes les inscriptions ont des cours valides.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='error'>✗ Erreur lors de la vérification des relations: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p class='error'>✗ La table inscriptions n'existe pas.</p>";
        echo "<p>Utilisez le script <a href='fix_inscriptions_table.php'>fix_inscriptions_table.php</a> pour créer cette table.</p>";
    }
    
    // Liens de navigation
    echo "<h2>Actions disponibles</h2>";
    echo "<ul>";
    echo "<li><a href='fix_inscriptions_table.php'>Corriger la table inscriptions</a></li>";
    echo "<li><a href='fix_utilisateurs_table.php'>Corriger la table utilisateurs</a></li>";
    echo "<li><a href='fix_cours_table.php'>Corriger la table cours</a></li>";
    echo "<li><a href='check_and_fix_database.php'>Vérifier et corriger la base de données</a></li>";
    echo "<li><a href='admin_scripts.php'>Tous les scripts d'administration</a></li>";
    echo "<li><a href='../index.php'>Retour à l'accueil</a></li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
