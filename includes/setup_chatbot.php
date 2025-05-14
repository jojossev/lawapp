<?php
require_once 'config.php';
require_once 'db_connect.php';

try {
    // Créer la table des conversations
    $sql = "CREATE TABLE IF NOT EXISTS chatbot_conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'chatbot_conversations' créée avec succès.\n";

    // Créer la table des messages
    $sql = "CREATE TABLE IF NOT EXISTS chatbot_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_conversation INT NOT NULL,
        message TEXT NOT NULL,
        est_utilisateur BOOLEAN DEFAULT TRUE,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_conversation) REFERENCES chatbot_conversations(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Table 'chatbot_messages' créée avec succès.\n";

    // Créer la table des réponses prédéfinies
    $sql = "CREATE TABLE IF NOT EXISTS chatbot_reponses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        reponse TEXT NOT NULL,
        mots_cles TEXT NOT NULL,
        categorie VARCHAR(50),
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'chatbot_reponses' créée avec succès.\n";

    // Insérer quelques réponses prédéfinies
    $reponses = [
        [
            'question' => 'Qu\'est-ce qu\'un contrat de travail ?',
            'reponse' => 'Un contrat de travail est une convention par laquelle une personne (le salarié) s\'engage à travailler pour le compte et sous la direction d\'une autre personne (l\'employeur) en contrepartie d\'une rémunération.',
            'mots_cles' => 'contrat travail emploi cdi cdd',
            'categorie' => 'Droit du Travail'
        ],
        [
            'question' => 'Comment créer une entreprise ?',
            'reponse' => 'Pour créer une entreprise, vous devez suivre plusieurs étapes : 1) Choisir la forme juridique (SARL, SAS, etc.), 2) Rédiger les statuts, 3) Déposer le capital social, 4) Publier une annonce légale, 5) Immatriculer l\'entreprise au RCS.',
            'mots_cles' => 'entreprise création sarl sas rcs statuts',
            'categorie' => 'Droit des Sociétés'
        ],
        [
            'question' => 'Quels sont mes droits en cas de licenciement ?',
            'reponse' => 'En cas de licenciement, vous avez droit à : 1) Un préavis ou une indemnité compensatrice, 2) Une indemnité de licenciement si vous avez plus de 8 mois d\'ancienneté, 3) Une indemnité compensatrice de congés payés, 4) La possibilité de contester le licenciement aux prud\'hommes.',
            'mots_cles' => 'licenciement droits indemnités préavis',
            'categorie' => 'Droit du Travail'
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
    echo "Réponses prédéfinies insérées avec succès.\n";

} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
