<?php
// view_course.php

require_once __DIR__ . '/includes/config.php'; // Contient db_connect.php et session_start
$page_title = "Détails du Cours"; // Sera défini plus précisément après avoir récupéré le titre du cours

// 1. Récupérer et valider l'ID du cours depuis l'URL
$course_id = $_GET['id'] ?? null;

if (!$course_id || !filter_var($course_id, FILTER_VALIDATE_INT)) {
    // Rediriger vers la page des cours ou afficher une erreur si l'ID est manquant ou invalide
    $_SESSION['error_message'] = "ID de cours invalide ou manquant.";
    header("Location: courses.php");
    exit;
}

$course_id = (int)$course_id;
$course = null;
$page_error = null;

try {
    // 2. Récupérer les détails du cours depuis la base de données
    // On joint avec categories_cours et utilisateurs pour avoir plus d'infos
    $stmt = $pdo->prepare("
        SELECT 
            c.id, 
            c.titre, 
            c.description, 
            c.contenu_principal_url, 
            c.contenu_principal_type, 
            c.image_url, 
            c.niveau,
            c.duree_estimee,
            c.prix, /* <-- Ajout du prix */
            c.statut,
            c.date_creation,
            c.date_mise_a_jour,
            cc.nom_categorie AS nom_categorie_cours,
            CONCAT(u.prenom, ' ', u.nom) AS nom_createur,
            u.email AS email_createur 
        FROM cours c
        LEFT JOIN categories_cours cc ON c.id_categorie = cc.id
        LEFT JOIN utilisateurs u ON c.id_createur = u.id
        WHERE c.id = :id AND c.statut = 'publie' 
    "); // On s'assure que le cours est publié
    
    $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        $_SESSION['error_message'] = "Cours non trouvé ou non disponible.";
        header("Location: courses.php");
        exit;
    }

    $page_title = htmlspecialchars($course['titre']); // Mettre à jour le titre de la page

    // Vérifier si l'utilisateur est connecté et s'il est inscrit (si le cours est payant)
    $is_logged_in = isset($_SESSION['user_id']);
    $user_id = $is_logged_in ? (int)$_SESSION['user_id'] : null;
    $user_is_enrolled = false;
    $is_paid_course = !is_null($course['prix']) && $course['prix'] > 0;

    if ($is_logged_in && $is_paid_course) {
        $stmt_enroll = $pdo->prepare("SELECT 1 FROM inscriptions WHERE id_utilisateur = :user_id AND id_cours = :course_id");
        $stmt_enroll->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_enroll->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt_enroll->execute();
        if ($stmt_enroll->fetch()) {
            $user_is_enrolled = true;
        }
    }

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du cours : " . $e->getMessage());
    $page_error = "Une erreur s'est produite lors du chargement du cours. Veuillez réessayer plus tard.";
    if (isset($_GET['debug'])) {
        echo "<pre>Erreur SQL : " . $e->getMessage() . "</pre>";
    }
    // Il serait bien de rediriger ou d'afficher une page d'erreur plus générique ici
}

require_once __DIR__ . '/includes/header.php'; // Inclure l'en-tête
?>

<div class="container mt-5">
    <?php if ($page_error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($page_error); ?></div>
    <?php elseif ($course): ?>
        <article class="course-detail">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-3"><?php echo htmlspecialchars($course['titre']); ?></h1>
                    
                    <p class="lead"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                    
                    <div class="course-meta mb-4">
                        <span class="badge bg-secondary me-2"><?php echo htmlspecialchars($course['niveau'] ?? 'N/A'); ?></span>
                        
                        <?php // Affichage du Prix ou Gratuit ?>
                        <span class="fw-bold me-3">
                            <?php if (!is_null($course['prix']) && $course['prix'] > 0): ?>
                                Prix: <?php echo number_format($course['prix'], 2, ',', ' '); ?> €
                            <?php else: ?>
                                <span class="text-success">Gratuit</span>
                            <?php endif; ?>
                        </span>

                        <?php if (!empty($course['nom_categorie_cours'])):
 ?>
                            <span class="text-muted">Catégorie: <?php echo htmlspecialchars($course['nom_categorie_cours']); ?></span> |
                        <?php endif; ?>
                        <?php if (!empty($course['nom_createur'])):
 ?>
                            <span class="text-muted">Par: <?php echo htmlspecialchars($course['nom_createur']); ?></span> |
                        <?php endif; ?>
                        <?php if (!empty($course['duree_estimee'])):
 ?>
                            <span class="text-muted">Durée: <?php echo htmlspecialchars($course['duree_estimee']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="course-content mt-4">
                        <?php // Condition pour afficher le contenu détaillé
                        $can_access_content = !$is_paid_course || ($is_logged_in && $user_is_enrolled);
                        ?>

                        <?php if ($can_access_content): ?>
                            <h2>Contenu du cours</h2>
                            <?php if (!empty($course['contenu_principal_url'])): ?>
                                <?php
                                $url = htmlspecialchars($course['contenu_principal_url']);
                                $type = strtolower($course['contenu_principal_type']);
                                
                                if (strpos($type, 'pdf') !== false) {
                                    // Affichage PDF intégré
                                    echo '<iframe src="' . $url . '" width="100%" height="600px" style="border:1px solid #ccc;"></iframe>';
                                } elseif (strpos($type, 'video') !== false || preg_match('/\.(mp4|webm|ogg)$/i', $url)) {
                                    // Lecteur vidéo HTML5
                                    echo '<video controls width="100%"><source src="' . $url . '">Votre navigateur ne supporte pas la vidéo.</video>';
                                } elseif (preg_match('/\.(docx|xlsx|pptx)$/i', $url)) {
                                    // Lien de téléchargement pour documents Office
                                    echo '<p>Ce cours contient un document Microsoft Office. <a href="' . $url . '" class="btn btn-primary" download>Télécharger le document</a></p>';
                                } else {
                                    // Lien générique pour autres types de fichiers
                                    echo '<p><a href="' . $url . '" class="btn btn-primary" target="_blank">Voir le contenu du cours</a></p>';
                                }
                                ?>
                            <?php else: ?>
                                <p>Le contenu détaillé de ce cours sera bientôt disponible.</p>
                            <?php endif; ?>
                        <?php elseif ($is_paid_course && !$is_logged_in): ?>
                            <div class="alert alert-info">
                                <h2>Accès Réservé</h2>
                                <p>Ce cours est payant. <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Connectez-vous</a> ou <a href="register.php">inscrivez-vous</a> pour l'acheter.</p>
                            </div>
                        <?php elseif ($is_paid_course && !$user_is_enrolled): ?>
                            <div class="alert alert-info">
                                <h2>Contenu Verrouillé</h2>
                                <p>Achetez ce cours pour accéder à son contenu.</p>
                                <?php // Bouton d'achat peut être répété ici ou on se fie à celui de la colonne droite ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <?php 
                    $image_path = !empty($course['image_url']) ? htmlspecialchars($course['image_url']) : 'https://placehold.co/400x250/EFEFEF/AAAAAA&text=' . urlencode($course['titre']);
                    ?>
                    <img src="<?php echo $image_path; ?>" class="img-fluid rounded shadow-sm mb-3" alt="<?php echo htmlspecialchars($course['titre']); ?>" style="width: 100%;">
                    
                    <!-- Potentiellement ajouter d'autres informations ici : prix, bouton d'inscription, progression, etc. -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informations</h5>
                            <ul class="list-unstyled">
                                <li><strong>Niveau:</strong> <?php echo htmlspecialchars($course['niveau'] ?? 'Non spécifié'); ?></li>
                                <li><strong>Durée estimée:</strong> <?php echo htmlspecialchars($course['duree_estimee'] ?? 'Non spécifiée'); ?></li>
                                <?php if (!empty($course['nom_categorie_cours'])):
 ?>
                                <li><strong>Catégorie:</strong> <?php echo htmlspecialchars($course['nom_categorie_cours']); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($course['nom_createur'])):
 ?>
                                <li><strong>Instructeur:</strong> <?php echo htmlspecialchars($course['nom_createur']); ?></li>
                                <?php endif; ?>
                                 <li><strong>Publié le:</strong> <?php echo date("d/m/Y", strtotime($course['date_creation'])); ?></li>
                                <?php if ($course['date_mise_a_jour'] && $course['date_mise_a_jour'] != $course['date_creation']):
 ?>
                                    <li><strong>Mis à jour le:</strong> <?php echo date("d/m/Y", strtotime($course['date_mise_a_jour'])); ?></li>
                                <?php endif; ?>
                            </ul>

                            <?php // Bouton d'action dynamique
                            if (!$is_paid_course || ($is_logged_in && $user_is_enrolled)):
                                // Gratuit ou déjà inscrit ?>
                                <a href="#" class="btn btn-success w-100">Accéder au contenu</a>
                                <?php // TODO: Mettre le bon lien/action pour accéder au contenu (e.g., module 1)
                            elseif ($is_paid_course && !$is_logged_in):
                                // Payant, non connecté ?>
                                <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary w-100">Se connecter pour acheter (<?php echo number_format($course['prix'], 2, ',', ' '); ?> €)</a>
                            elseif ($is_paid_course && !$user_is_enrolled):
                                // Payant, connecté, non inscrit ?>
                                <a href="checkout.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary w-100">Acheter (<?php echo number_format($course['prix'], 2, ',', ' '); ?> €)</a>
                                <?php // TODO: Implémenter checkout.php
                             endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </article>
    <?php else: ?>
        <div class="alert alert-warning">Le cours demandé n'a pas pu être chargé.</div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="courses.php" class="btn btn-outline-secondary">&laquo; Retour à la liste des cours</a>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php'; // Inclure le pied de page
?>
