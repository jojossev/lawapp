<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';

try {
    // Vérifier le contenu de la table quiz
    $stmt = $pdo->query('SELECT * FROM quiz');
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Contenu de la table quiz :\n";
    print_r($quizzes);
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    echo "Code : " . $e->getCode() . "\n";
}
?>
