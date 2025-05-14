<?php
// utils/redirect_helpers.php

if (!defined('BASE_URL')) {
    // Essayer de définir BASE_URL si ce n'est pas déjà fait (utile si inclus directement sans config)
    // Ceci est une solution de secours, idéalement BASE_URL est défini dans db_connect.php ou config.php
    // Détermine le protocole (http ou https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    // Détermine le nom d'hôte
    $host = $_SERVER['HTTP_HOST'];
    // Détermine le chemin de base du script actuel, remonte d'un niveau (de utils vers la racine)
    $scriptPath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Remonte de /utils vers la racine
    // S'assure qu'il y a un slash à la fin, sauf si c'est la racine du domaine
    $baseUrlPath = rtrim($scriptPath, '/') . '/';
    if ($baseUrlPath === '//') { // Cas où l'app est à la racine du domaine
        $baseUrlPath = '/';
    }
    define('BASE_URL', $protocol . $host . $baseUrlPath);
    error_log("Warning: BASE_URL was not defined. Guessed value: " . BASE_URL);
}

/**
 * Redirige l'utilisateur vers une URL spécifiée.
 * Utilise BASE_URL si l'URL fournie est relative.
 *
 * @param string $url L'URL de destination (relative ou absolue).
 */
function redirect(string $url): void
{
    // Si l'URL ne commence pas par http, on suppose qu'elle est relative à BASE_URL
    if (strpos($url, 'http') !== 0 && defined('BASE_URL')) {
        // Supprimer les slashs de début potentiels de l'URL relative pour éviter les doubles slashs
        $url = ltrim($url, '/');
        // Concaténer avec BASE_URL (qui doit se terminer par un slash)
        $finalUrl = rtrim(BASE_URL, '/') . '/' . $url;
    } else {
        $finalUrl = $url;
    }

    // Effectuer la redirection
    header("Location: " . $finalUrl);
    exit; // Terminer le script après la redirection
}

// Note: Les fonctions spécifiques comme redirectWithErrorCatCours, redirectWithSuccessCatCours, etc.
// sont gardées dans les fichiers _actions.php respectifs car elles utilisent des clés de session spécifiques
// et des URL de redirection spécifiques à ces modules.
// Si vous souhaitez les centraliser, il faudrait passer les clés de session et les URL en paramètres.

?>
