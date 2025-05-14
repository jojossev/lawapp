<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livres Juridiques - LawApp</title>
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
        <h2>Livres Juridiques</h2>
        <section id="recherche-livres">
            <input type="search" placeholder="Rechercher un livre...">
            <button>Rechercher</button>
        </section>
        <section id="liste-des-livres">
            <h3>Tous les livres</h3>
            <!-- Boucle PHP pour afficher les livres -->
            <p>Aucun livre disponible. <a href="livre_lecteur.php?id=exemple_pdf">Consulter un extrait PDF</a> ou <a href="livre_lecteur.php?id=exemple_epub">Consulter un extrait EPUB</a>.</p>
             <!-- Exemple de carte livre -->
            <div class="livre-card">
                <h4>Titre du Livre Exemple</h4>
                <p>Auteur. Brève description.</p>
                <a href="livre_lecteur.php?id=exemple1_extrait">Lire Extrait</a> | <a href="#">Acheter</a>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 LawApp. Tous droits réservés.</p>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
