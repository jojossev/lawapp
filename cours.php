<?php
$page_title = "Cours disponibles";
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

// Récupérer les filtres
$categorie_id = filter_input(INPUT_GET, 'categorie', FILTER_VALIDATE_INT);
$niveau = filter_input(INPUT_GET, 'niveau', FILTER_SANITIZE_SPECIAL_CHARS);
$recherche = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS);

try {
    // Récupérer les catégories pour le filtre
    $stmt_categories = $pdo->query("
        SELECT c.*, COUNT(co.id) as nombre_cours
        FROM categories_cours c
        LEFT JOIN cours co ON c.id = co.id_categorie AND co.statut = 'publie'
        GROUP BY c.id
        HAVING nombre_cours > 0
        ORDER BY c.nom ASC
    ");
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

    // Construire la requête SQL de base
    $sql = "SELECT c.*, cc.nom AS nom_categorie,
                   CONCAT(u.prenom, ' ', u.nom) AS nom_createur,
                   (SELECT COUNT(*) FROM modules m WHERE m.id_cours = c.id AND m.statut = 'publie') as nombre_modules
            FROM cours c
            LEFT JOIN categories_cours cc ON c.id_categorie = cc.id
            LEFT JOIN utilisateurs u ON c.id_createur = u.id
            WHERE c.statut = 'publie'";
    $params = [];

    // Ajouter les filtres si présents
    if ($categorie_id) {
        $sql .= " AND c.id_categorie = ?";
        $params[] = $categorie_id;
    }
    if ($niveau) {
        $sql .= " AND c.niveau = ?";
        $params[] = $niveau;
    }
    if ($recherche) {
        $sql .= " AND (c.titre LIKE ? OR c.description LIKE ?)";
        $params[] = "%$recherche%";
        $params[] = "%$recherche%";
    }

    $sql .= " ORDER BY c.date_creation DESC";

    $stmt_cours = $pdo->prepare($sql);
    $stmt_cours->execute($params);
    $cours = $stmt_cours->fetchAll(PDO::FETCH_ASSOC);

    // Si l'utilisateur est connecté, récupérer sa progression
    $progression_utilisateur = [];
    if (isset($_SESSION['user_id'])) {
        $stmt_prog = $pdo->prepare("
            SELECT id_cours, progression, statut
            FROM progression_utilisateurs
            WHERE id_utilisateur = ?
        ");
        $stmt_prog->execute([$_SESSION['user_id']]);
        while ($row = $stmt_prog->fetch(PDO::FETCH_ASSOC)) {
            $progression_utilisateur[$row['id_cours']] = $row;
        }
    }

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des cours : " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur est survenue lors du chargement des cours.";
    $cours = [];
    $categories = [];
}

$niveaux = ['debutant', 'intermediaire', 'avance'];
?>

<div class="container mt-4">
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <select name="categorie" id="categorie" class="form-select">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categorie_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nom']); ?> (<?php echo $cat['nombre_cours']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="niveau" class="form-label">Niveau</label>
                    <select name="niveau" id="niveau" class="form-select">
                        <option value="">Tous les niveaux</option>
                        <?php foreach ($niveaux as $niv): ?>
                            <option value="<?php echo $niv; ?>" <?php echo $niveau === $niv ? 'selected' : ''; ?>>
                                <?php echo ucfirst($niv); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="q" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="q" name="q" value="<?php echo htmlspecialchars($recherche ?? ''); ?>" placeholder="Rechercher un cours...">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des cours -->
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if (empty($cours)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucun cours ne correspond à vos critères de recherche.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($cours as $c): ?>
                <div class="col">
                    <div class="card h-100">
                        <img src="<?php echo !empty($c['image_url']) ? 'uploads/cours/' . htmlspecialchars($c['image_url']) : 'images/default_course.jpg'; ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($c['titre']); ?>">
                        
                        <?php if (isset($progression_utilisateur[$c['id']])): ?>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" 
                                     role="progressbar" 
                                     style="width: <?php echo $progression_utilisateur[$c['id']]['progression']; ?>%"
                                     aria-valuenow="<?php echo $progression_utilisateur[$c['id']]['progression']; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($c['titre']); ?></h5>
                            <p class="card-text text-muted small">
                                <?php if (!empty($c['nom_categorie'])): ?>
                                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($c['nom_categorie']); ?> •
                                <?php endif; ?>
                                <i class="fas fa-signal"></i> <?php echo ucfirst($c['niveau']); ?> •
                                <i class="fas fa-book"></i> <?php echo $c['nombre_modules']; ?> modules
                            </p>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($c['description'], 0, 150) . '...')); ?></p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Par <?php echo htmlspecialchars($c['nom_createur']); ?>
                                </small>
                                <?php if (isset($progression_utilisateur[$c['id']])): ?>
                                    <a href="view_cours.php?id=<?php echo $c['id']; ?>" class="btn btn-primary btn-sm">
                                        Continuer
                                        <span class="badge bg-white text-primary ms-1">
                                            <?php echo round($progression_utilisateur[$c['id']]['progression']); ?>%
                                        </span>
                                    </a>
                                <?php else: ?>
                                    <a href="view_cours.php?id=<?php echo $c['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        Commencer
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
