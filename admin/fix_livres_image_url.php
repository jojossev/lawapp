<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db_connect.php';

try {
    // Déterminer le type de base de données
    $dbType = parse_url(DATABASE_URL, PHP_URL_SCHEME) ?: 'mysql';

    // Requête pour ajouter la colonne image_url
    if ($dbType === 'pgsql') {
        // PostgreSQL
        $query = "
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT column_name 
                    FROM information_schema.columns 
                    WHERE table_name = 'livres' AND column_name = 'image_url'
                ) THEN
                    ALTER TABLE livres ADD COLUMN image_url VARCHAR(255);
                END IF;
            END $$;
        ";
    } else {
        // MySQL
        $query = "
            ALTER TABLE livres 
            ADD COLUMN IF NOT EXISTS image_url VARCHAR(255);
        ";
    }

    // Exécuter la requête
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    echo "Colonne image_url ajoutée avec succès à la table livres.\n";

    // Mettre à jour les livres existants avec une image par défaut
    $updateQuery = "
        UPDATE livres 
        SET image_url = COALESCE(image_url, 'default_book_image.jpg')
        WHERE image_url IS NULL
    ";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute();

    echo "Mise à jour des livres existants avec une image par défaut.\n";

} catch (PDOException $e) {
    echo "Erreur lors de l'ajout de la colonne image_url : " . $e->getMessage() . "\n";
}
