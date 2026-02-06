<?php
require_once 'config/init.php';

if (!isset($_GET['id'])) {
    redirect('categories.php');
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$_GET['id']]);
$categorie = $stmt->fetch();

if (!$categorie) {
    redirect('categories.php');
}

$pageTitle = $categorie['nom'] . ' - ' . SITE_NAME;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE categorie_id = ?");
$countStmt->execute([$categorie['id']]);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT * FROM produits WHERE categorie_id = ? ORDER BY date_ajout DESC LIMIT ? OFFSET ?");
$stmt->execute([$categorie['id'], $perPage, $offset]);
$produits = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item"><a href="categories.php">Catégories</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($categorie['nom']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><?= htmlspecialchars($categorie['nom']) ?></h1>
            <?php if ($categorie['description']): ?>
                <p class="text-muted"><?= htmlspecialchars($categorie['description']) ?></p>
            <?php endif; ?>
        </div>
        <span class="text-muted"><?= $total ?> produit(s)</span>
    </div>

    <?php if (empty($produits)): ?>
        <div class="alert alert-info">Aucun produit dans cette catégorie.</div>
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

        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?id=<?= $categorie['id'] ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
