<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Créer la table des catégories de podcasts
    $sql = "CREATE TABLE IF NOT EXISTS categories_podcasts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'categories_podcasts' créée avec succès.\n";

    // Créer la table des podcasts
    $sql = "CREATE TABLE IF NOT EXISTS podcasts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        description TEXT,
        duree INT, -- en minutes
        audio_url VARCHAR(255),
        image_url VARCHAR(255),
        id_categorie INT,
        id_createur INT,
        nombre_ecoutes INT DEFAULT 0,
        statut ENUM('brouillon', 'publie', 'archive') DEFAULT 'publie',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_mise_a_jour TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_categorie) REFERENCES categories_podcasts(id) ON DELETE SET NULL,
        FOREIGN KEY (id_createur) REFERENCES utilisateurs(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    echo "Table 'podcasts' créée avec succès.\n";

    // Catégories de podcasts
    $categories = [
        ['Actualités Juridiques', 'Les dernières nouvelles du monde juridique'],
        ['Cas Pratiques', 'Analyse de cas juridiques concrets'],
        ['Interviews d\'Experts', 'Entretiens avec des professionnels du droit'],
        ['Conseils Juridiques', 'Conseils pratiques sur des questions juridiques'],
        ['Débats Juridiques', 'Discussions sur des sujets juridiques controversés']
    ];

    // Insérer les catégories
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("INSERT INTO categories_podcasts (nom, description) 
                              SELECT ?, ? 
                              WHERE NOT EXISTS (SELECT 1 FROM categories_podcasts WHERE nom = ?)");
        $stmt->execute([$cat[0], $cat[1], $cat[0]]);
    }

    // Liste des podcasts à insérer
    $podcasts = [
        // Actualités Juridiques
        [
            'Réforme du Code Civil : Ce qui change',
            'Les principales modifications apportées par la réforme du Code Civil',
            'Actualités Juridiques',
            25
        ],
        [
            'Nouveautés en Droit du Travail',
            'Les dernières évolutions du droit du travail',
            'Actualités Juridiques',
            30
        ],
        [
            'L\'actualité du Droit des Sociétés',
            'Les changements récents en droit des sociétés',
            'Actualités Juridiques',
            20
        ],

        // Cas Pratiques
        [
            'Analyse d\'un Cas de Licenciement',
            'Étude détaillée d\'un cas de licenciement abusif',
            'Cas Pratiques',
            35
        ],
        [
            'Résolution d\'un Litige Commercial',
            'Comment résoudre un conflit entre entreprises',
            'Cas Pratiques',
            40
        ],
        [
            'Cas Pratique en Droit de la Famille',
            'Analyse d\'une procédure de divorce complexe',
            'Cas Pratiques',
            30
        ],

        // Interviews d'Experts
        [
            'Interview d\'un Juge aux Affaires Familiales',
            'Un juge partage son expérience quotidienne',
            'Interviews d\'Experts',
            45
        ],
        [
            'Rencontre avec un Avocat Pénaliste',
            'Discussion sur les défis du droit pénal',
            'Interviews d\'Experts',
            50
        ],
        [
            'Parole à un Notaire',
            'Un notaire explique les successions',
            'Interviews d\'Experts',
            40
        ],

        // Conseils Juridiques
        [
            'Comment Créer son Entreprise',
            'Guide pratique pour les entrepreneurs',
            'Conseils Juridiques',
            35
        ],
        [
            'Les Pièges à Éviter en Droit Immobilier',
            'Conseils pour vos transactions immobilières',
            'Conseils Juridiques',
            30
        ],
        [
            'Protéger ses Droits d\'Auteur',
            'Guide de la propriété intellectuelle',
            'Conseils Juridiques',
            25
        ],

        // Débats Juridiques
        [
            'La Justice Prédictive',
            'Débat sur l\'utilisation de l\'IA en droit',
            'Débats Juridiques',
            55
        ],
        [
            'Réforme de la Justice',
            'Discussion sur la modernisation de la justice',
            'Débats Juridiques',
            45
        ],
        [
            'L\'Avenir de la Profession d\'Avocat',
            'Débat sur l\'évolution du métier',
            'Débats Juridiques',
            50
        ]
    ];

    // Insérer les podcasts
    foreach ($podcasts as $p) {
        // Récupérer l'ID de la catégorie
        $stmt = $pdo->prepare("SELECT id FROM categories_podcasts WHERE nom = ?");
        $stmt->execute([$p[2]]);
        $categorie_id = $stmt->fetchColumn();

        // Insérer le podcast
        $stmt = $pdo->prepare("
            INSERT INTO podcasts (
                titre, description, duree, audio_url,
                image_url, id_categorie, nombre_ecoutes,
                statut
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?,
                'publie'
            )
        ");

        $audio_url = '/LawApp/assets/audio/' . strtolower(str_replace(' ', '-', $p[0])) . '.mp3';
        $image_url = '/LawApp/assets/images/podcasts/' . strtolower(str_replace(' ', '-', $p[0])) . '.jpg';
        $nombre_ecoutes = rand(50, 1000);

        $stmt->execute([
            $p[0],  // titre
            $p[1],  // description
            $p[3],  // durée
            $audio_url,
            $image_url,
            $categorie_id,
            $nombre_ecoutes
        ]);

        echo "Podcast '{$p[0]}' ajouté avec succès.\n";
    }

    echo "\nTous les podcasts ont été insérés avec succès !";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
