<?php
$loi_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : 'N/A';
$titre_loi = "Exemple: Loi sur la Protection des Données Personnelles"; // À remplacer
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Loi: <?php echo $titre_loi; ?> - LawApp</title>
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
        <h2><?php echo $titre_loi; ?> (ID: <?php echo $loi_id; ?>)</h2>
        <section id="texte-loi">
            <h3>Texte intégral / Articles</h3>
            <article>
                <h4>Article 1</h4>
                <p>Contenu de l'article 1...</p>
                <button class="btn-annotation">Annoter</button>
            </article>
            <article>
                <h4>Article 2</h4>
                <p>Contenu de l'article 2...</p>
                <button class="btn-annotation">Annoter</button>
            </article>
            <!-- Plus d'articles chargés par PHP -->
        </section>
        <section id="annotations-utilisateur">
            <h3>Mes Annotations</h3>
            <p>Vos annotations apparaîtront ici.</p>
        </section>
        <p><a href="lois_recherche.php">Retour à la recherche de lois</a></p>
    </main>

    <footer>
        <p>&copy; 2025 LawApp. Tous droits réservés.</p>
    </footer>

    <script src="js/main.js"></script>
    <script>
        document.querySelectorAll('.btn-annotation').forEach(button => {
            button.addEventListener('click', function() {
                // Logique pour ajouter une annotation
                const article = this.closest('article').querySelector('h4').textContent;
                const annotation = prompt("Votre annotation pour " + article + ":");
                if (annotation) {
                    console.log("Annotation pour", article, ":", annotation);
                    // Ici, sauvegarder l'annotation (côté serveur via AJAX)
                    alert("Annotation enregistrée (simulation) !");
                }
            });
        });
    </script>
</body>
</html>
