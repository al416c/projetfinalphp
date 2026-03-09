<?php
require_once 'config/init.php';
$pageTitle = 'Accueil';

// Fetch latest articles
$stmt = $pdo->query("
    SELECT a.*, s.quantite as stock, u.username as auteur_nom, c.nom as categorie_nom,
        (SELECT AVG(note) FROM commentaires WHERE article_id = a.id) as avg_note,
        (SELECT COUNT(*) FROM commentaires WHERE article_id = a.id) as nb_avis
    FROM articles a
    LEFT JOIN stock s ON a.id = s.article_id
    LEFT JOIN users u ON a.auteur_id = u.id
    LEFT JOIN categories c ON a.categorie_id = c.id
    ORDER BY a.date_publication DESC
    LIMIT 8
");
$articles = $stmt->fetchAll();

// Featured articles (top 2 most reviewed)
$stmtFeatured = $pdo->query("
    SELECT a.*, s.quantite as stock, c.nom as categorie_nom,
        (SELECT AVG(note) FROM commentaires WHERE article_id = a.id) as avg_note,
        (SELECT COUNT(*) FROM commentaires WHERE article_id = a.id) as nb_avis
    FROM articles a
    LEFT JOIN stock s ON a.id = s.article_id
    LEFT JOIN categories c ON a.categorie_id = c.id
    ORDER BY nb_avis DESC, avg_note DESC
    LIMIT 2
");
$featured = $stmtFeatured->fetchAll();

// Categories
$stmtCat = $pdo->query("
    SELECT c.*, COUNT(a.id) as nb_articles
    FROM categories c
    LEFT JOIN articles a ON c.id = a.categorie_id
    GROUP BY c.id
    ORDER BY nb_articles DESC
    LIMIT 6
");
$categories = $stmtCat->fetchAll();

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content fade-in">
        <p class="eyebrow">Bienvenue sur NOVA</p>
        <h1 class="headline-1">L'expérience tech<br>réinventée.</h1>
        <p>Découvrez, achetez et vendez les meilleurs produits tech sur la marketplace la plus premium du web.</p>
        <div class="hero-actions">
            <a href="produits.php" class="btn btn-primary btn-lg">Explorer</a>
            <?php if (!isLoggedIn()): ?>
                <a href="inscription.php" class="btn btn-secondary btn-lg" style="border-color: rgba(245,245,247,0.3); color: #f5f5f7;">Créer un compte</a>
            <?php else: ?>
                <a href="vendre.php" class="btn btn-secondary btn-lg" style="border-color: rgba(245,245,247,0.3); color: #f5f5f7;">Vendre un article</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<?php if (count($featured) >= 2): ?>
<section class="section">
    <div class="container-wide">
        <div class="section-header fade-in">
            <p class="eyebrow">En vedette</p>
            <h2 class="headline-2">Les incontournables.</h2>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;" class="stagger-children">
            <?php foreach ($featured as $feat): ?>
            <a href="produit.php?id=<?= $feat['id'] ?>" class="card-featured" style="text-decoration: none; color: inherit;">
                <p class="eyebrow"><?= sanitize($feat['categorie_nom'] ?? 'Nouveauté') ?></p>
                <h3 class="headline-3"><?= sanitize($feat['nom']) ?></h3>
                <p class="body-text text-secondary">À partir de <?= formatPrice($feat['prix']) ?></p>
                <?php if ($feat['image'] && file_exists("uploads/produits/{$feat['image']}")): ?>
                    <img src="uploads/produits/<?= sanitize($feat['image']) ?>" alt="<?= sanitize($feat['nom']) ?>" class="card-featured-img">
                <?php else: ?>
                    <div style="width: 200px; height: 200px; background: rgba(0,0,0,0.04); border-radius: 50%; margin: 30px 0; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-seam" style="font-size: 60px; color: var(--text-tertiary);"></i>
                    </div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories -->
<section class="section-alt">
    <div class="container-wide">
        <div class="section-header fade-in">
            <p class="eyebrow">Catégories</p>
            <h2 class="headline-2">Explorez par univers.</h2>
        </div>
        <div class="category-grid stagger-children">
            <?php foreach ($categories as $cat): ?>
            <a href="categorie.php?id=<?= $cat['id'] ?>" class="category-card">
                <?php if ($cat['image'] && file_exists("uploads/categories/{$cat['image']}")): ?>
                    <img src="uploads/categories/<?= sanitize($cat['image']) ?>" alt="<?= sanitize($cat['nom']) ?>">
                <?php else: ?>
                    <div style="position: absolute; inset: 0; background: var(--gradient-1);"></div>
                <?php endif; ?>
                <div class="category-info">
                    <h3><?= sanitize($cat['nom']) ?></h3>
                    <p><?= $cat['nb_articles'] ?> article<?= $cat['nb_articles'] > 1 ? 's' : '' ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Latest Articles -->
<section class="section">
    <div class="container-wide">
        <div class="section-header fade-in">
            <p class="eyebrow">Nouveautés</p>
            <h2 class="headline-2">Ajoutés récemment.</h2>
            <p>Les derniers articles mis en vente par notre communauté.</p>
        </div>
        <div class="product-grid stagger-children">
            <?php foreach ($articles as $article): ?>
            <a href="produit.php?id=<?= $article['id'] ?>" class="card" style="text-decoration: none; color: inherit;">
                <?php if ($article['image'] && file_exists("uploads/produits/{$article['image']}")): ?>
                    <img src="uploads/produits/<?= sanitize($article['image']) ?>" alt="<?= sanitize($article['nom']) ?>" class="card-img">
                <?php else: ?>
                    <div class="card-img" style="display: flex; align-items: center; justify-content: center; background: var(--bg-secondary);">
                        <i class="bi bi-box-seam" style="font-size: 40px; color: var(--text-tertiary);"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <p class="eyebrow"><?= sanitize($article['categorie_nom'] ?? 'Article') ?></p>
                    <h3><?= sanitize($article['nom']) ?></h3>
                    <div class="card-meta">
                        <span class="price"><?= formatPrice($article['prix']) ?></span>
                        <?php if ($article['avg_note']): ?>
                            <span class="caption" style="display: flex; align-items: center; gap: 4px;">
                                <i class="bi bi-star-fill" style="color: #ff9f0a;"></i>
                                <?= round($article['avg_note'], 1) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4 fade-in">
            <a href="produits.php" class="btn btn-secondary">Voir tous les articles <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section-dark">
    <div class="container" style="text-align: center;">
        <div class="fade-in">
            <p class="eyebrow" style="color: rgba(245,245,247,0.5);">Rejoignez NOVA</p>
            <h2 class="headline-2" style="margin-bottom: 16px;">Prêt à vendre ?</h2>
            <p class="body-large" style="color: rgba(245,245,247,0.6); max-width: 500px; margin: 0 auto 32px;">Listez vos articles tech en quelques clics et rejoignez une communauté de passionnés.</p>
            <?php if (isLoggedIn()): ?>
                <a href="vendre.php" class="btn btn-primary btn-lg">Mettre en vente</a>
            <?php else: ?>
                <a href="inscription.php" class="btn btn-primary btn-lg">Créer un compte gratuitement</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
