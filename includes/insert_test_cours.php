<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Créer la table des catégories de cours si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS categories_cours (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'categories_cours' créée avec succès.\n";

    // Vérifier/créer la table des cours
    $sql = "CREATE TABLE IF NOT EXISTS cours (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        description TEXT,
        objectifs TEXT,
        prerequis TEXT,
        duree INT, -- en heures
        prix DECIMAL(10,2),
        niveau VARCHAR(50),
        image_url VARCHAR(255),
        video_url VARCHAR(255),
        id_categorie INT,
        id_createur INT,
        statut ENUM('brouillon', 'publie', 'archive') DEFAULT 'publie',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_mise_a_jour TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_categorie) REFERENCES categories_cours(id) ON DELETE SET NULL,
        FOREIGN KEY (id_createur) REFERENCES utilisateurs(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    echo "Table 'cours' créée avec succès.\n";

    // Vérifier/créer la table des modules
    $sql = "CREATE TABLE IF NOT EXISTS modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        description TEXT,
        ordre INT NOT NULL,
        id_cours INT NOT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cours) REFERENCES cours(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'modules' créée avec succès.\n";

    // Vérifier/créer la table des leçons
    $sql = "CREATE TABLE IF NOT EXISTS lecons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        contenu TEXT,
        duree INT, -- en minutes
        ordre INT NOT NULL,
        id_module INT NOT NULL,
        type_contenu ENUM('texte', 'video', 'quiz') DEFAULT 'texte',
        video_url VARCHAR(255),
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_module) REFERENCES modules(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'lecons' créée avec succès.\n";

    // Catégories de cours
    $categories = [
        ['Droit Civil', 'Formation complète en droit civil'],
        ['Droit Pénal', 'Cours de droit pénal et procédure'],
        ['Droit Commercial', 'Formation en droit des affaires'],
        ['Droit Constitutionnel', 'Cours sur la constitution'],
        ['Droit International', 'Formation en droit international'],
        ['Droit du Travail', 'Cours de droit social'],
        ['Droit Administratif', 'Formation en droit public'],
        ['Droit Fiscal', 'Cours sur la fiscalité']
    ];

    // Insérer les catégories
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("INSERT INTO categories_cours (nom, description) 
                              SELECT ?, ? 
                              WHERE NOT EXISTS (SELECT 1 FROM categories_cours WHERE nom = ?)");
        $stmt->execute([$cat[0], $cat[1], $cat[0]]);
    }

    // Liste des cours à insérer
    $cours = [
        // Droit Civil
        [
            'titre' => 'Introduction au Droit Civil',
            'description' => 'Formation complète pour comprendre les bases du droit civil',
            'categorie' => 'Droit Civil',
            'modules' => [
                [
                    'titre' => 'Les Fondamentaux',
                    'lecons' => [
                        ['Les Sources du Droit', 45],
                        ['Les Personnes Juridiques', 30],
                        ['Les Biens et la Propriété', 40]
                    ]
                ],
                [
                    'titre' => 'Les Obligations',
                    'lecons' => [
                        ['Introduction aux Obligations', 35],
                        ['Les Contrats', 50],
                        ['La Responsabilité Civile', 45]
                    ]
                ]
            ]
        ],
        // Droit Pénal
        [
            'titre' => 'Droit Pénal Général',
            'description' => 'Maîtrisez les principes fondamentaux du droit pénal',
            'categorie' => 'Droit Pénal',
            'modules' => [
                [
                    'titre' => 'Principes Généraux',
                    'lecons' => [
                        ['La Loi Pénale', 40],
                        ['L\'Infraction', 45],
                        ['La Responsabilité Pénale', 50]
                    ]
                ],
                [
                    'titre' => 'Les Peines',
                    'lecons' => [
                        ['Classification des Peines', 35],
                        ['L\'Application des Peines', 40],
                        ['Les Alternatives aux Poursuites', 30]
                    ]
                ]
            ]
        ]
    ];

    // Niveaux possibles
    $niveaux = ['Débutant', 'Intermédiaire', 'Avancé'];

    // Insérer les cours et leurs modules/leçons
    foreach ($cours as $c) {
        // Récupérer l'ID de la catégorie
        $stmt = $pdo->prepare("SELECT id FROM categories_cours WHERE nom = ?");
        $stmt->execute([$c['categorie']]);
        $categorie_id = $stmt->fetchColumn();

        // Insérer le cours
        $stmt = $pdo->prepare("
            INSERT INTO cours (
                titre, description, objectifs, prerequis,
                duree, prix, niveau, image_url,
                id_categorie, statut
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, 'publie'
            )
        ");

        $duree_totale = 0;
        foreach ($c['modules'] as $module) {
            foreach ($module['lecons'] as $lecon) {
                $duree_totale += $lecon[1];
            }
        }

        $image_url = '/LawApp/assets/images/cours/' . strtolower(str_replace(' ', '-', $c['titre'])) . '.jpg';
        $prix = rand(99, 299);
        $niveau = $niveaux[array_rand($niveaux)];

        $stmt->execute([
            $c['titre'],
            $c['description'],
            "Objectifs du cours " . $c['titre'],
            "Aucun prérequis particulier",
            ceil($duree_totale/60), // Convertir en heures
            $prix,
            $niveau,
            $image_url,
            $categorie_id
        ]);

        $cours_id = $pdo->lastInsertId();

        // Insérer les modules
        foreach ($c['modules'] as $index => $module) {
            $stmt = $pdo->prepare("
                INSERT INTO modules (titre, description, ordre, id_cours)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $module['titre'],
                "Description du module " . $module['titre'],
                $index + 1,
                $cours_id
            ]);

            $module_id = $pdo->lastInsertId();

            // Insérer les leçons
            foreach ($module['lecons'] as $index => $lecon) {
                $stmt = $pdo->prepare("
                    INSERT INTO lecons (
                        titre, contenu, duree, ordre,
                        id_module, type_contenu
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $lecon[0],
                    "Contenu de la leçon " . $lecon[0],
                    $lecon[1],
                    $index + 1,
                    $module_id,
                    'texte'
                ]);
            }
        }

        echo "Cours '{$c['titre']}' ajouté avec succès.\n";
    }

    echo "\nTous les cours ont été insérés avec succès !";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
