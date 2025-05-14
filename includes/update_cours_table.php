<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Ajouter les colonnes manquantes à la table cours
    $sql = "ALTER TABLE cours 
            ADD COLUMN IF NOT EXISTS objectifs TEXT,
            ADD COLUMN IF NOT EXISTS prerequis TEXT,
            ADD COLUMN IF NOT EXISTS video_url VARCHAR(255),
            ADD COLUMN IF NOT EXISTS type_contenu ENUM('texte', 'video', 'quiz') DEFAULT 'texte'";
    
    $pdo->exec($sql);
    echo "Colonnes ajoutées à la table 'cours' avec succès.\n";

    // Ajouter les colonnes manquantes à la table lecons
    $sql = "ALTER TABLE lecons 
            ADD COLUMN IF NOT EXISTS objectifs TEXT,
            ADD COLUMN IF NOT EXISTS ressources TEXT,
            ADD COLUMN IF NOT EXISTS fichiers_url TEXT";
    
    $pdo->exec($sql);
    echo "Colonnes ajoutées à la table 'lecons' avec succès.\n";

    // Créer la table des quiz
    $sql = "CREATE TABLE IF NOT EXISTS quiz (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        description TEXT,
        duree INT, -- en minutes
        seuil_reussite INT DEFAULT 70, -- pourcentage minimum pour réussir
        id_lecon INT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_lecon) REFERENCES lecons(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'quiz' créée avec succès.\n";

    // Créer la table des questions
    $sql = "CREATE TABLE IF NOT EXISTS quiz_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        type ENUM('qcm', 'vrai_faux', 'texte') DEFAULT 'qcm',
        points INT DEFAULT 1,
        ordre INT NOT NULL,
        id_quiz INT NOT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'quiz_questions' créée avec succès.\n";

    // Créer la table des réponses
    $sql = "CREATE TABLE IF NOT EXISTS quiz_reponses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reponse TEXT NOT NULL,
        est_correcte BOOLEAN DEFAULT FALSE,
        explication TEXT,
        id_question INT NOT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_question) REFERENCES quiz_questions(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'quiz_reponses' créée avec succès.\n";

    // Créer la table de progression des utilisateurs
    $sql = "CREATE TABLE IF NOT EXISTS user_progression (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_cours INT NOT NULL,
        id_lecon INT,
        progression INT DEFAULT 0, -- pourcentage
        statut ENUM('non_commence', 'en_cours', 'complete') DEFAULT 'non_commence',
        derniere_activite TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_cours) REFERENCES cours(id) ON DELETE CASCADE,
        FOREIGN KEY (id_lecon) REFERENCES lecons(id) ON DELETE SET NULL,
        UNIQUE KEY unique_progression (id_utilisateur, id_cours, id_lecon)
    )";
    $pdo->exec($sql);
    echo "Table 'user_progression' créée avec succès.\n";

    // Créer la table des résultats des quiz
    $sql = "CREATE TABLE IF NOT EXISTS user_quiz_resultats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_quiz INT NOT NULL,
        id_question INT NOT NULL,
        id_reponse INT,
        reponse_texte TEXT,
        est_correcte BOOLEAN,
        points_obtenus INT DEFAULT 0,
        date_reponse TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE,
        FOREIGN KEY (id_question) REFERENCES quiz_questions(id) ON DELETE CASCADE,
        FOREIGN KEY (id_reponse) REFERENCES quiz_reponses(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    echo "Table 'user_quiz_resultats' créée avec succès.\n";

    // Créer la table des badges
    $sql = "CREATE TABLE IF NOT EXISTS badges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT,
        image_url VARCHAR(255),
        condition_obtention TEXT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'badges' créée avec succès.\n";

    // Créer la table des badges des utilisateurs
    $sql = "CREATE TABLE IF NOT EXISTS user_badges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_badge INT NOT NULL,
        date_obtention TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_badge) REFERENCES badges(id) ON DELETE CASCADE,
        UNIQUE KEY unique_badge (id_utilisateur, id_badge)
    )";
    $pdo->exec($sql);
    echo "Table 'user_badges' créée avec succès.\n";

    // Créer la table des favoris
    $sql = "CREATE TABLE IF NOT EXISTS user_favoris (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_cours INT NOT NULL,
        date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_cours) REFERENCES cours(id) ON DELETE CASCADE,
        UNIQUE KEY unique_favori (id_utilisateur, id_cours)
    )";
    $pdo->exec($sql);
    echo "Table 'user_favoris' créée avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
