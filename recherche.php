<?php
$page_title = "Résultats de Recherche";
require_once 'includes/header.php';

// Récupérer le terme de recherche de l'URL (paramètre 'q_hero' du formulaire d'accueil, ou 'q' d'un autre formulaire)
$query = "";
if (isset($_GET['q_hero'])) {
    $query = trim($_GET['q_hero']);
} elseif (isset($_GET['q'])) {
    $query = trim($_GET['q']);
}

?>

<div class="container page-content">
    <h2 class="page-title">Recherche</h2>

    <div class="search-form-page mb-4">
        <form action="recherche.php" method="get" class="d-flex">
            <input type="search" name="q" class="form-control form-control-lg me-2" placeholder="Rechercher dans tout le site..." value="<?php echo htmlspecialchars($query); ?>" aria-label="Terme de recherche">
            <button class="btn btn-primary btn-lg" type="submit">Rechercher</button>
        </form>
    </div>

    <?php if (!empty($query)):
    ?>
        <p class="lead">Terme recherché : <strong><?php echo htmlspecialchars($query); ?></strong></p>
        <div class="alert alert-warning mt-4" role="alert">
            <h4 class="alert-heading">Fonctionnalité en cours de développement</h4>
            <p>La recherche avancée sur l'ensemble du site est en cours de construction et sera bientôt disponible. Merci de votre patience !</p>
        </div>
    <?php else: ?>
        <p class="lead">Veuillez entrer un terme dans la barre de recherche ci-dessus pour trouver du contenu sur LawApp.</p>
    <?php endif; ?>

</div>

<?php
require_once 'includes/footer.php';
?>
