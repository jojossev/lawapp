<?php
require_once __DIR__ . '/../includes/config.php';

try {
    $result = $pdo->query("SHOW TABLES LIKE 'quiz'")->fetchAll();
    var_dump($result);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
