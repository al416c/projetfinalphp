<?php
require_once '../config/init.php';

if (!isAdmin()) {
    redirect('../connexion.php');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) redirect('commandes.php');

$stmt = $pdo->prepare("
    SELECT f.*, u.username, u.email
    FROM factures f
    JOIN users u ON f.user_id = u.id
    WHERE f.id = ?
");
$stmt->execute([$id]);
$facture = $stmt->fetch();

if (!$facture) {
    redirect('commandes.php');
}

$stmt = $pdo->prepare("
    SELECT fd.*, a.nom, a.image
    FROM facture_details fd
    JOIN articles a ON fd.article_id = a.id
    WHERE fd.facture_id = ?
");
$stmt->execute([$id]);
$details = $stmt->fetchAll();

$pageTitle = 'Facture #' . $facture['id'];
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
            <h1>Facture #<?= $facture['id'] ?></h1>
            <a href="<?= SITE_URL ?>/admin/commandes.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
        </div>

        <div class="admin-grid fade-in">
            <div class="admin-card">
                <h3><i class="bi bi-person"></i> Client</h3>
                <p><strong><?= sanitize($facture['username']) ?></strong></p>
                <p><?= sanitize($facture['email']) ?></p>
            </div>
            <div class="admin-card">
                <h3><i class="bi bi-geo-alt"></i> Adresse de facturation</h3>
                <p><?= sanitize($facture['adresse']) ?></p>
                <p><?= sanitize($facture['code_postal']) ?> <?= sanitize($facture['ville']) ?></p>
            </div>
            <div class="admin-card">
                <h3><i class="bi bi-info-circle"></i> Informations</h3>
                <p>Date : <?= date('d/m/Y à H:i', strtotime($facture['date_facture'])) ?></p>
                <p>Total : <strong><?= formatPrice($facture['total']) ?></strong></p>
            </div>
        </div>

        <div class="admin-card fade-in" style="margin-top:2rem;">
            <h3><i class="bi bi-list-ul"></i> Articles commandés</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Article</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item['image']): ?>
                                    <img src="<?= SITE_URL ?>/uploads/produits/<?= $item['image'] ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                <?php else: ?>
                                    <div style="width:50px;height:50px;background:#f5f5f7;border-radius:8px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= sanitize($item['nom']) ?></strong></td>
                            <td><?= formatPrice($item['prix_unitaire']) ?></td>
                            <td><?= $item['quantite'] ?></td>
                            <td><strong><?= formatPrice($item['prix_unitaire'] * $item['quantite']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;"><strong>Total</strong></td>
                        <td><strong><?= formatPrice($facture['total']) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
