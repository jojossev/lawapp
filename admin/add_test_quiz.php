<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';

try {
    // Ajouter un quiz de test
    $sql = "INSERT INTO quiz (id_lecon, titre, description, duree_limite, nombre_questions, score_minimum, statut) 
            VALUES (:id_lecon, :titre, :description, :duree_limite, :nombre_questions, :score_minimum, :statut)";
    
    $stmt = $pdo->prepare($sql);
    $params = [
        ':id_lecon' => 1,
        ':titre' => 'Quiz de test - Introduction au Droit',
        ':description' => 'Un quiz pour tester vos connaissances en droit',
        ':duree_limite' => 30, // 30 minutes
        ':nombre_questions' => 10,
        ':score_minimum' => 70,
        ':statut' => 'brouillon'
    ];
    
    if ($stmt->execute($params)) {
        $quiz_id = $pdo->lastInsertId();
        echo "Quiz créé avec succès ! ID = $quiz_id\n";
    } else {
        echo "Erreur lors de la création du quiz :\n";
        print_r($stmt->errorInfo());
    }
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    echo "Code : " . $e->getCode() . "\n";
}
?>
