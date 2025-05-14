<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Récupérer l'ID du cours
$cours_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$cours_id) {
    header('Location: cours.php');
    exit;
}

// Récupérer les détails du cours
$stmt = $pdo->prepare("
    SELECT c.*, cc.nom as categorie
    FROM cours c
    LEFT JOIN categories_cours cc ON c.id_categorie = cc.id
    WHERE c.id = ?
");
$stmt->execute([$cours_id]);
$cours = $stmt->fetch();

if (!$cours) {
    header('Location: cours.php');
    exit;
}

// Vérifier si l'utilisateur a déjà acheté ce cours
$acces_autorise = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM transactions 
        WHERE id_utilisateur = ? AND id_produit = ? AND type_produit = 'cours' AND statut = 'complete'
    ");
    $stmt->execute([$_SESSION['user_id'], $cours_id]);
    $acces_autorise = $stmt->fetchColumn() > 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($cours['titre']) ?> - LawApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4"><?= htmlspecialchars($cours['titre']) ?></h1>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Description</h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($cours['description'])) ?></p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Objectifs du cours</h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($cours['objectifs'])) ?></p>
                    </div>
                </div>

                <?php if ($acces_autorise): ?>
                    <div class="alert alert-success">
                        Vous avez accès à ce cours. <a href="view_cours.php?id=<?= $cours_id ?>" class="btn btn-primary">Commencer le cours</a>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Accéder au cours</h5>
                            <p class="card-text">Prix : <?= number_format($cours['prix'], 0, ',', ' ') ?> FCFA</p>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="paiement.php" method="POST">
                                    <input type="hidden" name="type" value="cours">
                                    <input type="hidden" name="id" value="<?= $cours_id ?>">
                                    <button type="submit" class="btn btn-primary">Acheter ce cours</button>
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
                            <li><strong>Catégorie :</strong> <?= htmlspecialchars($cours['categorie']) ?></li>
                            <li><strong>Niveau :</strong> <?= htmlspecialchars($cours['niveau']) ?></li>
                            <li><strong>Durée :</strong> <?= $cours['duree'] ?> heures</li>
                            <li><strong>Prérequis :</strong> <?= htmlspecialchars($cours['prerequis']) ?></li>
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
