<?php
require_once 'admin_auth_check.php';
$page_title = "Détail du livre";
require_once 'admin_header.php';

// Récupérer l'ID du livre
$livre_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$livre_id) {
    $_SESSION['error_message'] = "Identifiant du livre manquant.";
    header('Location: manage_livres.php');
    exit;
}

try {
    // Récupérer les détails du livre
    $stmt = $pdo->prepare("
        SELECT l.*, 
               COALESCE(AVG(al.note), 0) as note_moyenne,
               COUNT(DISTINCT al.id) as nombre_avis,
               COUNT(DISTINCT achat.id) as nombre_achats,
               SUM(CASE WHEN achat.statut = 'complete' THEN achat.prix_paye ELSE 0 END) as revenu_total
        FROM livres l
        LEFT JOIN avis_livres al ON l.id = al.id_livre
        LEFT JOIN achats_livres achat ON l.id = achat.id_livre
        WHERE l.id = :id
        GROUP BY l.id
    ");
    
    $stmt->execute([':id' => $livre_id]);
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$livre) {
        throw new Exception("Livre non trouvé");
    }

    // Récupérer les derniers achats
    $stmt = $pdo->prepare("
        SELECT al.*, u.prenom, u.nom, u.email
        FROM achats_livres al
        JOIN utilisateurs u ON al.id_utilisateur = u.id
        WHERE al.id_livre = :livre_id
        ORDER BY al.date_achat DESC
        LIMIT 10
    ");
    
    $stmt->execute([':livre_id' => $livre_id]);
    $achats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les avis
    $stmt = $pdo->prepare("
        SELECT al.*, u.prenom, u.nom
        FROM avis_livres al
        JOIN utilisateurs u ON al.id_utilisateur = u.id
        WHERE al.id_livre = :livre_id
        ORDER BY al.date_creation DESC
        LIMIT 10
    ");
    
    $stmt->execute([':livre_id' => $livre_id]);
    $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error_message'] = "Une erreur s'est produite lors du chargement du livre.";
    error_log("Erreur livre_detail.php pour livre ID {$livre_id}: " . $e->getMessage());
    header('Location: manage_livres.php');
    exit;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Détail du livre</h1>
    
    <div class="row mt-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h2 class="display-4"><?php echo number_format($livre['nombre_achats']); ?></h2>
                    <div>Ventes totales</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h2 class="display-4">$<?php echo number_format($livre['revenu_total'], 2); ?></h2>
                    <div>Revenus générés</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h2 class="display-4"><?php echo number_format($livre['note_moyenne'], 1); ?></h2>
                    <div>Note moyenne</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h2 class="display-4"><?php echo number_format($livre['nombre_avis']); ?></h2>
                    <div>Nombre d'avis</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations du livre -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-book me-1"></i>
                        Informations du livre
                    </div>
                    <a href="livre_form.php?id=<?php echo $livre['id']; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="<?php echo htmlspecialchars($livre['image_url'] ?? '../assets/images/default-book.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($livre['titre']); ?>"
                             class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <h5 class="card-title"><?php echo htmlspecialchars($livre['titre']); ?></h5>
                    <p class="card-text">
                        <strong>Auteur:</strong> <?php echo htmlspecialchars($livre['auteur']); ?><br>
                        <strong>Prix:</strong> $<?php echo number_format($livre['prix'], 2); ?><br>
                        <strong>Statut:</strong> 
                        <span class="badge <?php echo $livre['statut'] === 'disponible' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo ucfirst($livre['statut']); ?>
                        </span><br>
                        <strong>Date de création:</strong> <?php echo date('d/m/Y H:i', strtotime($livre['date_creation'])); ?>
                    </p>
                    <div class="mt-3">
                        <h6>Description</h6>
                        <p><?php echo nl2br(htmlspecialchars($livre['description'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Derniers achats -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shopping-cart me-1"></i>
                    Derniers achats
                </div>
                <div class="card-body">
                    <?php if (!empty($achats)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Utilisateur</th>
                                        <th>Prix payé</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($achats as $achat): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($achat['date_achat'])); ?></td>
                                            <td>
                                                <span data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($achat['email']); ?>">
                                                    <?php echo htmlspecialchars($achat['prenom'] . ' ' . $achat['nom']); ?>
                                                </span>
                                            </td>
                                            <td>$<?php echo number_format($achat['prix_paye'], 2); ?></td>
                                            <td>
                                                <span class="badge <?php echo $achat['statut'] === 'complete' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo ucfirst($achat['statut']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucun achat pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Derniers avis -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-comments me-1"></i>
                    Derniers avis
                </div>
                <div class="card-body">
                    <?php if (!empty($avis)): ?>
                        <?php foreach ($avis as $avis_item): ?>
                            <div class="border-bottom mb-3 pb-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?php echo htmlspecialchars($avis_item['prenom'] . ' ' . $avis_item['nom']); ?></strong>
                                        <div class="text-warning">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $avis_item['note']) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($avis_item['date_creation'])); ?>
                                    </small>
                                </div>
                                <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($avis_item['commentaire'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Aucun avis pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialiser les tooltips Bootstrap
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>

<?php require_once 'admin_footer.php'; ?>
