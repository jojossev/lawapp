<?php
// Script pour vérifier et corriger tous les problèmes de l'application en une seule fois
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

// Fonction pour exécuter un script PHP et capturer sa sortie
function executeScript($script) {
    $output = '';
    
    // Vérifier si le script existe
    if (file_exists($script)) {
        // Désactiver la sortie directe
        ob_start();
        
        // Inclure le script
        include $script;
        
        // Capturer la sortie
        $output = ob_get_clean();
    } else {
        $output = "Le script $script n'existe pas.";
    }
    
    return $output;
}

// Début du HTML
?>
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Correction de tous les problèmes</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .script-output { 
            background: #f4f4f4; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 20px;
            max-height: 300px;
            overflow: auto;
            border: 1px solid #ddd;
        }
        .progress-container {
            width: 100%;
            background-color: #f1f1f1;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .progress-bar {
            height: 30px;
            background-color: #4CAF50;
            border-radius: 5px;
            text-align: center;
            line-height: 30px;
            color: white;
        }
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
    </style>
</head>
<body>
    <div class='container'>
        <h1>Correction de tous les problèmes</h1>
        
        <div class="progress-container">
            <div class="progress-bar" id="progress-bar" style="width: 0%">0%</div>
        </div>
        
        <div id="status">Initialisation...</div>
        
        <div id="output"></div>
        
        <div>
            <a href="index.php" class="btn">Retour à l'accueil</a>
        </div>
        
        <script>
            // Liste des scripts à exécuter
            const scripts = [
                { name: "Création des tables de catégories", url: "create_categories_tables.php" },
                { name: "Correction de la table administrateurs", url: "fix_admin_table.php" },
                { name: "Correction de la table podcasts", url: "fix_podcasts_table.php" },
                { name: "Correction de la table livres", url: "fix_livres_table.php" },
                { name: "Correction de la table cours", url: "fix_cours_table.php" },
                { name: "Correction de la table vidéos", url: "fix_videos_table.php" },
                { name: "Correction des tables utilisateurs", url: "fix_users_tables.php" },
                { name: "Correction de la table inscriptions", url: "fix_inscriptions_table.php" },
                { name: "Correction des clés étrangères", url: "fix_foreign_keys.php" },
                { name: "Compatibilité MySQL/PostgreSQL", url: "fix_db_compatibility.php" },
                { name: "Optimisation des performances", url: "fix_db_performance.php" },
                { name: "Sécurité de la base de données", url: "fix_db_security.php" },
                { name: "Vérification des sessions et cookies", url: "fix_session_cookies.php" },
                { name: "Vérification des fichiers et permissions", url: "fix_files_permissions.php" },
                { name: "Correction des redirections", url: "fix_redirections.php" }
            ];
            
            const progressBar = document.getElementById('progress-bar');
            const status = document.getElementById('status');
            const output = document.getElementById('output');
            
            let currentScript = 0;
            const totalScripts = scripts.length;
            
            // Fonction pour exécuter un script via AJAX
            function executeScript(scriptUrl) {
                status.innerHTML = `Exécution de : ${scripts[currentScript].name}...`;
                
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', scriptUrl, true);
                    
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            // Créer un div pour la sortie du script
                            const scriptOutput = document.createElement('div');
                            scriptOutput.className = 'script-output';
                            
                            // Ajouter un titre pour le script
                            const scriptTitle = document.createElement('h3');
                            scriptTitle.textContent = scripts[currentScript].name;
                            
                            // Ajouter le contenu
                            const scriptContent = document.createElement('div');
                            
                            // Extraire uniquement le contenu du body
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(xhr.responseText, 'text/html');
                            const body = doc.body;
                            
                            if (body) {
                                scriptContent.innerHTML = body.innerHTML;
                            } else {
                                scriptContent.textContent = xhr.responseText;
                            }
                            
                            // Assembler le tout
                            scriptOutput.appendChild(scriptTitle);
                            scriptOutput.appendChild(scriptContent);
                            output.appendChild(scriptOutput);
                            
                            resolve();
                        } else {
                            reject(new Error(`Erreur lors de l'exécution de ${scriptUrl}: ${xhr.statusText}`));
                        }
                    };
                    
                    xhr.onerror = function() {
                        reject(new Error(`Erreur réseau lors de l'exécution de ${scriptUrl}`));
                    };
                    
                    xhr.send();
                });
            }
            
            // Fonction pour mettre à jour la barre de progression
            function updateProgress() {
                const progress = Math.round((currentScript / totalScripts) * 100);
                progressBar.style.width = `${progress}%`;
                progressBar.textContent = `${progress}%`;
            }
            
            // Fonction pour exécuter tous les scripts séquentiellement
            async function executeAllScripts() {
                try {
                    for (currentScript = 0; currentScript < totalScripts; currentScript++) {
                        await executeScript(scripts[currentScript].url);
                        updateProgress();
                    }
                    
                    // Tous les scripts ont été exécutés
                    status.innerHTML = '<span class="success">Tous les scripts ont été exécutés avec succès !</span>';
                    progressBar.style.width = '100%';
                    progressBar.textContent = '100%';
                } catch (error) {
                    status.innerHTML = `<span class="error">Erreur : ${error.message}</span>`;
                }
            }
            
            // Démarrer l'exécution des scripts
            executeAllScripts();
        </script>
    </div>
</body>
</html>
