<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    $reponses = [
        // Droit de la famille
        [
            'question' => 'Comment se déroule un divorce ?',
            'reponse' => "Le divorce peut se dérouler de plusieurs manières :\n1. Divorce par consentement mutuel : les époux sont d'accord sur tout\n2. Divorce pour faute : un époux demande le divorce en raison des fautes de l'autre\n3. Divorce pour altération définitive du lien conjugal : après 2 ans de séparation\n4. Divorce pour acceptation du principe de la rupture du mariage\n\nDans tous les cas, il est recommandé de consulter un avocat pour être bien conseillé.",
            'mots_cles' => 'divorce separation mariage epoux conjoint',
            'categorie' => 'Droit de la Famille'
        ],
        [
            'question' => 'Comment adopter un enfant ?',
            'reponse' => "L'adoption peut être simple ou plénière. Les conditions principales sont :\n1. Avoir plus de 28 ans (sauf cas particuliers)\n2. Avoir 15 ans de plus que l'enfant à adopter\n3. Être marié depuis plus de 2 ans ou être célibataire\n4. Obtenir l'agrément des services sociaux\n\nLa procédure implique plusieurs étapes administratives et judiciaires.",
            'mots_cles' => 'adoption enfant famille agrément',
            'categorie' => 'Droit de la Famille'
        ],

        // Droit immobilier
        [
            'question' => 'Quels sont mes droits en tant que locataire ?',
            'reponse' => "En tant que locataire, vous avez plusieurs droits fondamentaux :\n1. Droit à un logement décent\n2. Droit au maintien dans les lieux\n3. Droit de donner congé avec un préavis\n4. Droit de sous-louer avec l'accord du propriétaire\n5. Droit de faire des travaux d'aménagement\n\nLe propriétaire doit respecter ces droits sous peine de sanctions.",
            'mots_cles' => 'location bail locataire propriétaire logement',
            'categorie' => 'Droit Immobilier'
        ],
        [
            'question' => 'Comment acheter un bien immobilier ?',
            'reponse' => "Les étapes pour acheter un bien immobilier :\n1. Définir son budget et obtenir un prêt\n2. Rechercher le bien\n3. Faire une offre d'achat\n4. Signer un compromis de vente\n5. Obtenir le financement définitif\n6. Signer l'acte authentique chez le notaire\n\nIl est conseillé de se faire accompagner par un professionnel.",
            'mots_cles' => 'achat immobilier maison appartement notaire',
            'categorie' => 'Droit Immobilier'
        ],

        // Droit pénal
        [
            'question' => 'Que faire en cas de garde à vue ?',
            'reponse' => "En cas de garde à vue, vous avez des droits :\n1. Droit au silence\n2. Droit à un avocat\n3. Droit de prévenir un proche\n4. Droit à un médecin\n5. Droit de connaître les faits reprochés\n\nIl est fortement conseillé de demander l'assistance d'un avocat dès le début de la garde à vue.",
            'mots_cles' => 'garde vue police arrestation droits',
            'categorie' => 'Droit Pénal'
        ],
        [
            'question' => 'Comment porter plainte ?',
            'reponse' => "Pour porter plainte, vous avez plusieurs options :\n1. Au commissariat ou à la gendarmerie\n2. Par courrier au procureur de la République\n3. En ligne pour certaines infractions\n\nLa plainte doit contenir :\n- Vos coordonnées\n- Le récit détaillé des faits\n- Les preuves éventuelles\n- L'identité de l'auteur si connue",
            'mots_cles' => 'plainte police justice victime',
            'categorie' => 'Droit Pénal'
        ],

        // Droit de la consommation
        [
            'question' => 'Quels sont mes droits en cas de produit défectueux ?',
            'reponse' => "En cas de produit défectueux, vous bénéficiez :\n1. De la garantie légale de conformité (2 ans)\n2. De la garantie des vices cachés\n3. Du droit de retour dans le cas d'un achat en ligne (14 jours)\n\nVous pouvez demander :\n- La réparation ou le remplacement\n- Le remboursement si les solutions précédentes sont impossibles",
            'mots_cles' => 'garantie défaut produit consommation remboursement',
            'categorie' => 'Droit de la Consommation'
        ],
        [
            'question' => 'Comment résilier un abonnement ?',
            'reponse' => "Pour résilier un abonnement :\n1. Vérifier les conditions de résiliation dans le contrat\n2. Respecter le préavis si prévu\n3. Envoyer une lettre recommandée avec AR\n4. Conserver une copie du courrier et l'AR\n\nCertains contrats peuvent être résiliés à tout moment (loi Chatel), d'autres nécessitent un motif légitime.",
            'mots_cles' => 'résiliation abonnement contrat préavis',
            'categorie' => 'Droit de la Consommation'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO chatbot_reponses (question, reponse, mots_cles, categorie) VALUES (?, ?, ?, ?)");
    foreach ($reponses as $reponse) {
        $stmt->execute([
            $reponse['question'],
            $reponse['reponse'],
            $reponse['mots_cles'],
            $reponse['categorie']
        ]);
    }
    echo "Réponses supplémentaires insérées avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
