<?php
$page_title = "Nos Vidéos Juridiques";
require_once __DIR__ . '/includes/config.php'; // Assure la disponibilité de $pdo et BASE_URL
require_once __DIR__ . '/includes/header.php'; // Header commun

// Récupérer les vidéos publiées
$videos_liste = [];
$error_message = null;
try {
    $stmt_videos = $pdo->prepare("
        SELECT 
            v.id, 
            v.titre, 
            v.description,
            v.url_video, 
            v.image_thumbnail_url,
            v.duree,
            v.niveau,
            v.prix,
            cv.nom AS nom_categorie_video,
            'Administrateur' AS nom_createur
        FROM videos v
        LEFT JOIN categories_videos cv ON v.id_categorie = cv.id
        WHERE v.statut = 'publie'
        ORDER BY v.date_creation DESC
    ");
    $stmt_videos->execute();
    $videos_liste = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des vidéos. Veuillez réessayer plus tard."; 
    error_log("PDOException in videos.php: " . $e->getMessage());
}
?>

    <div class="container">
        <header class="mb-4 text-center">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="lead">Découvrez notre collection de vidéos pour approfondir vos connaissances juridiques.</p>
        </header>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (empty($videos_liste) && !$error_message): ?>
            <div class="alert alert-info text-center" role="alert">
                Aucune vidéo n'est disponible pour le moment. Revenez bientôt !
            </div>
        <?php elseif (!empty($videos_liste)):
 ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($videos_liste as $video):
 ?>
                    <div class="col">
                        <div class="card h-100 video-card shadow-sm">
                            <?php if (!empty($video['image_thumbnail_url'])):
                            ?>
                                <img src="<?php echo htmlspecialchars(BASE_URL . '/' . ltrim($video['image_thumbnail_url'], '/')); ?>" class="card-img-top" alt="Miniature de <?php echo htmlspecialchars($video['titre']); ?>" style="height: 180px; object-fit: cover;">
                            <?php else:
                            ?>
                                <img src="<?php echo htmlspecialchars(BASE_URL . '/images/placeholder_video.png'); ?>" class="card-img-top" alt="Vidéo sans miniature" style="height: 180px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <a href="view_video.php?id=<?php echo $video['id']; ?>">
                                        <?php echo htmlspecialchars($video['titre']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars(substr($video['description'] ?? '', 0, 120)) . (strlen($video['description'] ?? '') > 120 ? '...' : ''); ?>
                                </p>
                                <ul class="list-unstyled mt-auto mb-2 small text-muted">
                                    <li>Catégorie: <?php echo htmlspecialchars($video['nom_categorie_video'] ?? 'N/A'); ?></li>
                                    <li>Niveau: <?php echo htmlspecialchars($video['niveau'] ?? 'N/A'); ?></li>
                                    <li>Durée: <?php echo htmlspecialchars($video['duree'] ?? 'N/A'); ?></li>
                                    <li>Par: <?php echo htmlspecialchars($video['nom_createur'] ?? 'N/A'); ?></li>
                                </ul>
                                <a href="view_video.php?id=<?php echo $video['id']; ?>" class="btn btn-primary mt-auto">Regarder la vidéo</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php'; // Footer commun
?>
