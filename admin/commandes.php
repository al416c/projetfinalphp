<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$pageTitle = 'Gestion des factures';

$factures = $pdo->query("
    SELECT f.*, u.username
    FROM factures f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.date_facture DESC
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3><i class="bi bi-gear-fill"></i> Admin</h3>
        </div>
        <nav class="admin-nav">
            <a href="<?= SITE_URL ?>/admin/index.php" class="admin-nav-link">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
            <a href="<?= SITE_URL ?>/admin/produits.php" class="admin-nav-link">
                <i class="bi bi-box-seam"></i> Articles
            </a>
            <a href="<?= SITE_URL ?>/admin/categories.php" class="admin-nav-link">
                <i class="bi bi-grid"></i> Catégories
            </a>
            <a href="<?= SITE_URL ?>/admin/commandes.php" class="admin-nav-link active">
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
            <h1>Factures</h1>
        </div>

        <div class="admin-card fade-in">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Client</th>
                        <th>Adresse</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factures as $facture): ?>
                        <tr>
                            <td><strong>#<?= $facture['id'] ?></strong></td>
                            <td><?= sanitize($facture['username']) ?></td>
                            <td><?= sanitize($facture['adresse']) ?>, <?= sanitize($facture['ville']) ?></td>
                            <td><strong><?= formatPrice($facture['total']) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($facture['date_facture'])) ?></td>
                            <td>
                                <a href="<?= SITE_URL ?>/admin/commande-detail.php?id=<?= $facture['id'] ?>" class="btn-small">
                                    <i class="bi bi-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($factures)): ?>
                <p style="text-align:center;padding:2rem;color:#86868b;">Aucune facture.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
