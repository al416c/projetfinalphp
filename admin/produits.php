<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$pageTitle = 'Gestion des produits - ' . SITE_NAME;

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    redirect('produits.php');
}

$stmt = $pdo->query("SELECT p.*, c.nom as categorie_nom FROM produits p LEFT JOIN categories c ON p.categorie_id = c.id ORDER BY p.date_ajout DESC");
$produits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= SITE_URL ?>/admin/">
                <i class="fas fa-cog"></i> Admin - <?= SITE_NAME ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= SITE_URL ?>"><i class="fas fa-external-link-alt"></i> Voir le site</a>
                <a class="nav-link" href="<?= SITE_URL ?>/deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 admin-sidebar py-3">
                <nav class="nav flex-column">
                    <a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</a>
                    <a class="nav-link active" href="produits.php"><i class="fas fa-box me-2"></i>Produits</a>
                    <a class="nav-link" href="categories.php"><i class="fas fa-tags me-2"></i>Catégories</a>
                    <a class="nav-link" href="commandes.php"><i class="fas fa-shopping-cart me-2"></i>Commandes</a>
                    <a class="nav-link" href="utilisateurs.php"><i class="fas fa-users me-2"></i>Utilisateurs</a>
                </nav>
            </div>

            <div class="col-md-10 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Gestion des produits</h1>
                    <a href="produit-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter un produit</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Image</th>
                                        <th>Nom</th>
                                        <th>Catégorie</th>
                                        <th>Prix</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produits as $produit): ?>
                                    <tr>
                                        <td>
                                            <?php if ($produit['image']): ?>
                                                <img src="<?= SITE_URL ?>/uploads/produits/<?= $produit['image'] ?>" width="50" height="50" style="object-fit:cover;" class="rounded">
                                            <?php else: ?>
                                                <div class="img-placeholder rounded" style="width:50px;height:50px;"><i class="fas fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($produit['nom']) ?></td>
                                        <td><?= htmlspecialchars($produit['categorie_nom'] ?? 'Non classé') ?></td>
                                        <td><?= number_format($produit['prix'], 2, ',', ' ') ?> €</td>
                                        <td>
                                            <span class="badge bg-<?= $produit['stock'] > 10 ? 'success' : ($produit['stock'] > 0 ? 'warning' : 'danger') ?>">
                                                <?= $produit['stock'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="produit-form.php?id=<?= $produit['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                            <a href="produits.php?delete=<?= $produit['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce produit ?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
