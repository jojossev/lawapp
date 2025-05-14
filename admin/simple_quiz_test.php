<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure uniquement les fichiers essentiels
require_once __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Simple des Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Test Simple des Quiz</h1>
        
        <?php
        try {
            // 1. Vérifier la connexion à la base de données
            echo '<div class="alert alert-info">';
            echo '<h4>1. État de la connexion</h4>';
            echo '<p>Version MySQL : ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . '</p>';
            echo '</div>';

            // 2. Vérifier la leçon
            $lecon_id = 1; // ID fixe pour le test
            $stmt = $pdo->prepare("SELECT * FROM lecons WHERE id = ?");
            $stmt->execute([$lecon_id]);
            $lecon = $stmt->fetch(PDO::FETCH_ASSOC);

            echo '<div class="alert alert-info">';
            echo '<h4>2. Données de la leçon</h4>';
            echo '<pre>';
            print_r($lecon);
            echo '</pre>';
            echo '</div>';

            // 3. Vérifier les quiz
            $stmt = $pdo->prepare("SELECT * FROM quiz WHERE id_lecon = ?");
            $stmt->execute([$lecon_id]);
            $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<div class="alert alert-info">';
            echo '<h4>3. Quiz trouvés</h4>';
            if (empty($quizzes)) {
                echo '<p>Aucun quiz trouvé pour cette leçon.</p>';
            } else {
                echo '<pre>';
                print_r($quizzes);
                echo '</pre>';
            }
            echo '</div>';

            // 4. Afficher les quiz dans une interface simple
            if (!empty($quizzes)) {
                echo '<h2>Liste des Quiz</h2>';
                foreach ($quizzes as $quiz) {
                    echo '<div class="card mb-3">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($quiz['titre']) . '</h5>';
                    echo '<p class="card-text">' . htmlspecialchars($quiz['description']) . '</p>';
                    echo '<ul class="list-group list-group-flush">';
                    echo '<li class="list-group-item">Durée : ' . $quiz['duree_limite'] . ' minutes</li>';
                    echo '<li class="list-group-item">Questions : ' . $quiz['nombre_questions'] . '</li>';
                    echo '<li class="list-group-item">Score minimum : ' . $quiz['score_minimum'] . '%</li>';
                    echo '<li class="list-group-item">Statut : ' . $quiz['statut'] . '</li>';
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                }
            }

        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">';
            echo '<h4>Erreur SQL</h4>';
            echo '<p>Message : ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>Code : ' . htmlspecialchars($e->getCode()) . '</p>';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">';
            echo '<h4>Erreur générale</h4>';
            echo '<p>Message : ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>Code : ' . htmlspecialchars($e->getCode()) . '</p>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
