<?php
require_once 'config/init.php';

$pageTitle = 'Panier - ' . SITE_NAME;

if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT pa.*, p.nom, p.prix, p.image, p.stock FROM panier pa JOIN produits p ON pa.produit_id = p.id WHERE pa.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT pa.*, p.nom, p.prix, p.image, p.stock FROM panier pa JOIN produits p ON pa.produit_id = p.id WHERE pa.session_id = ?");
    $stmt->execute([session_id()]);
}
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += $item['prix'] * $item['quantite'];
}

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Mon panier</h1>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">
            Votre panier est vide. <a href="produits.php">Continuer mes achats</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <?php foreach ($items as $item): ?>
                        <div class="cart-item d-flex align-items-center">
                            <div class="flex-shrink-0" style="width: 100px;">
                                <?php if ($item['image']): ?>
                                    <img src="uploads/produits/<?= $item['image'] ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($item['nom']) ?>">
                                <?php else: ?>
                                    <div class="img-placeholder rounded" style="height:80px;"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">
                                    <a href="produit.php?id=<?= $item['produit_id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($item['nom']) ?>
                                    </a>
                                </h5>
                                <p class="text-muted mb-1"><?= number_format($item['prix'], 2, ',', ' ') ?> € / unité</p>
                                <div class="d-flex align-items-center">
                                    <input type="number" class="form-control quantity-change quantity-input" 
                                           data-id="<?= $item['id'] ?>" 
                                           value="<?= $item['quantite'] ?>" 
                                           min="1" 
                                           max="<?= $item['stock'] ?>">
                                    <button class="btn btn-outline-danger btn-sm ms-2 delete-cart-item" data-id="<?= $item['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="text-end">
                                <strong><?= number_format($item['prix'] * $item['quantite'], 2, ',', ' ') ?> €</strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="order-summary">
                    <h5 class="mb-3">Récapitulatif</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total</span>
                        <span><?= number_format($total, 2, ',', ' ') ?> €</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Livraison</span>
                        <span><?= $total >= 50 ? 'Gratuite' : '5,00 €' ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong><?= number_format($total < 50 ? $total + 5 : $total, 2, ',', ' ') ?> €</strong>
                    </div>
                    <?php if ($total < 50): ?>
                        <p class="small text-muted">Plus que <?= number_format(50 - $total, 2, ',', ' ') ?> € pour la livraison gratuite !</p>
                    <?php endif; ?>
                    <?php if (isLoggedIn()): ?>
                        <a href="checkout.php" class="btn btn-primary w-100">Commander</a>
                    <?php else: ?>
                        <a href="connexion.php" class="btn btn-primary w-100">Se connecter pour commander</a>
                    <?php endif; ?>
                    <a href="produits.php" class="btn btn-outline-secondary w-100 mt-2">Continuer mes achats</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
