<?php
require_once __DIR__ . '/includes/config.php'; // Assure la disponibilité de $pdo

// Fonction pour obtenir l'URL d'intégration pour YouTube/Vimeo
if (!function_exists('get_embed_url')) {
    function get_embed_url($original_url) {
        if (empty($original_url)) {
            return '';
        }

        // YouTube: youtu.be/VIDEO_ID or youtube.com/watch?v=VIDEO_ID or youtube.com/embed/VIDEO_ID
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $original_url, $match_youtube)) {
            $video_id = $match_youtube[1];
            return 'https://www.youtube.com/embed/' . $video_id . '?rel=0'; // rel=0 pour éviter les suggestions d'autres chaînes
        }

        // Vimeo: vimeo.com/VIDEO_ID or player.vimeo.com/video/VIDEO_ID
        if (preg_match('%(?:https?://)?(?:www\.)?(?:player\.)?vimeo\.com/(?:video/|)(\d+)(?:[/?#&].*)?$%i', $original_url, $match_vimeo)) {
            $video_id = $match_vimeo[1];
            return 'https://player.vimeo.com/video/' . $video_id;
        }
        
        // Si c'est un lien direct .mp4, .webm, .ogg
        if (preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $original_url)) {
             return $original_url; // Retourne l'URL telle quelle pour la balise <video>
        }

        // Par défaut, retourne une chaîne vide si non reconnu
        return ''; 
    }
}

$page_title = "Lecture Vidéo"; // Sera mis à jour avec le titre de la vidéo si elle est trouvée

$video_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$video = null;
$error_message = '';

if (!$video_id) {
    header("Location: videos.php");
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            v.id, 
            v.titre, 
            v.description,
            v.url_video,
            v.duree,
            v.niveau, 
            cv.nom AS nom_categorie_video,
            COALESCE(CONCAT(u.prenom, ' ', u.nom), 'Auteur inconnu') AS nom_createur,
            v.statut
        FROM videos v
        LEFT JOIN categories_videos cv ON v.id_categorie = cv.id
        LEFT JOIN utilisateurs u ON v.id_createur = u.id
        WHERE v.id = :video_id
    ");
    $stmt->bindParam(':video_id', $video_id, PDO::PARAM_INT);
    $stmt->execute();
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$video) {
        $error_message = "Vidéo non trouvée. Elle a peut-être été supprimée.";
    } elseif ($video['statut'] !== 'publie') {
        $error_message = "Cette vidéo n'est pas actuellement disponible au public.";
        $video = null; // Empêche l'affichage des détails si non publiée
    } else {
        $page_title = htmlspecialchars($video['titre']); // Mettre à jour le titre de la page avec le titre réel
    }

} catch (PDOException $e) {
    $error_message = "Une erreur technique est survenue lors du chargement de la vidéo. Veuillez réessayer plus tard.";
    error_log("PDOException in play_video.php for video_id {$video_id}: " . $e->getMessage());
    $video = null;
}

require_once __DIR__ . '/includes/header.php'; // Inclusion du header commun

?>

    <div class="container mt-4">
        <?php if ($error_message): ?>
            <div class="alert alert-danger text-center">
                <p><?php echo htmlspecialchars($error_message); ?></p>
                <a href="videos.php" class="btn btn-primary mt-2">Retour à la liste des vidéos</a>
            </div>
        <?php elseif ($video): ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="video-player-container shadow-sm">
                        <?php
                        $original_video_url = $video['url_video'];
                        $embed_url = get_embed_url($original_video_url);

                        if (!empty($embed_url)) {
                            // Vérifier si c'est une URL pour iframe (YouTube/Vimeo) ou pour la balise <video>
                            if (preg_match('/youtube\.com\/embed\//i', $embed_url) || preg_match('/player\.vimeo\.com\/video\//i', $embed_url)) {
                                echo '<iframe src="' . htmlspecialchars($embed_url) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
                            } elseif (preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $embed_url)) {
                                // C'est un lien direct vers un fichier vidéo
                                echo '<video controls width="100%" height="100%"><source src="' . htmlspecialchars($embed_url) . '" type="video/mp4">Votre navigateur ne supporte pas la balise vidéo HTML5.</video>';
                            } else {
                                // URL non reconnue comme embeddable ou lien direct vidéo
                                 echo '<div class="d-flex justify-content-center align-items-center h-100 bg-dark text-white"><p>Le format de l\'URL de la vidéo n\'est pas supporté ou l\'URL est incorrecte.</p></div>';
                            }
                        } else {
                            // get_embed_url a retourné une chaîne vide (URL originale vide ou non traitable)
                            echo '<div class="d-flex justify-content-center align-items-center h-100 bg-dark text-white"><p>L\'URL de la vidéo n\'est pas configurée, est invalide ou non supportée.</p></div>';
                        }
                        ?>
                    </div>
                     <div class="video-details-card mt-4">
                        <h1 class="h3 mb-3"><?php echo htmlspecialchars($video['titre']); ?></h1>
                        <p class="text-muted small mb-2">
                            <span>Par: <?php echo htmlspecialchars($video['nom_createur'] ?? 'N/A'); ?></span> | 
                            <span>Catégorie: <?php echo htmlspecialchars($video['nom_categorie_video'] ?? 'N/A'); ?></span>
                        </p>
                        <p class="text-muted small">
                            <span>Niveau: <?php echo htmlspecialchars($video['niveau'] ?? 'N/A'); ?></span> | 
                            <span>Durée: <?php echo htmlspecialchars($video['duree'] ?? 'N/A'); ?></span>
                        </p>
                        <hr>
                        <div class="video-description">
                            <p><?php echo nl2br(htmlspecialchars($video['description'] ?? 'Aucune description fournie.')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="related-videos-placeholder">
                        <h4>Vidéos similaires</h4>
                        <p><em>(Contenu à venir)</em></p>
                    </div>
                    <!-- Espace pour d'autres modules, ex: commentaires, ressources -->
                </div>
            </div>
        <?php else: // Fallback si $video est null et $error_message est vide (ne devrait pas arriver) ?>
            <div class="alert alert-warning text-center">
                <p>Impossible de charger les informations de la vidéo.</p>
                <a href="videos.php" class="btn btn-primary mt-2">Retour à la liste des vidéos</a>
            </div>
        <?php endif; ?>
    </div>

<?php
require_once __DIR__ . '/includes/footer.php'; // Inclusion du footer commun
?>
