<?php
$page_title = "Leçon";
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth_check.php'; // Vérifier que l'utilisateur est connecté
require_once 'includes/header.php';

// Récupérer l'ID de la leçon
$lecon_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$lecon_id) {
    $_SESSION['error_message'] = "Leçon non spécifiée.";
    header('Location: cours.php');
    exit;
}

try {
    // Récupérer les informations de la leçon, du module et du cours
    $stmt_lecon = $pdo->prepare("
        SELECT l.*, m.titre AS module_titre, m.id AS module_id, 
               c.titre AS cours_titre, c.id AS cours_id
        FROM lecons l
        JOIN modules m ON l.id_module = m.id
        JOIN cours c ON m.id_cours = c.id
        WHERE l.id = ? AND l.statut = 'publie'
    ");
    $stmt_lecon->execute([$lecon_id]);
    $lecon = $stmt_lecon->fetch(PDO::FETCH_ASSOC);

    if (!$lecon) {
        $_SESSION['error_message'] = "Leçon non trouvée.";
        header('Location: cours.php');
        exit;
    }

    // Récupérer les quiz de la leçon
    $stmt_quiz = $pdo->prepare("
        SELECT q.*, 
               (SELECT COUNT(*) FROM resultats_quiz rq WHERE rq.id_quiz = q.id AND rq.id_utilisateur = ?) as tentatives,
               (SELECT MAX(score) FROM resultats_quiz rq WHERE rq.id_quiz = q.id AND rq.id_utilisateur = ?) as meilleur_score
        FROM quiz q
        WHERE q.id_lecon = ? AND q.statut = 'publie'
        ORDER BY q.id ASC
    ");
    $stmt_quiz->execute([$_SESSION['user_id'], $_SESSION['user_id'], $lecon_id]);
    $quiz = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);

    // Mettre à jour ou créer la progression de l'utilisateur
    $stmt_prog = $pdo->prepare("
        INSERT INTO progression_utilisateurs 
            (id_utilisateur, id_cours, id_module, id_lecon, statut, progression, date_debut)
        VALUES 
            (?, ?, ?, ?, 'en_cours', 0, NOW())
        ON DUPLICATE KEY UPDATE 
            date_derniere_activite = NOW(),
            statut = CASE 
                WHEN statut = 'termine' THEN 'termine'
                ELSE 'en_cours'
            END
    ");
    $stmt_prog->execute([
        $_SESSION['user_id'],
        $lecon['cours_id'],
        $lecon['module_id'],
        $lecon_id
    ]);

    // Récupérer la leçon suivante pour la navigation
    $stmt_next = $pdo->prepare("
        SELECT l.id, l.titre
        FROM lecons l
        WHERE l.id_module = ? 
        AND l.ordre_affichage > ?
        AND l.statut = 'publie'
        ORDER BY l.ordre_affichage ASC
        LIMIT 1
    ");
    $stmt_next->execute([$lecon['module_id'], $lecon['ordre_affichage']]);
    $next_lecon = $stmt_next->fetch(PDO::FETCH_ASSOC);

    // Récupérer la leçon précédente
    $stmt_prev = $pdo->prepare("
        SELECT l.id, l.titre
        FROM lecons l
        WHERE l.id_module = ? 
        AND l.ordre_affichage < ?
        AND l.statut = 'publie'
        ORDER BY l.ordre_affichage DESC
        LIMIT 1
    ");
    $stmt_prev->execute([$lecon['module_id'], $lecon['ordre_affichage']]);
    $prev_lecon = $stmt_prev->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération de la leçon : " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur est survenue lors du chargement de la leçon.";
    header('Location: cours.php');
    exit;
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Contenu principal de la leçon -->
        <div class="col-md-9">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="cours.php">Cours</a></li>
                    <li class="breadcrumb-item"><a href="view_cours.php?id=<?php echo $lecon['cours_id']; ?>"><?php echo htmlspecialchars($lecon['cours_titre']); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($lecon['titre']); ?></li>
                </ol>
            </nav>

            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title h2"><?php echo htmlspecialchars($lecon['titre']); ?></h1>
                    
                    <?php if ($lecon['type_contenu'] === 'video'): ?>
                        <div class="ratio ratio-16x9 mb-4">
                            <?php echo $lecon['contenu']; // Iframe vidéo ?>
                        </div>
                    <?php else: ?>
                        <div class="lesson-content">
                            <?php echo $lecon['contenu']; // Contenu formaté en HTML ?>
                        </div>
                    <?php endif; ?>

                    <!-- Navigation entre les leçons -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <?php if ($prev_lecon): ?>
                            <a href="lecon.php?id=<?php echo $prev_lecon['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-chevron-left"></i> 
                                <?php echo htmlspecialchars($prev_lecon['titre']); ?>
                            </a>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>

                        <?php if ($next_lecon): ?>
                            <a href="lecon.php?id=<?php echo $next_lecon['id']; ?>" class="btn btn-primary">
                                <?php echo htmlspecialchars($next_lecon['titre']); ?> 
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn btn-success" id="completeLesson">
                                Terminer la leçon <i class="fas fa-check"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Section des quiz -->
            <?php if (!empty($quiz)): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Quiz de la leçon</h2>
                    </div>
                    <div class="card-body">
                        <?php foreach ($quiz as $q): ?>
                            <div class="quiz-item mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="h6 mb-1"><?php echo htmlspecialchars($q['titre']); ?></h3>
                                        <p class="small text-muted mb-0">
                                            <?php echo $q['nombre_questions']; ?> questions
                                            <?php if ($q['duree_limite']): ?>
                                                • <?php echo $q['duree_limite']; ?> minutes
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($q['tentatives'] > 0): ?>
                                            <p class="small text-muted mb-2">
                                                Meilleur score : <?php echo $q['meilleur_score']; ?>%
                                            </p>
                                        <?php endif; ?>
                                        <a href="quiz.php?id=<?php echo $q['id']; ?>" class="btn btn-primary btn-sm">
                                            <?php echo $q['tentatives'] > 0 ? 'Retenter' : 'Commencer'; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar avec la progression -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Progression du module</h2>
                </div>
                <div class="card-body">
                    <?php
                    // Récupérer toutes les leçons du module pour afficher la progression
                    $stmt_module_lecons = $pdo->prepare("
                        SELECT l.id, l.titre, l.ordre_affichage,
                               CASE 
                                   WHEN pu.statut = 'termine' THEN 'completed'
                                   WHEN pu.statut = 'en_cours' THEN 'in_progress'
                                   ELSE 'not_started'
                               END as status
                        FROM lecons l
                        LEFT JOIN progression_utilisateurs pu ON pu.id_lecon = l.id 
                            AND pu.id_utilisateur = ?
                        WHERE l.id_module = ? AND l.statut = 'publie'
                        ORDER BY l.ordre_affichage ASC
                    ");
                    $stmt_module_lecons->execute([$_SESSION['user_id'], $lecon['module_id']]);
                    $module_lecons = $stmt_module_lecons->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="list-group list-group-flush">
                        <?php foreach ($module_lecons as $module_lecon): 
                            $is_current = $module_lecon['id'] == $lecon_id;
                            $status_class = '';
                            $status_icon = '';
                            
                            switch ($module_lecon['status']) {
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
                            
                            if ($is_current) {
                                $status_class .= ' active';
                            }
                        ?>
                            <a href="lecon.php?id=<?php echo $module_lecon['id']; ?>" 
                               class="list-group-item list-group-item-action <?php echo $status_class; ?>">
                                <?php echo $status_icon; ?>
                                <span class="ms-2"><?php echo htmlspecialchars($module_lecon['titre']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestionnaire pour marquer une leçon comme terminée
    const completeButton = document.getElementById('completeLesson');
    if (completeButton) {
        completeButton.addEventListener('click', function() {
            fetch('ajax/complete_lesson.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `lecon_id=<?php echo $lecon_id; ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Rediriger vers la page du cours avec un message de succès
                    window.location.href = `view_cours.php?id=<?php echo $lecon['cours_id']; ?>&success=1`;
                } else {
                    alert(data.message || 'Erreur lors de la mise à jour de la progression');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la mise à jour de la progression');
            });
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
