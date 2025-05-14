<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';

try {
    echo "Test de connexion à la base de données...\n\n";

    // 1. Vérifier la connexion
    echo "1. État de la connexion :\n";
    echo "Base de données : " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
    echo "Version du serveur : " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";

    // 2. Vérifier la table lecons
    echo "2. Test de la table lecons :\n";
    $stmt = $pdo->prepare("SELECT l.*, m.titre as module_titre, c.titre as cours_titre 
                          FROM lecons l 
                          JOIN modules m ON l.id_module = m.id 
                          JOIN cours c ON m.id_cours = c.id 
                          WHERE l.id = ?");
    $stmt->execute([1]);
    $lecon = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Leçon trouvée :\n";
    print_r($lecon);
    echo "\n";

    // 3. Vérifier la table quiz
    echo "3. Test de la table quiz :\n";
    $stmt = $pdo->prepare("SELECT * FROM quiz WHERE id_lecon = ?");
    $stmt->execute([1]);
    $quiz = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Quiz trouvés :\n";
    print_r($quiz);
    echo "\n";

    // 4. Vérifier les permissions
    echo "4. Test des permissions :\n";
    $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Permissions de l'utilisateur actuel :\n";
    print_r($permissions);
    echo "\n";

    // 5. Vérifier les tables existantes
    echo "5. Tables existantes dans la base de données :\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($tables);

} catch (PDOException $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    echo "Code d'erreur : " . $e->getCode() . "\n";
    echo "Trace :\n" . $e->getTraceAsString() . "\n";
}
?>
