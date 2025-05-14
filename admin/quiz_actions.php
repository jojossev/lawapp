<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/admin_auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

// Vérifier si la méthode de requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Méthode non autorisée.";
    redirect('manage_cours.php'); // Redirection générique
}

// Récupérer et nettoyer les données du formulaire
$action = $_POST['action'] ?? '';
$quiz_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$lecon_id = filter_input(INPUT_POST, 'id_lecon', FILTER_VALIDATE_INT);
$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');
$type = $_POST['type'] ?? ''; // Valider contre une liste prédéfinie
$statut = $_POST['statut'] ?? ''; // Valider contre une liste prédéfinie
$ordre = filter_input(INPUT_POST, 'ordre', FILTER_VALIDATE_INT);

// --- Validation ---
$errors = [];
if (!$lecon_id) {
    $errors[] = "ID de leçon manquant ou invalide.";
}
if (empty($titre)) {
    $errors[] = "Le titre du quiz est obligatoire.";
}
$valid_types = ['qcm', 'vrai_faux', 'reponse_courte']; // Types de quiz autorisés
if (!in_array($type, $valid_types)) {
    $errors[] = "Type de quiz invalide.";
}
$valid_statuses = ['brouillon', 'publie']; // Statuts autorisés
if (!in_array($statut, $valid_statuses)) {
    $errors[] = "Statut invalide.";
}
if ($ordre === false || $ordre < 0) { // Permettre 0 mais pas négatif
    // Considérer 0 comme valide si l'input était vide ou invalide, ou renvoyer une erreur ?
    // Pour l'instant, on le met à 0 par défaut en cas d'erreur de validation.
    $ordre = 0; 
    // Ou $errors[] = "L'ordre d'affichage doit être un nombre entier positif ou nul.";
}
if (($action === 'edit' || $action === 'delete') && !$quiz_id) {
    $errors[] = "ID du quiz manquant pour l'action '$action'.";
}

// Si des erreurs sont trouvées, rediriger vers le formulaire avec les erreurs
if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
     // Rediriger vers le formulaire (ajout ou édition)
     $redirect_url = ($action === 'add') ? "quiz_form.php?lecon_id=$lecon_id" : "quiz_form.php?id=$quiz_id&lecon_id=$lecon_id";
    redirect($redirect_url);
}

// Définir la page de redirection par défaut (liste des quiz de la leçon)
$redirect_page = "manage_lecon_quiz.php?lecon_id=" . $lecon_id;

// --- Exécution des actions ---
try {
    // Supposons que la table 'quiz' existe
    if ($action === 'add') {
        $sql = "INSERT INTO quiz (id_lecon, titre, description, type, statut, ordre, date_creation, date_modification) 
                VALUES (:id_lecon, :titre, :description, :type, :statut, :ordre, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_lecon', $lecon_id, PDO::PARAM_INT);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':ordre', $ordre, PDO::PARAM_INT);
        $stmt->execute();
        $_SESSION['success_message'] = "Quiz ajouté avec succès.";

    } elseif ($action === 'edit' && $quiz_id) {
        $sql = "UPDATE quiz 
                SET titre = :titre, description = :description, type = :type, statut = :statut, ordre = :ordre, date_modification = NOW() 
                WHERE id = :quiz_id AND id_lecon = :id_lecon"; // Sécurité : vérifier id_lecon
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':ordre', $ordre, PDO::PARAM_INT);
        $stmt->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
         $stmt->bindParam(':id_lecon', $lecon_id, PDO::PARAM_INT); // Vérifier l'appartenance
        $stmt->execute();
        $_SESSION['success_message'] = "Quiz mis à jour avec succès.";

    } elseif ($action === 'delete' && $quiz_id) {
         // Attention : Avant de supprimer un quiz, il faudra gérer la suppression en cascade
         // des questions, réponses, et résultats utilisateurs associés !
         // Pour l'instant, simple suppression du quiz.
        $sql = "DELETE FROM quiz WHERE id = :quiz_id AND id_lecon = :id_lecon"; // Sécurité : vérifier id_lecon
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_lecon', $lecon_id, PDO::PARAM_INT);
        $stmt->execute();
        $_SESSION['success_message'] = "Quiz supprimé avec succès.";

    } else {
        $_SESSION['error_message'] = "Action non valide.";
    }

} catch (PDOException $e) {
    error_log("Erreur action quiz: " . $e->getMessage());
    // Gérer le cas où la table n'existe pas encore
     if ($e->getCode() === '42S02' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1146)) { // 1146: Table doesn't exist (MySQL)
        $_SESSION['error_message'] = "Erreur : La fonctionnalité Quiz n'est pas encore activée (table 'quiz' manquante).";
    } else {
        $_SESSION['error_message'] = "Une erreur technique est survenue lors de l'opération sur le quiz.";
    }
    // En cas d'erreur, rediriger vers la liste des quiz si possible
    redirect($redirect_page);
}

// Rediriger vers la page de gestion des quiz de la leçon
redirect($redirect_page);

?>
