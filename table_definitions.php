<?php
// Définitions des tables pour le script fix_all_tables.php
// Ce fichier contient les définitions SQL pour toutes les tables de l'application

$table_definitions = [
    // Table administrateurs
    'administrateurs' => [
        'mysql' => "CREATE TABLE administrateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            statut VARCHAR(20) DEFAULT 'actif'
        )",
        'postgresql' => "CREATE TABLE administrateurs (
            id SERIAL PRIMARY KEY,
            nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            statut VARCHAR(20) DEFAULT 'actif'
        )",
        'sample_data' => "INSERT INTO administrateurs (nom_utilisateur, mot_de_passe, email, nom, prenom) VALUES 
            ('admin', '" . password_hash('admin', PASSWORD_DEFAULT) . "', 'admin@lawapp.com', 'Admin', 'LawApp')"
    ],
    
    // Table utilisateurs
    'utilisateurs' => [
        'mysql' => "CREATE TABLE utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            statut VARCHAR(20) DEFAULT 'actif',
            role VARCHAR(20) DEFAULT 'etudiant',
            bio TEXT NULL,
            avatar VARCHAR(255) NULL
        )",
        'postgresql' => "CREATE TABLE utilisateurs (
            id SERIAL PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            derniere_connexion TIMESTAMP NULL,
            statut VARCHAR(20) DEFAULT 'actif',
            role VARCHAR(20) DEFAULT 'etudiant',
            bio TEXT NULL,
            avatar VARCHAR(255) NULL
        )",
        'sample_data' => "INSERT INTO utilisateurs (email, mot_de_passe, nom, prenom, role) VALUES 
            ('user@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'Utilisateur', 'Test', 'etudiant'),
            ('prof@example.com', '" . password_hash('password', PASSWORD_DEFAULT) . "', 'Professeur', 'Test', 'enseignant')"
    ],
    
    // Table categories_livres
    'categories_livres' => [
        'mysql' => "CREATE TABLE categories_livres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE categories_livres (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'sample_data' => "INSERT INTO categories_livres (nom, description) VALUES 
            ('Droit civil', 'Livres sur le droit civil'),
            ('Droit pénal', 'Livres sur le droit pénal'),
            ('Droit des affaires', 'Livres sur le droit des affaires')"
    ],
    
    // Table categories_podcasts
    'categories_podcasts' => [
        'mysql' => "CREATE TABLE categories_podcasts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE categories_podcasts (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'sample_data' => "INSERT INTO categories_podcasts (nom, description) VALUES 
            ('Interviews', 'Podcasts d''interviews avec des experts juridiques'),
            ('Analyses juridiques', 'Analyses de sujets juridiques actuels'),
            ('Conseils pratiques', 'Conseils pratiques sur des questions juridiques')"
    ],
    
    // Table categories_cours
    'categories_cours' => [
        'mysql' => "CREATE TABLE categories_cours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(20) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE categories_cours (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            statut VARCHAR(20) DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'sample_data' => "INSERT INTO categories_cours (nom, description) VALUES 
            ('Droit civil', 'Cours sur le droit civil'),
            ('Droit pénal', 'Cours sur le droit pénal'),
            ('Droit des affaires', 'Cours sur le droit des affaires'),
            ('Droit constitutionnel', 'Cours sur le droit constitutionnel'),
            ('Droit administratif', 'Cours sur le droit administratif')"
    ],
    
    // Table categories_videos
    'categories_videos' => [
        'mysql' => "CREATE TABLE categories_videos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE categories_videos (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'sample_data' => "INSERT INTO categories_videos (nom, description) VALUES 
            ('Tutoriels', 'Vidéos tutorielles sur des concepts juridiques'),
            ('Conférences', 'Enregistrements de conférences juridiques'),
            ('Explications', 'Explications détaillées de concepts juridiques')"
    ],
    
    // Table livres
    'livres' => [
        'mysql' => "CREATE TABLE livres (
            id INT AUTO_INCREMENT PRIMARY KEY,
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
            statut VARCHAR(20) DEFAULT 'actif'
        )",
        'postgresql' => "CREATE TABLE livres (
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
            statut VARCHAR(20) DEFAULT 'actif'
        )",
        'important_columns' => ['id_categorie'],
        'column_definitions' => [
            'id_categorie' => 'INT'
        ]
    ],
    
    // Table podcasts
    'podcasts' => [
        'mysql' => "CREATE TABLE podcasts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            duree VARCHAR(10),
            date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fichier_audio VARCHAR(255),
            image VARCHAR(255),
            id_categorie INT,
            statut VARCHAR(20) DEFAULT 'actif'
        )",
        'postgresql' => "CREATE TABLE podcasts (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            duree VARCHAR(10),
            date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fichier_audio VARCHAR(255),
            image VARCHAR(255),
            id_categorie INT,
            statut VARCHAR(20) DEFAULT 'actif'
        )",
        'important_columns' => ['id_categorie'],
        'column_definitions' => [
            'id_categorie' => 'INT'
        ]
    ],
    
    // Table cours
    'cours' => [
        'mysql' => "CREATE TABLE cours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            prix DECIMAL(10,2) NULL,
            image_url VARCHAR(255),
            niveau VARCHAR(50),
            duree VARCHAR(50),
            id_categorie INT,
            id_createur INT,
            note_moyenne DECIMAL(3,2) DEFAULT 0,
            statut VARCHAR(20) DEFAULT 'brouillon',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP NULL
        )",
        'postgresql' => "CREATE TABLE cours (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            prix DECIMAL(10,2) NULL,
            image_url VARCHAR(255),
            niveau VARCHAR(50),
            duree VARCHAR(50),
            id_categorie INT,
            id_createur INT,
            note_moyenne DECIMAL(3,2) DEFAULT 0,
            statut VARCHAR(20) DEFAULT 'brouillon',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_modification TIMESTAMP NULL
        )",
        'important_columns' => ['id_categorie', 'id_createur', 'note_moyenne'],
        'column_definitions' => [
            'id_categorie' => 'INT',
            'id_createur' => 'INT',
            'note_moyenne' => 'DECIMAL(3,2) DEFAULT 0'
        ]
    ],
    
    // Table modules
    'modules' => [
        'mysql' => "CREATE TABLE modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            id_cours INT NOT NULL,
            ordre INT DEFAULT 1,
            duree VARCHAR(50),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE modules (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            id_cours INT NOT NULL,
            ordre INT DEFAULT 1,
            duree VARCHAR(50),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    
    // Table lecons
    'lecons' => [
        'mysql' => "CREATE TABLE lecons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            contenu TEXT,
            id_module INT NOT NULL,
            ordre INT DEFAULT 1,
            duree VARCHAR(50),
            type VARCHAR(50) DEFAULT 'texte',
            video_url VARCHAR(255),
            fichier_pdf VARCHAR(255),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE lecons (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            contenu TEXT,
            id_module INT NOT NULL,
            ordre INT DEFAULT 1,
            duree VARCHAR(50),
            type VARCHAR(50) DEFAULT 'texte',
            video_url VARCHAR(255),
            fichier_pdf VARCHAR(255),
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    
    // Table quiz
    'quiz' => [
        'mysql' => "CREATE TABLE quiz (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            id_lecon INT,
            id_module INT,
            id_cours INT,
            temps_limite INT,
            note_passage INT DEFAULT 60,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE quiz (
            id SERIAL PRIMARY KEY,
            titre VARCHAR(255) NOT NULL,
            description TEXT,
            id_lecon INT,
            id_module INT,
            id_cours INT,
            temps_limite INT,
            note_passage INT DEFAULT 60,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    
    // Table quiz_questions
    'quiz_questions' => [
        'mysql' => "CREATE TABLE quiz_questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_quiz INT NOT NULL,
            question TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'choix_multiple',
            points INT DEFAULT 1,
            ordre INT DEFAULT 1
        )",
        'postgresql' => "CREATE TABLE quiz_questions (
            id SERIAL PRIMARY KEY,
            id_quiz INT NOT NULL,
            question TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'choix_multiple',
            points INT DEFAULT 1,
            ordre INT DEFAULT 1
        )"
    ],
    
    // Table quiz_reponses
    'quiz_reponses' => [
        'mysql' => "CREATE TABLE quiz_reponses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_question INT NOT NULL,
            reponse TEXT NOT NULL,
            est_correcte BOOLEAN DEFAULT FALSE,
            ordre INT DEFAULT 1
        )",
        'postgresql' => "CREATE TABLE quiz_reponses (
            id SERIAL PRIMARY KEY,
            id_question INT NOT NULL,
            reponse TEXT NOT NULL,
            est_correcte BOOLEAN DEFAULT FALSE,
            ordre INT DEFAULT 1
        )"
    ],
    
    // Table quiz_resultats
    'quiz_resultats' => [
        'mysql' => "CREATE TABLE quiz_resultats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_quiz INT NOT NULL,
            id_utilisateur INT NOT NULL,
            score INT,
            temps_passe INT,
            date_completion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reussi BOOLEAN DEFAULT FALSE
        )",
        'postgresql' => "CREATE TABLE quiz_resultats (
            id SERIAL PRIMARY KEY,
            id_quiz INT NOT NULL,
            id_utilisateur INT NOT NULL,
            score INT,
            temps_passe INT,
            date_completion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reussi BOOLEAN DEFAULT FALSE
        )"
    ],
    
    // Table resultats_quiz
    'resultats_quiz' => [
        'mysql' => "CREATE TABLE resultats_quiz (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_quiz INT NOT NULL,
            id_utilisateur INT NOT NULL,
            score INT,
            temps_passe INT,
            date_completion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reussi BOOLEAN DEFAULT FALSE
        )",
        'postgresql' => "CREATE TABLE resultats_quiz (
            id SERIAL PRIMARY KEY,
            id_quiz INT NOT NULL,
            id_utilisateur INT NOT NULL,
            score INT,
            temps_passe INT,
            date_completion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reussi BOOLEAN DEFAULT FALSE
        )"
    ],
    
    // Table quiz_reponses_utilisateurs
    'quiz_reponses_utilisateurs' => [
        'mysql' => "CREATE TABLE quiz_reponses_utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_resultat INT NOT NULL,
            id_question INT NOT NULL,
            id_reponse INT,
            reponse_texte TEXT,
            est_correcte BOOLEAN DEFAULT FALSE
        )",
        'postgresql' => "CREATE TABLE quiz_reponses_utilisateurs (
            id SERIAL PRIMARY KEY,
            id_resultat INT NOT NULL,
            id_question INT NOT NULL,
            id_reponse INT,
            reponse_texte TEXT,
            est_correcte BOOLEAN DEFAULT FALSE
        )"
    ],
    
    // Table inscriptions
    'inscriptions' => [
        'mysql' => "CREATE TABLE inscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif',
            progression INT DEFAULT 0,
            date_derniere_activite TIMESTAMP NULL
        )",
        'postgresql' => "CREATE TABLE inscriptions (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif',
            progression INT DEFAULT 0,
            date_derniere_activite TIMESTAMP NULL
        )"
    ],
    
    // Table progression_utilisateurs
    'progression_utilisateurs' => [
        'mysql' => "CREATE TABLE progression_utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            id_module INT,
            id_lecon INT,
            statut VARCHAR(20) DEFAULT 'en_cours',
            progression INT DEFAULT 0,
            date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_derniere_activite TIMESTAMP NULL,
            date_completion TIMESTAMP NULL
        )",
        'postgresql' => "CREATE TABLE progression_utilisateurs (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            id_module INT,
            id_lecon INT,
            statut VARCHAR(20) DEFAULT 'en_cours',
            progression INT DEFAULT 0,
            date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_derniere_activite TIMESTAMP NULL,
            date_completion TIMESTAMP NULL
        )"
    ],
    
    // Table user_progression
    'user_progression' => [
        'mysql' => "CREATE TABLE user_progression (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            id_module INT,
            id_lecon INT,
            statut VARCHAR(20) DEFAULT 'en_cours',
            progression INT DEFAULT 0,
            date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_derniere_activite TIMESTAMP NULL,
            date_completion TIMESTAMP NULL
        )",
        'postgresql' => "CREATE TABLE user_progression (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_cours INT NOT NULL,
            id_module INT,
            id_lecon INT,
            statut VARCHAR(20) DEFAULT 'en_cours',
            progression INT DEFAULT 0,
            date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_derniere_activite TIMESTAMP NULL,
            date_completion TIMESTAMP NULL
        )"
    ],
    
    // Table badges
    'badges' => [
        'mysql' => "CREATE TABLE badges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            image VARCHAR(255),
            condition_obtention TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE badges (
            id SERIAL PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            image VARCHAR(255),
            condition_obtention TEXT,
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    
    // Table user_badges
    'user_badges' => [
        'mysql' => "CREATE TABLE user_badges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_badge INT NOT NULL,
            date_obtention TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE user_badges (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_badge INT NOT NULL,
            date_obtention TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    
    // Table user_favoris
    'user_favoris' => [
        'mysql' => "CREATE TABLE user_favoris (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            type_item VARCHAR(50) NOT NULL,
            id_item INT NOT NULL,
            date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE user_favoris (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            type_item VARCHAR(50) NOT NULL,
            id_item INT NOT NULL,
            date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    
    // Table user_quiz_resultats
    'user_quiz_resultats' => [
        'mysql' => "CREATE TABLE user_quiz_resultats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_quiz INT NOT NULL,
            score INT,
            temps_passe INT,
            date_completion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reussi BOOLEAN DEFAULT FALSE,
            details TEXT
        )",
        'postgresql' => "CREATE TABLE user_quiz_resultats (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT NOT NULL,
            id_quiz INT NOT NULL,
            score INT,
            temps_passe INT,
            date_completion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reussi BOOLEAN DEFAULT FALSE,
            details TEXT
        )"
    ],
    
    // Table livre_categories
    'livre_categories' => [
        'mysql' => "CREATE TABLE livre_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_livre INT NOT NULL,
            id_categorie INT NOT NULL,
            date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE livre_categories (
            id SERIAL PRIMARY KEY,
            id_livre INT NOT NULL,
            id_categorie INT NOT NULL,
            date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    
    // Table avis_livres
    'avis_livres' => [
        'mysql' => "CREATE TABLE avis_livres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_livre INT NOT NULL,
            id_utilisateur INT NOT NULL,
            note INT NOT NULL,
            commentaire TEXT,
            date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'approuve'
        )",
        'postgresql' => "CREATE TABLE avis_livres (
            id SERIAL PRIMARY KEY,
            id_livre INT NOT NULL,
            id_utilisateur INT NOT NULL,
            note INT NOT NULL,
            commentaire TEXT,
            date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'approuve'
        )"
    ],
    
    // Table acces_livres
    'acces_livres' => [
        'mysql' => "CREATE TABLE acces_livres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_livre INT NOT NULL,
            id_utilisateur INT NOT NULL,
            date_acces TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            type_acces VARCHAR(50) DEFAULT 'gratuit'
        )",
        'postgresql' => "CREATE TABLE acces_livres (
            id SERIAL PRIMARY KEY,
            id_livre INT NOT NULL,
            id_utilisateur INT NOT NULL,
            date_acces TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            type_acces VARCHAR(50) DEFAULT 'gratuit'
        )"
    ],
    
    // Table achats_livres
    'achats_livres' => [
        'mysql' => "CREATE TABLE achats_livres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_livre INT NOT NULL,
            id_utilisateur INT NOT NULL,
            montant DECIMAL(10,2) NOT NULL,
            date_achat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reference_transaction VARCHAR(100),
            statut VARCHAR(50) DEFAULT 'complete'
        )",
        'postgresql' => "CREATE TABLE achats_livres (
            id SERIAL PRIMARY KEY,
            id_livre INT NOT NULL,
            id_utilisateur INT NOT NULL,
            montant DECIMAL(10,2) NOT NULL,
            date_achat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reference_transaction VARCHAR(100),
            statut VARCHAR(50) DEFAULT 'complete'
        )"
    ],
    
    // Table chatbot_conversations
    'chatbot_conversations' => [
        'mysql' => "CREATE TABLE chatbot_conversations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_utilisateur INT,
            session_id VARCHAR(100) NOT NULL,
            date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_derniere_activite TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif'
        )",
        'postgresql' => "CREATE TABLE chatbot_conversations (
            id SERIAL PRIMARY KEY,
            id_utilisateur INT,
            session_id VARCHAR(100) NOT NULL,
            date_debut TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            date_derniere_activite TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            statut VARCHAR(20) DEFAULT 'actif'
        )"
    ],
    
    // Table chatbot_messages
    'chatbot_messages' => [
        'mysql' => "CREATE TABLE chatbot_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_conversation INT NOT NULL,
            message TEXT NOT NULL,
            est_utilisateur BOOLEAN DEFAULT TRUE,
            date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        'postgresql' => "CREATE TABLE chatbot_messages (
            id SERIAL PRIMARY KEY,
            id_conversation INT NOT NULL,
            message TEXT NOT NULL,
            est_utilisateur BOOLEAN DEFAULT TRUE,
            date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    
    // Table chatbot_reponses
    'chatbot_reponses' => [
        'mysql' => "CREATE TABLE chatbot_reponses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mot_cle VARCHAR(100) NOT NULL,
            reponse TEXT NOT NULL,
            categorie VARCHAR(50) DEFAULT 'general'
        )",
        'postgresql' => "CREATE TABLE chatbot_reponses (
            id SERIAL PRIMARY KEY,
            mot_cle VARCHAR(100) NOT NULL,
            reponse TEXT NOT NULL,
            categorie VARCHAR(50) DEFAULT 'general'
        )",
        'sample_data' => "INSERT INTO chatbot_reponses (mot_cle, reponse, categorie) VALUES 
            ('bonjour', 'Bonjour ! Comment puis-je vous aider aujourd''hui ?', 'salutation'),
            ('aide', 'Je peux vous aider à trouver des livres, des cours ou des podcasts sur le droit. Que recherchez-vous ?', 'assistance'),
            ('contact', 'Vous pouvez nous contacter à l''adresse contact@lawapp.com ou par téléphone au 01 23 45 67 89.', 'information')"
    ]
];
?>
