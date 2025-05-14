<?php
// view_podcast.php

require_once __DIR__ . '/includes/config.php';
$page_title = "Détails du Podcast";

// Récupérer et valider l'ID du podcast depuis l'URL
$podcast_id = $_GET['id'] ?? null;

if (!$podcast_id || !filter_var($podcast_id, FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "ID de podcast invalide ou manquant.";
    header("Location: podcasts.php");
    exit;
}

$podcast_id = (int)$podcast_id;
$podcast = null;
$page_error = null;

try {
    // Récupérer les détails du podcast depuis la base de données
    $stmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.titre, 
            p.description,
            p.url_audio,
            p.type_audio,
            p.duree,
            p.image_url,
            p.niveau,
            p.prix,
            p.statut,
            p.date_creation,
            p.date_mise_a_jour,
            pc.nom AS nom_categorie,
            CONCAT(u.prenom, ' ', u.nom) AS nom_createur,
            u.email AS email_createur 
        FROM podcasts p
        LEFT JOIN podcast_categories pc ON p.id_categorie = pc.id
        LEFT JOIN utilisateurs u ON p.id_createur = u.id
        WHERE p.id = :id AND p.statut = 'publie'
    ");
    
    $stmt->bindParam(':id', $podcast_id, PDO::PARAM_INT);
    $stmt->execute();
    $podcast = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$podcast) {
        $_SESSION['error_message'] = "Podcast non trouvé ou non disponible.";
        header("Location: podcasts.php");
        exit;
    }

    $page_title = htmlspecialchars($podcast['titre']);

    // Vérifier si l'utilisateur est connecté et s'il est inscrit (si le podcast est payant)
    $is_logged_in = isset($_SESSION['user_id']);
    $user_id = $is_logged_in ? (int)$_SESSION['user_id'] : null;
    $user_has_access = false;
    $is_paid_content = !is_null($podcast['prix']) && $podcast['prix'] > 0;

    if ($is_logged_in && $is_paid_content) {
        $stmt_access = $pdo->prepare("SELECT 1 FROM acces_podcasts WHERE id_utilisateur = :user_id AND id_podcast = :podcast_id");
        $stmt_access->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_access->bindParam(':podcast_id', $podcast_id, PDO::PARAM_INT);
        $stmt_access->execute();
        if ($stmt_access->fetch()) {
            $user_has_access = true;
        }
    }

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du podcast : " . $e->getMessage());
    $page_error = "Une erreur s'est produite lors du chargement du podcast. Veuillez réessayer plus tard.";
    if (isset($_GET['debug'])) {
        echo "<pre>Erreur SQL : " . $e->getMessage() . "</pre>";
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <?php if ($page_error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($page_error); ?></div>
    <?php elseif ($podcast): ?>
        <article class="podcast-detail">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-3"><?php echo htmlspecialchars($podcast['titre']); ?></h1>

                    <?php if (!$is_paid_content || ($is_logged_in && $user_has_access)): ?>
                        <div class="podcast-player mb-4">
                            <?php
                            $url = htmlspecialchars($podcast['url_audio']);
                            $type = strtolower($podcast['type_audio']);
                            
                            if (preg_match('/\.(mp3|wav|ogg)$/i', $url)) {
                                echo '<div class="audio-player bg-light p-3 rounded">
                                        <audio controls class="w-100">
                                            <source src="' . $url . '" type="audio/' . pathinfo($url, PATHINFO_EXTENSION) . '">
                                            Votre navigateur ne supporte pas la lecture audio.
                                        </audio>
                                      </div>';
                                
                                // Ajout des contrôles de vitesse de lecture
                                echo '<div class="playback-controls mt-2">
                                        <label for="playbackSpeed">Vitesse de lecture:</label>
                                        <select id="playbackSpeed" class="form-select form-select-sm d-inline-block w-auto ms-2" onchange="document.querySelector(\'audio\').playbackRate = this.value;">
                                            <option value="0.5">0.5×</option>
                                            <option value="0.75">0.75×</option>
                                            <option value="1" selected>1×</option>
                                            <option value="1.25">1.25×</option>
                                            <option value="1.5">1.5×</option>
                                            <option value="2">2×</option>
                                        </select>
                                      </div>';
                            } else {
                                echo '<div class="alert alert-warning">Format audio non supporté</div>';
                            }
                            ?>
                        </div>
                        
                        <div class="podcast-description">
                            <h2>Description</h2>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($podcast['description'])); ?></p>
                        </div>
                    <?php elseif ($is_paid_content && !$is_logged_in): ?>
                        <div class="alert alert-info">
                            <h2>Contenu Premium</h2>
                            <p>Ce podcast est réservé aux membres. <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Connectez-vous</a> ou <a href="register.php">inscrivez-vous</a> pour y accéder.</p>
                        </div>
                    <?php elseif ($is_paid_content && !$user_has_access): ?>
                        <div class="alert alert-info">
                            <h2>Contenu Premium</h2>
                            <p>Ce podcast nécessite un accès premium.</p>
                            <a href="checkout.php?type=podcast&id=<?php echo $podcast_id; ?>" class="btn btn-primary">Obtenir l'accès (<?php echo number_format($podcast['prix'], 2, ',', ' '); ?> €)</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <?php if (!empty($podcast['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($podcast['image_url']); ?>" 
                             class="img-fluid rounded shadow-sm mb-3" 
                             alt="<?php echo htmlspecialchars($podcast['titre']); ?>">
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informations</h5>
                            <ul class="list-unstyled">
                                <?php if (!empty($podcast['duree'])): ?>
                                    <li><strong>Durée:</strong> <?php echo htmlspecialchars($podcast['duree']); ?></li>
                                <?php endif; ?>
                                <li><strong>Niveau:</strong> <?php echo htmlspecialchars($podcast['niveau'] ?? 'Non spécifié'); ?></li>
                                <?php if (!empty($podcast['nom_categorie'])): ?>
                                    <li><strong>Catégorie:</strong> <?php echo htmlspecialchars($podcast['nom_categorie']); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($podcast['nom_createur'])): ?>
                                    <li><strong>Auteur:</strong> <?php echo htmlspecialchars($podcast['nom_createur']); ?></li>
                                <?php endif; ?>
                                <li><strong>Publié le:</strong> <?php echo date("d/m/Y", strtotime($podcast['date_creation'])); ?></li>
                                <?php if ($podcast['date_mise_a_jour'] && $podcast['date_mise_a_jour'] != $podcast['date_creation']): ?>
                                    <li><strong>Mis à jour le:</strong> <?php echo date("d/m/Y", strtotime($podcast['date_mise_a_jour'])); ?></li>
                                <?php endif; ?>
                            </ul>

                            <?php if (!$is_paid_content || ($is_logged_in && $user_has_access)): ?>
                                <a href="#podcast-player" class="btn btn-success w-100">Écouter le podcast</a>
                            <?php elseif ($is_paid_content && !$is_logged_in): ?>
                                <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary w-100">Se connecter pour écouter</a>
                            <?php elseif ($is_paid_content && !$user_has_access): ?>
                                <a href="checkout.php?type=podcast&id=<?php echo $podcast_id; ?>" class="btn btn-primary w-100">Acheter (<?php echo number_format($podcast['prix'], 2, ',', ' '); ?> €)</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!$is_paid_content || ($is_logged_in && $user_has_access)): ?>
                        <div class="card mt-3">
                            <div class="card-body">
                                <h5 class="card-title">Téléchargement</h5>
                                <a href="<?php echo htmlspecialchars($podcast['url_audio']); ?>" 
                                   class="btn btn-outline-primary w-100" 
                                   download>
                                    Télécharger le podcast
                                </a>
                                <small class="text-muted mt-2 d-block">
                                    Pour écouter hors connexion
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php else: ?>
        <div class="alert alert-warning">Le podcast demandé n'a pas pu être chargé.</div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="podcasts.php" class="btn btn-outline-secondary">&laquo; Retour aux podcasts</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
