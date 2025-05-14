<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Table des quiz
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        description TEXT,
        type ENUM('qcm', 'vrai_faux', 'reponse_courte') NOT NULL DEFAULT 'qcm',
        duree_limite INT DEFAULT NULL COMMENT 'Durée en minutes',
        nombre_questions INT DEFAULT 0,
        score_minimum INT DEFAULT 70 COMMENT 'Score minimum en pourcentage',
        statut ENUM('brouillon', 'publie') NOT NULL DEFAULT 'brouillon',
        ordre INT DEFAULT 0,
        id_lecon INT NOT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_lecon) REFERENCES lecons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Table des questions
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_quiz INT NOT NULL,
        question TEXT NOT NULL,
        type ENUM('qcm', 'vrai_faux', 'reponse_courte') NOT NULL,
        points INT DEFAULT 1,
        ordre INT DEFAULT 0,
        feedback_correct TEXT COMMENT 'Feedback affiché si réponse correcte',
        feedback_incorrect TEXT COMMENT 'Feedback affiché si réponse incorrecte',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Table des réponses possibles
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_reponses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_question INT NOT NULL,
        reponse TEXT NOT NULL,
        est_correcte BOOLEAN NOT NULL DEFAULT FALSE,
        feedback TEXT COMMENT 'Feedback spécifique à cette réponse',
        ordre INT DEFAULT 0,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_question) REFERENCES quiz_questions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Table des résultats des utilisateurs
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_resultats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_quiz INT NOT NULL,
        score INT NOT NULL,
        temps_passe INT COMMENT 'Temps passé en secondes',
        date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_fin TIMESTAMP NULL DEFAULT NULL,
        statut ENUM('en_cours', 'termine', 'abandonne') NOT NULL DEFAULT 'en_cours',
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Table des réponses des utilisateurs
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_reponses_utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_resultat INT NOT NULL,
        id_question INT NOT NULL,
        id_reponse INT DEFAULT NULL,
        reponse_texte TEXT DEFAULT NULL COMMENT 'Pour les questions à réponse courte',
        est_correcte BOOLEAN NOT NULL DEFAULT FALSE,
        points_obtenus INT DEFAULT 0,
        FOREIGN KEY (id_resultat) REFERENCES quiz_resultats(id) ON DELETE CASCADE,
        FOREIGN KEY (id_question) REFERENCES quiz_questions(id) ON DELETE CASCADE,
        FOREIGN KEY (id_reponse) REFERENCES quiz_reponses(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "Les tables pour les quiz ont été créées avec succès !";

} catch (PDOException $e) {
    die("Erreur lors de la création des tables : " . $e->getMessage());
}
?>
