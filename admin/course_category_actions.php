<?php
// admin/course_category_actions.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php'; // Assure que seul un admin connecté peut exécuter ce script

// Vérifier si une action est spécifiée
$action = $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_categorie = trim($_POST['nom_categorie'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Pour l'édition

    // Validation de base
    if (empty($nom_categorie)) {
        $_SESSION['error_message'] = "Le nom de la catégorie ne peut pas être vide.";
        // Sauvegarder les données du formulaire pour les réafficher
        $_SESSION['form_data'] = ['nom_categorie' => $nom_categorie, 'description' => $description];
        if ($action === 'edit' && $category_id) {
            header("Location: course_category_form.php?id=" . $category_id);
        } else {
            header("Location: course_category_form.php");
        }
        exit;
    }

    if ($action === 'add') {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories_cours (nom_categorie, description) VALUES (:nom_categorie, :description)");
            $stmt->bindParam(':nom_categorie', $nom_categorie, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->execute();
            $_SESSION['success_message'] = "Catégorie de cours ajoutée avec succès.";
            header("Location: manage_course_categories.php");
            exit;
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout de la catégorie de cours : " . $e->getMessage());
            $_SESSION['error_message'] = "Erreur lors de l'ajout de la catégorie. Veuillez réessayer. Détail: " . $e->getCode();
             // Sauvegarder les données du formulaire
            $_SESSION['form_data'] = ['nom_categorie' => $nom_categorie, 'description' => $description];
            header("Location: course_category_form.php");
            exit;
        }
    } elseif ($action === 'edit' && $category_id) {
        try {
            $stmt = $pdo->prepare("UPDATE categories_cours SET nom_categorie = :nom_categorie, description = :description WHERE id = :id");
            $stmt->bindParam(':nom_categorie', $nom_categorie, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['success_message'] = "Catégorie de cours mise à jour avec succès.";
            header("Location: manage_course_categories.php");
            exit;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de la catégorie de cours : " . $e->getMessage());
            $_SESSION['error_message'] = "Erreur lors de la mise à jour de la catégorie. Veuillez réessayer. Détail: " . $e->getCode();
            // Sauvegarder les données du formulaire
            $_SESSION['form_data'] = ['nom_categorie' => $nom_categorie, 'description' => $description];
            header("Location: course_category_form.php?id=" . $category_id);
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Action non valide ou ID manquant pour la modification.";
        header("Location: manage_course_categories.php");
        exit;
    }

} elseif ($action === 'delete') {
    $category_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$category_id) {
        $_SESSION['error_message'] = "ID de catégorie manquant ou invalide pour la suppression.";
        header("Location: manage_course_categories.php");
        exit;
    }

    // Optionnel : Vérifier si la catégorie est utilisée par des cours avant de supprimer
    // $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE id_categorie = :category_id");
    // $stmt_check->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    // $stmt_check->execute();
    // if ($stmt_check->fetchColumn() > 0) {
    //     $_SESSION['error_message'] = "Impossible de supprimer cette catégorie car elle est utilisée par un ou plusieurs cours.";
    //     header("Location: manage_course_categories.php");
    //     exit;
    // }

    try {
        $stmt = $pdo->prepare("DELETE FROM categories_cours WHERE id = :id");
        $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        $_SESSION['success_message'] = "Catégorie de cours supprimée avec succès.";
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression de la catégorie de cours : " . $e->getMessage());
        // Vérifier si l'erreur est due à une contrainte de clé étrangère
        if ($e->getCode() == '23000') { // Code SQLSTATE pour violation de contrainte d'intégrité
             $_SESSION['error_message'] = "Impossible de supprimer cette catégorie car elle est actuellement utilisée par un ou plusieurs cours. Veuillez d'abord assigner ces cours à une autre catégorie ou les supprimer.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la suppression de la catégorie. Veuillez réessayer.";
        }
    }
    header("Location: manage_course_categories.php");
    exit;

} else {
    $_SESSION['error_message'] = "Aucune action spécifiée ou méthode non autorisée.";
    header("Location: manage_course_categories.php");
    exit;
}
?>
