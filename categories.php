<?php
require_once 'config/init.php';
$pageTitle = 'Catégories';

$stmt = $pdo->query("
    SELECT c.*, COUNT(a.id) as nb_articles
    FROM categories c
    LEFT JOIN articles a ON c.id = a.categorie_id
    GROUP BY c.id
    ORDER BY c.nom
");
$categories = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="fade-in">Catégories</h1>
        <p class="fade-in">Explorez nos univers tech.</p>
    </div>
</div>

<section class="section">
    <div class="container-wide">
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
                    <?php if ($cat['description']): ?>
                        <p style="margin-top: 4px; font-size: 13px; opacity: 0.7;"><?= sanitize(substr($cat['description'], 0, 80)) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
