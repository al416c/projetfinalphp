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

function getCategoryIcon(string $name): string {
    $label = mb_strtolower($name);
    return match (true) {
        str_contains($label, 'gaming') => 'bi-controller',
        str_contains($label, 'audio') => 'bi-headphones',
        str_contains($label, 'smartphone'), str_contains($label, 'mobile') => 'bi-phone',
        str_contains($label, 'ordinateur'), str_contains($label, 'laptop') => 'bi-laptop',
        str_contains($label, 'photo'), str_contains($label, 'camera') => 'bi-camera',
        str_contains($label, 'accessoire') => 'bi-usb-symbol',
        default => 'bi-grid-1x2-fill'
    };
}

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
                        <div class="category-fallback" aria-hidden="true">
                            <i class="bi <?= getCategoryIcon($cat['nom']) ?>"></i>
                        </div>
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
