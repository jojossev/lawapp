<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // 1. Vérifier si la table existe
    $tableExists = $pdo->query("SHOW TABLES LIKE 'administrateurs'")->rowCount() > 0;
    
    if (!$tableExists) {
        // 2. Créer la table si elle n'existe pas
        $sql = "CREATE TABLE administrateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            nom VARCHAR(100) NOT NULL,
            role ENUM('admin', 'editeur') NOT NULL DEFAULT 'admin',
            statut ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif',
            derniere_connexion DATETIME,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "Table 'administrateurs' créée avec succès.<br>";
        
        // 3. Insérer l'administrateur par défaut
        $stmt = $pdo->prepare("INSERT INTO administrateurs (email, mot_de_passe, prenom, nom, role, statut) 
                              VALUES (:email, :password, :prenom, :nom, :role, :statut)");
        
        $stmt->execute([
            'email' => 'admin@lawapp.com',
            'password' => 'admin',
            'prenom' => 'Admin',
            'nom' => 'Principal',
            'role' => 'admin',
            'statut' => 'actif'
        ]);
        
        echo "Administrateur par défaut créé :<br>";
        echo "Email : admin@lawapp.com<br>";
        echo "Mot de passe : admin<br>";
    } else {
        echo "La table 'administrateurs' existe déjà.<br>";
        
        // 4. Afficher la structure de la table
        $stmt = $pdo->query("DESCRIBE administrateurs");
        echo "<h3>Structure actuelle de la table :</h3>";
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
        
        // 5. Afficher les administrateurs
        $stmt = $pdo->query("SELECT id, email, prenom, nom, role, statut FROM administrateurs");
        echo "<h3>Liste des administrateurs :</h3>";
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
