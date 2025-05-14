<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Créer la table des transactions
    $sql = "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_type ENUM('cours', 'livre', 'podcast') NOT NULL,
        product_id INT NOT NULL,
        montant DECIMAL(10,2) NOT NULL,
        transaction_id VARCHAR(50) NOT NULL,
        statut ENUM('pending', 'completed', 'failed') NOT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
    )";
    
    $pdo->exec($sql);
    echo "Table 'transactions' créée avec succès.\n";

} catch(PDOException $e) {
    die("Erreur lors de la création de la table transactions : " . $e->getMessage());
}
?>
