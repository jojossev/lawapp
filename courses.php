<?php
$page_title = "Nos Cours";
require_once __DIR__ . '/includes/config.php'; // Contient db_connect.php et session_start
require_once __DIR__ . '/includes/header.php';

try {
    // Récupérer les cours publiés avec le nom de la catégorie et du créateur
    // On ne sélectionne que les cours avec le statut 'publie'
    $stmt = $pdo->query("
        SELECT 
            c.id, 
            c.titre, 
            c.description, 
            c.image_url, 
            c.niveau,
            c.duree_estimee,
            c.prix,
            cc.nom AS nom_categorie_cours,
            CONCAT(u.prenom, ' ', u.nom) AS nom_createur
        FROM cours c
        LEFT JOIN categories_cours cc ON c.id_categorie = cc.id
        LEFT JOIN utilisateurs u ON c.id_createur = u.id
        WHERE c.statut = 'publie'
        ORDER BY c.date_creation DESC
    ");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gérer l'erreur (par exemple, afficher un message ou logger)
    error_log("Erreur lors de la récupération des cours : " . $e->getMessage());
    $courses = []; // Initialiser comme tableau vide en cas d'erreur
    $page_error = "Une erreur s'est produite lors du chargement des cours. Veuillez réessayer plus tard.";
}
?>

<div class="container mt-5">
    <h1 class="mb-4"><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if (isset($page_error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($page_error); ?></div>
    <?php endif; ?>

    <?php if (empty($courses) && !isset($page_error)): ?>
        <div class="alert alert-info">Aucun cours n'est disponible pour le moment. Revenez bientôt !</div>
    <?php elseif (!empty($courses)): ?>
        <div class="row row-cols-1 g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col">
                    <div class="card mb-3 shadow-sm course-list-card overflow-hidden"> <?php /* Remplacement de h-100 par mb-3, ajout de overflow-hidden */ ?>
                       <div class="row g-0">
                            <div class="col-md-4"> <?php /* Colonne pour l'image */ ?>
                                <?php 
                                $image_path = !empty($course['image_url']) ? htmlspecialchars($course['image_url']) : '';
                                // Note: L'image doit être un chemin relatif depuis la racine du site ou une URL absolue
                                ?>
                                <?php if (!empty($image_path)): ?>
                                    <a href="view_course.php?id=<?php echo $course['id']; ?>" class="d-block h-100">
                                        <img src="<?php echo $image_path; ?>" class="img-fluid rounded-start w-100 h-100" alt="<?php echo htmlspecialchars($course['titre']); ?>" style="object-fit: cover;"> <?php /* Suppression card-img-top, ajout rounded-start, w-100, h-100 */ ?>
                                    </a>
                                <?php else:
                                    // Optionnel: Afficher un placeholder HTML/CSS générique si aucune image n'est fournie
                                    // Par exemple, un div avec une couleur de fond et un texte
                                    echo '<div class="d-block h-100 bg-light d-flex align-items-center justify-content-center text-muted rounded-start">Image non disponible</div>';
                                ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8"> <?php /* Colonne pour le texte */ ?>
                                <div class="card-body d-flex flex-column h-100"> <?php /* Ajout h-100 pour aligner bouton en bas */ ?>
                                    <h5 class="card-title"><a href="view_course.php?id=<?php echo $course['id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($course['titre']); ?></a></h5>
                                    <p class="card-text small text-muted mb-2">
                                        <?php if (!empty($course['nom_categorie_cours'])): ?>
                                            Catégorie: <?php echo htmlspecialchars($course['nom_categorie_cours']); ?> | 
                                        <?php endif; ?>
                                        <?php if (!empty($course['nom_createur'])): ?>
                                            Par: <?php echo htmlspecialchars($course['nom_createur']); ?> | 
                                        <?php endif; ?>
                                        <?php if (!empty($course['niveau'])): ?>
                                            Niveau: <?php echo htmlspecialchars($course['niveau']); ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="card-text flex-grow-1">
                                        <?php 
                                        $description = htmlspecialchars($course['description']);
                                        echo mb_strlen($description) > 150 ? mb_substr($description, 0, 147) . '...' : $description; // Utilisation mb_ pour multi-byte
                                        ?>
                                    </p>
                                    <div class="mt-auto d-flex justify-content-between align-items-center"> <?php /* Déplacé le footer ici */ ?>
                                        <a href="view_course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Voir le cours</a>
                                        <div class="text-end">
                                            <?php if (!is_null($course['prix']) && $course['prix'] > 0): ?>
                                                <span class="fw-bold me-2"><?php echo number_format($course['prix'], 2, ',', ' '); ?> €</span>
                                            <?php else: ?>
                                                <span class="fw-bold me-2 text-success">Gratuit</span>
                                            <?php endif; ?>
                                            <?php if (!empty($course['duree_estimee'])):
                                                ?>
                                                <small class="text-muted">Durée: <?php echo htmlspecialchars($course['duree_estimee']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                       </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
