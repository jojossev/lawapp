<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure les fichiers de configuration
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

echo "<h1>Test de la table inscriptions</h1>";

try {
    // Vérifier si la table inscriptions existe
    $sql = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public'
        AND table_name = 'inscriptions'
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        echo "<p style='color:green'>✓ La table 'inscriptions' existe.</p>";
        
        // Compter les inscriptions
        $sql = "SELECT COUNT(*) FROM inscriptions";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        echo "<p>Nombre d'inscriptions : $count</p>";
        
        // Afficher les inscriptions
        if ($count > 0) {
            $sql = "SELECT i.id, i.id_utilisateur, i.id_cours, i.date_inscription, i.progres, i.statut, 
                          u.nom, u.prenom, c.titre as cours_titre
                   FROM inscriptions i
                   JOIN utilisateurs u ON i.id_utilisateur = u.id
                   JOIN cours c ON i.id_cours = c.id
                   ORDER BY i.date_inscription DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h2>Liste des inscriptions</h2>";
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Utilisateur</th><th>Cours</th><th>Date d'inscription</th><th>Progrès</th><th>Statut</th></tr>";
            
            foreach ($inscriptions as $inscription) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($inscription['id']) . "</td>";
                echo "<td>" . htmlspecialchars($inscription['prenom'] . ' ' . $inscription['nom']) . "</td>";
                echo "<td>" . htmlspecialchars($inscription['cours_titre']) . "</td>";
                echo "<td>" . htmlspecialchars($inscription['date_inscription']) . "</td>";
                echo "<td>" . htmlspecialchars($inscription['progres']) . "%</td>";
                echo "<td>" . htmlspecialchars($inscription['statut']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } else {
        echo "<p style='color:red'>✗ La table 'inscriptions' n'existe pas.</p>";
        echo "<p>Veuillez exécuter le script d'initialisation de la base de données : <a href='admin/init_db.php'>Initialiser la base de données</a></p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur : " . $e->getMessage() . "</p>";
}

// Afficher un lien pour retourner à la page d'accueil
echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
?>
