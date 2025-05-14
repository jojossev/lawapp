<?php
$page_title = "Recherche de Lois"; // Titre spécifique pour cette page
require_once 'includes/header.php'; // Inclut le header commun

// Logique de recherche de lois
$terme_recherche = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
$resultats_lois = []; // Tableau pour stocker les résultats de la recherche

if (!empty($terme_recherche)) {
    // Simuler une recherche (à remplacer par une vraie recherche en BDD)
    $lois_fictives = [
        [
            'id' => 'loi001',
            'titre' => 'Loi sur la protection des données personnelles',
            'reference' => 'Loi n° 78-17 du 6 janvier 1978',
            'resume' => 'Relative à l\'informatique, aux fichiers et aux libertés.',
            'date_promulgation' => '06/01/1978',
            'domaines' => ['Protection des données', 'Numérique']
        ],
        [
            'id' => 'loi002',
            'titre' => 'Code de la consommation - Pratiques commerciales déloyales',
            'reference' => 'Article L121-1 et suivants',
            'resume' => 'Interdiction des pratiques commerciales trompeuses ou agressives.',
            'date_promulgation' => 'Modifié régulièrement',
            'domaines' => ['Droit de la consommation', 'Commerce']
        ],
        [
            'id' => 'loi003',
            'titre' => 'Loi pour une République numérique',
            'reference' => 'Loi n° 2016-1321 du 7 octobre 2016',
            'resume' => 'Favorise la circulation des données et du savoir, la protection des individus à l\'ère numérique et l\'accès au numérique.',
            'date_promulgation' => '07/10/2016',
            'domaines' => ['Numérique', 'Gouvernance Ouverte', 'Protection des données']
        ]
    ];

    foreach ($lois_fictives as $loi) {
        if (stripos($loi['titre'], $terme_recherche) !== false || stripos($loi['reference'], $terme_recherche) !== false || stripos($loi['resume'], $terme_recherche) !== false) {
            $resultats_lois[] = $loi;
        }
    }
}

?>

    <!-- Contenu spécifique à la page de recherche de lois -->
    <div class="container page-content">
        <h2 class="page-title">Moteur de Recherche de Lois</h2>

        <section class="search-form-section card">
            <form action="lois_recherche.php" method="GET" class="search-form">
                <input type="search" name="q" id="terme-recherche-loi" placeholder="Entrez mots-clés, numéro d'article..." value="<?php echo $terme_recherche; ?>" class="form-control form-control-lg">
                <button type="submit" class="btn btn-primary btn-lg">Rechercher</button>
            </form>
        </section>

        <section class="search-results-section">
            <h3 class="section-title">Résultats de la recherche <?php echo !empty($terme_recherche) ? 'pour "<em>' . $terme_recherche . '</em>"' : ''; ?></h3>
            
            <?php if (!empty($terme_recherche) && empty($resultats_lois)): ?>
                <p class="empty-state">Aucun résultat trouvé pour "<?php echo $terme_recherche; ?>". Veuillez essayer d'autres mots-clés.</p>
            <?php elseif (empty($terme_recherche) && isset($_GET['q'])): ?>
                 <p class="empty-state">Veuillez entrer un terme de recherche.</p>
            <?php elseif (!empty($resultats_lois)): ?>
                <div class="law-results-list">
                    <?php foreach ($resultats_lois as $loi): ?>
                    <div class="law-result-item card">
                        <h4><a href="loi_detail.php?id=<?php echo htmlspecialchars($loi['id']); ?>"><?php echo htmlspecialchars($loi['titre']); ?></a></h4>
                        <p class="reference"><strong>Référence :</strong> <?php echo htmlspecialchars($loi['reference']); ?></p>
                        <p class="promulgation"><strong>Promulgation :</strong> <?php echo htmlspecialchars($loi['date_promulgation']); ?></p>
                        <p class="resume"><?php echo htmlspecialchars($loi['resume']); ?></p>
                        <div class="domaines-tags">
                            <?php foreach ($loi['domaines'] as $domaine): ?>
                                <span class="tag"><?php echo htmlspecialchars($domaine); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <a href="loi_detail.php?id=<?php echo htmlspecialchars($loi['id']); ?>" class="btn btn-secondary btn-sm">Voir le détail</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">Utilisez le formulaire ci-dessus pour rechercher des textes de loi.</p>
            <?php endif; ?>
        </section>

        <section class="other-actions-section card">
            <p><a href="lois_comparateur.php" class="btn btn-outline-primary">Accéder au comparateur de lois</a></p>
        </section>
    </div>

<?php
require_once 'includes/footer.php'; // Inclut le footer commun
?>
