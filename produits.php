<?php
require_once 'config/init.php';

$pageTitle = 'Nos produits - ' . SITE_NAME;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = "1=1";
$params = [];

if (isset($_GET['categorie']) && !empty($_GET['categorie'])) {
    $where .= " AND p.categorie_id = ?";
    $params[] = $_GET['categorie'];
}

if (isset($_GET['prix_min']) && is_numeric($_GET['prix_min'])) {
    $where .= " AND p.prix >= ?";
    $params[] = $_GET['prix_min'];
}

if (isset($_GET['prix_max']) && is_numeric($_GET['prix_max'])) {
    $where .= " AND p.prix <= ?";
    $params[] = $_GET['prix_max'];
}

$orderBy = "p.date_ajout DESC";
if (isset($_GET['tri'])) {
    switch ($_GET['tri']) {
        case 'prix_asc':
            $orderBy = "p.prix ASC";
            break;
        case 'prix_desc':
            $orderBy = "p.prix DESC";
            break;
        case 'nom':
            $orderBy = "p.nom ASC";
            break;
    }
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM produits p WHERE $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id WHERE $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$produits = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

require_once 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item active">Produits</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Filtres</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label class="form-label">Catégorie</label>
                            <select name="categorie" class="form-select">
                                <option value="">Toutes</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= (isset($_GET['categorie']) && $_GET['categorie'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prix minimum</label>
                            <input type="number" name="prix_min" class="form-control" value="<?= $_GET['prix_min'] ?? '' ?>" min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prix maximum</label>
                            <input type="number" name="prix_max" class="form-control" value="<?= $_GET['prix_max'] ?? '' ?>" min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trier par</label>
                            <select name="tri" class="form-select">
                                <option value="">Plus récents</option>
                                <option value="prix_asc" <?= (isset($_GET['tri']) && $_GET['tri'] == 'prix_asc') ? 'selected' : '' ?>>Prix croissant</option>
                                <option value="prix_desc" <?= (isset($_GET['tri']) && $_GET['tri'] == 'prix_desc') ? 'selected' : '' ?>>Prix décroissant</option>
                                <option value="nom" <?= (isset($_GET['tri']) && $_GET['tri'] == 'nom') ? 'selected' : '' ?>>Nom</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                        <a href="produits.php" class="btn btn-outline-secondary w-100 mt-2">Réinitialiser</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Nos produits</h1>
                <span class="text-muted"><?= $total ?> produit(s) trouvé(s)</span>
            </div>

            <?php if (empty($produits)): ?>
                <div class="alert alert-info">Aucun produit trouvé avec ces critères.</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($produits as $produit): ?>
                    <div class="col-md-4">
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

                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Précédent</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Suivant</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
