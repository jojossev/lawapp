<?php
// config.php est maintenant inclus via header.php
$page_title = "Inscription au cours";
require_once 'includes/header.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'login.php?redirect=inscription_cours.php';</script>";
    echo "<div style='background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 10px; margin: 10px;'>Vous devez être connecté pour vous inscrire à un cours. Redirection vers la page de connexion...</div>";
    die();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Vérifier si le cours existe
try {
    $stmt_check_course = $pdo->prepare("SELECT id, titre, description FROM cours WHERE id = :id AND statut = 'actif'");
    $stmt_check_course->bindParam(':id', $course_id, PDO::PARAM_INT);
    $stmt_check_course->execute();
    $course = $stmt_check_course->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        $error_message = "Ce cours n'existe pas ou n'est pas disponible.";
    } else {
        // Vérifier si l'utilisateur est déjà inscrit à ce cours
        $stmt_check_inscription = $pdo->prepare("SELECT id FROM inscriptions WHERE id_utilisateur = :user_id AND id_cours = :course_id");
        $stmt_check_inscription->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_check_inscription->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt_check_inscription->execute();

        if ($stmt_check_inscription->fetch()) {
            $error_message = "Vous êtes déjà inscrit à ce cours.";
        }
    }
} catch (PDOException $e) {
    $error_message = "Erreur lors de la vérification du cours : " . $e->getMessage();
}

// Traiter l'inscription si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message)) {
    try {
        // Insérer l'inscription
        $stmt_insert = $pdo->prepare("INSERT INTO inscriptions (id_utilisateur, id_cours, date_inscription, progres, statut) VALUES (:user_id, :course_id, NOW(), 0, 'actif')");
        $stmt_insert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_insert->bindParam(':course_id', $course_id, PDO::PARAM_INT);

        if ($stmt_insert->execute()) {
            $success_message = "Félicitations ! Vous êtes maintenant inscrit au cours \"" . htmlspecialchars($course['titre']) . "\".";
            // Redirection vers la page du cours après 3 secondes
            echo "<script>setTimeout(function() { window.location.href = 'cours.php?id=" . $course_id . "'; }, 3000);</script>";
        } else {
            $error_message = "Une erreur s'est produite lors de l'inscription. Veuillez réessayer.";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '42S02') {
            $error_message = "Erreur système : La table des inscriptions semble manquante. Veuillez contacter l'administrateur.";
        } else {
            $error_message = "Erreur d'inscription : " . $e->getMessage();
        }
    }
}
?>

<div class="container page-content">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Inscription au cours</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error_message; ?>
                            <p class="mt-3">
                                <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
                            </p>
                        </div>
                    <?php elseif (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                            <p class="mt-3">
                                <a href="cours.php?id=<?php echo $course_id; ?>" class="btn btn-primary">Accéder au cours</a>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="course-details mb-4">
                            <h3><?php echo htmlspecialchars($course['titre']); ?></h3>
                            <p><?php echo htmlspecialchars($course['description']); ?></p>
                        </div>
                        <form method="post" action="">
                            <p class="text-center">Êtes-vous sûr de vouloir vous inscrire à ce cours ?</p>
                            <div class="text-center">
                                <button type="submit" class="btn btn-success">Confirmer l'inscription</button>
                                <a href="index.php" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
