<?php
require_once 'config/init.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('categories.php');

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$categorie = $stmt->fetch();

if (!$categorie) {
    setFlash('error', 'Catégorie introuvable.');
    redirect('categories.php');
}

$pageTitle = $categorie['nom'];
$sort = $_GET['sort'] ?? 'recent';

$orderSQL = match ($sort) {
    'prix_asc' => 'ORDER BY a.prix ASC',
    'prix_desc' => 'ORDER BY a.prix DESC',
    'populaire' => 'ORDER BY avg_note DESC',
    default => 'ORDER BY a.date_publication DESC',
};

$stmtArticles = $pdo->prepare("
    SELECT a.*, s.quantite as stock, u.username as auteur_nom,
        (SELECT AVG(note) FROM commentaires WHERE article_id = a.id) as avg_note,
        (SELECT COUNT(*) FROM commentaires WHERE article_id = a.id) as nb_avis
    FROM articles a
    LEFT JOIN stock s ON a.id = s.article_id
    LEFT JOIN users u ON a.auteur_id = u.id
    WHERE a.categorie_id = ?
    $orderSQL
");
$stmtArticles->execute([$id]);
$articles = $stmtArticles->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <p class="eyebrow fade-in" style="color: rgba(245,245,247,0.5);">Catégorie</p>
        <h1 class="fade-in"><?= sanitize($categorie['nom']) ?></h1>
        <?php if ($categorie['description']): ?>
            <p class="fade-in"><?= sanitize($categorie['description']) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="container-wide">
    <div class="filters-bar fade-in">
        <span class="caption"><?= count($articles) ?> article<?= count($articles) > 1 ? 's' : '' ?></span>
        <div style="margin-left: auto;">
            <select class="filter-select" onchange="location.href=this.value">
                <option value="categorie.php?id=<?= $id ?>&sort=recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                <option value="categorie.php?id=<?= $id ?>&sort=prix_asc" <?= $sort === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="categorie.php?id=<?= $id ?>&sort=prix_desc" <?= $sort === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                <option value="categorie.php?id=<?= $id ?>&sort=populaire" <?= $sort === 'populaire' ? 'selected' : '' ?>>Populaires</option>
            </select>
        </div>
    </div>

    <?php if (empty($articles)): ?>
        <div class="empty-state fade-in">
            <i class="bi bi-box-seam"></i>
            <h3>Aucun article dans cette catégorie</h3>
            <p>Soyez le premier à vendre dans cette catégorie !</p>
            <a href="vendre.php" class="btn btn-primary">Vendre un article</a>
        </div>
    <?php else: ?>
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
                    <p class="caption mt-1">par <?= sanitize($article['auteur_nom']) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div style="height: 80px;"></div>

<?php require_once 'includes/footer.php'; ?>
