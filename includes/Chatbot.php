<?php
class Chatbot {
    private $pdo;
    private $user_id;
    private $conversation_id;

    public function __construct($pdo, $user_id) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
        $this->initConversation();
    }

    private function initConversation() {
        // Créer une nouvelle conversation ou récupérer la dernière
        $stmt = $this->pdo->prepare("
            SELECT id FROM chatbot_conversations 
            WHERE id_utilisateur = ? 
            ORDER BY date_creation DESC 
            LIMIT 1
        ");
        $stmt->execute([$this->user_id]);
        $conversation = $stmt->fetch();

        if (!$conversation) {
            $stmt = $this->pdo->prepare("
                INSERT INTO chatbot_conversations (id_utilisateur) 
                VALUES (?)
            ");
            $stmt->execute([$this->user_id]);
            $this->conversation_id = $this->pdo->lastInsertId();
        } else {
            $this->conversation_id = $conversation['id'];
        }
    }

    public function processMessage($message) {
        // Enregistrer le message de l'utilisateur
        $this->saveMessage($message, true);

        // Trouver la meilleure réponse
        $response = $this->findBestResponse($message);

        // Enregistrer et retourner la réponse
        $this->saveMessage($response, false);
        return $response;
    }

    private function saveMessage($message, $est_utilisateur) {
        $stmt = $this->pdo->prepare("
            INSERT INTO chatbot_messages (id_conversation, message, est_utilisateur)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$this->conversation_id, $message, $est_utilisateur]);
    }

    private function findBestResponse($message) {
        // Convertir le message en mots-clés
        $keywords = $this->extractKeywords($message);
        
        // Rechercher la meilleure correspondance dans la base de données
        $stmt = $this->pdo->prepare("
            SELECT reponse, 
                   (LENGTH(mots_cles) - LENGTH(REPLACE(LOWER(mots_cles), LOWER(?), ''))) / LENGTH(?) as score
            FROM chatbot_reponses
            HAVING score > 0
            ORDER BY score DESC
            LIMIT 1
        ");
        $stmt->execute([$keywords, $keywords]);
        $result = $stmt->fetch();

        if ($result) {
            return $result['reponse'];
        }

        // Réponse par défaut si aucune correspondance n'est trouvée
        return "Je ne suis pas sûr de comprendre votre question. Pourriez-vous la reformuler ou être plus spécifique ? Vous pouvez me poser des questions sur le droit du travail, le droit des sociétés, ou d'autres domaines juridiques.";
    }

    private function extractKeywords($message) {
        // Convertir en minuscules
        $message = mb_strtolower($message);

        // Supprimer la ponctuation
        $message = preg_replace('/[^\p{L}\p{N}\s]/u', '', $message);

        // Supprimer les mots vides (stop words)
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'ce', 'ces', 'son', 'sa', 'ses', 'mon', 'ma', 'mes', 'est', 'sont'];
        $words = explode(' ', $message);
        $words = array_diff($words, $stopWords);

        // Rejoindre les mots
        return implode(' ', $words);
    }

    public function getConversationHistory() {
        $stmt = $this->pdo->prepare("
            SELECT message, est_utilisateur, date_creation
            FROM chatbot_messages
            WHERE id_conversation = ?
            ORDER BY date_creation ASC
        ");
        $stmt->execute([$this->conversation_id]);
        return $stmt->fetchAll();
    }
}
?>
