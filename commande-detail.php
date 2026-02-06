<?php
require_once 'config/init.php';

if (!isLoggedIn() || !isset($_GET['id'])) {
    redirect('commandes.php');
}

$stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ? AND user_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$commande = $stmt->fetch();

if (!$commande) {
    redirect('commandes.php');
}

$stmt = $pdo->prepare("SELECT cd.*, p.nom, p.image FROM commande_details cd JOIN produits p ON cd.produit_id = p.id WHERE cd.commande_id = ?");
$stmt->execute([$commande['id']]);
$details = $stmt->fetchAll();

$pageTitle = 'Commande #' . $commande['id'] . ' - ' . SITE_NAME;

require_once 'includes/header.php';
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item"><a href="commandes.php">Mes commandes</a></li>
            <li class="breadcrumb-item active">Commande #<?= $commande['id'] ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Produits commandés</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($details as $detail): ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div style="width: 80px;">
                            <?php if ($detail['image']): ?>
                                <img src="uploads/produits/<?= $detail['image'] ?>" class="img-fluid rounded" alt="">
                            <?php else: ?>
                                <div class="img-placeholder rounded" style="height:60px;"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0"><?= htmlspecialchars($detail['nom']) ?></h6>
                            <small class="text-muted">Quantité: <?= $detail['quantite'] ?></small>
                        </div>
                        <div class="text-end">
                            <strong><?= number_format($detail['prix_unitaire'] * $detail['quantite'], 2, ',', ' ') ?> €</strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Commande n°</strong> <?= $commande['id'] ?></p>
                    <p class="mb-2"><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></p>
                    <p class="mb-2">
                        <strong>Statut :</strong>
                        <?php
                        $statusClass = [
                            'en_attente' => 'warning',
                            'validee' => 'info',
                            'expediee' => 'primary',
                            'livree' => 'success',
                            'annulee' => 'danger'
                        ];
                        $statusText = [
                            'en_attente' => 'En attente',
                            'validee' => 'Validée',
                            'expediee' => 'Expédiée',
                            'livree' => 'Livrée',
                            'annulee' => 'Annulée'
                        ];
                        ?>
                        <span class="badge bg-<?= $statusClass[$commande['statut']] ?>">
                            <?= $statusText[$commande['statut']] ?>
                        </span>
                    </p>
                    <hr>
                    <p class="mb-2"><strong>Adresse de livraison :</strong></p>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($commande['adresse_livraison'])) ?></p>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total</strong>
                        <strong class="text-primary"><?= number_format($commande['total'], 2, ',', ' ') ?> €</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
