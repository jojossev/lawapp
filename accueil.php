<?php
$page_title = "Accueil";
$extra_css = "<link rel='stylesheet' href='css/animations.css'>
<link rel='stylesheet' href='css/home.css'>";
require_once 'includes/header.php';

// Récupération dynamique des catégories de cours depuis la base de données
$categories_cours = [];
try {
    $stmt_cat = $pdo->query("SELECT id, nom FROM categories_cours WHERE statut = 'actif' ORDER BY nom ASC"); // Supposant une colonne 'statut' et 'nom'
    if ($stmt_cat) {
        $categories_cours = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Gérer l'erreur, par exemple logger et/ou afficher un message discret
    error_log("Erreur de chargement des catégories de cours sur accueil.php: " . $e->getMessage());
    // On pourrait initialiser avec des catégories par défaut ou un tableau vide
}

// Ajout manuel de l'option "Tout voir" pour l'interface utilisateur
// Assurons-nous que l'ID est unique et ne risque pas de collision avec les ID de la BDD (par ex. utiliser 0 ou une chaîne)
array_push($categories_cours, ['id' => 'all', 'nom' => 'Tout voir']);


// Récupération dynamique des cours "populaires" (ici, les 4 plus récents publiés)
$cours_populaires = [];
try {
    $stmt_cours = $pdo->query("
        SELECT 
            c.id, 
            c.titre, 
            c.description, 
            c.prix, 
            c.image_url, 
            c.niveau, 
            c.duree, 
            c.note_moyenne AS note, /* Supposant une colonne note_moyenne */
            CONCAT(u.prenom, ' ', u.nom) AS auteur,
            c.id_categorie, /* Ajout de l'ID de la catégorie du cours */
            cc.nom AS nom_categorie /* Au cas où on en aurait besoin plus tard */
        FROM 
            cours c
        LEFT JOIN 
            utilisateurs u ON c.id_createur = u.id
        LEFT JOIN
            categories_cours cc ON c.id_categorie = cc.id
        WHERE 
            c.statut = 'publie'
        ORDER BY 
            c.id DESC /* Ou c.date_creation DESC si disponible */
        LIMIT 4
    ");
    if ($stmt_cours) {
        $cours_populaires = $stmt_cours->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Erreur de chargement des cours populaires sur accueil.php: " . $e->getMessage());
    // $cours_populaires reste un tableau vide, le HTML gérera l'affichage "Aucun cours"
}

// Si certaines colonnes attendues par le HTML (ex: 'note') ne sont pas dans la table 'cours'
// ou si l'on veut s'assurer qu'elles existent toujours, on peut itérer et ajouter des valeurs par défaut.
// Par exemple, si 'note' n'est pas toujours présente :
foreach ($cours_populaires as $key => $cours) {
    if (!isset($cours_populaires[$key]['note'])) {
        $cours_populaires[$key]['note'] = 'N/A'; // Ou une valeur par défaut
    }
    if (!isset($cours_populaires[$key]['auteur'])) {
        $cours_populaires[$key]['auteur'] = 'Auteur inconnu';
    }
    // S'assurer que prix a une valeur, même si NULL dans la BDD
    if (!isset($cours_populaires[$key]['prix']) || is_null($cours_populaires[$key]['prix'])) {
        $cours_populaires[$key]['prix'] = 'Gratuit'; // Ou 'Prix non spécifié'
    }
}

$prenom_utilisateur = "Visiteur"; // Ou récupérer depuis la session si connecté (déjà géré dans header.php pour affichage)
// Si $_SESSION['user_prenom'] existe, on pourrait l'utiliser ici pour personnaliser le message d'accueil
if (isset($_SESSION['user_prenom'])) {
    $prenom_utilisateur = $_SESSION['user_prenom'];
}

?>

        <!-- Hero Section Animée -->
        <section class="hero-banner glass gradient-primary animate-fadeIn">
            <div class="hero-content">
                <h1 class="animate-slideIn">Bienvenue sur LawApp, <?php echo htmlspecialchars($prenom_utilisateur); ?> !</h1>
                <p class="animate-fadeIn delay-200">Votre plateforme d'apprentissage du droit, simplifiée et accessible.</p>
                <div class="search-bar-hero glass animate-fadeIn delay-300">
                    <form action="recherche.php" method="get">
                        <input type="search" name="q_hero" class="glass" placeholder="Que voulez-vous apprendre aujourd'hui ? (ex: Droit des sociétés)">
                        <button type="submit" class="btn-search hover-scale">Rechercher</button>
                    </form>
                </div>
                <div class="hero-stats animate-fadeIn delay-400">
                    <div class="stat-item hover-lift">
                        <span class="stat-number">1000+</span>
                        <span class="stat-label">Étudiants</span>
                    </div>
                    <div class="stat-item hover-lift">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Cours</span>
                    </div>
                    <div class="stat-item hover-lift">
                        <span class="stat-number">95%</span>
                        <span class="stat-label">Satisfaction</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section Catégories -->
        <section class="categories-section animate-fadeIn delay-200">
            <h2 class="section-title">Explorez nos domaines de droit</h2>
            <div class="categories-grid">

        <section class="course-selection">
            <h3>Une large sélection de formations en Droit</h3>
            <div class="category-tabs">
                <?php foreach ($categories_cours as $categorie): ?>
                    <button type="button" class="<?php echo ($categorie['nom'] === 'Droit Constitutionnel' ? 'active' : ''); ?>" data-category-id="<?php echo $categorie['id']; ?>">
                        <?php echo htmlspecialchars($categorie['nom']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="course-grid" id="course-grid-container">
                <?php foreach ($cours_populaires as $cours): ?>
                <div class="course-card">
                    <img src="<?php echo htmlspecialchars($cours['image_url']); ?>" alt="Image du cours <?php echo htmlspecialchars($cours['titre']); ?>">
                    <div class="course-card-content">
                        <h4><?php echo htmlspecialchars($cours['titre']); ?></h4>
                        <p class="author"><?php echo htmlspecialchars($cours['auteur']); ?></p>
                        <div class="rating">
                            <span>⭐ <?php echo htmlspecialchars($cours['note']); ?></span>
                        </div>
                        <p class="price"><?php echo htmlspecialchars($cours['prix']); ?></p>
                        <a href="view_course.php?id=<?php echo $cours['id']; ?>" class="btn-view-course">Voir le cours</a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($cours_populaires)): ?>
                <p>Aucun cours à afficher pour le moment. Revenez bientôt !</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- D'autres sections peuvent être ajoutées ici : "Les étudiants consultent", "Catégories populaires", etc. -->

    <script>
        // Petit script pour la gestion des onglets de catégorie (simpliste)
        // Ce script est spécifique à accueil.php, donc on le laisse ici pour l'instant.
        // Il pourrait être déplacé dans un fichier js/accueil.js et inclus par le footer si besoin.
        document.addEventListener('DOMContentLoaded', function() {
            const categoryTabs = document.querySelectorAll('.category-tabs button');
            const courseGrid = document.getElementById('course-grid-container');
            
            // Simuler les données de cours pour JS (en PHP, elles viennent déjà du tableau $cours_populaires)
            const allCoursesData = <?php echo json_encode($cours_populaires); ?>;

            categoryTabs.forEach(button => {
                button.addEventListener('click', function() {
                    document.querySelector('.category-tabs button.active').classList.remove('active');
                    this.classList.add('active');
                    const categoryId = this.dataset.categoryId;
                    const categoryName = this.textContent.trim();
                    
                    console.log("Charger les cours pour la catégorie ID:", categoryId, "Nom:", categoryName);
                    
                    // Vider la grille actuelle
                    courseGrid.innerHTML = ''; 

                    if(categoryId === 'all') { // "Tout voir"
                        renderCourses(allCoursesData);
                    } else {
                        // Filtrer les cours par l'ID de catégorie
                        const filteredCourses = allCoursesData.filter(course => course.id_categorie == categoryId);
                        if (filteredCourses.length > 0) {
                            renderCourses(filteredCourses);
                        } else {
                            courseGrid.innerHTML = '<p>Aucun cours trouvé pour la catégorie \'' + categoryName + '\'.</p>';
                        }
                    }
                });
            });

            function renderCourses(coursesToRender) {
                if (coursesToRender.length === 0) {
                    courseGrid.innerHTML = '<p>Aucun cours trouvé pour cette sélection.</p>';
                    return;
                }
                coursesToRender.forEach(cours => {
                    const courseCardHTML = `
                        <div class="course-card">
                            <img src="${cours.image_url}" alt="Image du cours ${cours.titre}">
                            <div class="course-card-content">
                                <h4>${cours.titre}</h4>
                                <p class="author">${cours.auteur}</p>
                                <div class="rating">
                                    <span>⭐ ${cours.note}</span>
                                </div>
                                <p class="price">${cours.prix}</p>
                                <a href="view_course.php?id=${cours.id}" class="btn-view-course">Voir le cours</a>
                            </div>
                        </div>
                    `;
                    courseGrid.innerHTML += courseCardHTML;
                });
            }
        });
    </script>

<?php
require_once 'includes/footer.php'; // Inclut le footer commun
?>
