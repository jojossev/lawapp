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

// Création de la table categories_livres
$sql_categories_livres = "
CREATE TABLE IF NOT EXISTS categories_livres (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    statut VARCHAR(20) DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

executeQuery($pdo, $sql_categories_livres, "Création de la table categories_livres");

// Création de la table categories_podcasts
$sql_categories_podcasts = "
CREATE TABLE IF NOT EXISTS categories_podcasts (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    statut VARCHAR(20) DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

executeQuery($pdo, $sql_categories_podcasts, "Création de la table categories_podcasts");

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

// Création de la table livres
$sql_livres = "
CREATE TABLE IF NOT EXISTS livres (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    auteur VARCHAR(255) NOT NULL,
    description TEXT,
    annee_publication INT,
    editeur VARCHAR(255),
    isbn VARCHAR(20),
    id_categorie INT,
    image_couverture VARCHAR(255),
    fichier_pdf VARCHAR(255),
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut VARCHAR(20) DEFAULT 'actif',
    FOREIGN KEY (id_categorie) REFERENCES categories_livres(id) ON DELETE SET NULL
);";

executeQuery($pdo, $sql_livres, "Création de la table livres");

// Insertion de données de test pour les catégories de cours
$sql_insert_categories = "
INSERT INTO categories_cours (nom, description, statut) VALUES 
('Droit Civil', 'Cours sur les fondamentaux du droit civil', 'actif'),
('Droit Pénal', 'Cours sur le droit pénal et la procédure pénale', 'actif'),
('Droit des Affaires', 'Cours sur le droit commercial et des affaires', 'actif'),
('Droit International', 'Cours sur le droit international public et privé', 'actif')
ON CONFLICT (id) DO NOTHING;";

executeQuery($pdo, $sql_insert_categories, "Insertion des catégories de cours de test");

// Insertion de données de test pour les catégories de livres
$sql_insert_categories_livres = "
INSERT INTO categories_livres (nom, description, statut) VALUES 
('Manuels Juridiques', 'Manuels et ouvrages de référence en droit', 'actif'),
('Codes Annotés', 'Codes juridiques avec annotations et jurisprudence', 'actif'),
('Essais Juridiques', 'Essais et analyses sur des questions juridiques', 'actif'),
('Revues Spécialisées', 'Publications périodiques en droit', 'actif')
ON CONFLICT (id) DO NOTHING;";

executeQuery($pdo, $sql_insert_categories_livres, "Insertion des catégories de livres de test");

// Insertion de données de test pour les catégories de podcasts
$sql_insert_categories_podcasts = "
INSERT INTO categories_podcasts (nom, description, statut) VALUES 
('Actualités Juridiques', 'Podcasts sur les actualités récentes en droit', 'actif'),
('Entretiens avec des Experts', 'Interviews de professionnels du droit', 'actif'),
('Cas Pratiques', 'Analyses de cas juridiques concrets', 'actif'),
('Débats Juridiques', 'Discussions sur des sujets juridiques controversés', 'actif')
ON CONFLICT (id) DO NOTHING;";

executeQuery($pdo, $sql_insert_categories_podcasts, "Insertion des catégories de podcasts de test");

// Insertion de données de test pour les cours
$sql_insert_cours = "
INSERT INTO cours (titre, description, prix, categorie_id, niveau, duree_estimee, statut) VALUES 
('Introduction au Droit Civil', 'Découvrez les fondements du droit civil français.', 29.99, 1, 'débutant', '10 heures', 'actif'),
('Droit des Contrats', 'Apprenez tout sur la formation et l''exécution des contrats.', 39.99, 1, 'intermédiaire', '15 heures', 'actif'),
('Procédure Pénale', 'Maîtrisez les étapes de la procédure pénale française.', 49.99, 2, 'avancé', '20 heures', 'actif'),
('Droit des Sociétés', 'Comprendre la création et la gestion des sociétés.', 59.99, 3, 'intermédiaire', '25 heures', 'actif')
ON CONFLICT (id) DO NOTHING;";

executeQuery($pdo, $sql_insert_cours, "Insertion des cours de test");

// Création de la table inscriptions
$sql_inscriptions = "
CREATE TABLE IF NOT EXISTS inscriptions (
    id SERIAL PRIMARY KEY,
    id_utilisateur INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    id_cours INTEGER NOT NULL REFERENCES cours(id) ON DELETE CASCADE,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progres INTEGER DEFAULT 0,
    statut VARCHAR(20) DEFAULT 'actif',
    UNIQUE(id_utilisateur, id_cours)
);";

executeQuery($pdo, $sql_inscriptions, "Création de la table inscriptions");

// Insertion de données de test pour les inscriptions
$sql_insert_inscriptions = "
INSERT INTO inscriptions (id_utilisateur, id_cours, date_inscription, progres, statut) VALUES 
(1, 1, CURRENT_TIMESTAMP - INTERVAL '10 days', 75, 'actif'),
(1, 2, CURRENT_TIMESTAMP - INTERVAL '5 days', 30, 'actif'),
(1, 3, CURRENT_TIMESTAMP - INTERVAL '2 days', 10, 'actif')
ON CONFLICT (id_utilisateur, id_cours) DO NOTHING;";

executeQuery($pdo, $sql_insert_inscriptions, "Insertion des inscriptions de test");

// Insertion d'un utilisateur de test (mot de passe: Test123!)
$password_hash = password_hash('Test123!', PASSWORD_DEFAULT);
$sql_insert_user = "
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES 
('Admin', 'Test', 'admin@test.com', '$password_hash', 'admin')
ON CONFLICT (email) DO NOTHING;";

executeQuery($pdo, $sql_insert_user, "Insertion d'un utilisateur de test");

// Insertion de livres de test
$sql_insert_livres = "
INSERT INTO livres (titre, auteur, description, annee_publication, editeur, isbn, id_categorie, statut) VALUES
('Introduction au Droit Civil', 'Jean Dupont', 'Un manuel complet sur les fondamentaux du droit civil.', 2022, 'Éditions Juridiques', '978-2-1234-5678-9', 1, 'actif'),
('Droit Pénal Contemporain', 'Marie Laurent', 'Analyse des évolutions récentes du droit pénal.', 2021, 'Presses Universitaires', '978-2-9876-5432-1', 2, 'actif'),
('Droit des Affaires', 'Pierre Martin', 'Guide pratique pour comprendre le droit des affaires.', 2023, 'Business Legal', '978-2-5555-7777-3', 3, 'actif')
ON CONFLICT (id) DO NOTHING;";

executeQuery($pdo, $sql_insert_livres, "Insertion de livres de test");

// Afficher le pied de page
echo "
        <p><a href='../index.php'>Retour à l'accueil</a></p>
    </div>
</body>
</html>";
?>
