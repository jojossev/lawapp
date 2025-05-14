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
$question_id = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
$reponse_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT); // Pour edit et delete

// Validation de base de question_id
if (!$question_id) {
    $_SESSION['error_message'] = "ID de question manquant.";
    redirect($_SERVER['HTTP_REFERER'] ?? 'manage_cours.php');
}

// Redirection par défaut
$redirect_url = "manage_question_reponses.php?question_id=$question_id";


if ($action === 'add' || $action === 'edit') {
    $texte_reponse = trim($_POST['texte_reponse'] ?? '');
    $ordre = filter_input(INPUT_POST, 'ordre', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
    // $est_correcte est 1 si la case est cochée, 0 sinon (grâce au champ hidden et au filter_input)
    $est_correcte = filter_input(INPUT_POST, 'est_correcte', FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0, 'max_range' => 1]]);
    $feedback_specifique = trim($_POST['feedback_specifique'] ?? '');

    // Validation des champs
    $errors = [];
    if (empty($texte_reponse)) {
        $errors[] = "Le texte de la réponse ne peut pas être vide.";
    }
    if ($ordre === false || $ordre < 0) { 
        $errors[] = "L'ordre d'affichage doit être un entier positif ou nul.";
    }
    if ($est_correcte === null || !in_array($est_correcte, [0,1])) {
        $errors[] = "La valeur pour 'est_correcte' est invalide.";
    }
    if ($action === 'edit' && !$reponse_id) {
        $errors[] = "ID de réponse manquant pour la modification.";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        $form_redirect_url = "reponse_form.php?question_id=$question_id";
        if ($action === 'edit' && $reponse_id) {
            $form_redirect_url .= "&id=$reponse_id";
        }
        redirect($form_redirect_url);
    }

    // Récupérer le type de la question parente
    $type_question_parent = '';
    try {
        $stmt_type = $pdo->prepare("SELECT type_question FROM questions_quiz WHERE id = :question_id");
        $stmt_type->bindParam(':question_id', $question_id, PDO::PARAM_INT);
        $stmt_type->execute();
        $result_type = $stmt_type->fetch(PDO::FETCH_ASSOC);
        if ($result_type) {
            $type_question_parent = $result_type['type_question'];
        }
    } catch (PDOException $e) {
        error_log("Erreur récupération type question parente: " . $e->getMessage());
        // Gérer l'erreur si nécessaire, mais on peut continuer sans pour l'instant
        // La logique de réinitialisation ne s'appliquera pas si le type n'est pas récupéré
    }

    // Début de la transaction pour assurer l'atomicité des opérations
    $pdo->beginTransaction();

    try {
        // Si la réponse actuelle est marquée comme correcte et que le type de question l'exige,
        // mettre à jour les autres réponses de cette question pour qu'elles ne soient pas correctes.
        if ($est_correcte == 1 && ($type_question_parent === 'qcm' || $type_question_parent === 'vrai_faux')) {
            $sql_reset_correct = "UPDATE quiz_reponses SET est_correcte = 0 WHERE id_question = :question_id";
            // Si on édite, on ne veut pas réinitialiser la réponse qu'on est en train de marquer comme correcte (elle sera mise à jour après)
            // Si on ajoute, on réinitialise tout, puis on insère la nouvelle comme correcte.
            // Pour simplifier, on réinitialise tout, puis l'UPDATE/INSERT ci-dessous mettra la bonne valeur.
            // Une alternative serait d'exclure l'ID courant: AND id != :current_reponse_id (pour 'edit' uniquement)
            
            $stmt_reset = $pdo->prepare($sql_reset_correct);
            $stmt_reset->bindParam(':question_id', $question_id, PDO::PARAM_INT);
            $stmt_reset->execute();
        }

        if ($action === 'add') {
            $sql = "INSERT INTO quiz_reponses (id_question, texte_reponse, ordre, est_correcte, feedback_specifique, date_creation, date_mise_a_jour)
                    VALUES (:id_question, :texte_reponse, :ordre, :est_correcte, :feedback_specifique, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
        } elseif ($action === 'edit') {
            $sql = "UPDATE quiz_reponses
                    SET texte_reponse = :texte_reponse, ordre = :ordre, est_correcte = :est_correcte, feedback_specifique = :feedback_specifique, date_mise_a_jour = NOW()
                    WHERE id = :reponse_id AND id_question = :id_question";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':reponse_id', $reponse_id, PDO::PARAM_INT);
        }

        $stmt->bindParam(':id_question', $question_id, PDO::PARAM_INT);
        $stmt->bindParam(':texte_reponse', $texte_reponse);
        $stmt->bindParam(':ordre', $ordre, PDO::PARAM_INT);
        $stmt->bindParam(':est_correcte', $est_correcte, PDO::PARAM_INT);
        $stmt->bindParam(':feedback_specifique', $feedback_specifique);
        $stmt->execute();

        $pdo->commit(); // Valider la transaction
        $_SESSION['success_message'] = "Réponse " . ($action === 'add' ? "ajoutée" : "mise à jour") . " avec succès.";

    } catch (PDOException $e) {
        $pdo->rollBack(); // Annuler la transaction en cas d'erreur
        error_log("Erreur DB reponse_actions (transaction): " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données: " . $e->getMessage();
        $_SESSION['old_input'] = $_POST;
        $form_redirect_url = "reponse_form.php?question_id=$question_id";
        if ($action === 'edit' && $reponse_id) {
            $form_redirect_url .= "&id=$reponse_id";
        }
        redirect($form_redirect_url);
    }

} elseif ($action === 'delete') {
    if (!$reponse_id) {
        $_SESSION['error_message'] = "ID de réponse manquant pour la suppression.";
        redirect($redirect_url);
    }
    try {
        $sql = "DELETE FROM quiz_reponses WHERE id = :reponse_id AND id_question = :id_question";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':reponse_id', $reponse_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_question', $question_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Réponse supprimée avec succès.";
        } else {
            $_SESSION['error_message'] = "La réponse n'a pas pu être supprimée.";
        }
    } catch (PDOException $e) {
        error_log("Erreur DB suppression réponse: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données lors de la suppression de la réponse.";
    }
} else {
    $_SESSION['error_message'] = "Action non valide.";
}

redirect($redirect_url);
?>
