<?php
$livre_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : 'N/A';
$type_lecteur = strpos($livre_id, 'pdf') !== false ? 'PDF' : (strpos($livre_id, 'epub') !== false ? 'EPUB' : 'Inconnu');
$titre_livre = "Exemple Livre: Le Droit des Obligations"; // À remplacer
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecteur (<?php echo $type_lecteur; ?>): <?php echo $titre_livre; ?> - LawApp</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>LawApp</h1>
        <nav>
            <ul>
                <li><a href="accueil.php">Accueil</a></li>
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
        <h2><?php echo $titre_livre; ?> (ID: <?php echo $livre_id; ?>)</h2>
        <section id="lecteur-integre">
            <h3>Lecteur <?php echo $type_lecteur; ?></h3>
            <?php if ($type_lecteur === 'PDF'): ?>
                <p>Lecteur PDF à implémenter (ex: via &lt;iframe&gt; ou une librairie JS).</p>
                <!-- <iframe src="livres/exemple.pdf" width="100%" height="600px"></iframe> -->
            <?php elseif ($type_lecteur === 'EPUB'): ?>
                <p>Lecteur EPUB à implémenter (ex: via une librairie JS comme Epub.js).</p>
            <?php else: ?>
                <p>Type de fichier non supporté pour la lecture intégrée.</p>
            <?php endif; ?>
        </section>
        <section id="livre-options">
            <p>Options: Achat ou consultation d'extrait (si applicable)</p>
        </section>
        <p><a href="livres_liste.php">Retour à la liste des livres</a></p>
    </main>

    <footer>
        <p>&copy; 2025 LawApp. Tous droits réservés.</p>
    </footer>

    <script src="js/main.js"></script>
    <!-- Scripts spécifiques pour lecteurs PDF/EPUB si nécessaire -->
</body>
</html>
