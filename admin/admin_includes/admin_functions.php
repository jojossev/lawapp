<?php
// Fonctions utilitaires pour l'administration

// Fonction de base pour la redirection avec message d'erreur
// Peut être adaptée pour différents modules
function redirectWithError($module_key, $message, $form_data = [], $errors = []) {
    $_SESSION['error_message_' . $module_key] = $message;
    $_SESSION['form_errors_' . $module_key] = $errors;
    $_SESSION['form_data_' . $module_key] = $form_data;
    
    // Logique de redirection (exemple simple, à adapter)
    $redirect_url = 'manage_' . $module_key . '.php'; // Exemple: manage_podcasts.php
    if (!empty($form_data['action'])) {
        $form_page = $module_key . '_form.php'; // Exemple: podcast_form.php
        // Vérifier si le fichier formulaire existe avant de construire l'URL
        // Ceci est une simplification, une meilleure approche serait nécessaire
        // if (file_exists($form_page)) { 
            $redirect_url = $form_page . '?action=' . $form_data['action'];
            $id_key = $module_key . '_id'; // Exemple: podcast_id
            if (!empty($form_data[$id_key])) {
                $redirect_url .= '&id=' . $form_data[$id_key];
            }
        // }
    }
    header("Location: " . $redirect_url);
    exit;
}

// Fonction de base pour la redirection avec message de succès
function redirectWithSuccess($module_key, $message) {
    $_SESSION['success_message_' . $module_key] = $message;
    header("Location: manage_" . $module_key . ".php"); // Exemple: manage_podcasts.php
    exit;
}

// --- Fonctions spécifiques aux Podcasts ---
function redirectWithErrorPodcast($message, $form_data = [], $errors = []) {
    redirectWithError('podcast', $message, $form_data, $errors);
}

function redirectWithSuccessPodcast($message) {
    redirectWithSuccess('podcast', $message);
}

// --- Fonctions spécifiques aux Catégories de Podcasts ---
function redirectWithErrorCatPodcast($message, $form_data = [], $errors = []) {
    redirectWithError('cat_podcast', $message, $form_data, $errors);
}

function redirectWithSuccessCatPodcast($message) {
    redirectWithSuccess('cat_podcast', $message);
}

// --- Fonctions spécifiques aux Catégories de Cours (Exemple, à créer si besoin) ---
/*
function redirectWithErrorCatCours($message, $form_data = [], $errors = []) {
    redirectWithError('cat_cours', $message, $form_data, $errors);
}

function redirectWithSuccessCatCours($message) {
    redirectWithSuccess('cat_cours', $message);
}
*/

?>
