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
    <title>Test des tables lecons et quiz</title>
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
    <h1>Test des tables lecons et quiz</h1>';

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
        echo "<p class='error'>Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

try {
    // 1. Vérifier si les tables existent
    echo "<h2>1. Vérification des tables</h2>";
    
    $tables = ['modules', 'lecons', 'quiz', 'questions', 'reponses'];
    $missing_tables = [];
    
    echo "<table>";
    echo "<tr><th>Table</th><th>Statut</th></tr>";
    
    foreach ($tables as $table) {
        $exists = tableExists($pdo, $table);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($table) . "</td>";
        
        if ($exists) {
            echo "<td class='success'>Existe</td>";
        } else {
            echo "<td class='error'>Manquante</td>";
            $missing_tables[] = $table;
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    if (count($missing_tables) > 0) {
        echo "<p class='warning'>Certaines tables sont manquantes. Veuillez exécuter le script <a href='fix_lecons_quiz_tables.php'>fix_lecons_quiz_tables.php</a> pour les créer.</p>";
    } else {
        echo "<p class='success'>Toutes les tables nécessaires existent.</p>";
    }
    
    // 2. Tester les données dans la table lecons
    echo "<h2>2. Test de la table lecons</h2>";
    
    if (tableExists($pdo, 'lecons')) {
        try {
            $sql = "SELECT COUNT(*) FROM lecons";
            $count = $pdo->query($sql)->fetchColumn();
            
            echo "<p>Nombre de leçons dans la base de données: <strong>" . $count . "</strong></p>";
            
            if ($count > 0) {
                $sql = "
                    SELECT 
                        l.id, 
                        l.titre, 
                        SUBSTRING(l.contenu, 1, 100) AS contenu_apercu, 
                        l.duree, 
                        l.ordre, 
                        m.titre AS module_titre
                    FROM lecons l
                    LEFT JOIN modules m ON l.id_module = m.id
                    ORDER BY m.titre, l.ordre
                    LIMIT 10
                ";
                
                $stmt = $pdo->query($sql);
                $lecons = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h3>Aperçu des leçons</h3>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Titre</th><th>Contenu (aperçu)</th><th>Durée (min)</th><th>Ordre</th><th>Module</th></tr>";
                
                foreach ($lecons as $lecon) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($lecon['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($lecon['titre']) . "</td>";
                    echo "<td>" . htmlspecialchars($lecon['contenu_apercu']) . "...</td>";
                    echo "<td>" . htmlspecialchars($lecon['duree']) . "</td>";
                    echo "<td>" . htmlspecialchars($lecon['ordre']) . "</td>";
                    echo "<td>" . htmlspecialchars($lecon['module_titre']) . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p class='warning'>Aucune leçon trouvée dans la base de données.</p>";
                echo "<p class='info'>Exécutez le script <a href='fix_lecons_quiz_tables.php'>fix_lecons_quiz_tables.php</a> pour ajouter des données de test.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de la récupération des leçons: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>La table 'lecons' n'existe pas.</p>";
    }
    
    // 3. Tester les données dans la table quiz
    echo "<h2>3. Test de la table quiz</h2>";
    
    if (tableExists($pdo, 'quiz')) {
        try {
            $sql = "SELECT COUNT(*) FROM quiz";
            $count = $pdo->query($sql)->fetchColumn();
            
            echo "<p>Nombre de quiz dans la base de données: <strong>" . $count . "</strong></p>";
            
            if ($count > 0) {
                $sql = "
                    SELECT 
                        q.id, 
                        q.titre, 
                        q.description, 
                        l.titre AS lecon_titre,
                        (SELECT COUNT(*) FROM questions WHERE id_quiz = q.id) AS nb_questions
                    FROM quiz q
                    LEFT JOIN lecons l ON q.id_lecon = l.id
                    ORDER BY q.id
                    LIMIT 10
                ";
                
                $stmt = $pdo->query($sql);
                $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h3>Aperçu des quiz</h3>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Titre</th><th>Description</th><th>Leçon</th><th>Nb Questions</th></tr>";
                
                foreach ($quizzes as $quiz) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($quiz['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($quiz['titre']) . "</td>";
                    echo "<td>" . htmlspecialchars($quiz['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($quiz['lecon_titre']) . "</td>";
                    echo "<td>" . htmlspecialchars($quiz['nb_questions']) . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
                
                // Afficher les questions et réponses pour le premier quiz
                if (count($quizzes) > 0) {
                    $first_quiz_id = $quizzes[0]['id'];
                    
                    $sql = "
                        SELECT 
                            q.id, 
                            q.texte, 
                            q.type, 
                            q.points
                        FROM questions q
                        WHERE q.id_quiz = :quiz_id
                        ORDER BY q.id
                    ";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':quiz_id' => $first_quiz_id]);
                    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($questions) > 0) {
                        echo "<h3>Questions du quiz: " . htmlspecialchars($quizzes[0]['titre']) . "</h3>";
                        
                        foreach ($questions as $question) {
                            echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
                            echo "<p><strong>Question:</strong> " . htmlspecialchars($question['texte']) . "</p>";
                            echo "<p><strong>Type:</strong> " . htmlspecialchars($question['type']) . " | <strong>Points:</strong> " . htmlspecialchars($question['points']) . "</p>";
                            
                            // Récupérer les réponses pour cette question
                            if (tableExists($pdo, 'reponses')) {
                                $sql = "
                                    SELECT 
                                        r.id, 
                                        r.texte, 
                                        r.est_correcte
                                    FROM reponses r
                                    WHERE r.id_question = :question_id
                                    ORDER BY r.id
                                ";
                                
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([':question_id' => $question['id']]);
                                $reponses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (count($reponses) > 0) {
                                    echo "<p><strong>Réponses:</strong></p>";
                                    echo "<ul>";
                                    
                                    foreach ($reponses as $reponse) {
                                        $class = $reponse['est_correcte'] ? 'success' : '';
                                        echo "<li class='" . $class . "'>" . htmlspecialchars($reponse['texte']) . ($reponse['est_correcte'] ? ' ✓' : '') . "</li>";
                                    }
                                    
                                    echo "</ul>";
                                } else {
                                    echo "<p class='warning'>Aucune réponse trouvée pour cette question.</p>";
                                }
                            }
                            
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='warning'>Aucune question trouvée pour ce quiz.</p>";
                    }
                }
            } else {
                echo "<p class='warning'>Aucun quiz trouvé dans la base de données.</p>";
                echo "<p class='info'>Exécutez le script <a href='fix_lecons_quiz_tables.php'>fix_lecons_quiz_tables.php</a> pour ajouter des données de test.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de la récupération des quiz: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>La table 'quiz' n'existe pas.</p>";
    }
    
    echo "<h2>Conclusion</h2>";
    
    if (count($missing_tables) > 0) {
        echo "<p class='warning'>Certaines tables sont manquantes. Veuillez exécuter le script <a href='fix_lecons_quiz_tables.php'>fix_lecons_quiz_tables.php</a> pour les créer.</p>";
    } else {
        echo "<p class='success'>Toutes les tables nécessaires existent.</p>";
    }
    
    echo "<p><a href='../index.php'>Retour à l'accueil</a> | <a href='test_db_connection.php'>Tester la connexion à la base de données</a> | <a href='check_and_fix_database.php'>Vérifier et corriger la base de données</a></p>";

} catch (PDOException $e) {
    echo "<div class='error'>ERREUR : " . $e->getMessage() . "</div>";
}

echo '</body>
</html>';
?>
