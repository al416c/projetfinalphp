<!-- Footer -->
<footer class="footer">
    <div class="container-wide">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= SITE_URL ?>/" class="nav-logo"><span>NOVA</span></a>
                <p>La marketplace premium pour les passionnés de tech. Achetez et vendez des produits de qualité en toute confiance.</p>
            </div>
            <div>
                <h4>Explorer</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/produits.php">Tous les articles</a></li>
                    <li><a href="<?= SITE_URL ?>/categories.php">Catégories</a></li>
                    <li><a href="<?= SITE_URL ?>/recherche.php">Recherche</a></li>
                </ul>
            </div>
            <div>
                <h4>Compte</h4>
                <ul>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?= SITE_URL ?>/compte.php">Mon profil</a></li>
                        <li><a href="<?= SITE_URL ?>/commandes.php">Mes factures</a></li>
                        <li><a href="<?= SITE_URL ?>/vendre.php">Vendre un article</a></li>
                    <?php else: ?>
                        <li><a href="<?= SITE_URL ?>/connexion.php">Connexion</a></li>
                        <li><a href="<?= SITE_URL ?>/inscription.php">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div>
                <h4>Informations</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                    <li><a href="<?= SITE_URL ?>/cgv.php">CGV</a></li>
                    <li><a href="<?= SITE_URL ?>/mentions-legales.php">Mentions légales</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.</p>
            <div class="footer-social">
                <a href="#"><i class="bi bi-twitter-x"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-github"></i></a>
                <a href="#"><i class="bi bi-linkedin"></i></a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
