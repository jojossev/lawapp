<?php
// admin/index.php
// Redirige vers le tableau de bord principal

require_once __DIR__ . '/admin_auth_check.php'; // Assure l'authentification avant la redirection

header('Location: admin_dashboard.php');
exit; // Important pour arrêter l'exécution après la redirection
?>
