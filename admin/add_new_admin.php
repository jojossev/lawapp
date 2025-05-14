<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Insérer un nouvel administrateur
    $stmt = $pdo->prepare("INSERT INTO administrateurs (email, mot_de_passe, prenom, nom, role, statut) 
                          VALUES (:email, :password, :prenom, :nom, :role, :statut)");
    
    $stmt->execute([
        'email' => 'test@lawapp.com',
        'password' => 'test123', // Mot de passe en texte clair
        'prenom' => 'Test',
        'nom' => 'Admin',
        'role' => 'admin',
        'statut' => 'actif'
    ]);

    if ($stmt->rowCount() > 0) {
        echo "Nouvel administrateur ajouté avec succès !<br><br>";
        echo "Identifiants de connexion :<br>";
        echo "Email : test@lawapp.com<br>";
        echo "Mot de passe : test123<br>";
    }

    // Afficher la liste mise à jour des administrateurs
    $stmt = $pdo->query("SELECT id, email, prenom, nom, role, statut FROM administrateurs");
    echo "<h3>Liste mise à jour des administrateurs :</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
