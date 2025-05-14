<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Afficher la structure de la table
    $stmt = $pdo->query("DESCRIBE administrateurs");
    echo "<h3>Structure de la table administrateurs :</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    // Afficher les administrateurs (sans les mots de passe)
    $stmt = $pdo->query("SELECT id, email, prenom, nom, role, statut FROM administrateurs");
    echo "<h3>Liste des administrateurs :</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

    // Afficher les mots de passe actuels
    $stmt = $pdo->query("SELECT email, mot_de_passe FROM administrateurs");
    echo "<h3>Mots de passe actuels :</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
