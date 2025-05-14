<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Liste des tables à supprimer
    $tables = [
        'quiz_reponses_utilisateurs',
        'quiz_resultats',
        'quiz_reponses',
        'quiz_questions',
        'quiz'
    ];

    // 1. Désactiver les contraintes de clé étrangère
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    echo "<h3>Suppression des tables de quiz :</h3>";

    // 2. Supprimer chaque table
    foreach ($tables as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table");
            echo "Table '$table' supprimée avec succès.<br>";
        } catch (PDOException $e) {
            echo "Erreur lors de la suppression de la table '$table' : " . $e->getMessage() . "<br>";
        }
    }

    // 3. Réactiver les contraintes de clé étrangère
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

    // 4. Vérifier les tables restantes
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h3>Tables restantes dans la base de données :</h3>";
    echo "<pre>";
    print_r($tables);
    echo "</pre>";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
} finally {
    // S'assurer que les contraintes de clé étrangère sont réactivées
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}
?>
