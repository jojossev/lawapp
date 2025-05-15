<?php
session_start(); // Accéder à la session existante

// Détruire toutes les variables de session
$_SESSION = array();

// Si vous souhaitez détruire complètement la session, effacez également
// le cookie de session.
// Note : Cela détruira la session et pas seulement les données de session !
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalement, détruire la session.
session_destroy();

// Rediriger vers la page de connexion de l'admin
// Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
?>
<!DOCTYPE html>
<html>
<head>
    <title>Déconnexion Admin</title>
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
        <p>Vous avez été déconnecté de l'interface d'administration.</p>
        <p>Vous allez être redirigé vers la page de connexion admin dans quelques secondes...</p>
    </div>
    
    <script>
        // Redirection après 2 secondes
        setTimeout(function() {
            window.location.href = 'admin_login.php';
        }, 2000);
    </script>
</body>
</html>
