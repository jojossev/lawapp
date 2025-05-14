-- Création de la base de données
CREATE DATABASE IF NOT EXISTS lawapp;
USE lawapp;

-- Table des catégories de cours
CREATE TABLE IF NOT EXISTS categories_cours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des cours
CREATE TABLE IF NOT EXISTS cours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    id_categorie INT,
    niveau VARCHAR(50) DEFAULT 'debutant',
    duree_estimee VARCHAR(100),
    prix DECIMAL(10,2) DEFAULT 0.00,
    statut VARCHAR(50) DEFAULT 'brouillon',
    image_url VARCHAR(255),
    id_createur INT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES categories_cours(id) ON DELETE SET NULL
);

-- Table des modules
CREATE TABLE IF NOT EXISTS modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_cours INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    ordre_affichage INT DEFAULT 0,
    statut VARCHAR(50) DEFAULT 'brouillon',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cours) REFERENCES cours(id) ON DELETE CASCADE
);

-- Table des leçons
CREATE TABLE IF NOT EXISTS lecons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_module INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    contenu TEXT,
    type_contenu VARCHAR(50) DEFAULT 'texte',
    url_contenu VARCHAR(255),
    duree_estimee VARCHAR(100),
    ordre_affichage INT DEFAULT 0,
    statut VARCHAR(50) DEFAULT 'brouillon',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_module) REFERENCES modules(id) ON DELETE CASCADE
);

-- Table des quiz
CREATE TABLE IF NOT EXISTS quiz (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_lecon INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    duree_limite INT, -- en minutes
    nombre_questions INT DEFAULT 0,
    score_minimum INT DEFAULT 0,
    statut VARCHAR(50) DEFAULT 'brouillon',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_lecon) REFERENCES lecons(id) ON DELETE CASCADE
);

-- Table des questions de quiz
CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_quiz INT NOT NULL,
    question TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'qcm',
    points INT DEFAULT 1,
    ordre_affichage INT DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE
);

-- Table des réponses aux questions
CREATE TABLE IF NOT EXISTS quiz_reponses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_question INT NOT NULL,
    reponse TEXT NOT NULL,
    est_correcte BOOLEAN DEFAULT FALSE,
    ordre_affichage INT DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_question) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- Table de progression des utilisateurs
CREATE TABLE IF NOT EXISTS progression_utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    id_cours INT NOT NULL,
    id_module INT,
    id_lecon INT,
    statut VARCHAR(50) DEFAULT 'en_cours',
    progression INT DEFAULT 0,
    date_debut DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_derniere_activite DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progression (id_utilisateur, id_cours, id_module, id_lecon)
);

-- Table des résultats des quiz
CREATE TABLE IF NOT EXISTS resultats_quiz (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    id_quiz INT NOT NULL,
    score INT DEFAULT 0,
    temps_passe INT, -- en secondes
    date_completion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE
);

-- Insertion d'une catégorie de test
INSERT INTO categories_cours (nom, description) VALUES ('Droit Civil', 'Cours de droit civil');

-- Insertion d'un cours de test
INSERT INTO cours (titre, description, id_categorie, niveau, duree_estimee, prix, statut) 
VALUES ('Introduction au Droit Civil', 'Cours d\'introduction au droit civil', 1, 'debutant', '10 heures', 0.00, 'publie');
