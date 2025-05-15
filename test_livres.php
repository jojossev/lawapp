<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers de configuration
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

echo "<h1>Test de la table livres</h1>";

try {
    // Vérifier si la table livres existe
    $sql = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public'
        AND table_name = 'livres'
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        echo "<p style='color:green'>✓ La table 'livres' existe.</p>";
        
        // Compter les livres
        $sql = "SELECT COUNT(*) FROM livres";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        echo "<p>Nombre de livres : $count</p>";
        
        // Afficher les livres
        if ($count > 0) {
            $sql = "SELECT l.*, cl.nom as categorie_nom 
                   FROM livres l
                   LEFT JOIN categories_livres cl ON l.id_categorie = cl.id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h2>Liste des livres</h2>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Titre</th><th>Auteur</th><th>Année</th><th>Éditeur</th><th>Catégorie</th></tr>";
            
            foreach ($books as $book) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($book['id']) . "</td>";
                echo "<td>" . htmlspecialchars($book['titre']) . "</td>";
                echo "<td>" . htmlspecialchars($book['auteur']) . "</td>";
                echo "<td>" . htmlspecialchars($book['annee_publication']) . "</td>";
                echo "<td>" . htmlspecialchars($book['editeur']) . "</td>";
                echo "<td>" . htmlspecialchars($book['categorie_nom']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } else {
        echo "<p style='color:red'>✗ La table 'livres' n'existe pas.</p>";
        echo "<p>Veuillez exécuter le script d'initialisation de la base de données : <a href='admin/init_db.php'>Initialiser la base de données</a></p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur : " . $e->getMessage() . "</p>";
}

// Afficher un lien pour retourner à la page d'accueil
echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
?>
