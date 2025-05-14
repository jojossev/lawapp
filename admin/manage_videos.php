<?php
$page_title = "Gestion des Vidéos";
require_once __DIR__ . '/admin_auth_check.php'; // Auth et connexion BDD ($pdo)
require_once __DIR__ . '/admin_header.php';

// Messages flash (succès/erreur)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

try {
    // Récupérer les vidéos avec les noms des catégories et des créateurs
    $stmt_videos = $pdo->query("
        SELECT 
            v.id, 
            v.titre, 
            v.duree,
            v.niveau, 
            v.statut, 
            v.date_creation,
            cv.nom AS nom_categorie_video,
            CONCAT(u.prenom, ' ', u.nom) AS nom_createur
        FROM videos v
        LEFT JOIN categories_videos cv ON v.id_categorie = cv.id
        LEFT JOIN utilisateurs u ON v.id_createur = u.id
        ORDER BY v.date_creation DESC
    ");
    $videos_liste = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des vidéos : " . $e->getMessage();
    $videos_liste = []; // Initialiser comme tableau vide en cas d'erreur
}
?>

<div class="admin-content">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message && !isset($stmt_videos)): // Affiche l'erreur PDO uniquement si la requête a échoué initialement ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <p>
        <a href="video_form.php?action=add" class="btn btn-primary">Ajouter une nouvelle vidéo</a>
    </p>

    <?php if (empty($videos_liste) && !$error_message):
 ?>
        <p>Aucune vidéo n'a été ajoutée pour le moment.</p>
    <?php elseif (!empty($videos_liste)):
 ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Durée</th>
                    <th>Niveau</th>
                    <th>Statut</th>
                    <th>Créateur</th>
                    <th>Date Ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($videos_liste as $video_item):
 ?>
                    <tr>
                        <td><?php echo htmlspecialchars($video_item['id']); ?></td>
                        <td><?php echo htmlspecialchars($video_item['titre']); ?></td>
                        <td><?php echo htmlspecialchars($video_item['nom_categorie_video'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($video_item['duree'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($video_item['niveau'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($video_item['statut'])); ?></td>
                        <td><?php echo htmlspecialchars($video_item['nom_createur'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars( (new DateTime($video_item['date_creation']))->format('d/m/Y H:i') ); ?></td>
                        <td>
                            <a href="video_form.php?action=edit&id=<?php echo $video_item['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <a href="video_actions.php?action=delete&id=<?php echo $video_item['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vidéo ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<?php
require_once __DIR__ . '/admin_footer.php';
?>
