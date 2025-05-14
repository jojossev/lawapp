<?php
// view_livre.php

require_once __DIR__ . '/includes/config.php';
$page_title = "Détails du Livre";

// Récupérer et valider l'ID du livre depuis l'URL
$livre_id = $_GET['id'] ?? null;

if (!$livre_id || !filter_var($livre_id, FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "ID de livre invalide ou manquant.";
    header("Location: livres.php");
    exit;
}

$livre_id = (int)$livre_id;
$livre = null;
$page_error = null;

try {
    // Récupérer les détails du livre depuis la base de données
    $stmt = $pdo->prepare("
        SELECT 
            l.id, 
            l.titre, 
            l.description,
            l.url_document,
            l.type_document,
            l.nombre_pages,
            l.couverture_url,
            l.auteur,
            l.editeur,
            l.annee_publication,
            l.isbn,
            l.niveau,
            l.prix,
            l.statut,
            l.date_creation,
            l.date_mise_a_jour,
            lc.nom AS nom_categorie,
            CONCAT(u.prenom, ' ', u.nom) AS nom_createur,
            u.email AS email_createur 
        FROM livres l
        LEFT JOIN livre_categories lc ON l.id_categorie = lc.id
        LEFT JOIN utilisateurs u ON l.id_createur = u.id
        WHERE l.id = :id AND l.statut = 'publie'
    ");
    
    $stmt->bindParam(':id', $livre_id, PDO::PARAM_INT);
    $stmt->execute();
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livre) {
        $_SESSION['error_message'] = "Livre non trouvé ou non disponible.";
        header("Location: livres.php");
        exit;
    }

    $page_title = htmlspecialchars($livre['titre']);

    // Vérifier si l'utilisateur est connecté et s'il a accès (si le livre est payant)
    $is_logged_in = isset($_SESSION['user_id']);
    $user_id = $is_logged_in ? (int)$_SESSION['user_id'] : null;
    $user_has_access = false;
    $is_paid_content = !is_null($livre['prix']) && $livre['prix'] > 0;

    if ($is_logged_in && $is_paid_content) {
        $stmt_access = $pdo->prepare("SELECT 1 FROM acces_livres WHERE id_utilisateur = :user_id AND id_livre = :livre_id");
        $stmt_access->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_access->bindParam(':livre_id', $livre_id, PDO::PARAM_INT);
        $stmt_access->execute();
        if ($stmt_access->fetch()) {
            $user_has_access = true;
        }
    }

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du livre : " . $e->getMessage());
    $page_error = "Une erreur s'est produite lors du chargement du livre. Veuillez réessayer plus tard.";
    if (isset($_GET['debug'])) {
        echo "<pre>Erreur SQL : " . $e->getMessage() . "</pre>";
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <?php if ($page_error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($page_error); ?></div>
    <?php elseif ($livre): ?>
        <article class="livre-detail">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-3"><?php echo htmlspecialchars($livre['titre']); ?></h1>

                    <?php if (!$is_paid_content || ($is_logged_in && $user_has_access)): ?>
                        <div class="document-viewer mb-4">
                            <?php
                            $url = htmlspecialchars($livre['url_document']);
                            $type = strtolower($livre['type_document']);
                            
                            if (strpos($type, 'pdf') !== false) {
                                // Affichage PDF intégré avec contrôles de zoom et navigation
                                echo '<div class="pdf-controls mb-2">
                                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="document.getElementById(\'pdfViewer\').contentWindow.postMessage({\'action\': \'zoomIn\'}, \'*\')">Zoom +</button>
                                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="document.getElementById(\'pdfViewer\').contentWindow.postMessage({\'action\': \'zoomOut\'}, \'*\')">Zoom -</button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById(\'pdfViewer\').contentWindow.postMessage({\'action\': \'fitToPage\'}, \'*\')">Ajuster à la page</button>
                                      </div>';
                                echo '<iframe id="pdfViewer" src="' . $url . '" width="100%" height="800px" class="border rounded"></iframe>';
                            } elseif (preg_match('/\.(epub)$/i', $url)) {
                                // Pour les EPUB, proposer le téléchargement avec un lecteur EPUB recommandé
                                echo '<div class="alert alert-info">
                                        <h4>Livre au format EPUB</h4>
                                        <p>Ce livre est au format EPUB. Pour une meilleure expérience de lecture, nous vous recommandons d\'utiliser un lecteur EPUB comme :</p>
                                        <ul>
                                            <li>Calibre (Windows/Mac/Linux)</li>
                                            <li>Apple Books (iOS/Mac)</li>
                                            <li>Google Play Livres (Android)</li>
                                        </ul>
                                        <a href="' . $url . '" class="btn btn-primary mt-2" download>Télécharger l\'EPUB</a>
                                      </div>';
                            } else {
                                // Pour les autres formats, proposer le téléchargement
                                echo '<div class="alert alert-info">
                                        <h4>Document disponible au téléchargement</h4>
                                        <p>Ce document est disponible au format ' . strtoupper(pathinfo($url, PATHINFO_EXTENSION)) . '.</p>
                                        <a href="' . $url . '" class="btn btn-primary" download>Télécharger le document</a>
                                      </div>';
                            }
                            ?>
                        </div>
                        
                        <div class="livre-description">
                            <h2>À propos de ce livre</h2>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($livre['description'])); ?></p>
                            
                            <?php if (!empty($livre['auteur']) || !empty($livre['editeur'])): ?>
                                <div class="book-details mt-4">
                                    <h3>Détails de publication</h3>
                                    <ul class="list-unstyled">
                                        <?php if (!empty($livre['auteur'])): ?>
                                            <li><strong>Auteur:</strong> <?php echo htmlspecialchars($livre['auteur']); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($livre['editeur'])): ?>
                                            <li><strong>Éditeur:</strong> <?php echo htmlspecialchars($livre['editeur']); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($livre['annee_publication'])): ?>
                                            <li><strong>Année de publication:</strong> <?php echo htmlspecialchars($livre['annee_publication']); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($livre['isbn'])): ?>
                                            <li><strong>ISBN:</strong> <?php echo htmlspecialchars($livre['isbn']); ?></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($is_paid_content && !$is_logged_in): ?>
                        <div class="alert alert-info">
                            <h2>Contenu Premium</h2>
                            <p>Ce livre est réservé aux membres. <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Connectez-vous</a> ou <a href="register.php">inscrivez-vous</a> pour y accéder.</p>
                        </div>
                    <?php elseif ($is_paid_content && !$user_has_access): ?>
                        <div class="alert alert-info">
                            <h2>Contenu Premium</h2>
                            <p>Ce livre nécessite un accès premium.</p>
                            <a href="checkout.php?type=livre&id=<?php echo $livre_id; ?>" class="btn btn-primary">Obtenir l'accès (<?php echo number_format($livre['prix'], 2, ',', ' '); ?> €)</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <?php if (!empty($livre['couverture_url'])): ?>
                        <img src="<?php echo htmlspecialchars($livre['couverture_url']); ?>" 
                             class="img-fluid rounded shadow-sm mb-3" 
                             alt="Couverture de <?php echo htmlspecialchars($livre['titre']); ?>">
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informations</h5>
                            <ul class="list-unstyled">
                                <?php if (!empty($livre['nombre_pages'])): ?>
                                    <li><strong>Nombre de pages:</strong> <?php echo htmlspecialchars($livre['nombre_pages']); ?></li>
                                <?php endif; ?>
                                <li><strong>Niveau:</strong> <?php echo htmlspecialchars($livre['niveau'] ?? 'Non spécifié'); ?></li>
                                <?php if (!empty($livre['nom_categorie'])): ?>
                                    <li><strong>Catégorie:</strong> <?php echo htmlspecialchars($livre['nom_categorie']); ?></li>
                                <?php endif; ?>
                                <li><strong>Format:</strong> <?php echo strtoupper(pathinfo($livre['url_document'], PATHINFO_EXTENSION)); ?></li>
                                <li><strong>Ajouté le:</strong> <?php echo date("d/m/Y", strtotime($livre['date_creation'])); ?></li>
                                <?php if ($livre['date_mise_a_jour'] && $livre['date_mise_a_jour'] != $livre['date_creation']): ?>
                                    <li><strong>Mis à jour le:</strong> <?php echo date("d/m/Y", strtotime($livre['date_mise_a_jour'])); ?></li>
                                <?php endif; ?>
                            </ul>

                            <?php if (!$is_paid_content || ($is_logged_in && $user_has_access)): ?>
                                <?php if (strpos($type, 'pdf') !== false): ?>
                                    <a href="#document-viewer" class="btn btn-success w-100 mb-2">Lire en ligne</a>
                                <?php endif; ?>
                                <a href="<?php echo htmlspecialchars($livre['url_document']); ?>" 
                                   class="btn btn-outline-primary w-100" 
                                   download>
                                    Télécharger
                                </a>
                            <?php elseif ($is_paid_content && !$is_logged_in): ?>
                                <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary w-100">Se connecter pour lire</a>
                            <?php elseif ($is_paid_content && !$user_has_access): ?>
                                <a href="checkout.php?type=livre&id=<?php echo $livre_id; ?>" class="btn btn-primary w-100">Acheter (<?php echo number_format($livre['prix'], 2, ',', ' '); ?> €)</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    <?php else: ?>
        <div class="alert alert-warning">Le livre demandé n'a pas pu être chargé.</div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="livres.php" class="btn btn-outline-secondary">&laquo; Retour aux livres</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
