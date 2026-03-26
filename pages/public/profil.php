<?php
$pageTitle = 'Profil vendeur';
require_once 'config/init.php';

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    redirect('/produits.php');
}

// Fetch user info
$stmt = $pdo->prepare("SELECT id, username, photo, date_inscription FROM users WHERE id = ?");
$stmt->execute([$userId]);
$seller = $stmt->fetch();

if (!$seller) {
    redirect('/produits.php');
}

// Fetch seller's articles
$stmt = $pdo->prepare("
    SELECT a.*, s.quantite as stock, c.nom as categorie_nom
    FROM articles a
    LEFT JOIN stock s ON a.id = s.article_id
    LEFT JOIN categories c ON a.categorie_id = c.id
    WHERE a.auteur_id = ? AND a.statut = 'en_vente'
    ORDER BY a.date_publication DESC
");
$stmt->execute([$userId]);
$articles = $stmt->fetchAll();

// Stats
$totalArticles = count($articles);
$totalSold = 0;
$stmt2 = $pdo->prepare("
    SELECT COALESCE(SUM(fd.quantite), 0) as total_sold
    FROM facture_details fd
    JOIN articles a ON fd.article_id = a.id
    WHERE a.auteur_id = ?
");
$stmt2->execute([$userId]);
$totalSold = (int)$stmt2->fetchColumn();

// Average rating across all seller's articles
$stmt3 = $pdo->prepare("
    SELECT AVG(c.note) as avg_rating, COUNT(c.id) as total_reviews
    FROM commentaires c
    JOIN articles a ON c.article_id = a.id
    WHERE a.auteur_id = ?
");
$stmt3->execute([$userId]);
$ratingData = $stmt3->fetch();
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : null;
$totalReviews = (int)$ratingData['total_reviews'];

$memberSince = date('d/m/Y', strtotime($seller['date_inscription']));

require_once 'includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
    <div class="container">

        <!-- Seller Profile Card -->
        <div class="seller-profile-card animate-on-scroll">
            <div class="seller-profile-header">
                <div class="seller-avatar-large">
                    <?= strtoupper(substr($seller['username'], 0, 1)) ?>
                </div>
                <div class="seller-profile-info">
                    <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 4px;">
                        <?= sanitize($seller['username']) ?>
                    </h1>
                    <p class="caption" style="font-size: 15px;">
                        <i class="bi bi-calendar3"></i> Membre depuis <?= $memberSince ?>
                    </p>
                </div>
            </div>

            <div class="seller-stats-grid">
                <div class="seller-stat-item">
                    <span class="seller-stat-number"><?= $totalArticles ?></span>
                    <span class="seller-stat-label">Articles en vente</span>
                </div>
                <div class="seller-stat-item">
                    <span class="seller-stat-number"><?= $totalSold ?></span>
                    <span class="seller-stat-label">Ventes réalisées</span>
                </div>
                <div class="seller-stat-item">
                    <span class="seller-stat-number"><?= $avgRating ? $avgRating . '/5' : '—' ?></span>
                    <span class="seller-stat-label"><?= $totalReviews ?> avis</span>
                </div>
                <div class="seller-stat-item">
                    <span class="seller-stat-number"><?= timeAgo($seller['date_inscription']) ?></span>
                    <span class="seller-stat-label">Ancienneté</span>
                </div>
            </div>

            <?php if ($avgRating): ?>
            <div style="margin-top: 16px; text-align: center;">
                <?= renderStars($avgRating) ?>
                <span class="caption" style="margin-left: 8px;"><?= $avgRating ?>/5 sur <?= $totalReviews ?> avis</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Seller's Articles -->
        <div style="margin-top: 48px;">
            <h2 class="section-title animate-on-scroll">
                Articles de <?= sanitize($seller['username']) ?>
                <span class="badge" style="margin-left: 8px;"><?= $totalArticles ?></span>
            </h2>

            <?php if (empty($articles)): ?>
                <div class="empty-state animate-on-scroll">
                    <i class="bi bi-shop" style="font-size: 48px; opacity: .3;"></i>
                    <p>Aucun article en vente pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="products-grid" style="margin-top: 24px;">
                    <?php foreach ($articles as $article): ?>
                        <a href="<?= SITE_URL ?>/produit.php?id=<?= $article['id'] ?>" class="product-card animate-on-scroll">
                            <div class="product-image">
                                <?php if ($article['photo']): ?>
                                    <img src="<?= SITE_URL ?>/uploads/produits/<?= $article['photo'] ?>" alt="<?= sanitize($article['nom']) ?>">
                                <?php else: ?>
                                    <div style="height:100%;display:flex;align-items:center;justify-content:center;">
                                        <i class="bi bi-image" style="font-size:48px;opacity:.2"></i>
                                    </div>
                                <?php endif; ?>
                                <?php if ($article['stock'] <= 0): ?>
                                    <span class="stock-badge stock-out">Rupture</span>
                                <?php elseif ($article['stock'] <= 5): ?>
                                    <span class="stock-badge stock-low">Plus que <?= $article['stock'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <span class="product-category"><?= sanitize($article['categorie_nom']) ?></span>
                                <h3 class="product-name"><?= sanitize($article['nom']) ?></h3>
                                <div class="product-footer">
                                    <span class="product-price"><?= formatPrice($article['prix']) ?></span>
                                    <?php $rating = getAverageRating($article['id']); ?>
                                    <?php if ($rating['average'] > 0): ?>
                                        <span class="product-rating">
                                            <i class="bi bi-star-fill"></i> <?= number_format($rating['average'], 1) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
