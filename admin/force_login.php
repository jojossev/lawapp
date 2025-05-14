<?php
session_start();

// Forcer la session admin
$_SESSION['admin_id'] = 1;
$_SESSION['admin_prenom'] = 'Admin';
$_SESSION['admin_nom'] = 'Principal';
$_SESSION['admin_email'] = 'admin@lawapp.com';
$_SESSION['admin_role'] = 'admin';

// Afficher les variables de session
echo "<h3>Session admin créée :</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<p>Vous êtes maintenant connecté en tant qu'administrateur.</p>";
echo "<p><a href='admin_dashboard.php'>Aller au tableau de bord</a></p>";
?>
