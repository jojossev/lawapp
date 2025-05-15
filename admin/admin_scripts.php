<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Style CSS pour une meilleure présentation
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scripts d\'administration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
        .card h3 { margin-top: 0; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .button { display: inline-block; background-color: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
        .button:hover { background-color: #45a049; }
        .button.warning { background-color: #ff9800; }
        .button.warning:hover { background-color: #e68a00; }
        .button.danger { background-color: #f44336; }
        .button.danger:hover { background-color: #da190b; }
        .button.info { background-color: #2196F3; }
        .button.info:hover { background-color: #0b7dda; }
    </style>
</head>
<body>
    <h1>Scripts d\'administration</h1>
    <p>Cette page centralise tous les scripts de diagnostic et de correction disponibles pour l\'application LawApp.</p>';

// Fonction pour vérifier si un fichier existe
function fileExists($path) {
    return file_exists($path);
}

// Liste des scripts disponibles
$scripts = [
    'diagnostic' => [
        [
            'name' => 'Vérifier l\'intégrité de l\'application',
            'file' => 'check_app_integrity.php',
            'description' => 'Vérifie l\'intégrité globale de l\'application (tables, répertoires, fichiers, variables d\'environnement).',
            'type' => 'info'
        ],
        [
            'name' => 'Tester la connexion à la base de données',
            'file' => 'test_db_connection.php',
            'description' => 'Teste la connexion à la base de données et affiche les informations de configuration.',
            'type' => 'info'
        ],
        [
            'name' => 'Vérifier et corriger la base de données',
            'file' => 'check_and_fix_database.php',
            'description' => 'Interface complète pour vérifier et corriger la structure de la base de données.',
            'type' => 'warning'
        ],
        [
            'name' => 'Tester les tables leçons et quiz',
            'file' => 'test_lecons_quiz.php',
            'description' => 'Teste la structure et les données des tables lecons et quiz.',
            'type' => 'info'
        ],
        [
            'name' => 'Tester la table vidéos',
            'file' => 'test_videos.php',
            'description' => 'Teste la structure et les données de la table videos.',
            'type' => 'info'
        ],
        [
            'name' => 'Tester les inscriptions aux cours',
            'file' => 'test_inscriptions.php',
            'description' => 'Affiche les inscriptions aux cours avec les informations des utilisateurs et des cours.',
            'type' => 'info'
        ]
    ],
    'correction' => [
        [
            'name' => 'Corriger tous les problèmes',
            'file' => 'fix_all_issues.php',
            'description' => 'Exécute tous les scripts de correction en séquence avec une barre de progression.',
            'type' => 'danger'
        ],
        [
            'name' => 'Corriger la table utilisateurs',
            'file' => 'fix_utilisateurs_table.php',
            'description' => 'Vérifie et corrige la structure de la table utilisateurs pour l\'authentification.',
            'type' => 'warning'
        ],
        [
            'name' => 'Corriger la table cours',
            'file' => 'fix_cours_table.php',
            'description' => 'Vérifie et corrige la structure des tables cours, categories_cours et formateurs.',
            'type' => 'warning'
        ],
        [
            'name' => 'Corriger la table inscriptions',
            'file' => 'fix_inscriptions_table.php',
            'description' => 'Vérifie et corrige la structure de la table inscriptions pour les inscriptions aux cours.',
            'type' => 'warning'
        ],
        [
            'name' => 'Corriger la structure des vidéos',
            'file' => 'fix_videos_structure.php',
            'description' => 'Vérifie et corrige la structure de la table videos et ses relations.',
            'type' => 'warning'
        ],
        [
            'name' => 'Corriger les tables leçons et quiz',
            'file' => 'fix_lecons_quiz_tables.php',
            'description' => 'Vérifie et corrige la structure des tables lecons, quiz, questions et reponses.',
            'type' => 'warning'
        ]
    ],
    'render' => [
        [
            'name' => 'Tester Render',
            'file' => 'test_render.php',
            'description' => 'Affiche des informations de base sur le serveur Render et les variables d\'environnement.',
            'type' => 'info'
        ],
        [
            'name' => 'Déboguer Render',
            'file' => 'debug_render.php',
            'description' => 'Affiche des informations détaillées sur la configuration de Render.',
            'type' => 'info'
        ],
        [
            'name' => 'Vérifier les chemins d\'administration',
            'file' => 'check_admin_path.php',
            'description' => 'Vérifie l\'existence et l\'accessibilité du dossier admin et de ses fichiers.',
            'type' => 'info'
        ],
        [
            'name' => 'Vérifier les sessions sur Render',
            'file' => 'check_render_sessions.php',
            'description' => 'Vérifie l\'état des sessions sur Render.',
            'type' => 'info'
        ]
    ]
];

// Afficher les scripts de diagnostic
echo '<h2>Scripts de diagnostic</h2>';
echo '<div class="card-grid">';

foreach ($scripts['diagnostic'] as $script) {
    $exists = fileExists(__DIR__ . '/' . $script['file']);
    
    echo '<div class="card">';
    echo '<h3>' . htmlspecialchars($script['name']) . '</h3>';
    echo '<p>' . htmlspecialchars($script['description']) . '</p>';
    
    if ($exists) {
        echo '<a href="' . htmlspecialchars($script['file']) . '" class="button ' . $script['type'] . '">Exécuter</a>';
    } else {
        echo '<span class="error">Script non disponible</span>';
    }
    
    echo '</div>';
}

echo '</div>';

// Afficher les scripts de correction
echo '<h2>Scripts de correction</h2>';
echo '<div class="card-grid">';

foreach ($scripts['correction'] as $script) {
    $exists = fileExists(__DIR__ . '/' . $script['file']);
    
    echo '<div class="card">';
    echo '<h3>' . htmlspecialchars($script['name']) . '</h3>';
    echo '<p>' . htmlspecialchars($script['description']) . '</p>';
    
    if ($exists) {
        echo '<a href="' . htmlspecialchars($script['file']) . '" class="button ' . $script['type'] . '">Exécuter</a>';
    } else {
        echo '<span class="error">Script non disponible</span>';
    }
    
    echo '</div>';
}

echo '</div>';

// Afficher les scripts spécifiques à Render
echo '<h2>Scripts spécifiques à Render</h2>';
echo '<div class="card-grid">';

foreach ($scripts['render'] as $script) {
    $exists = fileExists(__DIR__ . '/' . $script['file']);
    
    echo '<div class="card">';
    echo '<h3>' . htmlspecialchars($script['name']) . '</h3>';
    echo '<p>' . htmlspecialchars($script['description']) . '</p>';
    
    if ($exists) {
        echo '<a href="' . htmlspecialchars($script['file']) . '" class="button ' . $script['type'] . '">Exécuter</a>';
    } else {
        echo '<span class="error">Script non disponible</span>';
    }
    
    echo '</div>';
}

echo '</div>';

// Liens de navigation
echo '<h2>Navigation</h2>';
echo '<p>';
echo '<a href="../index.php" class="button">Retour à l\'accueil</a> ';
echo '<a href="index.php" class="button">Tableau de bord d\'administration</a>';
echo '</p>';

echo '</body>
</html>';
?>
