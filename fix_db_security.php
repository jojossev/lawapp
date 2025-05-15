<?php
// Script pour améliorer la sécurité de la base de données
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

// Fonction pour exécuter une requête SQL et gérer les erreurs
function executeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "<p style='color:green'>✓ $description : Succès</p>";
        return true;
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ $description: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.tables 
                WHERE table_name = :table
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return (bool) $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT table_name FROM information_schema.tables 
                    WHERE table_schema = DATABASE() AND table_name = :table";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table]);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la table: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Fonction pour vérifier si une colonne existe dans une table
function columnExists($pdo, $table, $column) {
    try {
        // Pour PostgreSQL
        if (strpos(DB_URL, 'pgsql') !== false) {
            $sql = "SELECT EXISTS (
                SELECT 1 FROM information_schema.columns 
                WHERE table_name = :table AND column_name = :column
            )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return (bool) $stmt->fetchColumn();
        } 
        // Pour MySQL
        else {
            $sql = "SELECT column_name FROM information_schema.columns 
                    WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['table' => $table, 'column' => $column]);
            return $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erreur lors de la vérification de la colonne: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Amélioration de la sécurité de la base de données</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
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
        <h1>Amélioration de la sécurité de la base de données</h1>

<?php
// Vérifier si la connexion à la base de données est établie
if (!isset($pdo)) {
    echo "<p class='error'>Erreur : La connexion à la base de données n'est pas établie.</p>";
    exit;
}

// Déterminer le type de base de données
$is_postgres = strpos(DB_URL, 'pgsql') !== false;
$db_type = $is_postgres ? 'PostgreSQL' : 'MySQL';

echo "<p class='success'>Connexion à la base de données établie avec succès. Type de base de données : $db_type</p>";

// 1. Vérification et mise à jour des mots de passe en texte clair
echo "<h2>Vérification des mots de passe</h2>";

// Table administrateurs
if (tableExists($pdo, 'administrateurs')) {
    echo "<h3>Table administrateurs</h3>";
    
    try {
        // Vérifier s'il y a des mots de passe en texte clair
        $sql = "SELECT id, email, mot_de_passe FROM administrateurs WHERE LENGTH(mot_de_passe) < 40";
        $stmt = $pdo->query($sql);
        $admins_to_update = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($admins_to_update) > 0) {
            echo "<p class='warning'>Des administrateurs ont des mots de passe en texte clair. Mise à jour...</p>";
            
            foreach ($admins_to_update as $admin) {
                $id = $admin['id'];
                $email = $admin['email'];
                $password = $admin['mot_de_passe'];
                
                // Hacher le mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Mettre à jour le mot de passe
                $sql_update = "UPDATE administrateurs SET mot_de_passe = :password WHERE id = :id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute(['password' => $hashed_password, 'id' => $id]);
                
                echo "<p class='success'>Mot de passe mis à jour pour l'administrateur $email (ID: $id)</p>";
            }
        } else {
            echo "<p class='success'>Tous les mots de passe des administrateurs sont correctement hachés.</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification des mots de passe des administrateurs: " . $e->getMessage() . "</p>";
    }
}

// Table utilisateurs
if (tableExists($pdo, 'utilisateurs')) {
    echo "<h3>Table utilisateurs</h3>";
    
    try {
        // Vérifier s'il y a des mots de passe en texte clair
        $sql = "SELECT id, email, mot_de_passe FROM utilisateurs WHERE LENGTH(mot_de_passe) < 40";
        $stmt = $pdo->query($sql);
        $users_to_update = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users_to_update) > 0) {
            echo "<p class='warning'>Des utilisateurs ont des mots de passe en texte clair. Mise à jour...</p>";
            
            foreach ($users_to_update as $user) {
                $id = $user['id'];
                $email = $user['email'];
                $password = $user['mot_de_passe'];
                
                // Hacher le mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Mettre à jour le mot de passe
                $sql_update = "UPDATE utilisateurs SET mot_de_passe = :password WHERE id = :id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute(['password' => $hashed_password, 'id' => $id]);
                
                echo "<p class='success'>Mot de passe mis à jour pour l'utilisateur $email (ID: $id)</p>";
            }
        } else {
            echo "<p class='success'>Tous les mots de passe des utilisateurs sont correctement hachés.</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la vérification des mots de passe des utilisateurs: " . $e->getMessage() . "</p>";
    }
}

// 2. Ajout de colonnes de sécurité
echo "<h2>Ajout de colonnes de sécurité</h2>";

// Table administrateurs - ajout de colonnes pour la sécurité
if (tableExists($pdo, 'administrateurs')) {
    echo "<h3>Table administrateurs</h3>";
    
    // Colonne derniere_connexion
    if (!columnExists($pdo, 'administrateurs', 'derniere_connexion')) {
        echo "<p class='warning'>La colonne 'derniere_connexion' n'existe pas dans la table 'administrateurs'. Ajout...</p>";
        
        try {
            $sql_add_column = "ALTER TABLE administrateurs ADD COLUMN derniere_connexion TIMESTAMP NULL";
            executeQuery($pdo, $sql_add_column, "Ajout de la colonne derniere_connexion");
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='success'>La colonne 'derniere_connexion' existe déjà dans la table 'administrateurs'.</p>";
    }
    
    // Colonne tentatives_connexion
    if (!columnExists($pdo, 'administrateurs', 'tentatives_connexion')) {
        echo "<p class='warning'>La colonne 'tentatives_connexion' n'existe pas dans la table 'administrateurs'. Ajout...</p>";
        
        try {
            $sql_add_column = "ALTER TABLE administrateurs ADD COLUMN tentatives_connexion INT DEFAULT 0";
            executeQuery($pdo, $sql_add_column, "Ajout de la colonne tentatives_connexion");
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='success'>La colonne 'tentatives_connexion' existe déjà dans la table 'administrateurs'.</p>";
    }
    
    // Colonne verrouille_jusqu'a
    if (!columnExists($pdo, 'administrateurs', 'verrouille_jusqua')) {
        echo "<p class='warning'>La colonne 'verrouille_jusqua' n'existe pas dans la table 'administrateurs'. Ajout...</p>";
        
        try {
            $sql_add_column = "ALTER TABLE administrateurs ADD COLUMN verrouille_jusqua TIMESTAMP NULL";
            executeQuery($pdo, $sql_add_column, "Ajout de la colonne verrouille_jusqua");
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='success'>La colonne 'verrouille_jusqua' existe déjà dans la table 'administrateurs'.</p>";
    }
}

// Table utilisateurs - ajout de colonnes pour la sécurité
if (tableExists($pdo, 'utilisateurs')) {
    echo "<h3>Table utilisateurs</h3>";
    
    // Colonne tentatives_connexion
    if (!columnExists($pdo, 'utilisateurs', 'tentatives_connexion')) {
        echo "<p class='warning'>La colonne 'tentatives_connexion' n'existe pas dans la table 'utilisateurs'. Ajout...</p>";
        
        try {
            $sql_add_column = "ALTER TABLE utilisateurs ADD COLUMN tentatives_connexion INT DEFAULT 0";
            executeQuery($pdo, $sql_add_column, "Ajout de la colonne tentatives_connexion");
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='success'>La colonne 'tentatives_connexion' existe déjà dans la table 'utilisateurs'.</p>";
    }
    
    // Colonne verrouille_jusqu'a
    if (!columnExists($pdo, 'utilisateurs', 'verrouille_jusqua')) {
        echo "<p class='warning'>La colonne 'verrouille_jusqua' n'existe pas dans la table 'utilisateurs'. Ajout...</p>";
        
        try {
            $sql_add_column = "ALTER TABLE utilisateurs ADD COLUMN verrouille_jusqua TIMESTAMP NULL";
            executeQuery($pdo, $sql_add_column, "Ajout de la colonne verrouille_jusqua");
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='success'>La colonne 'verrouille_jusqua' existe déjà dans la table 'utilisateurs'.</p>";
    }
    
    // Colonne token_reinitialisation
    if (!columnExists($pdo, 'utilisateurs', 'token_reinitialisation')) {
        echo "<p class='warning'>La colonne 'token_reinitialisation' n'existe pas dans la table 'utilisateurs'. Ajout...</p>";
        
        try {
            $sql_add_column = "ALTER TABLE utilisateurs ADD COLUMN token_reinitialisation VARCHAR(100) NULL";
            executeQuery($pdo, $sql_add_column, "Ajout de la colonne token_reinitialisation");
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='success'>La colonne 'token_reinitialisation' existe déjà dans la table 'utilisateurs'.</p>";
    }
    
    // Colonne expiration_token
    if (!columnExists($pdo, 'utilisateurs', 'expiration_token')) {
        echo "<p class='warning'>La colonne 'expiration_token' n'existe pas dans la table 'utilisateurs'. Ajout...</p>";
        
        try {
            $sql_add_column = "ALTER TABLE utilisateurs ADD COLUMN expiration_token TIMESTAMP NULL";
            executeQuery($pdo, $sql_add_column, "Ajout de la colonne expiration_token");
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='success'>La colonne 'expiration_token' existe déjà dans la table 'utilisateurs'.</p>";
    }
}

// 3. Création d'une table pour les journaux d'activité
echo "<h2>Création d'une table pour les journaux d'activité</h2>";

if (!tableExists($pdo, 'logs_activite')) {
    echo "<p class='warning'>La table 'logs_activite' n'existe pas. Création...</p>";
    
    // Créer la table logs_activite
    if ($is_postgres) {
        // PostgreSQL
        $sql_create_logs = "
        CREATE TABLE logs_activite (
            id SERIAL PRIMARY KEY,
            type_utilisateur VARCHAR(20) NOT NULL,
            id_utilisateur INT NOT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT NULL,
            adresse_ip VARCHAR(45) NULL,
            user_agent TEXT NULL,
            date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    } else {
        // MySQL
        $sql_create_logs = "
        CREATE TABLE logs_activite (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type_utilisateur VARCHAR(20) NOT NULL,
            id_utilisateur INT NOT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT NULL,
            adresse_ip VARCHAR(45) NULL,
            user_agent TEXT NULL,
            date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    executeQuery($pdo, $sql_create_logs, "Création de la table logs_activite");
    
    // Créer un index sur la table logs_activite
    $sql_create_index = "CREATE INDEX idx_logs_utilisateur ON logs_activite (type_utilisateur, id_utilisateur)";
    executeQuery($pdo, $sql_create_index, "Création de l'index sur la table logs_activite");
    
    $sql_create_index2 = "CREATE INDEX idx_logs_date ON logs_activite (date_action)";
    executeQuery($pdo, $sql_create_index2, "Création de l'index sur la date d'action");
} else {
    echo "<p class='success'>La table 'logs_activite' existe déjà.</p>";
}

// 4. Recommandations de sécurité
echo "<h2>Recommandations de sécurité</h2>";

echo "<ul>";
echo "<li><strong>Utilisation de requêtes préparées :</strong> Toujours utiliser des requêtes préparées pour éviter les injections SQL.</li>";
echo "<li><strong>Hachage des mots de passe :</strong> Utiliser password_hash() et password_verify() pour gérer les mots de passe.</li>";
echo "<li><strong>Limitation des tentatives de connexion :</strong> Bloquer temporairement les comptes après plusieurs tentatives échouées.</li>";
echo "<li><strong>Journalisation des activités :</strong> Enregistrer les connexions, déconnexions et actions importantes.</li>";
echo "<li><strong>Validation des entrées :</strong> Toujours valider et filtrer les entrées utilisateur.</li>";
echo "<li><strong>Gestion des sessions :</strong> Régénérer l'ID de session après la connexion et utiliser des cookies sécurisés.</li>";
echo "<li><strong>Protocole HTTPS :</strong> Utiliser HTTPS pour toutes les communications.</li>";
echo "<li><strong>En-têtes de sécurité :</strong> Configurer les en-têtes HTTP de sécurité (X-XSS-Protection, Content-Security-Policy, etc.).</li>";
echo "<li><strong>Mise à jour régulière :</strong> Maintenir à jour PHP, le serveur web et toutes les dépendances.</li>";
echo "</ul>";

// Exemple de code pour la gestion des connexions
echo "<h3>Exemple de code pour la gestion des connexions</h3>";
echo "<pre>
// Fonction pour vérifier les tentatives de connexion
function verifierTentativesConnexion($pdo, $email, $table = 'utilisateurs') {
    $sql = \"SELECT tentatives_connexion, verrouille_jusqua FROM $table WHERE email = :email\";
    $stmt = \$pdo->prepare(\$sql);
    \$stmt->execute(['email' => \$email]);
    \$user = \$stmt->fetch(PDO::FETCH_ASSOC);
    
    if (\$user && \$user['verrouille_jusqua'] !== null) {
        \$now = new DateTime();
        \$verrouille_jusqua = new DateTime(\$user['verrouille_jusqua']);
        
        if (\$now < \$verrouille_jusqua) {
            return false; // Compte verrouillé
        }
    }
    
    return true; // Compte non verrouillé
}

// Fonction pour incrémenter les tentatives de connexion
function incrementerTentativesConnexion(\$pdo, \$email, \$table = 'utilisateurs') {
    \$sql = \"UPDATE $table SET tentatives_connexion = tentatives_connexion + 1 WHERE email = :email\";
    \$stmt = \$pdo->prepare(\$sql);
    \$stmt->execute(['email' => \$email]);
    
    // Vérifier si le compte doit être verrouillé
    \$sql = \"SELECT tentatives_connexion FROM $table WHERE email = :email\";
    \$stmt = \$pdo->prepare(\$sql);
    \$stmt->execute(['email' => \$email]);
    \$user = \$stmt->fetch(PDO::FETCH_ASSOC);
    
    if (\$user && \$user['tentatives_connexion'] >= 5) {
        // Verrouiller le compte pour 30 minutes
        \$verrouille_jusqua = (new DateTime())->add(new DateInterval('PT30M'))->format('Y-m-d H:i:s');
        \$sql = \"UPDATE $table SET verrouille_jusqua = :verrouille_jusqua WHERE email = :email\";
        \$stmt = \$pdo->prepare(\$sql);
        \$stmt->execute(['verrouille_jusqua' => \$verrouille_jusqua, 'email' => \$email]);
    }
}

// Fonction pour réinitialiser les tentatives de connexion
function reinitialiserTentativesConnexion(\$pdo, \$email, \$table = 'utilisateurs') {
    \$sql = \"UPDATE $table SET tentatives_connexion = 0, verrouille_jusqua = NULL WHERE email = :email\";
    \$stmt = \$pdo->prepare(\$sql);
    \$stmt->execute(['email' => \$email]);
}

// Fonction pour journaliser une activité
function journaliserActivite(\$pdo, \$type_utilisateur, \$id_utilisateur, \$action, \$details = null) {
    \$sql = \"INSERT INTO logs_activite (type_utilisateur, id_utilisateur, action, details, adresse_ip, user_agent) 
             VALUES (:type_utilisateur, :id_utilisateur, :action, :details, :adresse_ip, :user_agent)\";
    \$stmt = \$pdo->prepare(\$sql);
    \$stmt->execute([
        'type_utilisateur' => \$type_utilisateur,
        'id_utilisateur' => \$id_utilisateur,
        'action' => \$action,
        'details' => \$details,
        'adresse_ip' => \$_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => \$_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}
</pre>";

// Liens de retour
echo "<h2>Liens utiles :</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Retour à l'accueil</a></li>";
echo "<li><a href='fix_all_tables.php'>Corriger toutes les tables</a></li>";
echo "<li><a href='fix_db_compatibility.php'>Compatibilité MySQL/PostgreSQL</a></li>";
echo "<li><a href='fix_db_performance.php'>Optimiser les performances</a></li>";
echo "</ul>";
?>

    </div>
</body>
</html>
