<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers de configuration
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

echo "<h1>Test de la table utilisateurs</h1>";

try {
    // Vérifier si la table utilisateurs existe
    $sql = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public'
        AND table_name = 'utilisateurs'
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        echo "<p style='color:green'>✓ La table 'utilisateurs' existe.</p>";
        
        // Compter les utilisateurs
        $sql = "SELECT COUNT(*) FROM utilisateurs";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        echo "<p>Nombre d'utilisateurs : $count</p>";
        
        // Afficher les utilisateurs
        if ($count > 0) {
            $sql = "SELECT id, nom, prenom, email, role FROM utilisateurs";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h2>Liste des utilisateurs</h2>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th></tr>";
            
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['nom']) . "</td>";
                echo "<td>" . htmlspecialchars($user['prenom']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } else {
        echo "<p style='color:red'>✗ La table 'utilisateurs' n'existe pas.</p>";
        echo "<p>Veuillez exécuter le script d'initialisation de la base de données : <a href='admin/init_db.php'>Initialiser la base de données</a></p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur : " . $e->getMessage() . "</p>";
}

// Afficher un lien pour retourner à la page d'accueil
echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
?>
