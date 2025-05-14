<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // 1. D'abord, afficher les mots de passe actuels
    $stmt = $pdo->query("SELECT email, mot_de_passe FROM administrateurs");
    echo "<h3>Mots de passe avant modification :</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    // 2. Mettre à jour tous les mots de passe en texte clair
    $stmt = $pdo->query("UPDATE administrateurs SET mot_de_passe = 'admin'");
    
    if ($stmt->rowCount() > 0) {
        echo "<h3>Mots de passe réinitialisés avec succès !</h3>";
        echo "Tous les administrateurs ont maintenant le mot de passe : admin<br><br>";
    }

    // 3. Vérifier les nouveaux mots de passe
    $stmt = $pdo->query("SELECT email, mot_de_passe FROM administrateurs");
    echo "<h3>Mots de passe après modification :</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
