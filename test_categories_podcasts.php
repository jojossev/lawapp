<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers de configuration
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

echo "<h1>Test de la table categories_podcasts</h1>";

try {
    // Vérifier si la table categories_podcasts existe
    $sql = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public'
        AND table_name = 'categories_podcasts'
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        echo "<p style='color:green'>✓ La table 'categories_podcasts' existe.</p>";
        
        // Compter les catégories
        $sql = "SELECT COUNT(*) FROM categories_podcasts";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        echo "<p>Nombre de catégories de podcasts : $count</p>";
        
        // Afficher les catégories
        if ($count > 0) {
            $sql = "SELECT id, nom, description, statut FROM categories_podcasts ORDER BY nom ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h2>Liste des catégories de podcasts</h2>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Description</th><th>Statut</th></tr>";
            
            foreach ($categories as $category) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($category['id']) . "</td>";
                echo "<td>" . htmlspecialchars($category['nom']) . "</td>";
                echo "<td>" . htmlspecialchars($category['description']) . "</td>";
                echo "<td>" . htmlspecialchars($category['statut']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } else {
        echo "<p style='color:red'>✗ La table 'categories_podcasts' n'existe pas.</p>";
        echo "<p>Veuillez exécuter le script d'initialisation de la base de données : <a href='admin/init_db.php'>Initialiser la base de données</a></p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur : " . $e->getMessage() . "</p>";
}

// Afficher un lien pour retourner à la page d'accueil
echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
?>
