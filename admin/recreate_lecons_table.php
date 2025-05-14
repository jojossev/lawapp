<?php
require_once __DIR__ . '/../includes/config.php';

try {
    // Désactiver temporairement les contraintes de clé étrangère
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Supprimer les tables dépendantes d'abord
    $pdo->exec("DROP TABLE IF EXISTS quiz_reponses");
    $pdo->exec("DROP TABLE IF EXISTS quiz_questions");
    $pdo->exec("DROP TABLE IF EXISTS resultats_quiz");
    $pdo->exec("DROP TABLE IF EXISTS quiz");
    $pdo->exec("DROP TABLE IF EXISTS progression_utilisateurs");
    $pdo->exec("DROP TABLE IF EXISTS lecons");
    
    // Créer la table avec la bonne structure
    $sql = "CREATE TABLE lecons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_module INT NOT NULL,
        titre VARCHAR(255) NOT NULL,
        description TEXT,
        type_contenu ENUM('texte', 'video') DEFAULT 'texte',
        contenu_principal LONGTEXT,
        ordre INT DEFAULT 0,
        duree_estimee VARCHAR(50),
        statut ENUM('brouillon', 'publie') DEFAULT 'brouillon',
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_module) REFERENCES modules(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);

    // Recréer les tables dépendantes
    $pdo->exec("CREATE TABLE IF NOT EXISTS progression_utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_cours INT,
        id_module INT,
        id_lecon INT,
        statut ENUM('non_commence', 'en_cours', 'termine') DEFAULT 'non_commence',
        progression INT DEFAULT 0,
        date_debut DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_derniere_activite DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_completion DATETIME,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_cours) REFERENCES cours(id) ON DELETE CASCADE,
        FOREIGN KEY (id_module) REFERENCES modules(id) ON DELETE CASCADE,
        FOREIGN KEY (id_lecon) REFERENCES lecons(id) ON DELETE CASCADE,
        UNIQUE KEY unique_progression (id_utilisateur, id_cours, id_module, id_lecon)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_lecon INT NOT NULL,
        titre VARCHAR(255) NOT NULL,
        description TEXT,
        duree_limite INT DEFAULT NULL,
        nombre_questions INT DEFAULT 0,
        score_minimum INT DEFAULT 70,
        statut ENUM('brouillon', 'publie') DEFAULT 'brouillon',
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_lecon) REFERENCES lecons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_quiz INT NOT NULL,
        question TEXT NOT NULL,
        type ENUM('qcm', 'vrai_faux', 'texte') DEFAULT 'qcm',
        points INT DEFAULT 1,
        ordre INT DEFAULT 0,
        FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_reponses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_question INT NOT NULL,
        reponse TEXT NOT NULL,
        est_correcte BOOLEAN DEFAULT FALSE,
        ordre INT DEFAULT 0,
        FOREIGN KEY (id_question) REFERENCES quiz_questions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS resultats_quiz (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        id_quiz INT NOT NULL,
        score INT NOT NULL,
        temps_pris INT,
        date_debut DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_fin DATETIME,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_quiz) REFERENCES quiz(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Réactiver les contraintes de clé étrangère
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Tables recréées avec succès !";

} catch (PDOException $e) {
    die("Erreur lors de la recréation des tables : " . $e->getMessage());
}
