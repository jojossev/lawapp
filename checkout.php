<?php
// checkout.php

require_once __DIR__ . '/includes/config.php'; // Contient db_connect.php et session_start
$page_title = "Finaliser l'achat";

// 1. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour acheter un cours.";
    // Rediriger vers la page de connexion, en gardant la page actuelle en mémoire pour revenir
    $redirect_url = 'checkout.php' . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
    header('Location: login.php?redirect=' . urlencode($redirect_url));
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// 2. Récupérer et valider l'ID du cours
$course_id = $_GET['course_id'] ?? null;
if (!$course_id || !filter_var($course_id, FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "ID de cours invalide pour le paiement.";
    header("Location: courses.php");
    exit;
}
$course_id = (int)$course_id;

$course = null;
$page_error = null;

try {
    // 3. Récupérer les détails du cours (on a besoin au moins du titre et du prix)
    $stmt = $pdo->prepare("SELECT id, titre, prix, statut FROM cours WHERE id = :id");
    $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si le cours existe, est publié et a un prix
    if (!$course) {
        $_SESSION['error_message'] = "Cours non trouvé.";
        header("Location: courses.php");
        exit;
    }
    if ($course['statut'] !== 'publie') {
        $_SESSION['error_message'] = "Ce cours n'est pas disponible à l'achat.";
        header("Location: view_course.php?id=" . $course_id);
        exit;
    }
     if (is_null($course['prix']) || $course['prix'] <= 0) {
        $_SESSION['error_message'] = "Ce cours est gratuit ou n'a pas de prix défini.";
        // On pourrait aussi l'inscrire directement ici si c'est gratuit, mais le bouton ne devrait pas mener ici.
        header("Location: view_course.php?id=" . $course_id);
        exit;
    }

    // 4. Vérifier si l'utilisateur est déjà inscrit
    $stmt_enroll = $pdo->prepare("SELECT 1 FROM inscriptions WHERE id_utilisateur = :user_id AND id_cours = :course_id");
    $stmt_enroll->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_enroll->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt_enroll->execute();
    if ($stmt_enroll->fetch()) {
        $_SESSION['success_message'] = "Vous êtes déjà inscrit à ce cours.";
        header("Location: view_course.php?id=" . $course_id);
        exit;
    }

} catch (PDOException $e) {
    error_log("Erreur checkout : " . $e->getMessage());
    $page_error = "Une erreur technique est survenue. Veuillez réessayer.";
}

require_once __DIR__ . '/includes/header.php'; // Inclure l'en-tête
?>

<div class="container mt-5">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($page_error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($page_error); ?></div>
    <?php elseif ($course): ?>
        <div class="card">
            <div class="card-header">
                Récapitulatif de votre commande
            </div>
            <div class="card-body">
                <h5 class="card-title">Cours : <?php echo htmlspecialchars($course['titre']); ?></h5>
                <p class="card-text">Vous êtes sur le point d'acheter l'accès à ce cours.</p>
                <p class="fs-4 fw-bold">Prix : <?php echo number_format($course['prix'], 2, ',', ' '); ?> €</p>
                
                <hr>
                
                <?php // Ici viendrait l'intégration du paiement (ex: bouton Stripe, PayPal) ?>
                <form action="process_purchase.php" method="POST"> <?php // Action vers un script de traitement (à créer) ?>
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <?php // Ajouter d'autres champs cachés si nécessaire (ex: token CSRF) ?>
                    
                    <button type="submit" class="btn btn-primary btn-lg">
                        Confirmer et Payer (Simulation) <?php // Le texte changera avec une vraie intégration ?>
                    </button>
                    <a href="view_course.php?id=<?php echo $course_id; ?>" class="btn btn-secondary ms-2">Annuler</a>
                </form>
                <small class="d-block mt-3 text-muted">Ceci est une simulation. Aucun paiement réel ne sera effectué.</small>

            </div>
        </div>
    <?php else: ?>
        <?php // Normalement géré par les redirections plus haut, mais sécurité ?>
        <div class="alert alert-warning">Impossible de charger les informations de commande.</div>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/includes/footer.php'; // Inclure le pied de page
?>
