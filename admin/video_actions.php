<?php
require_once __DIR__ . '/admin_auth_check.php'; // Inclut la connexion PDO et la session

$action = $_POST['action'] ?? $_GET['action'] ?? null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération et assainissement des données du formulaire
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $url_video = trim($_POST['url_video'] ?? '');
        $duree = trim($_POST['duree'] ?? '');
        $niveau = trim($_POST['niveau'] ?? '');
        $id_categorie_video = !empty($_POST['id_categorie_video']) ? (int)$_POST['id_categorie_video'] : null;
        $image_thumbnail_url = trim($_POST['image_thumbnail_url'] ?? '');
        $id_createur = isset($_POST['id_createur']) ? (int)$_POST['id_createur'] : null;
        $statut = trim($_POST['statut'] ?? 'brouillon');
        $date_publication_str = trim($_POST['date_publication'] ?? '');
        
        $date_publication = null;
        if (!empty($date_publication_str)) {
            try {
                $date_publication_obj = new DateTime($date_publication_str);
                $date_publication = $date_publication_obj->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                // Gérer l'erreur de format de date, ou laisser $date_publication à null
                 $errors[] = "Le format de la date de publication n'est pas valide.";
            }
        }

        // Validation de base
        $errors = [];
        if (empty($titre)) {
            $errors[] = "Le titre de la vidéo est obligatoire.";
        }
        if (empty($url_video)) {
            $errors[] = "L'URL de la vidéo est obligatoire.";
        } elseif (!filter_var($url_video, FILTER_VALIDATE_URL)) {
            $errors[] = "L'URL de la vidéo n'est pas valide.";
        }
        if (empty($id_createur)) {
            $errors[] = "Le créateur de la vidéo est obligatoire.";
        }
        $valid_statuts = ['brouillon', 'publie', 'archive'];
        if (!in_array($statut, $valid_statuts)) {
            $errors[] = "Le statut de la vidéo n'est pas valide.";
        }
        if (!empty($image_thumbnail_url) && !filter_var($image_thumbnail_url, FILTER_VALIDATE_URL)) {
            $errors[] = "L'URL de l'image miniature n'est pas valide.";
        }
        if ($id_categorie_video !== null && !filter_var($id_categorie_video, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
             $errors[] = "L'ID de catégorie vidéo n'est pas valide.";
        }
         if ($id_createur !== null && !filter_var($id_createur, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
             $errors[] = "L'ID du créateur n'est pas valide.";
        }


        if (!empty($errors)) {
            $_SESSION['form_error_message'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST; // Sauvegarder les données du formulaire
            header("Location: video_form.php?action=" . ($id ? "edit&id=$id" : "add"));
            exit;
        }

        $params = [
            ':titre' => $titre,
            ':description' => $description ?: null, // Permet NULL si vide
            ':url_video' => $url_video,
            ':duree' => $duree ?: null,
            ':niveau' => $niveau ?: null,
            ':id_categorie_video' => $id_categorie_video,
            ':image_thumbnail_url' => $image_thumbnail_url ?: null,
            ':id_createur' => $id_createur,
            ':statut' => $statut,
            ':date_publication' => $date_publication
        ];

        if ($action === 'add') {
            $sql = "INSERT INTO videos (titre, description, url_video, duree, niveau, id_categorie_video, image_thumbnail_url, id_createur, statut, date_publication, date_creation_interne, date_mise_a_jour_interne) 
                    VALUES (:titre, :description, :url_video, :duree, :niveau, :id_categorie_video, :image_thumbnail_url, :id_createur, :statut, :date_publication, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['success_message'] = "Vidéo ajoutée avec succès.";

        } elseif ($action === 'edit' && $id) {
            $params[':id'] = $id;
            $sql = "UPDATE videos SET 
                        titre = :titre, 
                        description = :description, 
                        url_video = :url_video, 
                        duree = :duree, 
                        niveau = :niveau, 
                        id_categorie_video = :id_categorie_video, 
                        image_thumbnail_url = :image_thumbnail_url, 
                        id_createur = :id_createur, 
                        statut = :statut, 
                        date_publication = :date_publication,
                        date_mise_a_jour_interne = NOW()
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['success_message'] = "Vidéo mise à jour avec succès.";
        } else {
            throw new Exception("Action POST non valide ou ID manquant.");
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            throw new Exception("ID de vidéo manquant pour la suppression.");
        }
        // TODO: Ajouter une vérification de token CSRF ici pour plus de sécurité
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_message'] = "Vidéo supprimée avec succès.";

    } else {
        throw new Exception("Méthode de requête ou action non supportée.");
    }

    header("Location: manage_videos.php");
    exit;

} catch (PDOException $e) {
    $error_msg = "Erreur de base de données : " . $e->getMessage();
    // Si l'erreur contient des infos sensibles (ex: nom de table, colonne), loggez-la mais n'affichez qu'un message générique à l'utilisateur.
    error_log("PDOException in video_actions.php: " . $e->getMessage()); // Log pour admin
    
    $_SESSION['error_message'] = "Une erreur de base de données est survenue. Veuillez réessayer. Si le problème persiste, contactez un administrateur."; // Message pour l'utilisateur

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['form_data'] = $_POST; // Conserver les données du formulaire
        $current_action = $_POST['action'] ?? 'add';
        $current_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        // Rediriger vers le formulaire pour correction
        header("Location: video_form.php?action=" . ($current_id ? "edit&id=$current_id" : $current_action));
    } else {
        header("Location: manage_videos.php"); // Redirection générale pour les erreurs GET
    }
    exit;
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    error_log("Exception in video_actions.php: " . $e->getMessage()); // Log pour admin
    header("Location: manage_videos.php");
    exit;
}
?>
