<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Vérifier si la table existe
    $sql = "SHOW TABLES LIKE 'utilisateurs'";
    $result = $pdo->query($sql);
    
    if ($result->rowCount() == 0) {
        // Créer la table si elle n'existe pas
        $sql = "CREATE TABLE utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            prenom VARCHAR(100),
            nom VARCHAR(100),
            type_compte ENUM('etudiant', 'professeur', 'admin') DEFAULT 'etudiant',
            statut ENUM('actif', 'inactif') DEFAULT 'actif',
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            photo_profil VARCHAR(255) DEFAULT NULL,
            bio TEXT,
            preferences JSON
        )";
        $pdo->exec($sql);
        echo "Table 'utilisateurs' créée avec succès.\n";
    } else {
        // Vérifier et ajouter les colonnes manquantes
        $colonnes = [
            'email' => 'VARCHAR(255) NOT NULL UNIQUE',
            'mot_de_passe' => 'VARCHAR(255) NOT NULL',
            'prenom' => 'VARCHAR(100)',
            'nom' => 'VARCHAR(100)',
            'type_compte' => "ENUM('etudiant', 'professeur', 'admin') DEFAULT 'etudiant'",
            'statut' => "ENUM('actif', 'inactif') DEFAULT 'actif'",
            'date_inscription' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'derniere_connexion' => 'TIMESTAMP NULL',
            'photo_profil' => 'VARCHAR(255) DEFAULT NULL',
            'bio' => 'TEXT',
            'preferences' => 'JSON'
        ];
        
        // Récupérer les colonnes existantes
        $sql = "SHOW COLUMNS FROM utilisateurs";
        $result = $pdo->query($sql);
        $colonnes_existantes = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $colonnes_existantes[] = $row['Field'];
        }
        
        // Ajouter les colonnes manquantes
        foreach($colonnes as $nom => $definition) {
            if (!in_array($nom, $colonnes_existantes)) {
                $sql = "ALTER TABLE utilisateurs ADD COLUMN $nom $definition";
                $pdo->exec($sql);
                echo "Colonne '$nom' ajoutée avec succès.\n";
            }
        }
        
        echo "Structure de la table 'utilisateurs' vérifiée et mise à jour.\n";
    }

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
