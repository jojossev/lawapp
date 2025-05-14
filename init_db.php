<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

try {
    // Création de la table admin
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table categories_cours
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories_cours (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table cours
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cours (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(200) NOT NULL,
            description TEXT,
            categorie_id INTEGER REFERENCES categories_cours(id),
            prix DECIMAL(10,2),
            duree INTEGER, -- en minutes
            niveau VARCHAR(20), -- débutant, intermédiaire, avancé
            image_url VARCHAR(255),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table modules
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS modules (
            id SERIAL PRIMARY KEY,
            cours_id INTEGER REFERENCES cours(id),
            titre VARCHAR(200) NOT NULL,
            description TEXT,
            ordre INTEGER NOT NULL,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table lecons
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lecons (
            id SERIAL PRIMARY KEY,
            module_id INTEGER REFERENCES modules(id),
            titre VARCHAR(200) NOT NULL,
            contenu TEXT,
            duree INTEGER, -- en minutes
            ordre INTEGER NOT NULL,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table quiz
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quiz (
            id SERIAL PRIMARY KEY,
            lecon_id INTEGER REFERENCES lecons(id),
            titre VARCHAR(200) NOT NULL,
            description TEXT,
            duree INTEGER, -- en minutes
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table quiz_questions
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quiz_questions (
            id SERIAL PRIMARY KEY,
            quiz_id INTEGER REFERENCES quiz(id),
            question TEXT NOT NULL,
            type VARCHAR(20), -- qcm, vrai_faux, texte_libre
            points INTEGER DEFAULT 1,
            ordre INTEGER NOT NULL,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table quiz_reponses
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quiz_reponses (
            id SERIAL PRIMARY KEY,
            question_id INTEGER REFERENCES quiz_questions(id),
            reponse TEXT NOT NULL,
            est_correcte BOOLEAN DEFAULT false,
            ordre INTEGER NOT NULL,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table users
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table user_progression
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_progression (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id),
            cours_id INTEGER REFERENCES cours(id),
            lecon_id INTEGER REFERENCES lecons(id),
            statut VARCHAR(20), -- non_commencé, en_cours, terminé
            progression INTEGER DEFAULT 0, -- pourcentage
            derniere_activite TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Création de la table user_quiz_resultats
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_quiz_resultats (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id),
            quiz_id INTEGER REFERENCES quiz(id),
            score INTEGER,
            temps_pris INTEGER, -- en secondes
            date_completion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    echo "Base de données initialisée avec succès !";

} catch(PDOException $e) {
    die("Erreur d'initialisation de la base de données : " . $e->getMessage());
}
