<?php
/**
 * Utilitaire de compatibilité de base de données pour LawApp
 * Gère les différences de syntaxe SQL entre MySQL et PostgreSQL
 */

function getCreateTableSQL($table_name, $columns, $driver_name) {
    // Colonnes communes à tous les types de bases de données
    $common_columns = [];
    
    // Transformer les colonnes en fonction du type de base de données
    foreach ($columns as $column => $definition) {
        $transformed_definition = $definition;
        
        if ($driver_name === 'pgsql') {
            // Transformations spécifiques à PostgreSQL
            $transformed_definition = str_replace('AUTO_INCREMENT', 'SERIAL', $transformed_definition);
            $transformed_definition = preg_replace('/INT\s+PRIMARY\s+KEY/', 'SERIAL PRIMARY KEY', $transformed_definition);
        } elseif ($driver_name === 'mysql') {
            // Transformations spécifiques à MySQL
            $transformed_definition = str_replace('SERIAL', 'INT', $transformed_definition);
        }
        
        $common_columns[$column] = $transformed_definition;
    }
    
    // Construire la requête SQL
    $columns_sql = [];
    foreach ($common_columns as $column => $definition) {
        $columns_sql[] = "$column $definition";
    }
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (\n    " . 
           implode(",\n    ", $columns_sql) . 
           "\n)";
    
    return $sql;
}

function createTableIfNotExists(PDO $pdo, string $table_name, array $columns) {
    try {
        $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        $create_table_sql = getCreateTableSQL($table_name, $columns, $driver_name);
        
        $pdo->exec($create_table_sql);
        
        return true;
    } catch (PDOException $e) {
        error_log("Erreur lors de la création de la table $table_name : " . $e->getMessage());
        return false;
    }
}

function addColumnIfNotExists(PDO $pdo, string $table_name, string $column_name, string $column_definition) {
    try {
        $driver_name = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        // Vérifier si la colonne existe déjà
        $check_column_sql = $driver_name === 'pgsql' 
            ? "SELECT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = '$table_name' AND column_name = '$column_name')"
            : "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$table_name' AND column_name = '$column_name'";
        
        $stmt = $pdo->query($check_column_sql);
        $column_exists = $stmt->fetchColumn();
        
        if (!$column_exists) {
            // Transformer la définition de la colonne si nécessaire
            if ($driver_name === 'pgsql') {
                $column_definition = str_replace('AUTO_INCREMENT', '', $column_definition);
            }
            
            // Ajouter la colonne
            $add_column_sql = "ALTER TABLE $table_name ADD COLUMN $column_name $column_definition";
            $pdo->exec($add_column_sql);
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erreur lors de l'ajout de la colonne $column_name : " . $e->getMessage());
        return false;
    }
}
