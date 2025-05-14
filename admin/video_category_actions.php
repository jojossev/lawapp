<?php
require_once __DIR__ . '/admin_auth_check.php'; // Inclut la connexion PDO et la session

$action = $_POST['action'] ?? $_GET['action'] ?? null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $nom_categorie = trim($_POST['nom_categorie'] ?? '');
        $description_categorie = trim($_POST['description_categorie'] ?? '');

        // Validation
        $errors = [];
        if (empty($nom_categorie)) {
            $errors[] = "Le nom de la catégorie est obligatoire.";
        }
        // Vous pouvez ajouter d'autres validations ici (longueur max, etc.)

        if (!empty($errors)) {
            $_SESSION['form_error_message'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header("Location: video_category_form.php?action=" . ($id ? "edit&id=$id" : "add"));
            exit;
        }

        $params = [
            ':nom_categorie' => $nom_categorie,
            ':description_categorie' => $description_categorie ?: null
        ];

        if ($action === 'add') {
            $sql = "INSERT INTO categories_videos (nom_categorie, description_categorie, date_creation) 
                    VALUES (:nom_categorie, :description_categorie, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['success_message'] = "Catégorie de vidéo ajoutée avec succès.";

        } elseif ($action === 'edit' && $id) {
            $params[':id'] = $id;
            $sql = "UPDATE categories_videos SET 
                        nom_categorie = :nom_categorie, 
                        description_categorie = :description_categorie
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['success_message'] = "Catégorie de vidéo mise à jour avec succès.";
        } else {
            throw new Exception("Action POST non valide ou ID manquant.");
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            throw new Exception("ID de catégorie manquant pour la suppression.");
        }
        
        // Vérifier si la catégorie est utilisée par des vidéos
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE id_categorie_video = ?");
        $stmt_check->execute([$id]);
        $count_videos = $stmt_check->fetchColumn();

        if ($count_videos > 0) {
            $_SESSION['error_message'] = "Impossible de supprimer la catégorie : elle est actuellement utilisée par $count_videos vidéo(s). Veuillez d'abord modifier ou supprimer ces vidéos.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories_videos WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success_message'] = "Catégorie de vidéo supprimée avec succès.";
        }
    } else {
        throw new Exception("Méthode de requête ou action non supportée.");
    }

    header("Location: manage_video_categories.php");
    exit;

} catch (PDOException $e) {
    error_log("PDOException in video_category_actions.php: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur de base de données. Détails : " . $e->getMessage(); // Potentiellement trop verbeux pour l'utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['form_data'] = $_POST;
        $current_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        header("Location: video_category_form.php?action=" . ($current_id ? "edit&id=$current_id" : "add"));
    } else {
        header("Location: manage_video_categories.php");
    }
    exit;
} catch (Exception $e) {
    error_log("Exception in video_category_actions.php: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header("Location: manage_video_categories.php");
    exit;
}
?>
