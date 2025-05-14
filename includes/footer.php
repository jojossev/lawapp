</main> <!-- Fermeture de la balise main ouverte dans header.php -->

<footer class="main-footer-enhanced">
    <div class="footer-content">
        <div class="footer-section about">
            <h4>À propos de LawApp</h4>
            <p>Votre plateforme N°1 pour maîtriser le droit. Cours, vidéos, articles de loi, et plus encore, conçus par des experts.</p>
            <!-- Icônes de réseaux sociaux (exemple) -->
            <div class="social-icons">
                <a href="#"><img src="path/to/facebook-icon.svg" alt="Facebook"></a>
                <a href="#"><img src="path/to/twitter-icon.svg" alt="Twitter"></a>
                <a href="#"><img src="path/to/linkedin-icon.svg" alt="LinkedIn"></a>
            </div>
        </div>
        <div class="footer-section links">
            <h4>Liens Utiles</h4>
            <ul>
                <li><a href="accueil.php">Accueil</a></li>
                <li><a href="cours_liste.php">Tous les Cours</a></li>
                <li><a href="faq.php">FAQ</a></li> <!-- page FAQ à créer -->
                <li><a href="contact.php">Contactez-nous</a></li> <!-- page Contact à créer -->
                <li><a href="mentions_legales.php">Mentions Légales</a></li> <!-- page Mentions Légales à créer -->
            </ul>
        </div>
        <div class="footer-section newsletter">
            <h4>Notre Newsletter</h4>
            <p>Restez informé des dernières nouveautés et actualités juridiques.</p>
            <form action="subscribe_newsletter.php" method="post"> <!-- script de newsletter à créer -->
                <input type="email" name="email" placeholder="Votre adresse email" required>
                <button type="submit">S'inscrire</button>
            </form>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> LawApp. Tous droits réservés.</p>
        <p>Développé avec ❤️️ par [Votre Nom/Nom de l'Équipe]</p>
    </div>
</footer>

<!-- Scripts JS globaux (si nécessaire, par exemple pour des interactions communes) -->
<!-- <script src="js/main.js"></script> --> <!-- Chemin relatif depuis la racine -->
</body>
</html>
