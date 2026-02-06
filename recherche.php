<?php
require_once 'config/init.php';

$pageTitle = 'Recherche - ' . SITE_NAME;

$q = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$produits = [];

if (!empty($q)) {
    $searchTerm = "%$q%";
    $stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.nom LIKE ? OR p.description LIKE ? ORDER BY p.nom");
    $stmt->execute([$searchTerm, $searchTerm]);
    $produits = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Recherche</h1>

    <form method="GET" class="mb-4">
        <div class="input-group input-group-lg">
            <input type="search" name="q" class="form-control" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($q) ?>">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Rechercher</button>
        </div>
    </form>

    <?php if (!empty($q)): ?>
        <p class="text-muted mb-4"><?= count($produits) ?> résultat(s) pour "<?= htmlspecialchars($q) ?>"</p>

        <?php if (empty($produits)): ?>
            <div class="alert alert-info">Aucun produit ne correspond à votre recherche.</div>
        <?php else: ?>
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
                            <a href="produit.php?id=<?= $produit['id'] ?>" class="btn btn-outline-primary btn-sm w-100">Voir détails</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
