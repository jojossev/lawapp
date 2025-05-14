<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Catégories de droit
    $categories = [
        ['Droit Civil', 'Livres sur le droit civil et les obligations'],
        ['Droit Pénal', 'Ouvrages sur le droit pénal et la procédure pénale'],
        ['Droit Commercial', 'Livres sur le droit des affaires et le commerce'],
        ['Droit Constitutionnel', 'Ouvrages sur la constitution et les institutions'],
        ['Droit International', 'Livres sur le droit international et les relations entre États'],
        ['Droit du Travail', 'Ouvrages sur le droit social et le travail'],
        ['Droit Administratif', 'Livres sur l\'administration et le droit public'],
        ['Droit Fiscal', 'Ouvrages sur la fiscalité et les impôts']
    ];

    // Insérer les catégories
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("INSERT INTO livre_categories (nom, description) 
                              SELECT ?, ? 
                              WHERE NOT EXISTS (SELECT 1 FROM livre_categories WHERE nom = ?)");
        $stmt->execute([$cat[0], $cat[1], $cat[0]]);
    }

    // Niveaux possibles
    $niveaux = ['Débutant', 'Intermédiaire', 'Avancé'];

    // Éditeurs juridiques
    $editeurs = [
        'Éditions Juridiques',
        'Dalloz',
        'LexisNexis',
        'LGDJ',
        'Larcier'
    ];

    // Liste des livres à insérer
    $livres = [
        // Droit Civil
        ['Les Fondamentaux du Droit Civil', 'Marie Dupont', 'Une introduction complète au droit civil.', 29.99, 'Droit Civil'],
        ['Le Contrat en Droit Civil', 'Pierre Martin', 'Tout sur la théorie des contrats.', 34.99, 'Droit Civil'],
        ['La Responsabilité Civile', 'Sophie Laurent', 'Comprendre la responsabilité civile.', 39.99, 'Droit Civil'],
        ['Les Obligations en Droit Civil', 'Jean Dubois', 'Manuel complet sur les obligations.', 44.99, 'Droit Civil'],
        ['Le Droit de la Famille', 'Claire Moreau', 'Guide pratique du droit familial.', 32.99, 'Droit Civil'],
        
        // Droit Pénal
        ['Manuel de Droit Pénal', 'Thomas Bernard', 'Les bases du droit pénal.', 45.99, 'Droit Pénal'],
        ['La Procédure Pénale', 'Luc Girard', 'Guide de la procédure pénale.', 49.99, 'Droit Pénal'],
        ['Les Infractions Pénales', 'Marie Leclerc', 'Classification des infractions.', 39.99, 'Droit Pénal'],
        ['Droit Pénal Spécial', 'Paul Durant', 'Étude des infractions particulières.', 42.99, 'Droit Pénal'],
        ['La Responsabilité Pénale', 'Anne Richard', 'Analyse de la responsabilité.', 37.99, 'Droit Pénal'],

        // Droit Commercial
        ['Droit des Sociétés', 'Michel Robert', 'Les différentes formes de sociétés.', 54.99, 'Droit Commercial'],
        ['Le Fonds de Commerce', 'Julie Petit', 'Tout sur le fonds de commerce.', 47.99, 'Droit Commercial'],
        ['Les Contrats Commerciaux', 'François Martin', 'Guide des contrats commerciaux.', 49.99, 'Droit Commercial'],
        ['Droit de la Concurrence', 'Sophie Blanc', 'Comprendre la concurrence.', 52.99, 'Droit Commercial'],
        ['Le Droit des Entreprises', 'Pierre Durand', 'Manuel du droit des entreprises.', 56.99, 'Droit Commercial'],

        // Droit Constitutionnel
        ['La Constitution Expliquée', 'Jean-Marc Simon', 'Analyse de la constitution.', 44.99, 'Droit Constitutionnel'],
        ['Les Institutions Politiques', 'Marie-Claire Dubois', 'Guide des institutions.', 42.99, 'Droit Constitutionnel'],
        ['Le Conseil Constitutionnel', 'Paul Henri', 'Rôle et fonctionnement.', 39.99, 'Droit Constitutionnel'],
        ['Les Libertés Fondamentales', 'Claire Martin', 'Étude des droits fondamentaux.', 45.99, 'Droit Constitutionnel'],
        ['Le Système Politique', 'Thomas Leroy', 'Analyse du système politique.', 41.99, 'Droit Constitutionnel'],

        // Droit International
        ['Droit International Public', 'Marie Rousseau', 'Les bases du droit international.', 59.99, 'Droit International'],
        ['Droit International Privé', 'Jean-Pierre Mercier', 'Relations juridiques privées.', 57.99, 'Droit International'],
        ['Les Traités Internationaux', 'Sophie Moreau', 'Étude des traités.', 54.99, 'Droit International'],
        ['Droit Européen', 'Pierre Lambert', 'Le droit de l\'Union Européenne.', 56.99, 'Droit International'],
        ['Les Organisations Internationales', 'Claire Dupuis', 'Fonctionnement des OI.', 52.99, 'Droit International'],

        // Droit du Travail
        ['Le Contrat de Travail', 'Laurent Martin', 'Tout sur le contrat de travail.', 47.99, 'Droit du Travail'],
        ['Le Droit Syndical', 'Marie Dumont', 'Les syndicats et leurs droits.', 44.99, 'Droit du Travail'],
        ['Les Conflits du Travail', 'Pierre Roussel', 'Gestion des conflits.', 42.99, 'Droit du Travail'],
        ['La Protection Sociale', 'Sophie Bernard', 'Système de protection sociale.', 46.99, 'Droit du Travail'],
        ['Le Licenciement', 'Jean Dupuis', 'Procédures de licenciement.', 43.99, 'Droit du Travail'],

        // Droit Administratif
        ['Le Droit Public', 'Claire Lambert', 'Introduction au droit public.', 49.99, 'Droit Administratif'],
        ['Les Actes Administratifs', 'Thomas Martin', 'Nature et régime juridique.', 47.99, 'Droit Administratif'],
        ['Le Contentieux Administratif', 'Marie Simon', 'Procédures contentieuses.', 52.99, 'Droit Administratif'],
        ['Les Services Publics', 'Pierre Dubois', 'Organisation et fonctionnement.', 46.99, 'Droit Administratif'],
        ['La Responsabilité Administrative', 'Sophie Leroy', 'Étude de la responsabilité.', 48.99, 'Droit Administratif'],

        // Droit Fiscal
        ['La Fiscalité des Entreprises', 'Jean-Paul Martin', 'Guide fiscal des entreprises.', 57.99, 'Droit Fiscal'],
        ['L\'Impôt sur le Revenu', 'Marie Roussel', 'Comprendre l\'IR.', 44.99, 'Droit Fiscal'],
        ['La TVA', 'Pierre Moreau', 'Tout sur la TVA.', 46.99, 'Droit Fiscal'],
        ['Le Contrôle Fiscal', 'Sophie Dupont', 'Procédures de contrôle.', 49.99, 'Droit Fiscal'],
        ['La Fiscalité Internationale', 'Thomas Bernard', 'Aspects internationaux.', 54.99, 'Droit Fiscal']
    ];

    // Insérer les livres
    foreach ($livres as $livre) {
        // Récupérer l'ID de la catégorie
        $stmt = $pdo->prepare("SELECT id FROM livre_categories WHERE nom = ?");
        $stmt->execute([$livre[4]]);
        $categorie_id = $stmt->fetchColumn();

        // Générer des données aléatoires
        $annee = rand(2020, 2025);
        $pages = rand(200, 800);
        $niveau = $niveaux[array_rand($niveaux)];
        $editeur = $editeurs[array_rand($editeurs)];
        $isbn = '978' . rand(1000000000, 9999999999);

        // Insérer le livre
        $stmt = $pdo->prepare("
            INSERT INTO livres (
                titre, auteur, description, prix, 
                image_url, type_document, url_document, 
                nombre_pages, editeur, annee_publication,
                isbn, niveau, id_categorie, statut
            ) VALUES (
                ?, ?, ?, ?,
                ?, 'PDF', ?,
                ?, ?, ?,
                ?, ?, ?, 'publie'
            )
        ");

        $image_url = '/LawApp/assets/images/livres/default-' . strtolower(str_replace(' ', '-', $livre[4])) . '.jpg';
        $document_url = '/LawApp/assets/documents/' . strtolower(str_replace(' ', '-', $livre[0])) . '.pdf';

        $stmt->execute([
            $livre[0],  // titre
            $livre[1],  // auteur
            $livre[2],  // description
            $livre[3],  // prix
            $image_url, // image_url
            $document_url, // url_document
            $pages,     // nombre_pages
            $editeur,   // editeur
            $annee,     // annee_publication
            $isbn,      // isbn
            $niveau,    // niveau
            $categorie_id // id_categorie
        ]);

        echo "Livre '{$livre[0]}' ajouté avec succès.\n";
    }

    echo "\nTous les livres ont été insérés avec succès !";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
