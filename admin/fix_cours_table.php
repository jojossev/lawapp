<?php
// Désactiver la mise en mémoire tampon de sortie
ob_start();

// Configuration des rapports d"erreurs
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(E_ALL);

// Inclusion des fichiers de configuration
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/db_connect.php";

function fixCoursTable($pdo) {
    $results = [];
    $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    // Requête de vérification de l"existence de la table
    $table_check_query = $driver_name === "pgsql" 
        ? "SELECT EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema = "public" AND table_name = "cours")"
        : "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = "cours"";

    $table_exists = $pdo->query($table_check_query)->fetchColumn();

    if (!$table_exists) {
        // Création de la table cours
        $create_table_query = $driver_name === "pgsql" 
            ? "CREATE TABLE cours (
                id SERIAL PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                description TEXT,
                image VARCHAR(255),
                categorie_id INT,
                duree INT DEFAULT 0,
                niveau VARCHAR(50) DEFAULT "débutant",
                prix DECIMAL(10, 2) DEFAULT 0.00,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                statut VARCHAR(50) DEFAULT "actif"
            )"
            : "CREATE TABLE cours (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                titre VARCHAR(255) NOT NULL,
                description TEXT,
                image VARCHAR(255),
                categorie_id INT,
                duree INT DEFAULT 0,
                niveau VARCHAR(50) DEFAULT "débutant",
                prix DECIMAL(10, 2) DEFAULT 0.00,
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                date_mise_a_jour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                statut VARCHAR(50) DEFAULT "actif"
            ) ENGINE=InnoDB";

        try {
            $pdo->exec($create_table_query);
            $results[] = "Table cours créée avec succès";

            // Insertion de données de test
            $test_data_query = "
                INSERT INTO cours (titre, description, image, categorie_id, duree, niveau, prix, statut) VALUES
                ("Droit civil fondamental", "Introduction complète au droit civil", "droit_civil.jpg", 1, 120, "débutant", 49.99, "actif"),
                ("Procédure pénale avancée", "Techniques et stratégies juridiques", "droit_penal.jpg", 2, 180, "avancé", 79.99, "actif")
            ";
            $pdo->exec($test_data_query);
            $results[] = "Données de test insérées avec succès";
        } catch (PDOException $e) {
            $results[] = "Erreur : " . $e->getMessage();
        }
    }

    return $results;
}

try {
    $results = fixCoursTable($pdo);

    // Nettoyer tous les buffers de sortie
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Affichage des résultats
    echo "<!DOCTYPE html>";
    echo "<html lang=\"fr\">";
    echo "<head>";
    echo "<meta charset=\"UTF-8\">";
    echo "<title>Correction de la table cours</title>";
    echo "<style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>";
    echo "</head>";
    echo "<body>";
    echo "<h1>Résultat de la correction de la table cours</h1>";

    foreach ($results as $result) {
        echo "<p class=\"" . (strpos($result, "succès") !== false ? "success" : "error") . "\">";
        echo htmlspecialchars($result);
        echo "</p>";
    }

    echo "</body>";
    echo "</html>";

} catch (Exception $e) {
    error_log("Erreur critique : " . $e->getMessage());
    echo "Une erreur critique s"est produite.";
}
