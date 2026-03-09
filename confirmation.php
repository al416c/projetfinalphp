<?php
require_once 'config/init.php';
$pageTitle = 'Commande confirmée';

if (!isLoggedIn() || !isset($_SESSION['last_facture_id'])) {
    redirect('index.php');
}

$factureId = (int)$_SESSION['last_facture_id'];
unset($_SESSION['last_facture_id']);

// Fetch invoice
$stmt = $pdo->prepare("SELECT * FROM factures WHERE id = ? AND user_id = ?");
$stmt->execute([$factureId, $_SESSION['user_id']]);
$facture = $stmt->fetch();

if (!$facture) redirect('index.php');

// Fetch invoice details
$stmtDetails = $pdo->prepare("
    SELECT fd.*, a.nom, a.image
    FROM facture_details fd
    JOIN articles a ON fd.article_id = a.id
    WHERE fd.facture_id = ?
");
$stmtDetails->execute([$factureId]);
$details = $stmtDetails->fetchAll();

require_once 'includes/header.php';
?>

<div class="container">
    <div class="confirmation-page fade-in">
        <div class="check-circle">
            <i class="bi bi-check-lg"></i>
        </div>
        <h1>Commande confirmée !</h1>
        <p>Votre commande #<?= $factureId ?> a été validée avec succès.</p>
    </div>

    <!-- Invoice -->
    <div class="invoice fade-in">
        <div class="invoice-header">
            <div>
                <h2 style="font-size: 21px; margin-bottom: 4px;">NOVA</h2>
                <p class="caption">Facture</p>
            </div>
            <div class="invoice-number">
                <strong>Facture #<?= str_pad($factureId, 6, '0', STR_PAD_LEFT) ?></strong><br>
                <span class="caption"><?= date('d/m/Y H:i', strtotime($facture['date_transaction'])) ?></span>
            </div>
        </div>

        <div style="margin-bottom: 32px;">
            <p class="eyebrow" style="margin-bottom: 8px;">Adresse de facturation</p>
            <p><?= sanitize($facture['adresse']) ?><br>
            <?= sanitize($facture['code_postal']) ?> <?= sanitize($facture['ville']) ?></p>
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
                            <?= sanitize($detail['nom']) ?>
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

    <div style="text-align: center; padding: 40px 0 80px;">
        <a href="produits.php" class="btn btn-primary">Continuer mes achats</a>
        <a href="commandes.php" class="btn btn-ghost">Voir mes factures</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
