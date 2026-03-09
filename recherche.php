<?php
require_once 'config/init.php';
$pageTitle = 'Recherche';

$query = sanitize($_GET['q'] ?? '');
$articles = [];

if (!empty($query)) {
    $stmt = $pdo->prepare("
        SELECT a.*, s.quantite as stock, u.username as auteur_nom, c.nom as categorie_nom,
            (SELECT AVG(note) FROM commentaires WHERE article_id = a.id) as avg_note
        FROM articles a
        LEFT JOIN stock s ON a.id = s.article_id
        LEFT JOIN users u ON a.auteur_id = u.id
        LEFT JOIN categories c ON a.categorie_id = c.id
        WHERE a.nom LIKE ? OR a.description LIKE ?
        ORDER BY a.date_publication DESC
        LIMIT 50
    ");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $articles = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="fade-in">Recherche</h1>
        <?php if ($query): ?>
            <p class="fade-in"><?= count($articles) ?> résultat<?= count($articles) > 1 ? 's' : '' ?> pour « <?= sanitize($query) ?> »</p>
        <?php else: ?>
            <p class="fade-in">Trouvez l'article parfait.</p>
        <?php endif; ?>
    </div>
</div>

<div class="container-wide" style="padding-top: 32px; padding-bottom: 80px;">
    <!-- Search bar -->
    <form action="recherche.php" method="GET" style="max-width: 600px; margin: 0 auto 48px;" class="fade-in">
        <div style="display: flex; gap: 8px;">
            <input type="text" name="q" class="form-control" placeholder="Rechercher un article..." value="<?= sanitize($query) ?>" autofocus style="font-size: 19px; padding: 14px 20px;">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
        </div>
    </form>

    <?php if ($query && empty($articles)): ?>
        <div class="empty-state fade-in">
            <i class="bi bi-search"></i>
            <h3>Aucun résultat</h3>
            <p>Essayez avec d'autres termes de recherche.</p>
        </div>
    <?php elseif (!empty($articles)): ?>
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
                            <span class="caption"><i class="bi bi-star-fill" style="color: #ff9f0a;"></i> <?= round($article['avg_note'], 1) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
