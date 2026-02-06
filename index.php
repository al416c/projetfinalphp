<?php
require_once 'config/init.php';

$pageTitle = SITE_NAME . ' - Accueil';

$stmt = $pdo->query("SELECT * FROM categories LIMIT 4");
$categories = $stmt->fetchAll();

$stmt = $pdo->query("SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id ORDER BY p.date_ajout DESC LIMIT 8");
$produits = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<section class="hero-section text-center">
    <div class="container">
        <h1>Bienvenue sur <?= SITE_NAME ?></h1>
        <p class="lead">Découvrez notre sélection de produits de qualité</p>
        <a href="produits.php" class="btn btn-light btn-lg">Voir nos produits</a>
    </div>
</section>

<div class="container">
    <section class="mb-5">
        <h2 class="mb-4">Nos catégories</h2>
        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
            <div class="col-md-3">
                <a href="categorie.php?id=<?= $cat['id'] ?>" class="text-decoration-none">
                    <div class="category-card">
                        <?php if ($cat['image']): ?>
                            <img src="uploads/categories/<?= $cat['image'] ?>" alt="<?= htmlspecialchars($cat['nom']) ?>">
                        <?php else: ?>
                            <div class="img-placeholder h-100"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                        <div class="overlay">
                            <h5 class="mb-0"><?= htmlspecialchars($cat['nom']) ?></h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Nouveautés</h2>
            <a href="produits.php" class="btn btn-outline-primary">Voir tout</a>
        </div>
        <div class="row g-4">
            <?php foreach ($produits as $produit): ?>
            <div class="col-md-3">
                <div class="card card-product">
                    <?php if ($produit['image']): ?>
                        <img src="uploads/produits/<?= $produit['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($produit['nom']) ?>">
                    <?php else: ?>
                        <div class="card-img-top img-placeholder" style="height:200px;"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2"><?= htmlspecialchars($produit['categorie_nom'] ?? 'Non classé') ?></span>
                        <h5 class="card-title"><?= htmlspecialchars($produit['nom']) ?></h5>
                        <p class="price"><?= number_format($produit['prix'], 2, ',', ' ') ?> €</p>
                        <?php if ($produit['stock'] > 0): ?>
                            <span class="badge bg-success stock-badge mb-2">En stock</span>
                        <?php else: ?>
                            <span class="badge bg-danger stock-badge mb-2">Rupture</span>
                        <?php endif; ?>
                        <div class="d-grid gap-2">
                            <a href="produit.php?id=<?= $produit['id'] ?>" class="btn btn-outline-primary btn-sm">Voir détails</a>
                            <?php if ($produit['stock'] > 0): ?>
                            <form action="ajax/add_cart.php" method="POST" class="add-to-cart-form">
                                <input type="hidden" name="produit_id" value="<?= $produit['id'] ?>">
                                <input type="hidden" name="quantite" value="1">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-cart-plus"></i> Ajouter
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
