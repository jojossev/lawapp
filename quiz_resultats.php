<?php
$cours_id = isset($_GET['cours_id']) ? htmlspecialchars($_GET['cours_id']) : 'N/A';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats du Quiz - LawApp</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>LawApp</h1>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="cours_liste.php">Cours</a></li>
                <li><a href="videos_liste.php">Vidéos</a></li>
                <li><a href="livres_liste.php">Livres</a></li>
                <li><a href="lois_recherche.php">Lois</a></li>
                <li><a href="podcasts_liste.php">Podcasts</a></li>
                <li><a href="bibliotheque.php">Ma Bibliothèque</a></li>
                <li><a href="assistant_juridique.php">Assistant Juridique</a></li>
                <li><a href="profil.php">Profil</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Résultats du Quiz</h2>
        <p>Pour le cours ID: <?php echo $cours_id; ?></p>
        <section id="resultats">
            <h3>Vos Résultats</h3>
            <!-- Logique PHP pour afficher les résultats -->
            <p>Score: 8/10 (Exemple)</p>
            <p>Détails des réponses à venir...</p>
        </section>
        <p><a href="cours_detail.php?id=<?php echo $cours_id; ?>">Retourner au cours</a></p>
    </main>

    <footer>
        <p>&copy; 2025 LawApp. Tous droits réservés.</p>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
