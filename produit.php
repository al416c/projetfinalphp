<?php
require_once 'config/init.php';

if (!isset($_GET['id'])) {
    redirect('produits.php');
}

$stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.id = ?");
$stmt->execute([$_GET['id']]);
$produit = $stmt->fetch();

if (!$produit) {
    redirect('produits.php');
}

$pageTitle = $produit['nom'] . ' - ' . SITE_NAME;

$stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.categorie_id = ? AND p.id != ? LIMIT 4");
$stmt->execute([$produit['categorie_id'], $produit['id']]);
$produitsAssocies = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item"><a href="produits.php">Produits</a></li>
            <?php if ($produit['categorie_nom']): ?>
                <li class="breadcrumb-item"><a href="categorie.php?id=<?= $produit['categorie_id'] ?>"><?= htmlspecialchars($produit['categorie_nom']) ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?= htmlspecialchars($produit['nom']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <?php if ($produit['image']): ?>
                <img src="uploads/produits/<?= $produit['image'] ?>" class="img-fluid product-detail-img rounded" alt="<?= htmlspecialchars($produit['nom']) ?>">
            <?php else: ?>
                <div class="img-placeholder rounded" style="height:400px;"><i class="fas fa-image"></i></div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <span class="badge bg-secondary mb-2"><?= htmlspecialchars($produit['categorie_nom'] ?? 'Non classé') ?></span>
            <h1><?= htmlspecialchars($produit['nom']) ?></h1>
            
            <p class="price fs-2"><?= number_format($produit['prix'], 2, ',', ' ') ?> €</p>
            
            <?php if ($produit['stock'] > 0): ?>
                <span class="badge bg-success mb-3">En stock (<?= $produit['stock'] ?> disponible<?= $produit['stock'] > 1 ? 's' : '' ?>)</span>
            <?php else: ?>
                <span class="badge bg-danger mb-3">Rupture de stock</span>
            <?php endif; ?>
            
            <div class="mb-4">
                <h5>Description</h5>
                <p><?= nl2br(htmlspecialchars($produit['description'] ?? 'Aucune description disponible.')) ?></p>
            </div>
            
            <?php if ($produit['stock'] > 0): ?>
            <form action="ajax/add_cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="produit_id" value="<?= $produit['id'] ?>">
                <div class="row g-3 align-items-center mb-4">
                    <div class="col-auto">
                        <label class="form-label mb-0">Quantité:</label>
                    </div>
                    <div class="col-auto">
                        <input type="number" name="quantite" class="form-control quantity-input" value="1" min="1" max="<?= $produit['stock'] ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-cart-plus"></i> Ajouter au panier
                        </button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
            
            <hr>
            <div class="text-muted small">
                <p class="mb-1"><i class="fas fa-truck"></i> Livraison gratuite dès 50€ d'achat</p>
                <p class="mb-1"><i class="fas fa-undo"></i> Retours gratuits sous 30 jours</p>
                <p class="mb-0"><i class="fas fa-shield-alt"></i> Paiement sécurisé</p>
            </div>
        </div>
    </div>

    <?php if (!empty($produitsAssocies)): ?>
    <section class="mt-5">
        <h3>Produits similaires</h3>
        <div class="row g-4">
            <?php foreach ($produitsAssocies as $p): ?>
            <div class="col-md-3">
                <div class="card card-product">
                    <?php if ($p['image']): ?>
                        <img src="uploads/produits/<?= $p['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($p['nom']) ?>">
                    <?php else: ?>
                        <div class="card-img-top img-placeholder" style="height:200px;"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($p['nom']) ?></h5>
                        <p class="price"><?= number_format($p['prix'], 2, ',', ' ') ?> €</p>
                        <a href="produit.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm w-100">Voir détails</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
