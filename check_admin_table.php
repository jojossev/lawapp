<?php
// Inclure les fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Afficher l'en-tête
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de la table administrateurs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Vérification de la table administrateurs</h1>';

try {
    // Connexion à la base de données
    $pdo = getPDO();
    echo '<p class="success">✓ Connexion à la base de données réussie</p>';
    
    // Vérifier si la base de données est PostgreSQL ou MySQL
    $is_postgres = strpos(getenv('DATABASE_URL') ?? '', 'postgres') !== false;
    echo '<p>Type de base de données : ' . ($is_postgres ? 'PostgreSQL' : 'MySQL') . '</p>';
    
    // Vérifier si la table administrateurs existe
    if ($is_postgres) {
        $sql = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' AND table_name = 'administrateurs'
        )";
    } else {
        $sql = "SHOW TABLES LIKE 'administrateurs'";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    if ($is_postgres) {
        $table_exists = $stmt->fetchColumn();
    } else {
        $table_exists = $stmt->rowCount() > 0;
    }
    
    if ($table_exists) {
        echo '<p class="success">✓ La table administrateurs existe</p>';
        
        // Compter le nombre d'administrateurs
        $sql = "SELECT COUNT(*) FROM administrateurs";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        echo '<p>Nombre d\'administrateurs : ' . $count . '</p>';
        
        // Afficher les administrateurs
        $sql = "SELECT * FROM administrateurs";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<h2>Liste des administrateurs</h2>';
        echo '<pre>';
        foreach ($admins as $admin) {
            // Masquer le mot de passe complet
            $admin['mot_de_passe'] = substr($admin['mot_de_passe'], 0, 10) . '...';
            print_r($admin);
        }
        echo '</pre>';
        
        // Lien pour créer un nouvel administrateur
        echo '<h2>Créer un nouvel administrateur</h2>';
        echo '<form method="post">';
        echo '<input type="hidden" name="create_admin" value="1">';
        echo '<button type="submit">Créer un administrateur par défaut (admin@lawapp.com / admin)</button>';
        echo '</form>';
        
        // Traitement de la création d'un nouvel administrateur
        if (isset($_POST['create_admin'])) {
            $email = 'admin@lawapp.com';
            $password = password_hash('admin', PASSWORD_DEFAULT);
            
            // Vérifier si l'administrateur existe déjà
            $sql = "SELECT COUNT(*) FROM administrateurs WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                echo '<p class="error">Un administrateur avec cet email existe déjà.</p>';
            } else {
                // Insérer le nouvel administrateur
                $sql = "INSERT INTO administrateurs (email, mot_de_passe) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$email, $password]);
                
                if ($result) {
                    echo '<p class="success">✓ Administrateur créé avec succès.</p>';
                    echo '<p>Rechargez la page pour voir la liste mise à jour.</p>';
                } else {
                    echo '<p class="error">Erreur lors de la création de l\'administrateur.</p>';
                }
            }
        }
        
    } else {
        echo '<p class="error">✗ La table administrateurs n\'existe pas</p>';
        
        // Lien vers le script de création de la table
        echo '<p><a href="fix_admin_table_pg.php">Cliquez ici pour créer la table administrateurs</a></p>';
    }
    
} catch (PDOException $e) {
    echo '<p class="error">Erreur de base de données : ' . $e->getMessage() . '</p>';
}

echo '</body></html>';
?>
