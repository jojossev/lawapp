<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Récupérer l'ID du podcast
$podcast_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$podcast_id) {
    header('Location: podcasts.php');
    exit;
}

// Récupérer les détails du podcast
$stmt = $pdo->prepare("
    SELECT p.*, cp.nom as categorie
    FROM podcasts p
    LEFT JOIN categories_podcasts cp ON p.id_categorie = cp.id
    WHERE p.id = ?
");
$stmt->execute([$podcast_id]);
$podcast = $stmt->fetch();

if (!$podcast) {
    header('Location: podcasts.php');
    exit;
}

// Vérifier si l'utilisateur a déjà acheté ce podcast
$acces_autorise = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM transactions 
        WHERE id_utilisateur = ? AND id_produit = ? AND type_produit = 'podcast' AND statut = 'complete'
    ");
    $stmt->execute([$_SESSION['user_id'], $podcast_id]);
    $acces_autorise = $stmt->fetchColumn() > 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($podcast['titre']) ?> - LawApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4"><?= htmlspecialchars($podcast['titre']) ?></h1>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Description</h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($podcast['description'])) ?></p>
                    </div>
                </div>

                <?php if ($acces_autorise): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Écouter le podcast</h5>
                            <audio controls class="w-100">
                                <source src="<?= htmlspecialchars($podcast['audio_url']) ?>" type="audio/mpeg">
                                Votre navigateur ne supporte pas la lecture audio.
                            </audio>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Accéder au podcast</h5>
                            <p class="card-text">Prix : <?= number_format($podcast['prix'], 0, ',', ' ') ?> FCFA</p>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="paiement.php" method="POST">
                                    <input type="hidden" name="type" value="podcast">
                                    <input type="hidden" name="id" value="<?= $podcast_id ?>">
                                    <button type="submit" class="btn btn-primary">Acheter ce podcast</button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">Connectez-vous pour acheter</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Informations</h5>
                        <ul class="list-unstyled">
                            <li><strong>Catégorie :</strong> <?= htmlspecialchars($podcast['categorie']) ?></li>
                            <li><strong>Durée :</strong> <?= floor($podcast['duree']/60) ?>:<?= str_pad($podcast['duree']%60, 2, '0', STR_PAD_LEFT) ?></li>
                            <li><strong>Format :</strong> <?= htmlspecialchars($podcast['format']) ?></li>
                            <li><strong>Nombre d'écoutes :</strong> <?= number_format($podcast['nombre_ecoutes'], 0, ',', ' ') ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
