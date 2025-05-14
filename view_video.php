<?php
// view_video.php

require_once __DIR__ . '/includes/config.php';
$page_title = "Détails de la Vidéo";

// Récupérer et valider l'ID de la vidéo depuis l'URL
$video_id = $_GET['id'] ?? null;

if (!$video_id || !filter_var($video_id, FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "ID de vidéo invalide ou manquant.";
    header("Location: videos.php");
    exit;
}

$video_id = (int)$video_id;
$video = null;
$page_error = null;

try {
    // Récupérer les détails de la vidéo depuis la base de données
    $stmt = $pdo->prepare("
        SELECT 
            v.id, 
            v.titre, 
            v.description,
            v.url_video,
            v.type_video,
            v.duree,
            v.miniature_url,
            v.niveau,
            v.prix,
            v.statut,
            v.date_creation,
            v.date_mise_a_jour,
            vc.nom AS nom_categorie,
            CONCAT(u.prenom, ' ', u.nom) AS nom_createur,
            u.email AS email_createur 
        FROM videos v
        LEFT JOIN video_categories vc ON v.id_categorie = vc.id
        LEFT JOIN utilisateurs u ON v.id_createur = u.id
        WHERE v.id = :id AND v.statut = 'publie'
    ");
    
    $stmt->bindParam(':id', $video_id, PDO::PARAM_INT);
    $stmt->execute();
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$video) {
        $_SESSION['error_message'] = "Vidéo non trouvée ou non disponible.";
        header("Location: videos.php");
        exit;
    }

    $page_title = htmlspecialchars($video['titre']);

    // Vérifier si l'utilisateur est connecté et s'il est inscrit (si la vidéo est payante)
    $is_logged_in = isset($_SESSION['user_id']);
    $user_id = $is_logged_in ? (int)$_SESSION['user_id'] : null;
    $user_has_access = false;
    $is_paid_content = !is_null($video['prix']) && $video['prix'] > 0;

    if ($is_logged_in && $is_paid_content) {
        $stmt_access = $pdo->prepare("SELECT 1 FROM acces_videos WHERE id_utilisateur = :user_id AND id_video = :video_id");
        $stmt_access->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_access->bindParam(':video_id', $video_id, PDO::PARAM_INT);
        $stmt_access->execute();
        if ($stmt_access->fetch()) {
            $user_has_access = true;
        }
    }

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération de la vidéo : " . $e->getMessage());
    $page_error = "Une erreur s'est produite lors du chargement de la vidéo. Veuillez réessayer plus tard.";
    if (isset($_GET['debug'])) {
        echo "<pre>Erreur SQL : " . $e->getMessage() . "</pre>";
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <?php if ($page_error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($page_error); ?></div>
    <?php elseif ($video): ?>
        <article class="video-detail">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-3"><?php echo htmlspecialchars($video['titre']); ?></h1>

                    <?php if (!$is_paid_content || ($is_logged_in && $user_has_access)): ?>
                        <div class="video-player mb-4">
                            <?php
                            $url = htmlspecialchars($video['url_video']);
                            $type = strtolower($video['type_video']);
                            
                            if (strpos($type, 'youtube') !== false) {
                                // Extraire l'ID de la vidéo YouTube de l'URL
                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
                                if (isset($matches[1])) {
                                    echo '<div class="ratio ratio-16x9">
                                            <iframe src="https://www.youtube.com/embed/' . $matches[1] . '" 
                                                    frameborder="0" 
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                    allowfullscreen>
                                            </iframe>
                                          </div>';
                                }
                            } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $url)) {
                                echo '<video controls class="w-100">
                                        <source src="' . $url . '" type="video/' . pathinfo($url, PATHINFO_EXTENSION) . '">
                                        Votre navigateur ne supporte pas la lecture de vidéos.
                                      </video>';
                            } else {
                                echo '<div class="alert alert-warning">Format vidéo non supporté</div>';
                            }
                            ?>
                        </div>
                        
                        <div class="video-description">
                            <h2>Description</h2>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
                        </div>
                    <?php elseif ($is_paid_content && !$is_logged_in): ?>
                        <div class="alert alert-info">
                            <h2>Contenu Premium</h2>
                            <p>Cette vidéo est réservée aux membres. <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Connectez-vous</a> ou <a href="register.php">inscrivez-vous</a> pour y accéder.</p>
                        </div>
                    <?php elseif ($is_paid_content && !$user_has_access): ?>
                        <div class="alert alert-info">
                            <h2>Contenu Premium</h2>
                            <p>Cette vidéo nécessite un accès premium.</p>
                            <a href="checkout.php?type=video&id=<?php echo $video_id; ?>" class="btn btn-primary">Obtenir l'accès (<?php echo number_format($video['prix'], 2, ',', ' '); ?> €)</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informations</h5>
                            <ul class="list-unstyled">
                                <?php if (!empty($video['duree'])): ?>
                                    <li><strong>Durée:</strong> <?php echo htmlspecialchars($video['duree']); ?></li>
                                <?php endif; ?>
                                <li><strong>Niveau:</strong> <?php echo htmlspecialchars($video['niveau'] ?? 'Non spécifié'); ?></li>
                                <?php if (!empty($video['nom_categorie'])): ?>
                                    <li><strong>Catégorie:</strong> <?php echo htmlspecialchars($video['nom_categorie']); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($video['nom_createur'])): ?>
                                    <li><strong>Auteur:</strong> <?php echo htmlspecialchars($video['nom_createur']); ?></li>
                                <?php endif; ?>
                                <li><strong>Publié le:</strong> <?php echo date("d/m/Y", strtotime($video['date_creation'])); ?></li>
                                <?php if ($video['date_mise_a_jour'] && $video['date_mise_a_jour'] != $video['date_creation']): ?>
                                    <li><strong>Mis à jour le:</strong> <?php echo date("d/m/Y", strtotime($video['date_mise_a_jour'])); ?></li>
                                <?php endif; ?>
                            </ul>

                            <?php if (!$is_paid_content || ($is_logged_in && $user_has_access)): ?>
                                <a href="#video-player" class="btn btn-success w-100">Regarder la vidéo</a>
                            <?php elseif ($is_paid_content && !$is_logged_in): ?>
                                <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary w-100">Se connecter pour regarder</a>
                            <?php elseif ($is_paid_content && !$user_has_access): ?>
                                <a href="checkout.php?type=video&id=<?php echo $video_id; ?>" class="btn btn-primary w-100">Acheter (<?php echo number_format($video['prix'], 2, ',', ' '); ?> €)</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    <?php else: ?>
        <div class="alert alert-warning">La vidéo demandée n'a pas pu être chargée.</div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="videos.php" class="btn btn-outline-secondary">&laquo; Retour aux vidéos</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
