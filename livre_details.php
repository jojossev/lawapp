<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Récupérer l'ID du livre
$livre_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$livre_id) {
    header('Location: livres.php');
    exit;
}

// Récupérer les détails du livre
$stmt = $pdo->prepare("
    SELECT l.*, cl.nom as categorie
    FROM livres l
    LEFT JOIN categories_livres cl ON l.id_categorie = cl.id
    WHERE l.id = ?
");
$stmt->execute([$livre_id]);
$livre = $stmt->fetch();

if (!$livre) {
    header('Location: livres.php');
    exit;
}

// Vérifier si l'utilisateur a déjà acheté ce livre
$acces_autorise = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM transactions 
        WHERE id_utilisateur = ? AND id_produit = ? AND type_produit = 'livre' AND statut = 'complete'
    ");
    $stmt->execute([$_SESSION['user_id'], $livre_id]);
    $acces_autorise = $stmt->fetchColumn() > 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($livre['titre']) ?> - LawApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4"><?= htmlspecialchars($livre['titre']) ?></h1>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Description</h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($livre['description'])) ?></p>
                    </div>
                </div>

                <?php if ($acces_autorise): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Lire le livre</h5>
                            <a href="view_livre.php?id=<?= $livre_id ?>" class="btn btn-primary">Accéder au contenu</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Accéder au livre</h5>
                            <p class="card-text">Prix : <?= number_format($livre['prix'], 0, ',', ' ') ?> FCFA</p>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="paiement.php" method="POST">
                                    <input type="hidden" name="type" value="livre">
                                    <input type="hidden" name="id" value="<?= $livre_id ?>">
                                    <button type="submit" class="btn btn-primary">Acheter ce livre</button>
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
                            <li><strong>Auteur :</strong> <?= htmlspecialchars($livre['auteur']) ?></li>
                            <li><strong>Éditeur :</strong> <?= htmlspecialchars($livre['editeur']) ?></li>
                            <li><strong>Catégorie :</strong> <?= htmlspecialchars($livre['categorie']) ?></li>
                            <li><strong>ISBN :</strong> <?= htmlspecialchars($livre['isbn']) ?></li>
                            <li><strong>Langue :</strong> <?= htmlspecialchars($livre['langue']) ?></li>
                            <li><strong>Nombre de pages :</strong> <?= number_format($livre['nombre_pages'], 0, ',', ' ') ?></li>
                            <li><strong>Date de publication :</strong> <?= date('d/m/Y', strtotime($livre['date_publication'])) ?></li>
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
