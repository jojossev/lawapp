<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Nos Livres Juridiques";
require_once __DIR__ . '/includes/config.php'; // Contient la connexion PDO $pdo et BASE_URL
require_once __DIR__ . '/includes/header.php';

// Récupérer les catégories pour le filtrage (optionnel, mais utile)
$categories = [];
$selected_category_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : null;

try {
    $stmt_cat = $pdo->query("SELECT id, nom FROM categories_livres ORDER BY nom");
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gérer l'erreur discrètement ou loguer
    error_log("Erreur récupération catégories livres: " . $e->getMessage());
}

// Récupérer les livres publiés
$livres = [];
$error_message = null;
try {
    $sql = "SELECT 
                l.id, 
                l.titre, 
                l.auteur, 
                COALESCE(l.image_url, l.url_image) AS image_url,
                l.statut,
                cl.date_creation,
                cl.nom AS nom_categorie_livre
            FROM livres l
            LEFT JOIN categories_livres cl ON l.id_categorie = cl.id
            WHERE l.statut = 'publie'";
    
    $params = [];
    if ($selected_category_id) {
        $sql .= " AND l.id_categorie = :category_id";
        $params[':category_id'] = $selected_category_id;
    }
    
    $sql .= " ORDER BY l.date_creation DESC"; // Ou date_creation DESC

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $livres = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Erreur SQL: " . $e->getMessage();
    error_log("Erreur récupération livres: " . $e->getMessage());
}

?>

<div class="container mt-4">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <?php if ($selected_category_id && !empty($categories)): ?>
            <?php foreach ($categories as $cat) { 
                if ($cat['id'] == $selected_category_id) {
                    echo "<p class=\"lead\">Filtré par : " . htmlspecialchars($cat['nom']) . " <a href=\"livres.php\" class=\"small\">(Voir tout)</a></p>";
                    break;
                }
            } ?>
        <?php endif; ?>
    </div>

    <!-- Filtres (optionnel) -->
    <?php if (!empty($categories)): ?>
    <div class="filters mb-4">
        <span>Filtrer par catégorie:</span>
        <a href="livres.php" class="btn btn-sm <?php echo !$selected_category_id ? 'btn-primary' : 'btn-outline-secondary'; ?>">Toutes</a>
        <?php foreach ($categories as $cat): ?>
            <a href="livres.php?categorie=<?php echo $cat['id']; ?>" 
               class="btn btn-sm <?php echo ($selected_category_id == $cat['id']) ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                <?php echo htmlspecialchars($cat['nom']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (empty($livres) && !$error_message): ?>
        <div class="alert alert-info">Aucun livre n'est disponible <?php echo $selected_category_id ? 'dans cette catégorie' : '' ; ?> pour le moment.</div>
    <?php elseif (!empty($livres)): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($livres as $livre): ?>
                <div class="col">
                    <div class="card h-100 book-card">
                        <a href="view_livre.php?id=<?php echo $livre['id']; ?>">
                            <?php 
                            $imagePath = !empty($livre['image_url']) ? BASE_URL . '/' . htmlspecialchars($livre['image_url']) : BASE_URL . '/assets/images/placeholder_book.png'; // Placeholder
                            ?>
                            <img src="<?php echo $imagePath; ?>" class="card-img-top book-cover" alt="Couverture de <?php echo htmlspecialchars($livre['titre']); ?>">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="view_livre.php?id=<?php echo $livre['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($livre['titre']); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small mb-2">
                                <?php echo !empty($livre['auteur']) ? 'Par ' . htmlspecialchars($livre['auteur']) : 'Auteur inconnu'; ?>
                            </p>
                            <?php if (!empty($livre['nom_categorie'])): ?>
                                <p class="card-text small"><span class="badge bg-secondary"><?php echo htmlspecialchars($livre['nom_categorie']); ?></span></p>
                            <?php endif; ?>
                            <div class="mt-auto pt-2">
                                <a href="view_livre.php?id=<?php echo $livre['id']; ?>" class="btn btn-sm btn-outline-primary">Voir détails</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div> <!-- /container -->

<?php
require_once __DIR__ . '/includes/footer.php';
?>
