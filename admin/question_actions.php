<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Méthode non autorisée.";
    redirect('manage_cours.php'); // Redirection générique
}

$action = $_POST['action'] ?? '';
$id_quiz = filter_input(INPUT_POST, 'id_quiz', FILTER_VALIDATE_INT);
$question_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT); // Pour edit et delete

// Validation de base
if (!$id_quiz) {
    $_SESSION['error_message'] = "ID de quiz manquant.";
    // Essayez de rediriger vers une page pertinente si possible, sinon une page générale
    redirect($_SERVER['HTTP_REFERER'] ?? 'manage_cours.php');
}

// --- Traitement des actions ---
$valid_question_types = ['qcm', 'vrai_faux', 'reponse_courte'];

if ($action === 'add' || $action === 'edit') {
    $texte_question = trim($_POST['texte_question'] ?? '');
    $type_question = $_POST['type_question'] ?? '';
    $points = filter_input(INPUT_POST, 'points', FILTER_VALIDATE_INT);
    $ordre = filter_input(INPUT_POST, 'ordre', FILTER_VALIDATE_INT);

    // Validation des champs
    $errors = [];
    if (empty($texte_question)) {
        $errors[] = "Le texte de la question ne peut pas être vide.";
    }
    if (!in_array($type_question, $valid_question_types)) {
        $errors[] = "Type de question invalide.";
    }
    if ($points === false || $points < 0) {
        $errors[] = "Le nombre de points doit être un entier positif ou nul.";
    }
    if ($ordre === false || $ordre < 0) {
        $errors[] = "L\'ordre d\'affichage doit être un entier positif ou nul.";
    }
    if ($action === 'edit' && !$question_id) {
        $errors[] = "ID de question manquant pour la modification.";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_input'] = $_POST; // Sauvegarder les anciennes entrées
        $redirect_url = "question_form.php?quiz_id=$id_quiz";
        if ($action === 'edit' && $question_id) {
            $redirect_url .= "&id=$question_id";
        }
        redirect($redirect_url);
    }

    // Si pas d\'erreurs, procéder à l\'insertion ou à la mise à jour
    try {
        if ($action === 'add') {
            $sql = "INSERT INTO questions_quiz (id_quiz, texte_question, type_question, points, ordre, date_creation, date_modification)
                    VALUES (:id_quiz, :texte_question, :type_question, :points, :ordre, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            // Pour date_creation et date_modification, si vos colonnes existent et ont des valeurs par défaut CURRENT_TIMESTAMP,
            // vous n\'avez pas besoin de les inclure explicitement ici, sauf si vous voulez forcer une valeur.
            // Je les inclus pour être explicite, adaptez si vos colonnes n\'existent pas.
        } elseif ($action === 'edit') {
            $sql = "UPDATE questions_quiz
                    SET texte_question = :texte_question, type_question = :type_question, points = :points, ordre = :ordre, date_modification = NOW()
                    WHERE id = :question_id AND id_quiz = :id_quiz"; // Sécurité: vérifier id_quiz
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
        }

        $stmt->bindParam(':id_quiz', $id_quiz, PDO::PARAM_INT);
        $stmt->bindParam(':texte_question', $texte_question);
        $stmt->bindParam(':type_question', $type_question);
        $stmt->bindParam(':points', $points, PDO::PARAM_INT);
        $stmt->bindParam(':ordre', $ordre, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['success_message'] = "Question " . ($action === 'add' ? "ajoutée" : "mise à jour") . " avec succès.";

    } catch (PDOException $e) {
        error_log("Erreur DB question_actions: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données: " . $e->getMessage();
        // Rediriger vers le formulaire en cas d\'erreur, avec les anciennes données si possible
        $_SESSION['old_input'] = $_POST;
        $redirect_url = "question_form.php?quiz_id=$id_quiz";
        if ($action === 'edit' && $question_id) {
            $redirect_url .= "&id=$question_id";
        }
        redirect($redirect_url);
    }

} elseif ($action === 'delete') {
    if (!$question_id) {
        $_SESSION['error_message'] = "ID de question manquant pour la suppression.";
        redirect("manage_quiz_questions.php?quiz_id=$id_quiz");
    }
    try {
        // Avant de supprimer la question, il faudrait supprimer ses réponses associées (table quiz_reponses)
        // $sql_delete_reponses = "DELETE FROM quiz_reponses WHERE id_question = :question_id";
        // $stmt_delete_reponses = $pdo->prepare($sql_delete_reponses);
        // $stmt_delete_reponses->bindParam(':question_id', $question_id, PDO::PARAM_INT);
        // $stmt_delete_reponses->execute();

        $sql = "DELETE FROM questions_quiz WHERE id = :question_id AND id_quiz = :id_quiz"; // Sécurité
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_quiz', $id_quiz, PDO::PARAM_INT); // S\'assurer qu\'elle appartient au bon quiz
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Question supprimée avec succès.";
        } else {
            $_SESSION['error_message'] = "La question n\'a pas pu être supprimée (peut-être déjà supprimée ou n\'appartient pas à ce quiz).";
        }
    } catch (PDOException $e) {
        error_log("Erreur DB suppression question: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données lors de la suppression de la question. Assurez-vous que les réponses associées sont gérées si des contraintes existent.";
    }
} else {
    $_SESSION['error_message'] = "Action non valide.";
}

redirect("manage_quiz_questions.php?quiz_id=$id_quiz");
?>
