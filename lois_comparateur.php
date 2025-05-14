<?php
$page_title = "Comparateur de Lois";
require_once 'includes/header.php';
?>

<div class="container page-content">
    <h2 class="page-title">Comparateur de Lois</h2>
    <section id="selection-lois">
        <p>Sélectionnez les lois ou articles à comparer (max 2 ou 3 pour une bonne lisibilité).</p>
        <!-- Formulaires de sélection PHP/JS -->
        <input type="text" placeholder="ID ou nom de la loi 1" class="form-control mb-2">
        <input type="text" placeholder="ID ou nom de la loi 2" class="form-control mb-2">
        <button class="btn btn-primary">Comparer</button>
    </section>
    <section id="affichage-comparaison" class="mt-4">
        <h3>Comparaison</h3>
        <div class="comparateur-container">
            <div class="colonne-loi" id="loi1-contenu">
                <h4>Loi/Article 1</h4>
                <p>Contenu de la loi/article 1 s'affichera ici.</p>
            </div>
            <div class="colonne-loi" id="loi2-contenu">
                <h4>Loi/Article 2</h4>
                <p>Contenu de la loi/article 2 s'affichera ici.</p>
            </div>
        </div>
    </section>
    <p class="mt-4"><a href="lois_recherche.php">Retour à la recherche de lois</a></p>
</div>

<?php
require_once 'includes/footer.php';
?>
