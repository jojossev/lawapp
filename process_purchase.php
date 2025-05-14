<?php
// process_purchase.php

require_once __DIR__ . '/includes/config.php'; // Contient db_connect.php et session_start

// 1. Vérifier la méthode de requête (doit être POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Méthode non autorisée.";
    header("Location: index.php"); // Ou une page d'erreur générique
    exit;
}

// 2. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour finaliser l'achat.";
    // Essayer de rediriger vers la page de connexion puis vers le checkout
    $course_id_from_post = $_POST['course_id'] ?? null;
    $redirect_checkout = 'checkout.php' . ($course_id_from_post ? '?course_id=' . $course_id_from_post : '');
    header('Location: login.php?redirect=' . urlencode($redirect_checkout));
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// 3. Récupérer et valider l'ID du cours depuis POST
$course_id = $_POST['course_id'] ?? null;
if (!$course_id || !filter_var($course_id, FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "ID de cours invalide.";
    header("Location: courses.php");
    exit;
}
$course_id = (int)$course_id;


try {
    // 4. Récupérer les détails du cours pour revérification
    $stmt = $pdo->prepare("SELECT id, titre, prix, statut FROM cours WHERE id = :id");
    $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier existence, statut et prix (similaire à checkout.php pour la sécurité)
    if (!$course || $course['statut'] !== 'publie' || is_null($course['prix']) || $course['prix'] <= 0) {
        $_SESSION['error_message'] = "Impossible de traiter l'achat pour ce cours.";
        header("Location: view_course.php?id=" . $course_id);
        exit;
    }

    // 5. Vérifier (encore) si l'utilisateur est déjà inscrit
    $stmt_check = $pdo->prepare("SELECT 1 FROM inscriptions WHERE id_utilisateur = :user_id AND id_cours = :course_id");
    $stmt_check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt_check->execute();
    if ($stmt_check->fetch()) {
        $_SESSION['success_message'] = "Vous êtes déjà inscrit à ce cours."; // Message informatif plutôt qu'erreur
        header("Location: view_course.php?id=" . $course_id);
        exit;
    }

    // 6. Insérer l'inscription dans la base de données
    $stmt_insert = $pdo->prepare("INSERT INTO inscriptions (id_utilisateur, id_cours, date_inscription) VALUES (:user_id, :course_id, NOW())");
    $stmt_insert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    
    if ($stmt_insert->execute()) {
        // 7. Succès : définir message et rediriger vers la page du cours
        $_SESSION['success_message'] = "Félicitations ! Vous avez maintenant accès au cours '" . htmlspecialchars($course['titre']) . "'.";
        header("Location: view_course.php?id=" . $course_id);
        exit;
    } else {
        // 8. Échec de l'insertion
        $_SESSION['error_message'] = "Une erreur s'est produite lors de l'enregistrement de votre inscription.";
        error_log("Erreur BDD lors de l'inscription : user=$user_id, course=$course_id"); // Log pour debug
        header("Location: checkout.php?course_id=" . $course_id); // Retour au checkout
        exit;
    }

} catch (PDOException $e) {
    error_log("Erreur PDO process_purchase : " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur technique est survenue lors du traitement de votre achat.";
    header("Location: checkout.php?course_id=" . $course_id); // Retour au checkout
    exit;
}

?>
