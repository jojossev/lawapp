<?php
session_start();
require_once '../db_connect.php';
require_once 'admin_auth_check.php';
require_once 'admin_includes/admin_functions.php'; // Charger les fonctions utilitaires

// === Définir les chemins d'upload et autres configurations ===
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$podcast_id = isset($_POST['podcast_id']) ? (int)$_POST['podcast_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);

// Définir les chemins d'upload
$base_upload_dir = '../uploads/podcasts/';
$audio_upload_dir = $base_upload_dir . 'audio/';
$image_upload_dir = $base_upload_dir . 'images/';

// Créer les dossiers s'ils n'existent pas (basique, améliorer avec gestion erreurs/permissions)
if (!is_dir($audio_upload_dir)) { mkdir($audio_upload_dir, 0775, true); }
if (!is_dir($image_upload_dir)) { mkdir($image_upload_dir, 0775, true); }

// === GESTION DE LA SUPPRESSION ===
if ($action === 'delete' && $podcast_id) {
    try {
        // 1. Récupérer les chemins des fichiers avant de supprimer l'enregistrement
        $stmt_get = $pdo->prepare("SELECT url_audio, image_url FROM podcasts WHERE id = ?");
        $stmt_get->execute([$podcast_id]);
        $file_paths = $stmt_get->fetch(PDO::FETCH_ASSOC);

        // 2. Supprimer l'enregistrement de la base de données
        $stmt_delete = $pdo->prepare("DELETE FROM podcasts WHERE id = ?");
        $stmt_delete->execute([$podcast_id]);

        // 3. Supprimer les fichiers associés du serveur
        if ($file_paths) {
            if (!empty($file_paths['url_audio']) && file_exists($base_upload_dir . $file_paths['url_audio'])) {
                unlink($base_upload_dir . $file_paths['url_audio']);
            }
            if (!empty($file_paths['image_url']) && file_exists($base_upload_dir . $file_paths['image_url'])) {
                unlink($base_upload_dir . $file_paths['image_url']);
            }
        }

        redirectWithSuccessPodcast("Podcast supprimé avec succès.");

    } catch (PDOException $e) {
        error_log("Erreur suppression podcast ID {$podcast_id}: " . $e->getMessage());
        redirectWithErrorPodcast("Erreur lors de la suppression du podcast.");
    }
}

// === GESTION AJOUT / MODIFICATION (POST Request) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {

    // Récupérer les données du formulaire
    $titre_episode = trim($_POST['titre_episode'] ?? '');
    $nom_podcast_serie = trim($_POST['nom_podcast_serie'] ?? '') ?: null; // null si vide
    $auteur = trim($_POST['auteur'] ?? '') ?: null;
    $description = trim($_POST['description'] ?? '') ?: null;
    $id_categorie = !empty($_POST['id_categorie']) ? (int)$_POST['id_categorie'] : null;
    $duree_secondes = !empty($_POST['duree_secondes']) ? (int)$_POST['duree_secondes'] : null;
    $date_publication_str = trim($_POST['date_publication_episode'] ?? date('Y-m-d H:i:s'));
    $est_publie = isset($_POST['est_publie']) ? 1 : 0;

    $errors = [];
    $form_data = $_POST; // Pour repeupler le formulaire en cas d'erreur

    // --- Validation --- 
    if (empty($titre_episode)) {
        $errors[] = "Le titre de l'épisode est requis.";
    }
    // Valider l'ID de catégorie si fourni
    if ($id_categorie !== null) {
        try {
            $stmtCheckCat = $pdo->prepare("SELECT id FROM categories_podcasts WHERE id = ?");
            $stmtCheckCat->execute([$id_categorie]);
            if (!$stmtCheckCat->fetch()) {
                $errors[] = "La catégorie sélectionnée n'est pas valide.";
                $id_categorie = null; // Renvoyer null au form
            }
        } catch (PDOException $e) {
             $errors[] = "Erreur lors de la validation de la catégorie.";
        }
    }
    // Valider le format de la date
    $date_publication_obj = DateTime::createFromFormat('Y-m-d\TH:i', $date_publication_str);
    if (!$date_publication_obj) {
         $date_publication_obj = DateTime::createFromFormat('Y-m-d H:i:s', $date_publication_str); // Essayer autre format
    }
    if (!$date_publication_obj) {
        $errors[] = "Format de date de publication invalide.";
        $date_publication_sql = date('Y-m-d H:i:s'); // Default
    } else {
        $date_publication_sql = $date_publication_obj->format('Y-m-d H:i:s');
    }

    // --- Gestion des Uploads ---
    $audio_file_path = null;
    $image_file_path = null;
    $old_audio_path = null;
    $old_image_path = null;

    // Récupérer les anciens chemins si édition
    if ($action === 'edit' && $podcast_id) {
        try {
            $stmt_old = $pdo->prepare("SELECT url_audio, image_url FROM podcasts WHERE id = ?");
            $stmt_old->execute([$podcast_id]);
            $old_paths = $stmt_old->fetch(PDO::FETCH_ASSOC);
            if ($old_paths) {
                $old_audio_path = $old_paths['url_audio'];
                $old_image_path = $old_paths['image_url'];
            }
        } catch (PDOException $e) { /* Ignorer l'erreur ici, on essaiera quand même */ }
    }
    
    // Traitement Fichier Audio
    if (isset($_FILES['fichier_audio']) && $_FILES['fichier_audio']['error'] === UPLOAD_ERR_OK) {
        $audio_tmp_name = $_FILES['fichier_audio']['tmp_name'];
        $audio_original_name = basename($_FILES['fichier_audio']['name']);
        $audio_extension = strtolower(pathinfo($audio_original_name, PATHINFO_EXTENSION));
        $allowed_audio_ext = ['mp3', 'wav', 'ogg', 'm4a']; // Ajuster si besoin

        if (!in_array($audio_extension, $allowed_audio_ext)) {
            $errors[] = "Format de fichier audio non autorisé.";
        } else {
            $audio_unique_name = uniqid('audio_', true) . '.' . $audio_extension;
            $audio_destination = $audio_upload_dir . $audio_unique_name;
            if (move_uploaded_file($audio_tmp_name, $audio_destination)) {
                $audio_file_path = 'audio/' . $audio_unique_name; // Chemin relatif pour DB
            } else {
                $errors[] = "Erreur lors du déplacement du fichier audio.";
            }
        }
    } elseif ($action === 'add' && (!isset($_FILES['fichier_audio']) || $_FILES['fichier_audio']['error'] !== UPLOAD_ERR_OK)) {
        // Fichier audio requis pour l'ajout
         $errors[] = "Le fichier audio est requis pour ajouter un podcast.";
    }

    // Traitement Image Cover
    if (isset($_FILES['image_cover']) && $_FILES['image_cover']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image_cover']['tmp_name'];
        $image_original_name = basename($_FILES['image_cover']['name']);
        $image_extension = strtolower(pathinfo($image_original_name, PATHINFO_EXTENSION));
        $allowed_image_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($image_extension, $allowed_image_ext)) {
            $errors[] = "Format d'image non autorisé.";
        } else {
            $image_unique_name = uniqid('cover_', true) . '.' . $image_extension;
            $image_destination = $image_upload_dir . $image_unique_name;
            if (move_uploaded_file($image_tmp_name, $image_destination)) {
                $image_file_path = 'images/' . $image_unique_name; // Chemin relatif pour DB
            } else {
                $errors[] = "Erreur lors du déplacement de l'image de couverture.";
            }
        }
    }

    // S'il y a des erreurs de validation ou d'upload, rediriger vers le formulaire
    if (!empty($errors)) {
        // Si un fichier a été uploadé mais qu'une autre erreur survient, le supprimer
        if ($audio_file_path && file_exists($audio_upload_dir . basename($audio_file_path))) { unlink($audio_upload_dir . basename($audio_file_path)); }
        if ($image_file_path && file_exists($image_upload_dir . basename($image_file_path))) { unlink($image_upload_dir . basename($image_file_path)); }
        
        redirectWithErrorPodcast("Erreurs dans le formulaire.", $form_data, $errors);
    }

    // --- Opération Base de Données --- 
    try {
        if ($action === 'add') {
            // Insertion
            $sql = "INSERT INTO podcasts (titre_episode, nom_podcast_serie, description, url_audio, duree_secondes, id_categorie, auteur, image_url, est_publie, date_publication_episode) 
                    VALUES (:titre, :serie, :desc, :audio, :duree, :cat_id, :auteur, :image, :publie, :date_pub)";
            $stmt = $pdo->prepare($sql);

        } elseif ($action === 'edit' && $podcast_id) {
            // Mise à jour
            // Construire la requête dynamiquement pour ne mettre à jour que les fichiers si fournis
            $sql_parts = [];
            $params = [
                'titre' => $titre_episode,
                'serie' => $nom_podcast_serie,
                'desc' => $description,
                'duree' => $duree_secondes,
                'cat_id' => $id_categorie,
                'auteur' => $auteur,
                'publie' => $est_publie,
                'date_pub' => $date_publication_sql,
                'id' => $podcast_id
            ];

            $sql = "UPDATE podcasts SET titre_episode = :titre, nom_podcast_serie = :serie, description = :desc, 
                    duree_secondes = :duree, id_categorie = :cat_id, auteur = :auteur, est_publie = :publie, 
                    date_publication_episode = :date_pub";
            
            if ($audio_file_path) {
                $sql .= ", url_audio = :audio";
                $params['audio'] = $audio_file_path;
            }
            if ($image_file_path) {
                $sql .= ", image_url = :image";
                $params['image'] = $image_file_path;
            }

            $sql .= " WHERE id = :id";
            $stmt = $pdo->prepare($sql);

        } else {
            throw new Exception("Action invalide ou ID manquant.");
        }

        // Binder les paramètres communs ou spécifiques
        if ($action === 'add') {
             $stmt->execute([
                ':titre' => $titre_episode,
                ':serie' => $nom_podcast_serie,
                ':desc' => $description,
                ':audio' => $audio_file_path, // Doit être défini pour ADD
                ':duree' => $duree_secondes,
                ':cat_id' => $id_categorie,
                ':auteur' => $auteur,
                ':image' => $image_file_path,
                ':publie' => $est_publie,
                ':date_pub' => $date_publication_sql
            ]);
        } elseif ($action === 'edit') {
             $stmt->execute($params); // Utiliser le tableau de paramètres construit

             // Si la mise à jour réussit ET de nouveaux fichiers ont été uploadés, supprimer les anciens
             if ($audio_file_path && $old_audio_path && $old_audio_path !== $audio_file_path && file_exists($base_upload_dir . $old_audio_path)) {
                 unlink($base_upload_dir . $old_audio_path);
             }
             if ($image_file_path && $old_image_path && $old_image_path !== $image_file_path && file_exists($base_upload_dir . $old_image_path)) {
                 unlink($base_upload_dir . $old_image_path);
             }
        }

        $success_message = ($action === 'add') ? "Podcast ajouté avec succès." : "Podcast modifié avec succès.";
        redirectWithSuccessPodcast($success_message);

    } catch (PDOException $e) {
        error_log("Erreur DB action {$action} podcast: " . $e->getMessage());
        // Supprimer les fichiers uploadés si l'opération DB échoue
         if ($audio_file_path && file_exists($audio_upload_dir . basename($audio_file_path))) { unlink($audio_upload_dir . basename($audio_file_path)); }
         if ($image_file_path && file_exists($image_upload_dir . basename($image_file_path))) { unlink($image_upload_dir . basename($image_file_path)); }

        redirectWithErrorPodcast("Erreur lors de l'enregistrement du podcast dans la base de données.", $form_data);
    } catch (Exception $e) {
         error_log("Erreur Générale action {$action} podcast: " . $e->getMessage());
         redirectWithErrorPodcast("Une erreur inattendue est survenue.", $form_data);
    }

} else {
    // Si accès direct ou méthode non POST pour add/edit
    redirectWithErrorPodcast("Action non autorisée ou méthode invalide.");
}

?>
