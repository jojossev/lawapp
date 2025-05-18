<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db_connect.php';

// Fonction pour ajouter une colonne de manière sécurisée
function addColumnSafely($pdo, $table, $column, $type) {
    try {
        // Vérifier si la colonne existe déjà
        $checkQuery = $pdo->prepare("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = :table AND column_name = :column
        ");
        $checkQuery->execute([
            ':table' => $table,
            ':column' => $column
        ]);
        
        // Si la colonne n'existe pas, l'ajouter
        if ($checkQuery->rowCount() == 0) {
            $alterQuery = "ALTER TABLE $table ADD COLUMN $column $type";
            
            // Gestion des différences entre PostgreSQL et MySQL
            if (strpos(DATABASE_URL, 'pgsql') !== false) {
                $alterQuery = "
                    DO $$
                    BEGIN
                        IF NOT EXISTS (
                            SELECT column_name 
                            FROM information_schema.columns 
                            WHERE table_name = '$table' AND column_name = '$column'
                        ) THEN
                            ALTER TABLE $table ADD COLUMN $column $type;
                        END IF;
                    END $$;
                ";
            }
            
            $pdo->exec($alterQuery);
            echo "Colonne $column ajoutée à la table $table.\n";
        } else {
            echo "La colonne $column existe déjà dans la table $table.\n";
        }
        
        // Mettre à jour les livres existants avec une image par défaut
        $updateQuery = "
            UPDATE $table 
            SET $column = COALESCE($column, 'default_book_image.jpg')
            WHERE $column IS NULL
        ";
        $pdo->exec($updateQuery);
        echo "Mise à jour des livres existants avec une image par défaut.\n";
        
    } catch (PDOException $e) {
        echo "Erreur lors de l'ajout de la colonne $column : " . $e->getMessage() . "\n";
    }
}

// Ajouter la colonne image_url à la table livres
addColumnSafely($pdo, 'livres', 'image_url', 'VARCHAR(255)');

// Afficher la structure actuelle de la table
try {
    $tableInfoQuery = $pdo->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'livres'
        ORDER BY ordinal_position
    ");
    
    echo "\nStructure actuelle de la table livres :\n";
    while ($row = $tableInfoQuery->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['column_name']} ({$row['data_type']})\n";
    }
} catch (PDOException $e) {
    echo "Erreur lors de la récupération de la structure de la table : " . $e->getMessage() . "\n";
}
