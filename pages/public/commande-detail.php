<?php
require_once 'config/init.php';

if (!isLoggedIn()) redirect('connexion.php');

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('commandes.php');

// Fetch invoice (user or admin)
if (isAdmin()) {
    $stmt = $pdo->prepare("SELECT f.*, u.username, u.email FROM factures f JOIN users u ON f.user_id = u.id WHERE f.id = ?");
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare("SELECT f.*, u.username, u.email FROM factures f JOIN users u ON f.user_id = u.id WHERE f.id = ? AND f.user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
}
$facture = $stmt->fetch();

if (!$facture) {
    setFlash('error', 'Facture introuvable.');
    redirect('commandes.php');
}

$pageTitle = 'Facture #' . str_pad($id, 6, '0', STR_PAD_LEFT);

// Fetch details
$stmtDetails = $pdo->prepare("
    SELECT fd.*, a.nom, a.image
    FROM facture_details fd
    JOIN articles a ON fd.article_id = a.id
    WHERE fd.facture_id = ?
");
$stmtDetails->execute([$id]);
$details = $stmtDetails->fetchAll();

require_once 'includes/header.php';
?>

<div class="container" style="padding: 48px 22px 80px;">
    <div style="margin-bottom: 24px;">
        <a href="commandes.php" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left"></i> Retour aux factures</a>
    </div>

    <div class="invoice fade-in">
        <div class="invoice-header">
            <div>
                <h2 style="font-size: 21px; margin-bottom: 4px;">NOVA</h2>
                <p class="caption">Facture</p>
            </div>
            <div class="invoice-number">
                <strong>Facture #<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?></strong><br>
                <span class="caption"><?= date('d/m/Y à H:i', strtotime($facture['date_transaction'])) ?></span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 32px;">
            <div>
                <p class="eyebrow" style="margin-bottom: 8px;">Client</p>
                <strong><?= sanitize($facture['username']) ?></strong><br>
                <span class="caption"><?= sanitize($facture['email']) ?></span>
            </div>
            <div>
                <p class="eyebrow" style="margin-bottom: 8px;">Adresse de facturation</p>
                <p><?= sanitize($facture['adresse']) ?><br>
                <?= sanitize($facture['code_postal']) ?> <?= sanitize($facture['ville']) ?></p>
            </div>
        </div>

        <table class="table" style="margin-bottom: 24px;">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Qté</th>
                    <th>Prix unitaire</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($details as $detail): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <?php if ($detail['image'] && file_exists("uploads/produits/{$detail['image']}")): ?>
                                <img src="uploads/produits/<?= sanitize($detail['image']) ?>" alt="" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                            <?php endif; ?>
                            <a href="produit.php?id=<?= $detail['article_id'] ?>" style="color: inherit;"><?= sanitize($detail['nom']) ?></a>
                        </div>
                    </td>
                    <td><?= $detail['quantite'] ?></td>
                    <td><?= formatPrice($detail['prix_unitaire']) ?></td>
                    <td style="text-align: right; font-weight: 600;"><?= formatPrice($detail['prix_unitaire'] * $detail['quantite']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="text-align: right; padding-top: 16px; border-top: 2px solid var(--bg-secondary);">
            <span class="caption">Total payé</span><br>
            <span style="font-size: 28px; font-weight: 700;"><?= formatPrice($facture['montant']) ?></span>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
