<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$pageTitle = 'Administration - ' . SITE_NAME;

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM commandes WHERE statut != 'annulee'")->fetchColumn();

$recentOrders = $pdo->query("SELECT c.*, u.nom, u.prenom FROM commandes c JOIN users u ON c.user_id = u.id ORDER BY c.date_commande DESC LIMIT 5")->fetchAll();

$lowStock = $pdo->query("SELECT * FROM produits WHERE stock < 10 ORDER BY stock ASC LIMIT 5")->fetchAll();
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
                    <a class="nav-link active" href="index.php"><i class="fas fa-tachometer-alt me-2"></i>Tableau de bord</a>
                    <a class="nav-link" href="produits.php"><i class="fas fa-box me-2"></i>Produits</a>
                    <a class="nav-link" href="categories.php"><i class="fas fa-tags me-2"></i>Catégories</a>
                    <a class="nav-link" href="commandes.php"><i class="fas fa-shopping-cart me-2"></i>Commandes</a>
                    <a class="nav-link" href="utilisateurs.php"><i class="fas fa-users me-2"></i>Utilisateurs</a>
                </nav>
            </div>

            <div class="col-md-10 py-4">
                <h1 class="mb-4">Tableau de bord</h1>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stats-card bg-blue">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0"><?= $totalUsers ?></h3>
                                    <p class="mb-0">Clients</p>
                                </div>
                                <i class="fas fa-users fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-green">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0"><?= $totalProducts ?></h3>
                                    <p class="mb-0">Produits</p>
                                </div>
                                <i class="fas fa-box fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-orange">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0"><?= $totalOrders ?></h3>
                                    <p class="mb-0">Commandes</p>
                                </div>
                                <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-purple">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="mb-0"><?= number_format($totalRevenue, 0, ',', ' ') ?> €</h3>
                                    <p class="mb-0">Chiffre d'affaires</p>
                                </div>
                                <i class="fas fa-euro-sign fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Dernières commandes</h5>
                                <a href="commandes.php" class="btn btn-sm btn-primary">Voir tout</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>N°</th>
                                                <th>Client</th>
                                                <th>Total</th>
                                                <th>Statut</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td>#<?= $order['id'] ?></td>
                                                <td><?= htmlspecialchars($order['prenom'] . ' ' . $order['nom']) ?></td>
                                                <td><?= number_format($order['total'], 2, ',', ' ') ?> €</td>
                                                <td>
                                                    <?php
                                                    $statusClass = [
                                                        'en_attente' => 'warning',
                                                        'validee' => 'info',
                                                        'expediee' => 'primary',
                                                        'livree' => 'success',
                                                        'annulee' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?= $statusClass[$order['statut']] ?>"><?= $order['statut'] ?></span>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($order['date_commande'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning"></i> Stock faible</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($lowStock)): ?>
                                    <p class="text-muted mb-0">Aucun produit en stock faible.</p>
                                <?php else: ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($lowStock as $product): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= htmlspecialchars($product['nom']) ?>
                                            <span class="badge bg-<?= $product['stock'] == 0 ? 'danger' : 'warning' ?>"><?= $product['stock'] ?></span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
