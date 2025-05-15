<?php
require_once '../includes/config.php';

// Fonction pour exécuter une requête SQL et gérer les erreurs
function executeQuery($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "<p style='color:green'>✓ $description</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ $description: " . $e->getMessage() . "</p>";
    }
}

// Vérifier si l'utilisateur est connecté en tant qu'admin (à implémenter)
// if (!isset($_SESSION['admin_id'])) {
//     header('Location: login.php');
//     exit;
// }

// Afficher l'en-tête
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Initialisation de la base de données</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Initialisation de la base de données</h1>";

// Création de la table categories_cours
$sql_categories_cours = "
CREATE TABLE IF NOT EXISTS categories_cours (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    statut VARCHAR(20) DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

executeQuery($pdo, $sql_categories_cours, "Création de la table categories_cours");

// Création de la table cours
$sql_cours = "
CREATE TABLE IF NOT EXISTS cours (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    prix DECIMAL(10, 2) DEFAULT 0.00,
    categorie_id INT REFERENCES categories_cours(id),
    niveau VARCHAR(50) DEFAULT 'débutant',
    duree_estimee VARCHAR(50),
    statut VARCHAR(20) DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

executeQuery($pdo, $sql_cours, "Création de la table cours");

// Création de la table utilisateurs
$sql_utilisateurs = "
CREATE TABLE IF NOT EXISTS utilisateurs (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'utilisateur',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP,
    statut VARCHAR(20) DEFAULT 'actif',
    token_reset VARCHAR(255),
    date_token_reset TIMESTAMP,
    photo_profil VARCHAR(255)
);";

executeQuery($pdo, $sql_utilisateurs, "Création de la table utilisateurs");

// Insertion de données de test pour les catégories
$sql_insert_categories = "
INSERT INTO categories_cours (nom, description, statut) VALUES 
('Droit Civil', 'Fondements du droit civil, contrats, responsabilité...', 'actif'),
('Droit Pénal', 'Principes du droit pénal, procédures, infractions...', 'actif'),
('Droit des Affaires', 'Droit commercial, sociétés, concurrence...', 'actif'),
('Droit Public', 'Droit administratif, constitutionnel...', 'actif')
ON CONFLICT (id) DO NOTHING;";

executeQuery($pdo, $sql_insert_categories, "Insertion des catégories de test");

// Insertion de données de test pour les cours
$sql_insert_cours = "
INSERT INTO cours (titre, description, prix, categorie_id, niveau, duree_estimee, statut) VALUES 
('Introduction au Droit Civil', 'Découvrez les fondements du droit civil français.', 29.99, 1, 'débutant', '10 heures', 'actif'),
('Droit des Contrats', 'Apprenez tout sur la formation et l''exécution des contrats.', 39.99, 1, 'intermédiaire', '15 heures', 'actif'),
('Procédure Pénale', 'Maîtrisez les étapes de la procédure pénale française.', 49.99, 2, 'avancé', '20 heures', 'actif'),
('Droit des Sociétés', 'Comprendre la création et la gestion des sociétés.', 59.99, 3, 'intermédiaire', '25 heures', 'actif')
ON CONFLICT (id) DO NOTHING;";

executeQuery($pdo, $sql_insert_cours, "Insertion des cours de test");

// Insertion d'un utilisateur de test (mot de passe: Test123!)
$password_hash = password_hash('Test123!', PASSWORD_DEFAULT);
$sql_insert_user = "
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES 
('Admin', 'Test', 'admin@test.com', '$password_hash', 'admin')
ON CONFLICT (email) DO NOTHING;";

executeQuery($pdo, $sql_insert_user, "Insertion d'un utilisateur de test");

// Afficher le pied de page
echo "
        <p><a href='../index.php'>Retour à l'accueil</a></p>
    </div>
</body>
</html>";
?>
