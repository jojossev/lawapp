<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';

try {
    // Forcer une erreur SQL
    $stmt = $pdo->prepare("SELECT * FROM table_qui_nexiste_pas");
    $stmt->execute();
} catch (PDOException $e) {
    echo "Message d'erreur brut :<br>";
    echo $e->getMessage() . "<br><br>";
    
    echo "Code d'erreur :<br>";
    echo $e->getCode() . "<br><br>";
    
    echo "Trace :<br>";
    echo nl2br($e->getTraceAsString());
}
?>
