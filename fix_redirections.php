<?php
// Script pour vérifier et corriger les redirections HTTP dans l'application
require_once __DIR__ . '/includes/config.php';

// Fonction pour vérifier si un fichier contient des appels à header('Location:')
function checkForHeaderRedirects($file_path) {
    if (!file_exists($file_path)) {
        return ['exists' => false, 'has_header_redirects' => false, 'lines' => []];
    }
    
    $content = file_get_contents($file_path);
    $has_header_redirects = preg_match('/header\s*\(\s*[\'"]Location:/i', $content);
    
    $lines = [];
    if ($has_header_redirects) {
        $file_lines = file($file_path);
        foreach ($file_lines as $line_num => $line) {
            if (preg_match('/header\s*\(\s*[\'"]Location:/i', $line)) {
                $lines[] = ['line_num' => $line_num + 1, 'content' => trim($line)];
            }
        }
    }
    
    return ['exists' => true, 'has_header_redirects' => $has_header_redirects, 'lines' => $lines];
}

// Fonction pour corriger les redirections HTTP dans un fichier
function fixHeaderRedirects($file_path) {
    if (!file_exists($file_path)) {
        return ['success' => false, 'message' => "Le fichier n'existe pas."];
    }
    
    $content = file_get_contents($file_path);
    $original_content = $content;
    
    // Remplacer les redirections header('Location: ...') par des redirections JavaScript
    $pattern = '/header\s*\(\s*[\'"]Location:\s*([^\'"]+)[\'"]\s*\)\s*;/i';
    $replacement = "echo \"<div style='text-align: center; margin: 20px; font-family: Arial, sans-serif;'>\";
echo \"<h2>Redirection en cours...</h2>\";
echo \"<p>Vous allez être redirigé vers une autre page. Si la redirection ne fonctionne pas, <a href='\\1'>cliquez ici</a>.</p>\";
echo \"<div style='margin: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;'>\";
echo \"<img src='assets/img/loading.gif' alt='Chargement...' style='width: 50px; height: 50px;'>\";
echo \"</div>\";
echo \"</div>\";
echo \"<script>window.location.href = '\\1';</script>\";
die();";
    
    $content = preg_replace($pattern, $replacement, $content);
    
    // Vérifier si des modifications ont été apportées
    if ($content === $original_content) {
        return ['success' => false, 'message' => "Aucune redirection à corriger dans ce fichier."];
    }
    
    // Écrire le contenu modifié dans le fichier
    $result = file_put_contents($file_path, $content);
    
    if ($result === false) {
        return ['success' => false, 'message' => "Erreur lors de l'écriture dans le fichier."];
    }
    
    return ['success' => true, 'message' => "Les redirections ont été corrigées avec succès."];
}

// Liste des fichiers à vérifier
$files_to_check = [
    'login.php' => __DIR__ . '/login.php',
    'logout.php' => __DIR__ . '/logout.php',
    'register.php' => __DIR__ . '/register.php',
    'update_settings_process.php' => __DIR__ . '/update_settings_process.php',
    'edit_profil_process.php' => __DIR__ . '/edit_profil_process.php',
    'change_password_process.php' => __DIR__ . '/change_password_process.php',
    'view_cours.php' => __DIR__ . '/view_cours.php',
    'admin/admin_login_process.php' => __DIR__ . '/admin/admin_login_process.php',
    'admin/admin_logout.php' => __DIR__ . '/admin/admin_logout.php',
    'inscription_cours.php' => __DIR__ . '/inscription_cours.php'
];

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vérification et correction des redirections</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-warning {
            background-color: #ff9800;
        }
        .btn-warning:hover {
            background-color: #e68a00;
        }
        .code {
            font-family: monospace;
            background-color: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Vérification et correction des redirections</h1>
        
        <p>Ce script vérifie et corrige les redirections HTTP dans l'application en remplaçant les appels à <span class="code">header('Location: ...')</span> par des redirections JavaScript.</p>
        
        <h2>Pourquoi utiliser des redirections JavaScript ?</h2>
        <p>Les redirections JavaScript sont utilisées pour éviter les erreurs "headers already sent" qui peuvent survenir lorsque du contenu est envoyé au navigateur avant l'appel à <span class="code">header()</span>. Cette approche est plus robuste et permet d'afficher un message à l'utilisateur pendant la redirection.</p>
        
        <h2>Fichiers vérifiés</h2>
        
        <table>
            <tr>
                <th>Fichier</th>
                <th>Statut</th>
                <th>Redirections trouvées</th>
                <th>Action</th>
            </tr>
            
            <?php
            $files_with_redirects = [];
            
            foreach ($files_to_check as $name => $path) {
                $result = checkForHeaderRedirects($path);
                
                echo "<tr>";
                echo "<td>" . $name . "</td>";
                
                if (!$result['exists']) {
                    echo "<td><span class='warning'>N'existe pas</span></td>";
                    echo "<td>N/A</td>";
                    echo "<td>N/A</td>";
                } else {
                    echo "<td><span class='success'>Existe</span></td>";
                    
                    if ($result['has_header_redirects']) {
                        echo "<td><span class='error'>" . count($result['lines']) . " redirection(s) trouvée(s)</span></td>";
                        echo "<td><a href='?fix=" . urlencode($name) . "' class='btn btn-warning'>Corriger</a></td>";
                        $files_with_redirects[$name] = $path;
                    } else {
                        echo "<td><span class='success'>Aucune redirection trouvée</span></td>";
                        echo "<td>Aucune action nécessaire</td>";
                    }
                }
                
                echo "</tr>";
            }
            ?>
        </table>
        
        <?php
        // Traiter la demande de correction
        if (isset($_GET['fix']) && array_key_exists($_GET['fix'], $files_with_redirects)) {
            $file_to_fix = $_GET['fix'];
            $path_to_fix = $files_with_redirects[$file_to_fix];
            
            echo "<h2>Correction des redirections dans " . $file_to_fix . "</h2>";
            
            $result = fixHeaderRedirects($path_to_fix);
            
            if ($result['success']) {
                echo "<p class='success'>" . $result['message'] . "</p>";
            } else {
                echo "<p class='error'>" . $result['message'] . "</p>";
            }
            
            // Vérifier à nouveau le fichier
            $check_result = checkForHeaderRedirects($path_to_fix);
            
            if ($check_result['has_header_redirects']) {
                echo "<p class='warning'>Il reste encore des redirections à corriger dans ce fichier. Cliquez à nouveau sur 'Corriger' pour les traiter.</p>";
            } else {
                echo "<p class='success'>Toutes les redirections ont été corrigées dans ce fichier.</p>";
            }
        }
        
        // Afficher un bouton pour corriger tous les fichiers
        if (!empty($files_with_redirects)) {
            echo "<h2>Correction automatique</h2>";
            echo "<p>Cliquez sur le bouton ci-dessous pour corriger automatiquement toutes les redirections dans tous les fichiers.</p>";
            echo "<a href='?fix_all=1' class='btn'>Corriger toutes les redirections</a>";
            
            // Traiter la demande de correction de tous les fichiers
            if (isset($_GET['fix_all'])) {
                echo "<h3>Résultats de la correction automatique</h3>";
                
                foreach ($files_with_redirects as $name => $path) {
                    echo "<p><strong>" . $name . ":</strong> ";
                    
                    $result = fixHeaderRedirects($path);
                    
                    if ($result['success']) {
                        echo "<span class='success'>" . $result['message'] . "</span>";
                    } else {
                        echo "<span class='error'>" . $result['message'] . "</span>";
                    }
                    
                    echo "</p>";
                }
            }
        } else {
            echo "<h2>Aucune correction nécessaire</h2>";
            echo "<p class='success'>Tous les fichiers vérifiés utilisent déjà des redirections JavaScript. Aucune correction n'est nécessaire.</p>";
        }
        ?>
        
        <h2>Exemple de redirection JavaScript</h2>
        <p>Voici un exemple de redirection JavaScript qui remplace un appel à <span class="code">header('Location: page.php')</span> :</p>
        
        <pre>echo "&lt;div style='text-align: center; margin: 20px; font-family: Arial, sans-serif;'&gt;";
echo "&lt;h2&gt;Redirection en cours...&lt;/h2&gt;";
echo "&lt;p&gt;Vous allez être redirigé vers une autre page. Si la redirection ne fonctionne pas, &lt;a href='page.php'&gt;cliquez ici&lt;/a&gt;.&lt;/p&gt;";
echo "&lt;div style='margin: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;'&gt;";
echo "&lt;img src='assets/img/loading.gif' alt='Chargement...' style='width: 50px; height: 50px;'&gt;";
echo "&lt;/div&gt;";
echo "&lt;/div&gt;";
echo "&lt;script&gt;window.location.href = 'page.php';&lt;/script&gt;";
die();</pre>
        
        <h2>Liens utiles</h2>
        <ul>
            <li><a href="index.php">Retour à l'accueil</a></li>
            <li><a href="fix_all_issues.php" class="btn">Corriger tous les problèmes</a></li>
            <li><a href="check_app_integrity.php" class="btn">Vérifier l'intégrité de l'application</a></li>
        </ul>
    </div>
</body>
</html>
