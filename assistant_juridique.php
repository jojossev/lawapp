<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/Chatbot.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialiser le chatbot pour récupérer l'historique
$chatbot = new Chatbot($pdo, $_SESSION['user_id']);
$history = $chatbot->getConversationHistory();

// Récupérer les dernières recommandations
$stmt = $pdo->prepare("
    SELECT c.id, c.titre, 'cours' as type
    FROM cours c
    ORDER BY c.date_creation DESC
    LIMIT 2
    UNION
    SELECT l.id, l.titre, 'livre' as type
    FROM livres l
    ORDER BY l.date_creation DESC
    LIMIT 2
    UNION
    SELECT p.id, p.titre, 'podcast' as type
    FROM podcasts p
    ORDER BY p.date_creation DESC
    LIMIT 2
");
$stmt->execute();
$recommendations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant Juridique (IA) - LawApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/chatbot.css" rel="stylesheet">
    <style>
        #chat-interface {
            height: 500px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            overflow-y: auto;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #fff;
        }
        .message {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 0.375rem;
            max-width: 80%;
            word-wrap: break-word;
        }
        .user-message {
            background: #e3f2fd;
            margin-left: auto;
            text-align: right;
        }
        .ia-message {
            background: #f8f9fa;
            margin-right: auto;
        }
        .loading {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .loading::after {
            content: "...";
            animation: dots 1s steps(5, end) infinite;
        }
        @keyframes dots {
            0%, 20% { content: "."; }
            40% { content: ".."; }
            60%, 100% { content: "..."; }
        }
        #historique-questions ul {
            list-style-type: none;
            padding: 0;
        }
        #historique-questions li {
            padding: 0.5rem;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        #historique-questions li:hover {
            background-color: #f8f9fa;
        }
        #liens-recommandes ul {
            list-style-type: none;
            padding: 0;
        }
        #liens-recommandes li {
            padding: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }
        #liens-recommandes a {
            color: #0d6efd;
            text-decoration: none;
        }
        #liens-recommandes a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Assistant Juridique (IA)</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div id="chat-interface" class="mb-3">
                            <div class="message ia-message">Bonjour ! Je suis votre assistant juridique. Comment puis-je vous aider aujourd'hui ?</div>
                            <?php foreach ($history as $message): ?>
                                <div class="message <?= $message['est_utilisateur'] ? 'user-message' : 'ia-message' ?>">
                                    <?= htmlspecialchars($message['message']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="input-group">
                            <textarea id="user-input" class="form-control" placeholder="Posez votre question juridique ici..." rows="3"></textarea>
                            <button class="btn btn-primary" type="button" onclick="sendMessage()">
                                <i class="bi bi-send"></i> Envoyer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Historique des Questions</h5>
                    </div>
                    <div class="card-body" id="historique-questions">
                        <ul>
                            <?php 
                            $shown_messages = [];
                            foreach ($history as $message) {
                                if ($message['est_utilisateur'] && !in_array($message['message'], $shown_messages)) {
                                    $shown_messages[] = $message['message'];
                                    echo '<li>' . htmlspecialchars($message['message']) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ressources Recommandées</h5>
                    </div>
                    <div class="card-body" id="liens-recommandes">
                        <ul>
                            <?php foreach ($recommendations as $item): ?>
                                <li>
                                    <a href="<?= $item['type'] ?>_details.php?id=<?= $item['id'] ?>">
                                        <?= htmlspecialchars($item['titre']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/chatbot.js"></script>
        const chatInterface = document.getElementById('chat-interface');
        const userInput = document.getElementById('user-input');

        async function envoyerMessage() {
            const messageText = userInput.value.trim();
            if (messageText === '') return;

            appendMessage(messageText, 'user-message');
            userInput.value = '';

            // Afficher un message temporaire pendant le traitement
            appendMessage('L'assistant réfléchit...', 'ia-message typing-indicator');

            try {
                const response = await fetch('assistant_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'question=' + encodeURIComponent(messageText)
                });

                // Supprimer l'indicateur "réfléchit..."
                const typingIndicator = chatInterface.querySelector('.typing-indicator');
                if (typingIndicator) {
                    chatInterface.removeChild(typingIndicator);
                }

                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }

                const data = await response.json();

                if (data && data.reponse) {
                    appendMessage(data.reponse, 'ia-message');
                } else if (data && data.error) {
                    appendMessage(`Erreur de l'assistant: ${data.error}`, 'ia-message');
                } else {
                    appendMessage('Réponse inattendue de l'assistant.', 'ia-message');
                }

            } catch (error) {
                // Supprimer l'indicateur "réfléchit..." en cas d'erreur aussi
                const typingIndicator = chatInterface.querySelector('.typing-indicator');
                if (typingIndicator) {
                    chatInterface.removeChild(typingIndicator);
                }
                console.error('Erreur lors de la communication avec l'assistant:', error);
                appendMessage('Désolé, une erreur est survenue. Veuillez réessayer plus tard.', 'ia-message');
            }
        }

        function appendMessage(text, className) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', className);
            messageDiv.textContent = text; // Utiliser textContent pour éviter les injections XSS simples
            chatInterface.appendChild(messageDiv);
            chatInterface.scrollTop = chatInterface.scrollHeight; // Auto-scroll
        }

        // Optionnel: Permettre d'envoyer avec la touche Entrée
        userInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Empêche le saut de ligne dans le textarea
                envoyerMessage();
            }
        });
    </script>
</body>
</html>
