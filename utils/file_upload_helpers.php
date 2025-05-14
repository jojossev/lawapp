<?php

/**
 * Gère l'upload d'un fichier.
 *
 * @param string $fileInputName Le nom du champ input de type file.
 * @param string $destinationDir Le dossier de destination (chemin serveur absolu).
 * @param array $allowedTypes Les types MIME autorisés (ex: ['image/jpeg', 'image/png']).
 * @param int $maxSize La taille maximale autorisée en octets.
 * @return array Un tableau avec 'success' (bool), et 'filename' ou 'error' (string).
 */
function handleFileUpload(string $fileInputName, string $destinationDir, array $allowedTypes, int $maxSize): array
{
    if (!isset($_FILES[$fileInputName])) {
        return ['success' => false, 'error' => 'Aucun fichier reçu.'];
    }

    $file = $_FILES[$fileInputName];

    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'Le fichier dépasse la taille maximale autorisée par le serveur.',
            UPLOAD_ERR_FORM_SIZE  => 'Le fichier dépasse la taille maximale spécifiée dans le formulaire.',
            UPLOAD_ERR_PARTIAL    => 'Le fichier n\'a été que partiellement téléchargé.',
            UPLOAD_ERR_NO_FILE    => 'Aucun fichier n\'a été téléchargé.',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant sur le serveur.',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque.',
            UPLOAD_ERR_EXTENSION  => 'Une extension PHP a arrêté l\'upload du fichier.',
        ];
        return ['success' => false, 'error' => $uploadErrors[$file['error']] ?? 'Erreur inconnue lors de l\'upload.'];
    }

    // Vérifier la taille du fichier
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Le fichier est trop volumineux (Max: ' . ($maxSize / 1024 / 1024) . 'MB).'];
    }

    // Vérifier le type MIME
    // Utiliser finfo pour une meilleure détection MIME si disponible
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileMimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } else {
        // Fallback (moins fiable)
        $fileMimeType = mime_content_type($file['tmp_name']); 
    }

    if (!in_array($fileMimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé (' . htmlspecialchars($fileMimeType) . '). Types permis: ' . implode(', ', $allowedTypes)];
    }

    // Générer un nom de fichier unique
    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeOriginalName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $originalName); // Nettoyer le nom original
    $safeOriginalName = substr($safeOriginalName, 0, 100); // Limiter la longueur du nom original nettoyé
    // Ajouter un timestamp et une chaîne aléatoire pour garantir l'unicité
    $uniqueFilename = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeOriginalName . '.' . $extension;
    $destinationPath = rtrim($destinationDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $uniqueFilename;

    // S'assurer que le dossier de destination existe et est accessible en écriture
    if (!is_dir($destinationDir)) {
        // Tenter de créer le dossier (au cas où il aurait été supprimé entre-temps)
        if (!mkdir($destinationDir, 0777, true)) {
             error_log("handleFileUpload: Impossible de créer le dossier de destination: " . $destinationDir);
             return ['success' => false, 'error' => 'Erreur serveur: Impossible de créer le dossier de destination.'];
        }
    }
    if (!is_writable($destinationDir)) {
        error_log("handleFileUpload: Le dossier de destination n'est pas accessible en écriture: " . $destinationDir);
        return ['success' => false, 'error' => 'Erreur serveur: Permissions d\'écriture manquantes pour le dossier de destination.'];
    }
    
    // Déplacer le fichier uploadé
    if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
        // Optionnel: Définir les permissions du fichier uploadé
        // chmod($destinationPath, 0644);
        return ['success' => true, 'filename' => $uniqueFilename];
    } else {
        error_log("handleFileUpload: move_uploaded_file a échoué pour " . $destinationPath . " depuis " . $file['tmp_name']);
        return ['success' => false, 'error' => 'Erreur serveur lors du déplacement du fichier téléchargé.'];
    }
}

?>
