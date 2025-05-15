<?php
// Script spécifique pour créer la table administrateurs avec la syntaxe PostgreSQL correcte
// Ce script est une version corrigée de fix_admin_table.php pour PostgreSQL

// Inclure les fichiers de configuration
require_once __DIR__ . '/includes/config.php';

// Afficher un message de début
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Correction de la table administrateurs pour PostgreSQL - LawApp</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Correction de la table administrateurs pour PostgreSQL</h1>
        <p>Ce script crée la table administrateurs avec la syntaxe PostgreSQL correcte.</p>";

// Vérifier la connexion à la base de données
echo "<h2>1. Vérification de la connexion à la base de données</h2>";
try {
    if (!isset($pdo)) {
        throw new Exception("La connexion à la base de données n'est pas établie.");
    }
    
    // Tester la connexion avec une requête simple
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo "<p class='success'>✅ Connexion à la base de données établie avec succès.</p>";
    } else {
        throw new Exception("Impossible d'exécuter une requête simple sur la base de données.");
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
    die("<p>Impossible de continuer sans connexion à la base de données.</p></div></body></html>");
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        $sql = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' AND table_name = '$table'
        )";
        $stmt = $pdo->query($sql);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Vérifier si la table administrateurs existe
echo "<h2>2. Vérification de la table administrateurs</h2>";
if (!tableExists($pdo, 'administrateurs')) {
    echo "<p>La table 'administrateurs' n'existe pas. Création de la table...</p>";
    
    try {
        // Créer la table avec la syntaxe PostgreSQL correcte
        $sql = "CREATE TABLE administrateurs (
            id SERIAL PRIMARY KEY,
            nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            statut VARCHAR(20) DEFAULT 'actif'
        )";
        $pdo->exec($sql);
        echo "<p class='success'>✅ Création de la table administrateurs réussie.</p>";
        
        // Insérer un administrateur par défaut
        $sql = "INSERT INTO administrateurs (nom_utilisateur, mot_de_passe, email, nom, prenom) VALUES 
            ('admin', :password, 'admin@lawapp.com', 'Admin', 'LawApp')";
        $stmt = $pdo->prepare($sql);
        $password_hash = password_hash('admin', PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $password_hash);
        $stmt->execute();
        echo "<p class='success'>✅ Insertion de l'administrateur par défaut réussie.</p>";
        echo "<p>Identifiants par défaut:</p>";
        echo "<ul>";
        echo "<li>Nom d'utilisateur: admin</li>";
        echo "<li>Mot de passe: admin</li>";
        echo "<li>Email: admin@lawapp.com</li>";
        echo "</ul>";
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erreur lors de la création de la table administrateurs: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='success'>✅ La table 'administrateurs' existe déjà.</p>";
}

// Afficher les administrateurs existants
echo "<h2>3. Administrateurs existants</h2>";
try {
    $sql = "SELECT id, nom_utilisateur, email, nom, prenom, date_creation, statut FROM administrateurs";
    $stmt = $pdo->query($sql);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($admins) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom d'utilisateur</th><th>Email</th><th>Nom</th><th>Prénom</th><th>Date de création</th><th>Statut</th></tr>";
        
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['nom_utilisateur']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['prenom']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['date_creation']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['statut']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucun administrateur trouvé dans la base de données.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erreur lors de la récupération des administrateurs: " . $e->getMessage() . "</p>";
}

// Afficher la structure de la table
echo "<h2>4. Structure de la table administrateurs</h2>";
try {
    $sql = "SELECT column_name, data_type, character_maximum_length, column_default, is_nullable 
            FROM information_schema.columns 
            WHERE table_schema = 'public' AND table_name = 'administrateurs'
            ORDER BY ordinal_position";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns) > 0) {
        echo "<table>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Longueur</th><th>Défaut</th><th>Nullable</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['column_name']) . "</td>";
            echo "<td>" . htmlspecialchars($column['data_type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['character_maximum_length']) . "</td>";
            echo "<td>" . htmlspecialchars($column['column_default']) . "</td>";
            echo "<td>" . htmlspecialchars($column['is_nullable']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Aucune colonne trouvée pour la table administrateurs.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erreur lors de la récupération de la structure de la table: " . $e->getMessage() . "</p>";
}

// Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='fix_all_tables.php'>Corriger toutes les tables</a></li>";
echo "<li><a href='test_db_connection.php'>Test de connexion à la base de données</a></li>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "</ul>";

echo "<p>Opération terminée.</p>";
echo "</div></body></html>";
?>
