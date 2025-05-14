<?php
$page_title = "Détail du Cours";
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

// Récupérer l'ID du cours
$cours_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$cours_id) {
    header('Location: cours.php');
    exit;
}

try {
    // Récupérer les informations du cours
    $stmt_cours = $pdo->prepare("
        SELECT c.*, cc.nom AS nom_categorie,
               CONCAT(u.prenom, ' ', u.nom) AS nom_createur
        FROM cours c
        LEFT JOIN categories_cours cc ON c.id_categorie = cc.id
        LEFT JOIN utilisateurs u ON c.id_createur = u.id
        WHERE c.id = ? AND c.statut = 'publie'
    ");
    $stmt_cours->execute([$cours_id]);
    $cours = $stmt_cours->fetch(PDO::FETCH_ASSOC);

    if (!$cours) {
        $_SESSION['error_message'] = "Cours non trouvé.";
        header('Location: cours.php');
        exit;
    }

    // Récupérer les modules du cours
    $stmt_modules = $pdo->prepare("
        SELECT m.*, 
               (SELECT COUNT(*) FROM lecons l WHERE l.id_module = m.id AND l.statut = 'publie') as nombre_lecons
        FROM modules m
        WHERE m.id_cours = ? AND m.statut = 'publie'
        ORDER BY m.ordre_affichage ASC
    ");
    $stmt_modules->execute([$cours_id]);
    $modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);

    // Si l'utilisateur est connecté, récupérer sa progression
    $progression = null;
    if (isset($_SESSION['user_id'])) {
        $stmt_prog = $pdo->prepare("
            SELECT * FROM progression_utilisateurs 
            WHERE id_utilisateur = ? AND id_cours = ?
        ");
        $stmt_prog->execute([$_SESSION['user_id'], $cours_id]);
        $progression = $stmt_prog->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération du cours : " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur est survenue lors du chargement du cours.";
    header('Location: cours.php');
    exit;
}
?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar avec les informations du cours -->
        <div class="col-md-4">
            <div class="card mb-4">
                <img src="<?php echo !empty($cours['image_url']) ? 'uploads/cours/' . htmlspecialchars($cours['image_url']) : 'images/default_course.jpg'; ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($cours['titre']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($cours['titre']); ?></h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($cours['description'])); ?></p>
                    
                    <div class="course-meta">
                        <p><i class="fas fa-user"></i> Par <?php echo htmlspecialchars($cours['nom_createur']); ?></p>
                        <p><i class="fas fa-folder"></i> <?php echo htmlspecialchars($cours['nom_categorie']); ?></p>
                        <p><i class="fas fa-signal"></i> Niveau : <?php echo htmlspecialchars(ucfirst($cours['niveau'])); ?></p>
                        <p><i class="fas fa-clock"></i> Durée : <?php echo htmlspecialchars($cours['duree_estimee']); ?></p>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($progression): ?>
                            <div class="progress mb-3">
                                <div class="progress-bar" 
                                     role="progressbar" 
                                     style="width: <?php echo $progression['progression']; ?>%"
                                     aria-valuenow="<?php echo $progression['progression']; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?php echo $progression['progression']; ?>%
                                </div>
                            </div>
                            <a href="lecon.php?id=<?php echo $progression['id_lecon']; ?>" class="btn btn-primary btn-block">
                                Continuer le cours
                            </a>
                        <?php else: ?>
                            <a href="lecon.php?id=<?php echo $modules[0]['id']; ?>" class="btn btn-success btn-block">
                                Commencer le cours
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-block">
                            Connectez-vous pour suivre ce cours
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Contenu principal avec les modules et leçons -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Contenu du cours</h2>
                </div>
                <div class="card-body p-0">
                    <div class="accordion" id="moduleAccordion">
                        <?php foreach ($modules as $index => $module): ?>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="heading<?php echo $module['id']; ?>">
                                    <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?php echo $module['id']; ?>" 
                                            aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                            aria-controls="collapse<?php echo $module['id']; ?>">
                                        <?php echo htmlspecialchars($module['titre']); ?>
                                        <span class="badge bg-primary ms-2"><?php echo $module['nombre_lecons']; ?> leçons</span>
                                    </button>
                                </h3>
                                <div id="collapse<?php echo $module['id']; ?>" 
                                     class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                     aria-labelledby="heading<?php echo $module['id']; ?>" 
                                     data-bs-parent="#moduleAccordion">
                                    <div class="accordion-body">
                                        <div class="list-group list-group-flush">
                                            <?php
                                            // Récupérer les leçons du module
                                            $stmt_lecons = $pdo->prepare("
                                                SELECT l.*, 
                                                       (SELECT COUNT(*) FROM quiz q WHERE q.id_lecon = l.id) as nombre_quiz,
                                                       CASE 
                                                           WHEN pu.id IS NOT NULL THEN 'completed'
                                                           WHEN pu2.id IS NOT NULL THEN 'in_progress'
                                                           ELSE 'not_started'
                                                       END as status
                                                FROM lecons l
                                                LEFT JOIN progression_utilisateurs pu ON pu.id_lecon = l.id 
                                                    AND pu.id_utilisateur = ? AND pu.statut = 'termine'
                                                LEFT JOIN progression_utilisateurs pu2 ON pu2.id_lecon = l.id 
                                                    AND pu2.id_utilisateur = ? AND pu2.statut = 'en_cours'
                                                WHERE l.id_module = ? AND l.statut = 'publie'
                                                ORDER BY l.ordre_affichage ASC
                                            ");
                                            $user_id = $_SESSION['user_id'] ?? 0;
                                            $stmt_lecons->execute([$user_id, $user_id, $module['id']]);
                                            $lecons = $stmt_lecons->fetchAll(PDO::FETCH_ASSOC);

                                            foreach ($lecons as $lecon):
                                                $status_class = '';
                                                $status_icon = '';
                                                switch ($lecon['status']) {
                                                    case 'completed':
                                                        $status_class = 'list-group-item-success';
                                                        $status_icon = '<i class="fas fa-check-circle text-success"></i>';
                                                        break;
                                                    case 'in_progress':
                                                        $status_class = 'list-group-item-warning';
                                                        $status_icon = '<i class="fas fa-clock text-warning"></i>';
                                                        break;
                                                    default:
                                                        $status_icon = '<i class="far fa-circle"></i>';
                                                }
                                            ?>
                                                <a href="lecon.php?id=<?php echo $lecon['id']; ?>" 
                                                   class="list-group-item list-group-item-action <?php echo $status_class; ?>">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div>
                                                            <?php echo $status_icon; ?>
                                                            <span class="ms-2"><?php echo htmlspecialchars($lecon['titre']); ?></span>
                                                        </div>
                                                        <div>
                                                            <?php if ($lecon['nombre_quiz'] > 0): ?>
                                                                <span class="badge bg-info me-2">
                                                                    <i class="fas fa-question-circle"></i> Quiz
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($lecon['duree_estimee'])): ?>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-clock"></i> 
                                                                    <?php echo htmlspecialchars($lecon['duree_estimee']); ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
