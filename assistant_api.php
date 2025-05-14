<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $question = trim($_POST['question']);

    if (empty($question)) {
        echo json_encode(['error' => 'La question ne peut pas être vide.']);
        exit;
    }

    // Ici, plus tard, vous appellerez l'API de l'IA (par exemple, OpenAI)
    // Pour l'instant, nous simulons une réponse.
    $reponseSimulee = "Le serveur a bien reçu votre question : \"" . htmlspecialchars($question) . "\". L'IA est en cours de configuration.";

    // Simuler un petit délai, comme si une IA réfléchissait
    sleep(1); 

    echo json_encode(['reponse' => $reponseSimulee]);

} else {
    echo json_encode(['error' => 'Méthode non autorisée ou question manquante.']);
}
?>
