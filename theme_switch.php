<?php
// Fichier séparé pour gérer le changement de thème
session_start();
require_once 'includes/config.php';

if (isset($_GET['theme'])) {
    $new_theme = $_GET['theme'] === 'dark' ? 'dark' : 'light';
    setcookie('theme', $new_theme, time() + (86400 * 30), "/"); // Cookie pour 30 jours
    
    // Rediriger vers la page précédente ou l'accueil
    $redirect_to = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
    header('Location: ' . $redirect_to);
    exit;
} else {
    // Si aucun thème n'est spécifié, rediriger vers l'accueil
    header('Location: index.php');
    exit;
}
?>
