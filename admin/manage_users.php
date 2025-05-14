<?php
require_once __DIR__ . '/admin_auth_check.php';

$page_title = "Admin - Gérer les Utilisateurs";
include_once __DIR__ . '/admin_header.php';
?>
<div class="admin-container">
    <div class="page-header">
        <h1>Gérer les Utilisateurs</h1>
        <a href="user_form.php?action=add" class="btn-add">Ajouter un utilisateur</a> <!-- Lien placeholder -->
    </div>
    <p>Cette section permettra de lister, modifier et supprimer des utilisateurs.</p>
    <!-- Le paragraphe "Fonctionnalité à venir" est supprimé car la fonctionnalité est maintenant implémentée -->
    
    <?php
    // Récupérer les utilisateurs de la base de données
    $users = [];
    $fetch_error_message = null; 
    try {
        // Assurez-vous que $pdo est disponible (normalement via admin_auth_check.php -> config.php -> db_connect.php)
        if (!isset($pdo)) {
            throw new Exception("La connexion PDO n'est pas disponible. Vérifiez les fichiers d'inclusion (db_connect.php, config.php).");
        }
        $stmt_users = $pdo->query("SELECT id, prenom, nom, email, role FROM utilisateurs ORDER BY prenom ASC, nom ASC");
        $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $fetch_error_message = "Erreur PDO lors de la récupération des utilisateurs : " . $e->getMessage();
        error_log("Admin manage_users.php - PDOException: " . $fetch_error_message);
    } catch (Exception $e) {
        $fetch_error_message = "Erreur générale lors de la récupération des utilisateurs : " . $e->getMessage();
        error_log("Admin manage_users.php - Exception: " . $fetch_error_message);
    }
    ?>

    <?php if ($fetch_error_message): ?>
        <div class="alert alert-danger" role="alert">
            <p><strong>Erreur de chargement des données :</strong></p>
            <p><?php echo htmlspecialchars($fetch_error_message); ?></p>
            <p>Veuillez vérifier les logs du serveur pour plus de détails (souvent C:\\xampp\\apache\\logs\\error.log ou C:\\xampp\\php\\logs\\php_error_log). Assurez-vous également que la table 'utilisateurs' existe et que la connexion à la base de données est correcte.</p>
        </div>
    <?php endif; ?>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Prénom</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)):
                foreach ($users as $user):
            ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($user['nom']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                        <td class="actions">
                            <a href="user_form.php?action=edit&id=<?php echo $user['id']; ?>" class="btn-action btn-edit">Modifier</a>
                            <a href="user_actions.php?action=delete&id=<?php echo $user['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer l\'utilisateur \'<?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>\' (ID: <?php echo htmlspecialchars($user['id']); ?>) ? Cette action est irréversible.');">Supprimer</a>
                        </td>
                    </tr>
            <?php 
                endforeach;
            elseif (!$fetch_error_message): ?>
                <tr>
                    <td colspan="6">Aucun utilisateur à afficher pour le moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include_once __DIR__ . '/admin_footer.php'; ?>
