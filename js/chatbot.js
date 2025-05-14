document.addEventListener('DOMContentLoaded', function() {
    const chatInterface = document.getElementById('chat-interface');
    const userInput = document.getElementById('user-input');
    const historique = document.getElementById('historique-questions');
    const liensRecommandes = document.getElementById('liens-recommandes');

    // Fonction pour ajouter un message au chat
    function appendMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;
        chatInterface.appendChild(messageDiv);
        chatInterface.scrollTop = chatInterface.scrollHeight;
    }

    // Fonction pour mettre à jour l'historique
    function updateHistory(history) {
        historique.innerHTML = '';
        const ul = document.createElement('ul');
        
        history.forEach(item => {
            if (item.est_utilisateur) {
                const li = document.createElement('li');
                li.textContent = item.message;
                li.addEventListener('click', () => {
                    userInput.value = item.message;
                });
                ul.appendChild(li);
            }
        });
        
        historique.appendChild(ul);
    }

    // Fonction pour mettre à jour les recommandations
    function updateRecommendations(recommendations) {
        liensRecommandes.innerHTML = '';
        
        if (recommendations.length === 0) {
            liensRecommandes.innerHTML = '<p>Aucun contenu recommandé pour le moment.</p>';
            return;
        }

        const ul = document.createElement('ul');
        recommendations.forEach(item => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = `${item.type}_details.php?id=${item.id}`;
            a.textContent = item.titre;
            li.appendChild(a);
            ul.appendChild(li);
        });
        
        liensRecommandes.appendChild(ul);
    }

    // Fonction pour envoyer un message
    async function sendMessage() {
        const message = userInput.value.trim();
        if (message === '') return;

        // Afficher le message de l'utilisateur
        appendMessage(message, 'user-message');
        
        // Afficher un indicateur de chargement
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'message ia-message loading';
        loadingDiv.textContent = 'En train de réfléchir...';
        chatInterface.appendChild(loadingDiv);
        
        // Vider l'input
        userInput.value = '';

        try {
            // Envoyer la requête au serveur
            const formData = new FormData();
            formData.append('message', message);

            const response = await fetch('chatbot_process.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            // Supprimer l'indicateur de chargement
            chatInterface.removeChild(loadingDiv);

            if (data.error) {
                appendMessage('Désolé, une erreur est survenue. Veuillez réessayer.', 'ia-message error');
                return;
            }

            // Afficher la réponse
            appendMessage(data.response, 'ia-message');

            // Mettre à jour l'historique et les recommandations
            if (data.history) updateHistory(data.history);
            if (data.recommendations) updateRecommendations(data.recommendations);

        } catch (error) {
            // Supprimer l'indicateur de chargement
            chatInterface.removeChild(loadingDiv);
            appendMessage('Désolé, une erreur est survenue. Veuillez réessayer.', 'ia-message error');
        }
    }

    // Event listeners
    userInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    document.querySelector('button[onclick="envoyerMessage()"]').onclick = function(e) {
        e.preventDefault();
        sendMessage();
    };
});
