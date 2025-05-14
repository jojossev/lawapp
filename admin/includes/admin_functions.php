<?php
/**
 * Fonctions utilitaires pour l'administration
 */

/**
 * Affiche les messages de succès et d'erreur
 * @return void
 */
function displayMessages() {
    if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        </div>
    <?php
    unset($_SESSION['success_message']);
    endif;

    if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        </div>
    <?php
    unset($_SESSION['error_message']);
    endif;
}

/**
 * Redirige vers une URL donnée
 * @param string $url L'URL de destination
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Vérifie si l'utilisateur est un administrateur
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']);
}

/**
 * Génère un slug à partir d'un titre
 * @param string $title Le titre à convertir en slug
 * @return string
 */
function generate_slug($title) {
    // Convertir en minuscules
    $slug = strtolower($title);
    
    // Remplacer les caractères accentués
    $slug = str_replace(
        array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ'),
        array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y'),
        $slug
    );
    
    // Remplacer tout ce qui n'est pas alphanumérique par un tiret
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    
    // Remplacer les multiples tirets par un seul
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Supprimer les tirets au début et à la fin
    return trim($slug, '-');
}

/**
 * Formate une date MySQL en format français
 * @param string $date La date au format MySQL
 * @param bool $with_time Inclure l'heure ou non
 * @return string
 */
function format_date($date, $with_time = false) {
    if ($with_time) {
        return date('d/m/Y H:i', strtotime($date));
    }
    return date('d/m/Y', strtotime($date));
}

/**
 * Tronque un texte à une longueur donnée
 * @param string $text Le texte à tronquer
 * @param int $length La longueur maximale
 * @param string $suffix Le suffixe à ajouter si le texte est tronqué
 * @return string
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Vérifie si un fichier est une image
 * @param string $file Le chemin du fichier
 * @return bool
 */
function is_image($file) {
    $allowed = array('jpg', 'jpeg', 'png', 'gif');
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($extension, $allowed);
}

/**
 * Génère un nom de fichier unique
 * @param string $original_name Le nom original du fichier
 * @return string
 */
function generate_unique_filename($original_name) {
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    return uniqid() . '.' . $extension;
}

/**
 * Formate le prix en euros
 * @param float $price Le prix à formater
 * @return string
 */
function format_price($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

/**
 * Retourne une liste des statuts possibles
 * @return array
 */
function get_status_list() {
    return array(
        'brouillon' => 'Brouillon',
        'publie' => 'Publié',
        'archive' => 'Archivé'
    );
}

/**
 * Retourne une liste des niveaux possibles
 * @return array
 */
function get_niveau_list() {
    return array(
        'debutant' => 'Débutant',
        'intermediaire' => 'Intermédiaire',
        'avance' => 'Avancé'
    );
}
