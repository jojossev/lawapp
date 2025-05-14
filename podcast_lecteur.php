<?php
require_once 'includes/config.php'; // Charger config, session, etc.
require_once 'includes/db_connect.php'; // Charger la connexion PDO

$podcast_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$podcast = null;
$error_message = '';

if ($podcast_id <= 0) {
    $error_message = "ID de podcast invalide.";
} else {
    try {
        $sql = "SELECT 
                    p.titre_episode, 
                    p.description, 
                    p.url_audio, 
                    p.auteur, 
                    p.date_publication_episode, 
                    cp.nom_categorie 
                FROM podcasts p
                JOIN categories_podcasts cp ON p.id_categorie = cp.id 
                WHERE p.id = :id AND p.est_publie = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $podcast_id, PDO::PARAM_INT);
        $stmt->execute();
        $podcast = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$podcast) {
            $error_message = "Podcast non trouvé ou non publié.";
        }

    } catch (PDOException $e) {
        $error_message = "Erreur lors de la récupération du podcast : " . $e->getMessage();
        error_log($error_message);
    }
}

// Définir le titre de la page avant d'inclure le header
$page_title = ($podcast && !$error_message) ? htmlspecialchars($podcast['titre_episode']) : "Podcast non trouvé";
require_once 'includes/header.php'; // Inclut le header commun

?>

<div class="container page-content podcast-player-page">

    <?php if (!empty($error_message)): ?>
        <h2 class="page-title">Erreur</h2>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <p><a href="podcasts_liste.php" class="btn btn-secondary">Retour à la liste des podcasts</a></p>
    
    <?php elseif ($podcast): ?>
        <h2 class="page-title podcast-title"><?php echo htmlspecialchars($podcast['titre_episode']); ?></h2>
        
        <div class="podcast-meta">
            <span class="meta-item">Catégorie: <strong><?php echo htmlspecialchars($podcast['nom_categorie']); ?></strong></span>
            <?php if (!empty($podcast['auteur'])): ?>
            <span class="meta-item">Par: <strong><?php echo htmlspecialchars($podcast['auteur']); ?></strong></span>
            <?php endif; ?>
            <?php if (!empty($podcast['date_publication_episode'])): ?>
            <span class="meta-item">Publié le: <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($podcast['date_publication_episode']))); ?></strong></span>
            <?php endif; ?>
        </div>

        <section id="lecteur-audio" class="audio-player-section">
            <?php 
            // Construire l'URL complète du fichier audio
            // url_audio contient deja le sous-dossier 'audio/'
            $audio_url = !empty($podcast['url_audio']) ? BASE_URL . '/uploads/podcasts/' . htmlspecialchars($podcast['url_audio']) : '';
            ?>
            <?php if (!empty($audio_url)): ?>
                <audio controls preload="metadata" class="podcast-audio-player">
                    <source src="<?php echo $audio_url; ?>" type="audio/mpeg"> <!-- Ajuster le type si nécessaire (ogg, wav...) -->
                    Votre navigateur ne supporte pas la balise audio.
                </audio>
                <div class="audio-controls">
                    <label for="vitesse">Vitesse de lecture:</label>
                    <select id="vitesse" onchange="changerVitesseAudio(this.value)">
                        <option value="0.75">0.75x</option>
                        <option value="1" selected>1x</option>
                        <option value="1.25">1.25x</option>
                        <option value="1.5">1.5x</option>
                        <option value="1.75">1.75x</option>
                        <option value="2">2x</option>
                    </select>
                </div>
            <?php else: ?>
                <p class="alert alert-warning">Fichier audio non disponible pour ce podcast.</p>
            <?php endif; ?>
        </section>

        <?php if (!empty($podcast['description'])): ?>
        <section id="podcast-description" class="content-section">
            <h3>Description de l'épisode</h3>
            <p><?php echo nl2br(htmlspecialchars($podcast['description'])); ?></p>
        </section>
        <?php endif; ?>

        <section id="podcast-actions" class="content-section">
             <!-- Optionnel: Bouton Télécharger (nécessiterait une logique serveur) -->
             <!-- <a href="download_podcast.php?id=<?php echo $podcast_id; ?>" class="btn btn-secondary">Télécharger l'épisode</a> -->
             <a href="podcasts_liste.php" class="btn btn-secondary">Retour à la liste</a>
        </section>

    <?php endif; // Fin de if($podcast) ?>

</div>

<?php
require_once 'includes/footer.php'; // Inclut le footer commun
?>

<script>
// Assurer que le script s'exécute après le chargement du DOM si footer.php ne le fait pas déjà
document.addEventListener('DOMContentLoaded', (event) => {
    const audioPlayer = document.querySelector('.podcast-audio-player');
    window.changerVitesseAudio = function(vitesse) { // Rendre la fonction globale
        if(audioPlayer) {
            audioPlayer.playbackRate = parseFloat(vitesse);
        }
    }
});
</script>

