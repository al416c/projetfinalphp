<?php
require_once 'config/init.php';
$pageTitle = 'Mes factures';

if (!isLoggedIn()) redirect('connexion.php');

$stmt = $pdo->prepare("SELECT * FROM factures WHERE user_id = ? ORDER BY date_transaction DESC");
$stmt->execute([$_SESSION['user_id']]);
$factures = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="fade-in">Mes factures</h1>
        <p class="fade-in"><?= count($factures) ?> facture<?= count($factures) > 1 ? 's' : '' ?></p>
    </div>
</div>

<div class="container-wide" style="padding-top: 32px; padding-bottom: 80px;">
    <?php if (empty($factures)): ?>
        <div class="empty-state fade-in">
            <i class="bi bi-receipt"></i>
            <h3>Aucune facture</h3>
            <p>Vos factures apparaîtront ici après vos achats.</p>
            <a href="produits.php" class="btn btn-primary">Explorer les articles</a>
        </div>
    <?php else: ?>
        <div class="table-container fade-in">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Adresse</th>
                        <th>Ville</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factures as $f): ?>
                    <tr>
                        <td><strong>#<?= str_pad($f['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($f['date_transaction'])) ?></td>
                        <td><strong><?= formatPrice($f['montant']) ?></strong></td>
                        <td class="caption"><?= sanitize($f['adresse']) ?></td>
                        <td><?= sanitize($f['code_postal']) ?> <?= sanitize($f['ville']) ?></td>
                        <td><a href="commande-detail.php?id=<?= $f['id'] ?>" class="btn btn-ghost btn-sm">Voir <i class="bi bi-arrow-right"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
