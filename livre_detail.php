<?php
require_once __DIR__ . '/includes/config.php'; // $pdo, BASE_URL, session_start()

// 1. Récupérer et valider l'ID du livre
$livre_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($livre_id <= 0) {
    // Rediriger vers la liste si ID invalide ou manquant
    header('Location: livres.php');
    exit;
}

// 2. Récupérer les détails du livre depuis la BDD
$livre = null;
$error_message = null;
try {
    $sql = "SELECT 
                l.id, 
                l.titre, 
                l.auteur, 
                l.description,
                l.image_url,
                l.fichier_pdf_url,
                cl.nom_categorie
            FROM livres l
            LEFT JOIN categories_livres cl ON l.id_categorie_livre = cl.id
            WHERE l.id = :id AND l.statut = 'publie'"; // Seulement les livres publiés
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $livre_id, PDO::PARAM_INT);
    $stmt->execute();
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livre) {
        // Si le livre n'existe pas ou n'est pas publié
        $error_message = "Le livre demandé n'a pas été trouvé ou n'est pas disponible.";
        // On pourrait définir un code de statut 404 ici
        // http_response_code(404);
    }

} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des informations du livre.";
    error_log("Erreur détail livre (ID: {$livre_id}): " . $e->getMessage());
    // http_response_code(500);
}

// Définir le titre de la page après avoir récupéré les données
$page_title = $livre ? htmlspecialchars($livre['titre']) : "Livre non trouvé";
require_once __DIR__ . '/includes/header.php';

?>

<div class="container mt-5">

    <?php if ($error_message): ?>
        <div class="alert alert-warning"><?php echo htmlspecialchars($error_message); ?></div>
        <p><a href="livres.php" class="btn btn-primary">Retour à la liste des livres</a></p>
    <?php elseif ($livre): ?>
        <div class="row">
            <div class="col-md-4 text-center">
                <?php 
                $imagePath = !empty($livre['image_url']) 
                             ? BASE_URL . '/' . htmlspecialchars($livre['image_url']) 
                             : BASE_URL . '/assets/images/placeholder_book_large.png'; // Placeholder plus grand
                ?>
                <img src="<?php echo $imagePath; ?>" class="img-fluid rounded shadow-sm mb-3 book-detail-cover" alt="Couverture de <?php echo htmlspecialchars($livre['titre']); ?>">
            </div>
            <div class="col-md-8">
                <h1><?php echo htmlspecialchars($livre['titre']); ?></h1>
                <?php if (!empty($livre['auteur'])): ?>
                    <p class="lead">Par <strong><?php echo htmlspecialchars($livre['auteur']); ?></strong></p>
                <?php endif; ?>
                <?php if (!empty($livre['nom_categorie'])): ?>
                    <p>Catégorie : <span class="badge bg-secondary"><?php echo htmlspecialchars($livre['nom_categorie']); ?></span></p>
                <?php endif; ?>
                
                <hr>

                <h2>Description</h2>
                <div class="book-description">
                    <?php echo !empty($livre['description']) ? nl2br(htmlspecialchars($livre['description'])) : '<p><em>Aucune description disponible.</em></p>'; ?>
                </div>

                <?php if (!empty($livre['fichier_pdf_url'])): ?>
                    <hr>
                    <div class="mt-4">
                        <a href="<?php echo htmlspecialchars($livre['fichier_pdf_url']); ?>" target="_blank" class="btn btn-success">
                            <i class="fas fa-file-pdf"></i> Consulter le PDF
                        </a>
                        <small class="d-block text-muted mt-1">Le PDF s'ouvrira dans un nouvel onglet.</small>
                        <!-- TODO: Ajouter ici logique d'achat ou d'accès restreint si nécessaire -->
                    </div>
                <?php endif; ?>
                
                <hr>
                 <a href="livres.php" class="btn btn-outline-secondary mt-3">Retour à la liste</a>

            </div>
        </div>
    <?php endif; ?>

</div> <!-- /container -->

<?php
require_once __DIR__ . '/includes/footer.php';
?>
