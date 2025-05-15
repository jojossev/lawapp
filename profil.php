<?php
$page_title = "Profil Utilisateur"; // Titre spécifique pour cette page
require_once 'includes/header.php'; // Inclut le header commun et démarre la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si non connecté
    $_SESSION['error_message'] = "Veuillez vous connecter pour accéder à votre profil.";
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'login.php';</script>";
    echo "<div class='alert alert-warning'>Vous devez être connecté pour accéder à cette page. Redirection en cours...</div>";
    // Ne pas utiliser exit ici pour permettre l'affichage du message
}

$user_id = (int)$_SESSION['user_id'];
$utilisateur = null;
$cours_inscrits = [];
$page_error = null;

try {
    // 1. Récupérer les informations de l'utilisateur depuis la BDD
    $stmt_user = $pdo->prepare("SELECT nom, prenom, email, date_inscription, receive_email_notifications, ui_theme FROM utilisateurs WHERE id = :id");
    $stmt_user->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt_user->execute();
    $utilisateur = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$utilisateur) {
        // Cas improbable si l'ID de session est valide mais l'utilisateur n'existe plus
        throw new Exception("Utilisateur non trouvé.");
    }

    // 2. Récupérer les cours auxquels l'utilisateur est inscrit
    $stmt_courses = $pdo->prepare("
        SELECT c.id, c.titre, c.description, c.image_url, i.date_inscription
        FROM inscriptions i
        JOIN cours c ON i.id_cours = c.id
        WHERE i.id_utilisateur = :user_id
        ORDER BY i.date_inscription DESC
    ");
    $stmt_courses->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_courses->execute();
    $cours_inscrits = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur PDO profil.php pour user ID {$user_id}: " . $e->getMessage());
    $page_error = "Une erreur technique est survenue lors du chargement de votre profil.";
} catch (Exception $e) {
    // Gérer l'utilisateur non trouvé ou autre exception
    error_log("Erreur profil.php pour user ID {$user_id}: " . $e->getMessage());
    unset($_SESSION['user_id']); // Déconnecter l'utilisateur si son compte est invalide
    $_SESSION['error_message'] = "Votre session est invalide. Veuillez vous reconnecter.";
    // Utiliser JavaScript pour la redirection au lieu de header() pour éviter les erreurs
    echo "<script>window.location.href = 'login.php';</script>";
    echo "<div class='alert alert-danger'>Votre session est invalide. Redirection vers la page de connexion...</div>";
    // Ne pas utiliser exit ici pour permettre l'affichage du message
}

// Données placeholder pour les stats (à remplacer par des requêtes réelles si nécessaire)
$stats_placeholders = [
    'cours_suivis' => count($cours_inscrits), // On peut déjà utiliser le compte réel ici
    'quiz_reussis' => 12, // Placeholder
    'temps_etude_total' => '48 heures' // Placeholder
];

?>

    <!-- Contenu spécifique à la page profil -->
    <div class="container page-content mt-4">
        <?php if ($page_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($page_error); ?></div>
        <?php elseif ($utilisateur): ?>
            <h2 class="page-title mb-4">Profil de <?php echo htmlspecialchars($utilisateur['prenom']) . ' ' . htmlspecialchars($utilisateur['nom']); ?></h2>

            <div class="row gy-4">
                <!-- Colonne Informations & Paramètres -->
                <div class="col-lg-4">
                    <section id="informations-personnelles" class="card profile-section mb-4">
                        <div class="card-header">Informations Personnelles</div>
                        <div class="card-body">
                            <p><strong>Nom:</strong> <?php echo htmlspecialchars($utilisateur['nom']); ?></p>
                            <p><strong>Prénom:</strong> <?php echo htmlspecialchars($utilisateur['prenom']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($utilisateur['email']); ?></p>
                            <p><strong>Membre depuis:</strong> <?php echo date('d/m/Y', strtotime($utilisateur['date_inscription'])); ?></p>
                            <div class="actions mt-3">
                                <a href="edit_profil.php" class="btn btn-primary btn-sm">Modifier mes informations</a>
                                <a href="change_password.php" class="btn btn-secondary btn-sm mt-2">Changer le mot de passe</a>
                            </div>
                        </div>
                    </section>

                    <section id="parametres-compte" class="card profile-section">
                         <div class="card-header">Paramètres du Compte</div>
                         <div class="card-body">
                            <form action="update_settings_process.php" method="POST"> 
                                <div class="form-check mb-3">
                                    <input type="checkbox" id="notifications-email" name="notifications_email" value="1" class="form-check-input" <?php echo ($utilisateur['receive_email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                    <label for="notifications-email" class="form-check-label">Recevoir les notifications par email</label>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="theme-app" class="form-label">Thème de l'interface :</label>
                                    <select id="theme-app" name="theme_app" class="form-select">
                                        <option value="light" <?php echo (($utilisateur['ui_theme'] ?? 'light') === 'light') ? 'selected' : ''; ?>>Clair</option>
                                        <option value="dark" <?php echo (($utilisateur['ui_theme'] ?? 'light') === 'dark') ? 'selected' : ''; ?>>Sombre</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm mt-2">Enregistrer les paramètres</button>
                            </form>
                         </div>
                     </section>
                </div>

                <!-- Colonne Cours Inscrits & Statistiques -->
                <div class="col-lg-8">
                    <section id="cours-inscrits" class="card profile-section mb-4">
                        <div class="card-header">Mes Cours Inscrits</div>
                        <div class="card-body">
                            <?php if (empty($cours_inscrits)): ?>
                                <p class="text-muted">Vous n'êtes inscrit à aucun cours pour le moment.</p>
                                <a href="courses.php" class="btn btn-primary">Explorer les cours</a>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($cours_inscrits as $cours): ?>
                                        <a href="view_course.php?id=<?php echo $cours['id']; ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($cours['image_url'] ?? 'assets/images/default-course.png'); ?>" alt="Image <?php echo htmlspecialchars($cours['titre']); ?>" class="me-3" style="width: 60px; height: 40px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1"><?php echo htmlspecialchars($cours['titre']); ?></h5>
                                                <small class="text-muted">Inscrit le: <?php echo date('d/m/Y', strtotime($cours['date_inscription'])); ?></small>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">Accéder</span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                    
                    <section id="statistiques-progression" class="card profile-section">
                         <div class="card-header">Statistiques (Bientôt)</div>
                         <div class="card-body">
                            <p><strong>Cours suivis:</strong> <?php echo htmlspecialchars($stats_placeholders['cours_suivis']); ?></p>
                            <p><strong>Quiz réussis:</strong> <?php echo htmlspecialchars($stats_placeholders['quiz_reussis']); ?> (placeholder)</p>
                            <p><strong>Temps d'étude total:</strong> <?php echo htmlspecialchars($stats_placeholders['temps_etude_total']); ?> (placeholder)</p>
                            <a href="#" class="btn btn-link" disabled>Voir plus de détails (Bientôt)</a>
                        </div>
                    </section>
                </div>
            </div>

        <?php endif; // Fin de if($utilisateur) ?>
    </div>

<?php
require_once 'includes/footer.php'; // Inclut le footer commun
?>
