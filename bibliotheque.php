<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Bibliothèque Personnelle - LawApp</title>
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
        <h2>Ma Bibliothèque Personnelle</h2>
        <section id="filtres-bibliotheque">
            <h3>Filtrer par type de contenu</h3>
            <button data-type="all">Tout</button>
            <button data-type="cours">Cours Sauvegardés</button>
            <button data-type="videos">Vidéos Favorites</button>
            <button data-type="livres">Livres (Extraits/Achetés)</button>
            <button data-type="lois">Lois Annotées</button>
            <button data-type="podcasts">Podcasts Téléchargés</button>
        </section>

        <section id="contenu-bibliotheque">
            <h3>Contenu (accessible hors ligne si applicable)</h3>
            <!-- Contenu dynamique chargé par PHP/JS -->
            <p>Votre bibliothèque est vide pour le moment.</p>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 LawApp. Tous droits réservés.</p>
    </footer>

    <script src="js/main.js"></script>
    <script>
        document.querySelectorAll('#filtres-bibliotheque button').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type;
                console.log("Filtrer bibliothèque par:", type);
                // Logique pour filtrer et afficher le contenu correspondant
            });
        });
    </script>
</body>
</html>
