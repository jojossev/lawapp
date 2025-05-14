<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/Chatbot.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Vous devez être connecté pour utiliser l\'assistant juridique']);
    exit;
}

// Vérifier si la requête est en POST et contient un message
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Requête invalide']);
    exit;
}

try {
    // Initialiser le chatbot
    $chatbot = new Chatbot($pdo, $_SESSION['user_id']);
    
    // Traiter le message et obtenir la réponse
    $response = $chatbot->processMessage($_POST['message']);
    
    // Récupérer l'historique de la conversation
    $history = $chatbot->getConversationHistory();
    
    // Préparer les liens recommandés basés sur le message
    $stmt = $pdo->prepare("
        SELECT c.id, c.titre, 'cours' as type
        FROM cours c
        WHERE MATCH(c.titre, c.description) AGAINST (? IN NATURAL LANGUAGE MODE)
        UNION
        SELECT l.id, l.titre, 'livre' as type
        FROM livres l
        WHERE MATCH(l.titre, l.description) AGAINST (? IN NATURAL LANGUAGE MODE)
        UNION
        SELECT p.id, p.titre, 'podcast' as type
        FROM podcasts p
        WHERE MATCH(p.titre, p.description) AGAINST (? IN NATURAL LANGUAGE MODE)
        LIMIT 5
    ");
    $stmt->execute([$_POST['message'], $_POST['message'], $_POST['message']]);
    $recommendations = $stmt->fetchAll();
    
    // Envoyer la réponse
    echo json_encode([
        'success' => true,
        'response' => $response,
        'history' => $history,
        'recommendations' => $recommendations
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue']);
}
?>
