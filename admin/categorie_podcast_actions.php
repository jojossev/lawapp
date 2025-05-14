<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once 'admin_auth_check.php';
require_once 'admin_includes/admin_functions.php'; // Pour les redirections

// Les fonctions redirectWithErrorCatPodcast et redirectWithSuccessCatPodcast sont chargées via admin_functions.php

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$categorie_id = isset($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : null);

// == Gestion de l'Ajout / Modification ==
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    
    $nom_categorie = trim($_POST['nom_categorie'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $errors = [];
    $form_data = $_POST;

    // Validation
    if (empty($nom_categorie)) {
        $errors[] = "Le nom de la catégorie est obligatoire.";
    }
    // Ajouter d'autres validations si nécessaire (ex: longueur max, unicité)

    if (!empty($errors)) {
        redirectWithErrorCatPodcast("Erreurs de validation.", $form_data, $errors);
    }

    try {
        if ($action === 'add') {
            // Ajouter la catégorie
            $sql = "INSERT INTO categories_podcasts (nom_categorie, description, date_creation) VALUES (:nom, :desc, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nom', $nom_categorie, PDO::PARAM_STR);
            $stmt->bindParam(':desc', $description, PDO::PARAM_STR);
            $stmt->execute();
            redirectWithSuccessCatPodcast("Catégorie de podcast ajoutée avec succès.");

        } elseif ($action === 'edit' && $categorie_id) {
            // Mettre à jour la catégorie
            $sql = "UPDATE categories_podcasts SET nom_categorie = :nom, description = :desc WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nom', $nom_categorie, PDO::PARAM_STR);
            $stmt->bindParam(':desc', $description, PDO::PARAM_STR);
            $stmt->bindParam(':id', $categorie_id, PDO::PARAM_INT);
            $stmt->execute();
            redirectWithSuccessCatPodcast("Catégorie de podcast mise à jour avec succès.");
        }

    } catch (PDOException $e) {
        error_log("Erreur DB catégorie podcast: " . $e->getMessage());
        redirectWithErrorCatPodcast("Erreur lors de la sauvegarde de la catégorie: " . $e->getMessage(), $form_data, ["Erreur base de données."]);
    }

// == Gestion de la Suppression ==
} elseif ($action === 'delete' && $categorie_id) {
    // Validation CSRF pourrait être ajoutée ici

    try {
        // Avant de supprimer, vérifier si des podcasts utilisent cette catégorie ?
        // Pour l'instant, suppression directe.
        $sql = "DELETE FROM categories_podcasts WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $categorie_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            redirectWithSuccessCatPodcast("Catégorie de podcast supprimée avec succès.");
        } else {
            redirectWithErrorCatPodcast("Catégorie non trouvée ou déjà supprimée.");
        }

    } catch (PDOException $e) {
        error_log("Erreur suppression catégorie podcast: " . $e->getMessage());
        // Gérer les erreurs de contrainte de clé étrangère si un podcast l'utilise
        if ($e->getCode() == '23000') { // Code SQLSTATE pour violation de contrainte d'intégrité
             redirectWithErrorCatPodcast("Impossible de supprimer cette catégorie car elle est utilisée par un ou plusieurs podcasts.");
        } else {
            redirectWithErrorCatPodcast("Erreur lors de la suppression de la catégorie: " . $e->getMessage());
        }
    }

} else {
    // Si l'action n'est pas reconnue ou méthode incorrecte
    redirectWithErrorCatPodcast("Action non valide ou requête incorrecte.");
}

?>
