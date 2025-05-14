<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Liste des cours supplémentaires
    $cours = [
        // Droit Commercial
        [
            'titre' => 'Droit des Sociétés Approfondi',
            'description' => 'Maîtrisez tous les aspects du droit des sociétés',
            'categorie' => 'Droit Commercial',
            'modules' => [
                [
                    'titre' => 'Les Différentes Formes de Sociétés',
                    'lecons' => [
                        ['La SARL', 45, 'Caractéristiques et fonctionnement de la SARL'],
                        ['La SA', 40, 'Structure et gouvernance de la SA'],
                        ['La SAS', 35, 'Flexibilité et avantages de la SAS']
                    ]
                ],
                [
                    'titre' => 'Gestion des Sociétés',
                    'lecons' => [
                        ['Les Assemblées Générales', 50, 'Organisation et déroulement des AG'],
                        ['La Responsabilité des Dirigeants', 45, 'Droits et obligations des dirigeants'],
                        ['Les Opérations sur Capital', 40, 'Augmentation et réduction de capital']
                    ]
                ]
            ]
        ],

        // Droit du Travail
        [
            'titre' => 'Relations de Travail',
            'description' => 'Tout sur les relations employeur-employé',
            'categorie' => 'Droit du Travail',
            'modules' => [
                [
                    'titre' => 'Le Contrat de Travail',
                    'lecons' => [
                        ['Types de Contrats', 40, 'CDI, CDD, Intérim'],
                        ['Clauses Essentielles', 35, 'Analyse des clauses importantes'],
                        ['Modification du Contrat', 30, 'Procédures de modification']
                    ]
                ],
                [
                    'titre' => 'Rupture du Contrat',
                    'lecons' => [
                        ['Le Licenciement', 45, 'Procédure et motifs'],
                        ['La Démission', 30, 'Droits et obligations'],
                        ['La Rupture Conventionnelle', 40, 'Procédure et avantages']
                    ]
                ]
            ]
        ],

        // Droit Fiscal
        [
            'titre' => 'Fiscalité des Entreprises',
            'description' => 'Formation complète sur la fiscalité des entreprises',
            'categorie' => 'Droit Fiscal',
            'modules' => [
                [
                    'titre' => 'Impôt sur les Sociétés',
                    'lecons' => [
                        ['Calcul du Résultat Fiscal', 50, 'Détermination de l\'assiette'],
                        ['Régimes Spéciaux', 45, 'PME, holdings, groupes'],
                        ['Optimisation Fiscale', 40, 'Stratégies légales']
                    ]
                ],
                [
                    'titre' => 'TVA',
                    'lecons' => [
                        ['Mécanisme de la TVA', 35, 'Principes fondamentaux'],
                        ['TVA Déductible', 40, 'Conditions de déduction'],
                        ['Déclarations de TVA', 30, 'Obligations déclaratives']
                    ]
                ]
            ]
        ],

        // Droit International
        [
            'titre' => 'Commerce International',
            'description' => 'Les bases du droit du commerce international',
            'categorie' => 'Droit International',
            'modules' => [
                [
                    'titre' => 'Contrats Internationaux',
                    'lecons' => [
                        ['Incoterms', 45, 'Comprendre et utiliser les Incoterms'],
                        ['Clause d\'Arbitrage', 40, 'Résolution des litiges'],
                        ['Droit Applicable', 35, 'Choix de la loi applicable']
                    ]
                ],
                [
                    'titre' => 'Douanes et Changes',
                    'lecons' => [
                        ['Régimes Douaniers', 50, 'Les différents régimes'],
                        ['Réglementation des Changes', 45, 'Contrôle des changes'],
                        ['Documents d\'Export', 40, 'Documentation nécessaire']
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
        $prix = rand(149, 399);
        $niveau = $niveaux[array_rand($niveaux)];
        $objectifs = "À la fin de ce cours, vous serez capable de :\n";
        $objectifs .= "- Comprendre les concepts fondamentaux\n";
        $objectifs .= "- Appliquer les connaissances en pratique\n";
        $objectifs .= "- Analyser des situations complexes";

        $stmt->execute([
            $c['titre'],
            $c['description'],
            $objectifs,
            "Niveau précédent recommandé",
            ceil($duree_totale/60),
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
                "Module complet sur " . $module['titre'],
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
                    $lecon[2],
                    $lecon[1],
                    $index + 1,
                    $module_id,
                    'texte'
                ]);
            }
        }

        echo "Cours '{$c['titre']}' ajouté avec succès.\n";
    }

    echo "\nTous les cours supplémentaires ont été insérés avec succès !";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
