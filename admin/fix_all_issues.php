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
    <title>Correction de tous les problèmes de base de données</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .progress-container { width: 100%; background-color: #f3f3f3; border-radius: 5px; margin: 20px 0; }
        .progress-bar { height: 30px; background-color: #4CAF50; border-radius: 5px; text-align: center; line-height: 30px; color: white; }
        .step { margin: 10px 0; padding: 10px; border-left: 4px solid #ddd; }
        .step.active { border-left: 4px solid #4CAF50; background-color: #f9f9f9; }
        .step.completed { border-left: 4px solid #4CAF50; }
        .step.pending { border-left: 4px solid #ddd; color: #999; }
        .step.error { border-left: 4px solid #f44336; }
    </style>
</head>
<body>
    <h1>Correction de tous les problèmes de base de données</h1>';

// Liste des scripts à exécuter
$scripts = [
    [
        'name' => 'Test de connexion à la base de données',
        'file' => 'test_db_connection.php',
        'description' => 'Vérifie la connexion à la base de données et les paramètres de configuration.'
    ],
    [
        'name' => 'Correction de la table utilisateurs',
        'file' => 'fix_utilisateurs_table.php',
        'description' => 'Vérifie et corrige la structure de la table utilisateurs pour l\'authentification.'
    ],
    [
        'name' => 'Correction de la table cours',
        'file' => 'fix_cours_table.php',
        'description' => 'Vérifie et corrige la structure des tables cours, categories_cours et formateurs.'
    ],
    [
        'name' => 'Correction de la table inscriptions',
        'file' => 'fix_inscriptions_table.php',
        'description' => 'Vérifie et corrige la structure de la table inscriptions pour les inscriptions aux cours.'
    ],
    [
        'name' => 'Correction de la structure des vidéos',
        'file' => 'fix_videos_structure.php',
        'description' => 'Vérifie et corrige la structure de la table videos et ses relations.'
    ],
    [
        'name' => 'Correction des tables leçons et quiz',
        'file' => 'fix_lecons_quiz_tables.php',
        'description' => 'Vérifie et corrige la structure des tables lecons, quiz, questions et reponses.'
    ]
];

// Paramètres
$total_steps = count($scripts);
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 0;
$auto_continue = isset($_GET['auto']) && $_GET['auto'] == 1;

// Afficher la barre de progression
$progress_percentage = $current_step > 0 ? min(100, round(($current_step / $total_steps) * 100)) : 0;
echo '<div class="progress-container">
    <div class="progress-bar" style="width: ' . $progress_percentage . '%">' . $progress_percentage . '%</div>
</div>';

echo '<p>Étape ' . ($current_step + 1) . ' sur ' . $total_steps . '</p>';

// Afficher la liste des étapes
echo '<div class="steps">';
for ($i = 0; $i < $total_steps; $i++) {
    $class = 'step';
    if ($i < $current_step) {
        $class .= ' completed';
    } elseif ($i == $current_step) {
        $class .= ' active';
    } else {
        $class .= ' pending';
    }
    
    echo '<div class="' . $class . '">';
    echo '<h3>' . ($i + 1) . '. ' . htmlspecialchars($scripts[$i]['name']) . '</h3>';
    echo '<p>' . htmlspecialchars($scripts[$i]['description']) . '</p>';
    echo '</div>';
}
echo '</div>';

// Exécuter l'étape actuelle
if ($current_step < $total_steps) {
    $script = $scripts[$current_step];
    
    echo '<h2>Exécution de : ' . htmlspecialchars($script['name']) . '</h2>';
    echo '<div class="script-output">';
    
    // Si le script existe, l'inclure
    $script_path = __DIR__ . '/' . $script['file'];
    if (file_exists($script_path)) {
        // Capturer la sortie du script
        ob_start();
        include $script_path;
        $output = ob_get_clean();
        
        // Filtrer le contenu pour éviter les doublons d'en-têtes HTML
        // Supprimer les balises DOCTYPE, html, head et body
        $output = preg_replace('/<\!DOCTYPE.*?>|<html.*?>|<\/html>|<head>.*?<\/head>|<body.*?>|<\/body>/s', '', $output);
        echo $output;
        
        echo '<div class="success">Script exécuté avec succès.</div>';
        
        // Lien vers la prochaine étape
        $next_step = $current_step + 1;
        if ($next_step < $total_steps) {
            echo '<p><a href="?step=' . $next_step . '&auto=' . ($auto_continue ? '1' : '0') . '" class="button">Passer à l\'étape suivante</a></p>';
            
            // Redirection automatique si demandé
            if ($auto_continue) {
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "?step=' . $next_step . '&auto=1";
                    }, 3000);
                </script>';
                echo '<p class="info">Redirection automatique dans 3 secondes...</p>';
            }
        } else {
            echo '<div class="success">Toutes les étapes ont été complétées avec succès!</div>';
            echo '<p><a href="../index.php" class="button">Retour à l\'accueil</a></p>';
        }
    } else {
        echo '<div class="error">Le script ' . htmlspecialchars($script['file']) . ' n\'existe pas.</div>';
        echo '<p><a href="?step=' . ($current_step + 1) . '&auto=' . ($auto_continue ? '1' : '0') . '" class="button">Passer à l\'étape suivante</a></p>';
    }
    
    echo '</div>';
} else {
    echo '<div class="success">Toutes les étapes ont été complétées avec succès!</div>';
    echo '<p><a href="../index.php" class="button">Retour à l\'accueil</a></p>';
}

// Options de configuration
echo '<h2>Options</h2>';
echo '<form method="get">';
echo '<input type="hidden" name="step" value="0">';
echo '<label><input type="checkbox" name="auto" value="1" ' . ($auto_continue ? 'checked' : '') . '> Continuer automatiquement</label><br>';
echo '<button type="submit">Redémarrer avec ces options</button>';
echo '</form>';

echo '<p><a href="../index.php">Retour à l\'accueil</a> | <a href="check_and_fix_database.php">Vérifier et corriger la base de données</a></p>';

echo '</body>
</html>';
?>
