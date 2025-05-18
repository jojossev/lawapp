<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db_connect.php';

try {
    // Vérifier le type de base de données
    $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    // Requête adaptée pour PostgreSQL
    if ($driver_name === 'pgsql') {
        $alterQuery = "
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT column_name 
                    FROM information_schema.columns 
                    WHERE table_name = 'livres' AND column_name = 'image_url'
                ) THEN
                    ALTER TABLE livres ADD COLUMN image_url VARCHAR(255) DEFAULT 'default_book_image.jpg';
                END IF;
            END $$;
        ";
    } else {
        // Requête pour MySQL
        $alterQuery = "
            ALTER TABLE livres 
            ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) DEFAULT 'default_book_image.jpg'
        ";
    }
    
    // Exécuter la requête
    $pdo->exec($alterQuery);
    
    // Mettre à jour les enregistrements existants
    $updateQuery = "
        UPDATE livres 
        SET image_url = 'default_book_image.jpg' 
        WHERE image_url IS NULL OR image_url = ''
    ";
    $pdo->exec($updateQuery);
    
    // Afficher la structure de la table
    $table_info_query = $driver_name === 'pgsql' 
        ? "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'livres'"
        : "DESCRIBE livres";
    
    $stmt = $pdo->query($table_info_query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nStructure actuelle de la table livres :\n";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    echo "Colonne image_url ajoutée et mise à jour avec succès.\n";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}

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
            try {
                // Déterminer le type de base de données
                $isPostgreSQL = strpos(DATABASE_URL, 'pgsql') !== false;
                
                // Requête pour ajouter la colonne
                if ($isPostgreSQL) {
                    // PostgreSQL
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
                } else {
                    // MySQL
                    $alterQuery = "ALTER TABLE $table ADD COLUMN IF NOT EXISTS $column $type";
                }
                
                // Exécuter la requête d'ajout de colonne
                $pdo->exec($alterQuery);
                echo "Colonne $column ajoutée à la table $table.\n";
                
                // Mettre à jour les enregistrements existants
                $updateQuery = "
                    UPDATE $table 
                    SET $column = COALESCE($column, 'default_book_image.jpg')
                    WHERE $column IS NULL
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
