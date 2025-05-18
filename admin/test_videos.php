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
    <title>Test de la table videos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
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
    <h1>Test de la table videos</h1>';

require_once __DIR__ . '/../includes/config.php';

try {
    // 1. Vérifier si la table videos existe
    echo "<h2>1. Vérification de la table videos</h2>";
    
    if (strpos(DB_URL, 'pgsql') !== false) {
        // PostgreSQL
        $stmt = $pdo->prepare("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_name = 'videos'
            )
        ");
    } else {
        // MySQL
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = :dbname AND table_name = 'videos'
        ");
        $stmt->execute([':dbname' => DB_NAME]);
    }
    
    $tableExists = $stmt->fetchColumn();
    
    if ($tableExists) {
        echo "<p class='success'>La table 'videos' existe.</p>";
        
        // 2. Vérifier la structure de la table videos
        echo "<h2>2. Structure de la table videos</h2>";
        
        if (strpos(DB_URL, 'pgsql') !== false) {
            // PostgreSQL
            $sql = "
                SELECT column_name, data_type, character_maximum_length, column_default, is_nullable
                FROM information_schema.columns
                WHERE table_name = 'videos'
                ORDER BY ordinal_position
            ";
        } else {
            // MySQL
            $sql = "
                SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT, IS_NULLABLE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'videos'
                ORDER BY ORDINAL_POSITION
            ";
        }
        
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Longueur</th><th>Défaut</th><th>Nullable</th></tr>";
        
        $hasIdCreateur = false;
        
        foreach ($columns as $column) {
            $columnName = $column['column_name'] ?? $column['COLUMN_NAME'];
            $dataType = $column['data_type'] ?? $column['DATA_TYPE'];
            $maxLength = $column['character_maximum_length'] ?? $column['CHARACTER_MAXIMUM_LENGTH'] ?? '';
            $default = $column['column_default'] ?? $column['COLUMN_DEFAULT'] ?? '';
            $nullable = $column['is_nullable'] ?? $column['IS_NULLABLE'];
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($columnName) . "</td>";
            echo "<td>" . htmlspecialchars($dataType) . "</td>";
            echo "<td>" . htmlspecialchars($maxLength) . "</td>";
            echo "<td>" . htmlspecialchars($default) . "</td>";
            echo "<td>" . htmlspecialchars($nullable) . "</td>";
            echo "</tr>";
            
            if (strtolower($columnName) === 'id_createur') {
                $hasIdCreateur = true;
            }
        }
        
        echo "</table>";
        
        if (!$hasIdCreateur) {
            echo "<p class='error'>La colonne 'id_createur' n'existe pas dans la table 'videos'.</p>";
            echo "<p class='info'>Vous pouvez exécuter le script <a href='fix_videos_structure.php'>fix_videos_structure.php</a> pour corriger ce problème.</p>";
        } else {
            echo "<p class='success'>La colonne 'id_createur' existe dans la table 'videos'.</p>";
        }
        
        // 3. Tester une requête SQL sur la table videos
        echo "<h2>3. Test de requête SQL</h2>";
        
        try {
            $sql = "
                SELECT 
                    v.id, 
                    v.titre, 
                    v.description,
                    v.url_video,
                    v.type_video,
                    v.duree,
                    v.miniature_url,
                    v.niveau,
                    v.prix,
                    v.statut,
                    v.id_createur,
                    v.date_creation,
                    v.date_mise_a_jour,
                    cv.nom AS nom_categorie
                FROM videos v
                LEFT JOIN categories_videos cv ON v.id_categorie = cv.id
                LIMIT 5
            ";
            
            $stmt = $pdo->query($sql);
            $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($videos) > 0) {
                echo "<p class='success'>Requête SQL exécutée avec succès. " . count($videos) . " vidéo(s) trouvée(s).</p>";
                
                echo "<table>";
                echo "<tr>";
                foreach (array_keys($videos[0]) as $key) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                
                foreach ($videos as $video) {
                    echo "<tr>";
                    foreach ($video as $value) {
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>Aucune vidéo trouvée dans la base de données.</p>";
                echo "<p class='info'>Vous pouvez exécuter le script <a href='fix_videos_structure.php'>fix_videos_structure.php</a> pour ajouter des données de test.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'exécution de la requête SQL : " . $e->getMessage() . "</p>";
            echo "<p class='info'>Vous pouvez exécuter le script <a href='fix_videos_structure.php'>fix_videos_structure.php</a> pour corriger les problèmes de structure.</p>";
        }
    } else {
        echo "<p class='error'>La table 'videos' n'existe pas.</p>";
        echo "<p class='info'>Vous pouvez exécuter le script <a href='fix_videos_structure.php'>fix_videos_structure.php</a> pour créer la table et ajouter des données de test.</p>";
    }
    
    echo "<h2>Conclusion</h2>";
    echo "<p>Ce test permet de vérifier l'existence et la structure de la table 'videos', ainsi que la présence de la colonne 'id_createur'.</p>";
    echo "<p>Si des problèmes sont détectés, vous pouvez exécuter le script <a href='fix_videos_structure.php'>fix_videos_structure.php</a> pour les corriger.</p>";
    echo "<p><a href='../index.php'>Retour à l'accueil</a> | <a href='test_db_connection.php'>Tester la connexion à la base de données</a></p>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
