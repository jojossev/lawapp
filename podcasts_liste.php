<?php
$page_title = "Podcasts Juridiques"; // Titre spécifique pour cette page
require_once 'includes/config.php'; // Charger la configuration (BASE_URL, etc.)
require_once 'includes/db_connect.php'; // Charger la connexion PDO
require_once 'includes/header.php'; // Inclut le header commun

$podcasts_a_afficher = [];
$categories_existantes = [];
$error_message = '';
$categorie_filtree_nom = $_GET['categorie'] ?? null;

// Fonction pour convertir secondes en MM:SS ou HH:MM:SS
function formatDuration($seconds) {
    if (!is_numeric($seconds) || $seconds < 0) {
        return 'N/A';
    }
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    if ($hours > 0) {
        return sprintf("%d:%02d:%02d", $hours, $minutes, $secs);
    } else {
        return sprintf("%d:%02d min", $minutes, $secs); // Ou juste %d min si secondes pas utiles
    }
}

try {
    // 1. Récupérer les catégories existantes pour le filtre
    $stmt_cat = $pdo->query("SELECT id, nom FROM categories_podcasts ORDER BY nom ASC");
    $categories_existantes = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

    // 2. Construire la requête pour les podcasts
    $sql = "SELECT 
                p.id, 
                p.titre, 
                p.description, 
                p.duree,
                p.url_audio,
                p.statut,
                p.date_creation,
                cp.nom AS nom_categorie 
            FROM podcasts p
            JOIN categories_podcasts cp ON p.id_categorie = cp.id 
            WHERE p.statut = 'publie'";

    $params = [];

    // 3. Ajouter le filtre par catégorie si spécifié
    if ($categorie_filtree_nom !== null) {
        $sql .= " AND cp.nom = :nom_categorie";
        $params[':nom_categorie'] = $categorie_filtree_nom;
    }

    $sql .= " ORDER BY p.date_creation DESC"; // Trier par date de création

    // 4. Exécuter la requête
    $stmt_pod = $pdo->prepare($sql);
    $stmt_pod->execute($params);
    $podcasts_a_afficher = $stmt_pod->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des données : " . $e->getMessage();
    // Logguer l'erreur pour le débogage
    error_log($error_message);
}

// --- Définition du titre pour cette page ---
$page_title = $categorie_filtree_nom ? "Podcasts - " . htmlspecialchars($categorie_filtree_nom) : "Tous les Podcasts";

require_once 'includes/header.php';
?>

    <!-- Contenu spécifique à la page liste des podcasts -->
    <div class="container page-content">
        <h2 class="page-title">Nos Podcasts Juridiques</h2>

        <div class="layout-sidebar-main">
            <aside class="sidebar filters-sidebar">
                <h3>Filtrer par catégorie</h3>
                <?php if (!empty($categories_existantes)): ?>
                <ul class="category-filter-list">
                    <li><a href="podcasts_liste.php" class="<?php echo ($categorie_filtree_nom === null) ? 'active' : ''; ?>">Toutes les catégories</a></li>
                    <?php foreach ($categories_existantes as $categorie): ?>
                        <li>
                            <a href="podcasts_liste.php?categorie=<?php echo urlencode($categorie['nom']); ?>" 
                               class="<?php echo ($categorie_filtree_nom === $categorie['nom']) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($categorie['nom']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <p>Aucune catégorie de podcast disponible.</p>
                <?php endif; ?>
            </aside>

            <section class="main-content podcast-listing">
                <?php if (!empty($error_message)): ?>
                    <p class="alert alert-warning"><?php echo htmlspecialchars($error_message); ?></p>
                <?php elseif (!empty($podcasts_a_afficher)): ?>
                    <div class="row row-cols-1 g-4"> 
                        <?php foreach ($podcasts_a_afficher as $podcast): ?>
                            <div class="col">
                                <div class="card mb-3 shadow-sm overflow-hidden"> 
                                    <div class="row g-0">
                                        <div class="col-md-4"> 
                                            <?php 
                                            // Construire l'URL de l'image
                                            $image_path = !empty($podcast['image_url']) ? BASE_URL . '/uploads/podcasts/' . htmlspecialchars($podcast['image_url']) : BASE_URL . '/images/placeholder_podcast.png'; 
                                            ?>
                                            <a href="view_podcast.php?id=<?php echo $podcast['id']; ?>" class="d-block h-100">
                                                <img src="<?php echo $image_path; ?>" class="img-fluid rounded-start w-100" alt="<?php echo htmlspecialchars($podcast['titre']); ?>" style="object-fit: cover;">
                                            </a>
                                        </div>
                                        <div class="col-md-8"> 
                                            <div class="card-body d-flex flex-column h-100">
                                                <h5 class="card-title"><a href="view_podcast.php?id=<?php echo $podcast['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($podcast['titre']); ?></a></h5>
                                                <p class="card-text small text-muted mb-2">
                                                    Catégorie: <?php echo htmlspecialchars($podcast['nom_categorie']); ?>
                                                </p>
                                                <p class="card-text flex-grow-1">
                                                    <?php 
                                                    $description = htmlspecialchars($podcast['description']);
                                                    echo mb_strlen($description) > 150 ? mb_substr($description, 0, 147) . '...' : $description;
                                                    ?>
                                                </p>
                                                <div class="mt-auto d-flex justify-content-between align-items-center"> 
                                                    <a href="view_podcast.php?id=<?php echo $podcast['id']; ?>" class="btn btn-primary btn-sm">Écouter</a>
                                                    <?php if (!empty($podcast['duree_secondes'])): ?>
                                                        <small class="text-muted">Durée: <?php echo formatDuration($podcast['duree_secondes']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="alert alert-info">Aucun podcast trouvé pour le moment.</p>
                <?php endif; ?>
             </section>
         </div>
     </div>

<?php
require_once 'includes/footer.php'; // Inclut le footer commun
?>
