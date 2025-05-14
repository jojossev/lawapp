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
// BASE_URL est normalement défini dans config.php.
if (defined('BASE_URL')) {
    header("Location: " . BASE_URL . "/accueil.php?logout=success");
} else {
    // Fallback au cas où BASE_URL ne serait pas défini (ne devrait pas arriver).
    error_log("BASE_URL non défini dans logout.php. Redirection vers accueil.php relatif.");
    header("Location: accueil.php?logout=success");
}
exit; // Assurer qu'aucun autre code n'est exécuté après la redirection.
?>
