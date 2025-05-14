<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // 1. Vérifier si la table existe
    $tableExists = $pdo->query("SHOW TABLES LIKE 'administrateurs'")->rowCount() > 0;

    if (!$tableExists) {
        // 2. Créer la table administrateurs
        $sql = "CREATE TABLE administrateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255),
            prenom VARCHAR(100),
            nom VARCHAR(100),
            role ENUM('admin', 'editeur') DEFAULT 'admin',
            statut ENUM('actif', 'inactif') DEFAULT 'actif',
            derniere_connexion DATETIME,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "Table 'administrateurs' créée avec succès.<br>";
    }

    // 3. Vérifier si un administrateur existe déjà
    $stmt = $pdo->query("SELECT COUNT(*) FROM administrateurs");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // 4. Créer l'administrateur par défaut
        $sql = "INSERT INTO administrateurs (email, mot_de_passe, prenom, nom, role, statut) 
                VALUES ('admin@lawapp.com', 'admin', 'Admin', 'Principal', 'admin', 'actif')";
        $pdo->exec($sql);
        echo "Administrateur par défaut créé :<br>";
        echo "Email : admin@lawapp.com<br>";
        echo "Mot de passe : admin<br>";
    }

    // 5. Afficher tous les administrateurs
    $stmt = $pdo->query("SELECT id, email, prenom, nom, role, statut FROM administrateurs");
    echo "<h3>Liste des administrateurs :</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "<br>";
    echo "Code : " . $e->getCode() . "<br>";
    echo "Trace : <pre>" . $e->getTraceAsString() . "</pre>";
}
?>
