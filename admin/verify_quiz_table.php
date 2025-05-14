<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';

try {
    // Vérifier si la table quiz existe
    $tables = $pdo->query("SHOW TABLES LIKE 'quiz'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables trouvées : \n";
    var_dump($tables);

    if (!empty($tables)) {
        // Vérifier la structure de la table
        echo "\nStructure de la table quiz :\n";
        $columns = $pdo->query("SHOW COLUMNS FROM quiz")->fetchAll(PDO::FETCH_ASSOC);
        var_dump($columns);

        // Vérifier les clés étrangères
        echo "\nClés étrangères de la table quiz :\n";
        $foreignKeys = $pdo->query("
            SELECT *
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'quiz'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ")->fetchAll(PDO::FETCH_ASSOC);
        var_dump($foreignKeys);

        // Vérifier le contenu de la table
        echo "\nContenu de la table quiz :\n";
        $content = $pdo->query("SELECT * FROM quiz")->fetchAll(PDO::FETCH_ASSOC);
        var_dump($content);
    } else {
        echo "La table quiz n'existe pas !";
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    echo "Code : " . $e->getCode() . "\n";
}
?>
