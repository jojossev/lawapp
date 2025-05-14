<?php
// Configuration de la base de données
$db_host = 'localhost';
$db_name = 'lawapp';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

try {
    // Ouvrir un fichier pour écrire
    $output = fopen('database_dump.sql', 'w');
    
    // Récupérer toutes les tables
    $tables = array();
    $result = $pdo->query("SHOW TABLES");
    while($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    // Pour chaque table
    foreach($tables as $table) {
        // Écrire l'instruction DROP TABLE
        fwrite($output, "DROP TABLE IF EXISTS `$table`;\n");
        
        // Obtenir la création de la table
        $result = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch(PDO::FETCH_NUM);
        fwrite($output, $row[1] . ";\n\n");
        
        // Obtenir le contenu de la table
        $result = $pdo->query("SELECT * FROM `$table`");
        $columnCount = $result->columnCount();
        
        while($row = $result->fetch(PDO::FETCH_NUM)) {
            $values = array_map(function($value) use ($pdo) {
                if($value === null) return 'NULL';
                return $pdo->quote($value);
            }, $row);
            
            fwrite($output, "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n");
        }
        fwrite($output, "\n");
    }
    
    fclose($output);
    echo "Base de données exportée avec succès dans database_dump.sql\n";
    
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
