<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Assurer que config.php est inclus.
// config.php gère session_start() et définit BASE_URL.
require_once __DIR__ . "/includes/config.php";

// 1. Vider toutes les variables de session.
$_SESSION = array();

// 2. Si les cookies sont utilisés pour les sessions, effacer le cookie de session.
// Ceci est important pour détruire complètement la session.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '', // Valeur vide
        time() - 42000, // Expiration dans le passé
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Finalement, détruire la session côté serveur.
session_destroy();

// 4. Rediriger vers la page d'accueil avec un message de succès.
// Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
?>
<!DOCTYPE html>
<html>
<head>
    <title>Déconnexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px auto;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <div class="success-message">
        <h2>Déconnexion réussie</h2>
        <p>Vous avez été déconnecté avec succès.</p>
        <p>Vous allez être redirigé vers la page d'accueil dans quelques secondes...</p>
    </div>
    
    <script>
        // Redirection après 2 secondes
        setTimeout(function() {
            window.location.href = 'index.php?logout=success';
        }, 2000);
    </script>
</body>
</html>
