<?php
require_once 'config/init.php';
$pageTitle = 'Articles';

// Filters
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$sort = $_GET['sort'] ?? 'recent';
$prix_min = isset($_GET['prix_min']) ? (float)$_GET['prix_min'] : 0;
$prix_max = isset($_GET['prix_max']) ? (float)$_GET['prix_max'] : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if ($categorie_id > 0) {
    $where[] = 'a.categorie_id = ?';
    $params[] = $categorie_id;
}
if ($prix_min > 0) {
    $where[] = 'a.prix >= ?';
    $params[] = $prix_min;
}
if ($prix_max > 0) {
    $where[] = 'a.prix <= ?';
    $params[] = $prix_max;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$orderSQL = match ($sort) {
    'prix_asc' => 'ORDER BY a.prix ASC',
    'prix_desc' => 'ORDER BY a.prix DESC',
    'nom' => 'ORDER BY a.nom ASC',
    'populaire' => 'ORDER BY avg_note DESC, nb_avis DESC',
    default => 'ORDER BY a.date_publication DESC',
};

// Count total
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM articles a $whereSQL");
$stmtCount->execute($params);
$total = $stmtCount->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Fetch articles
$stmtArticles = $pdo->prepare("
    SELECT a.*, s.quantite as stock, u.username as auteur_nom, c.nom as categorie_nom,
        (SELECT AVG(note) FROM commentaires WHERE article_id = a.id) as avg_note,
        (SELECT COUNT(*) FROM commentaires WHERE article_id = a.id) as nb_avis
    FROM articles a
    LEFT JOIN stock s ON a.id = s.article_id
    LEFT JOIN users u ON a.auteur_id = u.id
    LEFT JOIN categories c ON a.categorie_id = c.id
    $whereSQL
    $orderSQL
    LIMIT $perPage OFFSET $offset
");
$stmtArticles->execute($params);
$articles = $stmtArticles->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT c.*, COUNT(a.id) as nb FROM categories c LEFT JOIN articles a ON c.id = a.categorie_id GROUP BY c.id ORDER BY c.nom")->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="fade-in">Explorer</h1>
        <p class="fade-in"><?= $total ?> article<?= $total > 1 ? 's' : '' ?> disponible<?= $total > 1 ? 's' : '' ?></p>
    </div>
</div>

<div class="container-wide">
    <!-- Filters -->
    <div class="filters-bar fade-in">
        <a href="produits.php" class="filter-chip <?= !$categorie_id ? 'active' : '' ?>">Tout</a>
        <?php foreach ($categories as $cat): ?>
            <a href="produits.php?categorie=<?= $cat['id'] ?>&sort=<?= $sort ?>" class="filter-chip <?= $categorie_id === (int)$cat['id'] ? 'active' : '' ?>">
                <?= sanitize($cat['nom']) ?>
            </a>
        <?php endforeach; ?>

        <div style="margin-left: auto;">
            <select class="filter-select" onchange="location.href=this.value">
                <option value="produits.php?<?= $categorie_id ? "categorie=$categorie_id&" : '' ?>sort=recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                <option value="produits.php?<?= $categorie_id ? "categorie=$categorie_id&" : '' ?>sort=prix_asc" <?= $sort === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="produits.php?<?= $categorie_id ? "categorie=$categorie_id&" : '' ?>sort=prix_desc" <?= $sort === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                <option value="produits.php?<?= $categorie_id ? "categorie=$categorie_id&" : '' ?>sort=populaire" <?= $sort === 'populaire' ? 'selected' : '' ?>>Populaires</option>
                <option value="produits.php?<?= $categorie_id ? "categorie=$categorie_id&" : '' ?>sort=nom" <?= $sort === 'nom' ? 'selected' : '' ?>>Nom A-Z</option>
            </select>
        </div>
    </div>

    <!-- Products grid -->
    <?php if (empty($articles)): ?>
        <div class="empty-state fade-in">
            <i class="bi bi-box-seam"></i>
            <h3>Aucun article trouvé</h3>
            <p>Essayez de modifier vos filtres ou revenez plus tard.</p>
            <a href="produits.php" class="btn btn-primary">Voir tous les articles</a>
        </div>
    <?php else: ?>
        <div class="product-grid stagger-children">
            <?php foreach ($articles as $article): ?>
            <a href="produit.php?id=<?= $article['id'] ?>" class="card" style="text-decoration: none; color: inherit; position: relative;">
                <?php if ($article['image'] && file_exists("uploads/produits/{$article['image']}")): ?>
                    <img src="uploads/produits/<?= sanitize($article['image']) ?>" alt="<?= sanitize($article['nom']) ?>" class="card-img">
                <?php else: ?>
                    <div class="card-img" style="display: flex; align-items: center; justify-content: center; background: var(--bg-secondary);">
                        <i class="bi bi-box-seam" style="font-size: 40px; color: var(--text-tertiary);"></i>
                    </div>
                <?php endif; ?>
                <?php if ($article['stock'] <= 0): ?>
                    <span class="stock-badge stock-out">Rupture</span>
                <?php elseif ($article['stock'] <= 5): ?>
                    <span class="stock-badge stock-low pulse-badge">Plus que <?= $article['stock'] ?></span>
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
                                <span>(<?= $article['nb_avis'] ?>)</span>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="caption mt-1">par <?= sanitize($article['auteur_nom']) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination fade-in">
            <?php if ($page > 1): ?>
                <a href="produits.php?page=<?= $page - 1 ?>&categorie=<?= $categorie_id ?>&sort=<?= $sort ?>"><i class="bi bi-chevron-left"></i></a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="produits.php?page=<?= $i ?>&categorie=<?= $categorie_id ?>&sort=<?= $sort ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="produits.php?page=<?= $page + 1 ?>&categorie=<?= $categorie_id ?>&sort=<?= $sort ?>"><i class="bi bi-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div style="height: 80px;"></div>

<?php require_once 'includes/footer.php'; ?>
