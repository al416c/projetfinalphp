<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$pageTitle = 'Administration';

// Stats
$stats = [];
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['articles'] = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$stats['factures'] = $pdo->query("SELECT COUNT(*) FROM factures")->fetchColumn();
$stats['revenue'] = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM factures")->fetchColumn();

// Recent orders
$recentOrders = $pdo->query("
    SELECT f.*, u.username
    FROM factures f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.date_facture DESC
    LIMIT 5
")->fetchAll();

// Recent users
$recentUsers = $pdo->query("
    SELECT * FROM users ORDER BY date_inscription DESC LIMIT 5
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3><i class="bi bi-gear-fill"></i> Admin</h3>
        </div>
        <nav class="admin-nav">
            <a href="<?= SITE_URL ?>/admin/index.php" class="admin-nav-link active">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
            <a href="<?= SITE_URL ?>/admin/produits.php" class="admin-nav-link">
                <i class="bi bi-box-seam"></i> Articles
            </a>
            <a href="<?= SITE_URL ?>/admin/categories.php" class="admin-nav-link">
                <i class="bi bi-grid"></i> Catégories
            </a>
            <a href="<?= SITE_URL ?>/admin/commandes.php" class="admin-nav-link">
                <i class="bi bi-receipt"></i> Factures
            </a>
            <a href="<?= SITE_URL ?>/admin/utilisateurs.php" class="admin-nav-link">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
            <hr>
            <a href="<?= SITE_URL ?>/index.php" class="admin-nav-link">
                <i class="bi bi-arrow-left"></i> Retour au site
            </a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Tableau de bord</h1>
            <p>Vue d'ensemble de la plateforme NOVA</p>
        </div>

        <div class="admin-stats-grid fade-in">
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: var(--accent-gradient);">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-number"><?= $stats['users'] ?></span>
                    <span class="admin-stat-label">Utilisateurs</span>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: linear-gradient(135deg, #34c759, #30d158);">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-number"><?= $stats['articles'] ?></span>
                    <span class="admin-stat-label">Articles</span>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: linear-gradient(135deg, #ff9500, #ff6b00);">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-number"><?= $stats['factures'] ?></span>
                    <span class="admin-stat-label">Factures</span>
                </div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-icon" style="background: linear-gradient(135deg, #af52de, #bf5af2);">
                    <i class="bi bi-currency-euro"></i>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-number"><?= formatPrice($stats['revenue']) ?></span>
                    <span class="admin-stat-label">Chiffre d'affaires</span>
                </div>
            </div>
        </div>

        <div class="admin-grid fade-in">
            <div class="admin-card">
                <h3><i class="bi bi-clock-history"></i> Dernières commandes</h3>
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted">Aucune commande pour le moment.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Client</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= sanitize($order['username']) ?></td>
                                    <td><strong><?= formatPrice($order['total']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($order['date_facture'])) ?></td>
                                    <td>
                                        <a href="<?= SITE_URL ?>/admin/commande-detail.php?id=<?= $order['id'] ?>" class="btn-small">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="admin-card">
                <h3><i class="bi bi-person-plus"></i> Derniers inscrits</h3>
                <?php if (empty($recentUsers)): ?>
                    <p class="text-muted">Aucun utilisateur.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?= sanitize($user['username']) ?></td>
                                    <td><?= sanitize($user['email']) ?></td>
                                    <td>
                                        <span class="badge <?= $user['role'] === 'admin' ? 'badge-primary' : 'badge-default' ?>">
                                            <?= $user['role'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
