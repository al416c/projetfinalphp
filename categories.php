<?php
require_once 'config/init.php';

$pageTitle = 'Catégories - ' . SITE_NAME;

$stmt = $pdo->query("SELECT c.*, COUNT(p.id) as nb_produits FROM categories c LEFT JOIN produits p ON c.id = p.categorie_id GROUP BY c.id ORDER BY c.nom");
$categories = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item active">Catégories</li>
        </ol>
    </nav>

    <h1 class="mb-4">Nos catégories</h1>

    <div class="row g-4">
        <?php foreach ($categories as $cat): ?>
        <div class="col-md-4">
            <a href="categorie.php?id=<?= $cat['id'] ?>" class="text-decoration-none">
                <div class="card h-100">
                    <?php if ($cat['image']): ?>
                        <img src="uploads/categories/<?= $cat['image'] ?>" class="card-img-top" style="height:200px; object-fit:cover;" alt="<?= htmlspecialchars($cat['nom']) ?>">
                    <?php else: ?>
                        <div class="card-img-top img-placeholder" style="height:200px;"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title text-dark"><?= htmlspecialchars($cat['nom']) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($cat['description'] ?? '') ?></p>
                        <span class="badge bg-primary"><?= $cat['nb_produits'] ?> produit<?= $cat['nb_produits'] > 1 ? 's' : '' ?></span>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
