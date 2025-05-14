<?php
session_start(); // Démarrer la session pour gérer les messages flash

// Si l'administrateur est déjà connecté, le rediriger vers le tableau de bord
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$error_message = $_SESSION['login_error_message'] ?? null;
unset($_SESSION['login_error_message']); // Effacer le message après l'avoir affiché

// Déterminer le chemin de base pour les ressources CSS
// Ceci suppose que admin_login.php est dans le dossier /admin/
$base_path = '../'; // Remonter d'un niveau pour accéder à css/
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur - LawApp</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/admin_styles.css"> <!-- Assurez-vous que le chemin est correct -->
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f6f8;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .login-container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h1 {
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-login {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        .btn-login:hover {
            background-color: #0056b3;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Interface d'Administration</h1>
        <p>Veuillez vous connecter pour continuer.</p>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="admin_login_process.php" method="post" class="login-form">
            <div class="form-group">
                <label for="email">Email administrateur :</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
         <p style="font-size: 0.9em; margin-top: 20px;">
            <a href="<?php echo rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'); ?>/index.php">Retour au site principal</a>
        </p>
    </div>
</body>
</html>
