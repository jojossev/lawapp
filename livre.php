<?php
$page_title = "Détail du livre";
require_once 'includes/header.php';

// Récupérer l'ID du livre
$livre_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$livre_id) {
    $_SESSION['error_message'] = "Identifiant du livre manquant.";
    header('Location: catalogue.php');
    exit;
}

try {
    // Récupérer les détails du livre
    $stmt = $pdo->prepare("
        SELECT l.*, 
               COALESCE(AVG(al.note), 0) as note_moyenne,
               COUNT(DISTINCT al.id) as nombre_avis,
               COALESCE(achat.id, 0) as est_achete
        FROM livres l
        LEFT JOIN avis_livres al ON l.id = al.id_livre
        LEFT JOIN achats_livres achat ON l.id = achat.id_livre 
            AND achat.id_utilisateur = :user_id 
            AND achat.statut = 'complete'
        WHERE l.id = :id
        GROUP BY l.id
    ");
    
    $stmt->execute([
        ':id' => $livre_id,
        ':user_id' => $_SESSION['user_id'] ?? 0
    ]);
    
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$livre) {
        throw new Exception("Livre non trouvé");
    }

    // Récupérer les avis
    $stmt = $pdo->prepare("
        SELECT al.*, u.prenom, u.nom
        FROM avis_livres al
        JOIN utilisateurs u ON al.id_utilisateur = u.id
        WHERE al.id_livre = :livre_id
        ORDER BY al.date_creation DESC
        LIMIT 5
    ");
    
    $stmt->execute([':livre_id' => $livre_id]);
    $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error_message'] = "Une erreur s'est produite lors du chargement du livre. Veuillez réessayer plus tard.";
    error_log("Erreur livre.php pour livre ID {$livre_id}: " . $e->getMessage());
    header('Location: catalogue.php');
    exit;
}
?>

<div class="container mt-4">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Image et informations principales -->
        <div class="col-md-4">
            <div class="card">
                <img src="<?php echo htmlspecialchars($livre['image_url'] ?? 'assets/images/default-book.jpg'); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($livre['titre']); ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($livre['prix']); ?> USD</h5>
                        <?php if ($livre['est_achete']): ?>
                            <a href="<?php echo htmlspecialchars($livre['fichier_url']); ?>" 
                               class="btn btn-success" 
                               target="_blank">
                                <i class="fas fa-download"></i> Télécharger
                            </a>
                        <?php else: ?>
                            <a href="paiement.php?type=livre&id=<?php echo $livre['id']; ?>" 
                               class="btn btn-primary">
                                Acheter
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Informations détaillées -->
                    <div class="book-details mt-4">
                        <p><strong>Auteur:</strong> <?php echo htmlspecialchars($livre['auteur']); ?></p>
                        <p><strong>Éditeur:</strong> <?php echo htmlspecialchars($livre['editeur']); ?></p>
                        <p><strong>Date de publication:</strong> <?php echo date('d/m/Y', strtotime($livre['date_publication'])); ?></p>
                        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($livre['isbn']); ?></p>
                        <p><strong>Nombre de pages:</strong> <?php echo htmlspecialchars($livre['nombre_pages']); ?></p>
                        <p><strong>Langue:</strong> <?php echo htmlspecialchars($livre['langue']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description et avis -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title"><?php echo htmlspecialchars($livre['titre']); ?></h2>
                    
                    <!-- Note moyenne -->
                    <div class="rating-summary mb-4">
                        <div class="d-flex align-items-center">
                            <div class="rating-stars">
                                <?php
                                $note_moyenne = round($livre['note_moyenne']);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $note_moyenne) {
                                        echo '<i class="fas fa-star text-warning"></i>';
                                    } else {
                                        echo '<i class="far fa-star text-warning"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="ms-2"><?php echo number_format($livre['note_moyenne'], 1); ?>/5</span>
                            <span class="text-muted ms-2">(<?php echo $livre['nombre_avis']; ?> avis)</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <h3>Description</h3>
                    <div class="book-description mb-4">
                        <?php echo nl2br(htmlspecialchars($livre['description'])); ?>
                    </div>

                    <!-- Avis -->
                    <h3>Avis des lecteurs</h3>
                    <?php if ($livre['est_achete']): ?>
                        <button class="btn btn-outline-primary mb-4" 
                                data-bs-toggle="modal" 
                                data-bs-target="#ajoutAvisModal">
                            Donner mon avis
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($avis)): ?>
                        <div class="reviews-list">
                            <?php foreach ($avis as $avis_item): ?>
                                <div class="review-item mb-4">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo htmlspecialchars($avis_item['prenom'] . ' ' . $avis_item['nom']); ?></strong>
                                            <div class="rating-stars">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $avis_item['note']) {
                                                        echo '<i class="fas fa-star text-warning"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star text-warning"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($avis_item['date_creation'])); ?>
                                        </small>
                                    </div>
                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($avis_item['commentaire'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucun avis pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout Avis -->
<?php if ($livre['est_achete']): ?>
<div class="modal fade" id="ajoutAvisModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Donner mon avis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="ajouter_avis.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="livre_id" value="<?php echo $livre['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <div class="rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="note" value="<?php echo $i; ?>" 
                                       id="star<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>">
                                    <i class="far fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Commentaire</label>
                        <textarea class="form-control" id="commentaire" name="commentaire" 
                                  rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Publier</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
