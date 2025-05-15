<?php
// Script de diagnostic minimal pour Render
header('Content-Type: text/plain');

echo "=== TEST DE BASE RENDER ===\n\n";

// Informations sur PHP
echo "Version PHP: " . phpversion() . "\n";
echo "Extensions chargées: " . implode(', ', get_loaded_extensions()) . "\n\n";

// Informations sur le serveur
echo "Serveur: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

// Variables d'environnement
echo "Variables d'environnement:\n";
$env_vars = ['DATABASE_URL', 'ENVIRONMENT', 'APP_URL', 'RENDER_EXTERNAL_URL'];
foreach ($env_vars as $var) {
    echo "$var: " . (getenv($var) ? getenv($var) : 'Non défini') . "\n";
}

echo "\n=== FIN DU TEST ===\n";
