<?php
require_once __DIR__ . '/../includes/config.php';

try {
    echo "Structure de la table quiz:\n";
    $stmt = $pdo->query('DESCRIBE quiz');
    if ($stmt) {
        var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo "Erreur lors de la description de la table\n";
        print_r($pdo->errorInfo());
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>
