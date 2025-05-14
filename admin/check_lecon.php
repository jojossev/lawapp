<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';

// Récupérer l'ID de la leçon depuis les arguments de ligne de commande
$lecon_id = 1; // ID fixé à 1 pour ce test

try {
    // Vérifier si la leçon existe
    echo "Recherche de la leçon avec ID = $lecon_id :\n";
    $stmt = $pdo->prepare("SELECT * FROM lecons WHERE id = ?");
    $stmt->execute([$lecon_id]);
    $lecon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lecon) {
        echo "\nLeçon trouvée :\n";
        var_dump($lecon);
        
        // Vérifier les quiz associés
        echo "\nQuiz associés à cette leçon :\n";
        $stmt = $pdo->prepare("SELECT * FROM quiz WHERE id_lecon = ?");
        $stmt->execute([$lecon_id]);
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        var_dump($quizzes);
        
    } else {
        echo "\nAucune leçon trouvée avec cet ID.\n";
        
        echo "\nListe de toutes les leçons disponibles :\n";
        $stmt = $pdo->query("SELECT id, titre FROM lecons ORDER BY id");
        $lecons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        var_dump($lecons);
    }
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    echo "Code : " . $e->getCode() . "\n";
}
?>
