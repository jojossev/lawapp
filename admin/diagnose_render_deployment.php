<?php
// Diagnostic avancé pour le déploiement sur Render

// Configuration de l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour collecter des informations système
function collectSystemInfo() {
    $info = [];

    // Informations PHP
    $info['php_version'] = PHP_VERSION;
    $info['php_sapi'] = php_sapi_name();
    $info['php_extensions'] = get_loaded_extensions();

    // Informations serveur
    $info['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Non disponible';
    $info['server_name'] = $_SERVER['SERVER_NAME'] ?? 'Non disponible';
    $info['request_method'] = $_SERVER['REQUEST_METHOD'] ?? 'Non disponible';

    // Variables d'environnement
    $env_vars = [
        'DATABASE_URL', 
        'DB_USER', 
        'DB_PASS', 
        'RENDER_SERVICE_ID', 
        'RENDER_EXTERNAL_URL'
    ];
    
    foreach ($env_vars as $var) {
        $info['env_vars'][$var] = getenv($var) ?: 'Non définie';
    }

    // Vérification des fichiers critiques
    $critical_files = [
        'includes/config.php',
        'includes/db_connect.php',
        'admin/database_migration.php'
    ];
    
    foreach ($critical_files as $file) {
        $full_path = dirname(__DIR__) . '/' . $file;
        $info['file_checks'][$file] = [
            'exists' => file_exists($full_path),
            'readable' => is_readable($full_path),
            'size' => file_exists($full_path) ? filesize($full_path) : 0
        ];
    }

    // Test de connexion à la base de données
    try {
        require_once dirname(__DIR__) . '/includes/config.php';
        require_once dirname(__DIR__) . '/includes/db_connect.php';
        
        $test_query = $pdo->query("SELECT 1");
        $info['database_connection'] = 'Réussie';
    } catch (Exception $e) {
        $info['database_connection'] = 'Échec : ' . $e->getMessage();
    }

    return $info;
}

// Fonction pour générer un rapport détaillé
function generateDiagnosticReport($info) {
    $report = "=== DIAGNOSTIC DE DÉPLOIEMENT RENDER ===\n\n";
    
    $report .= "1. INFORMATIONS SYSTÈME\n";
    $report .= "   Version PHP: {$info['php_version']}\n";
    $report .= "   SAPI: {$info['php_sapi']}\n";
    $report .= "   Extensions PHP: " . implode(', ', $info['php_extensions']) . "\n\n";
    
    $report .= "2. INFORMATIONS SERVEUR\n";
    $report .= "   Logiciel serveur: {$info['server_software']}\n";
    $report .= "   Nom du serveur: {$info['server_name']}\n";
    $report .= "   Méthode de requête: {$info['request_method']}\n\n";
    
    $report .= "3. VARIABLES D'ENVIRONNEMENT\n";
    foreach ($info['env_vars'] as $var => $value) {
        $report .= "   $var: $value\n";
    }
    $report .= "\n";
    
    $report .= "4. VÉRIFICATION DES FICHIERS CRITIQUES\n";
    foreach ($info['file_checks'] as $file => $check) {
        $report .= "   $file:\n";
        $report .= "     Existe: " . ($check['exists'] ? 'Oui' : 'Non') . "\n";
        $report .= "     Lisible: " . ($check['readable'] ? 'Oui' : 'Non') . "\n";
        $report .= "     Taille: {$check['size']} octets\n";
    }
    $report .= "\n";
    
    $report .= "5. CONNEXION BASE DE DONNÉES\n";
    $report .= "   Statut: {$info['database_connection']}\n";
    
    return $report;
}

// Exécution du diagnostic
$system_info = collectSystemInfo();
$diagnostic_report = generateDiagnosticReport($system_info);

// Affichage du rapport
header('Content-Type: text/plain; charset=utf-8');
echo $diagnostic_report;

// Optionnel : Enregistrement du rapport dans un fichier
file_put_contents(dirname(__DIR__) . '/render_diagnostic_report.txt', $diagnostic_report);
