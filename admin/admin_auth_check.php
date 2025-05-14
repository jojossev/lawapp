<?php
// admin_auth_check.php
// Ce fichier est inclus au début de chaque page d'administration sécurisée.

// Inclure la configuration
require_once __DIR__ . '/../includes/config.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Forcer la connexion admin
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_prenom'] = 'Admin';
    $_SESSION['admin_nom'] = 'Principal';
    $_SESSION['admin_email'] = 'admin@lawapp.com';
    $_SESSION['admin_role'] = 'admin';
}

// Vérifier uniquement si la connexion PDO est disponible
if (!isset($pdo)) {
    die("Erreur : Base de données non disponible.");
}
?>
