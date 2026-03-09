<?php
require_once 'config/init.php';
$pageTitle = 'Panier';

// Fetch cart items
if (isLoggedIn()) {
    $stmt = $pdo->prepare("
        SELECT p.*, a.nom, a.prix, a.image, s.quantite as stock, u.username as auteur_nom
        FROM panier p
        JOIN articles a ON p.article_id = a.id
        LEFT JOIN stock s ON a.id = s.article_id
        LEFT JOIN users u ON a.auteur_id = u.id
        WHERE p.user_id = ?
        ORDER BY p.date_ajout DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("
        SELECT p.*, a.nom, a.prix, a.image, s.quantite as stock, u.username as auteur_nom
        FROM panier p
        JOIN articles a ON p.article_id = a.id
        LEFT JOIN stock s ON a.id = s.article_id
        LEFT JOIN users u ON a.auteur_id = u.id
        WHERE p.session_id = ?
        ORDER BY p.date_ajout DESC
    ");
    $stmt->execute([session_id()]);
}
$items = $stmt->fetchAll();

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['prix'] * $item['quantite'];
}

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="fade-in">Votre panier</h1>
        <p class="fade-in"><?= count($items) ?> article<?= count($items) > 1 ? 's' : '' ?></p>
    </div>
</div>

<div class="container-wide">
    <?php if (empty($items)): ?>
        <div class="empty-state fade-in">
            <i class="bi bi-bag"></i>
            <h3>Votre panier est vide</h3>
            <p>Découvrez nos articles et ajoutez vos favoris au panier.</p>
            <a href="produits.php" class="btn btn-primary">Explorer les articles</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <!-- Cart items -->
            <div class="fade-in">
                <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <a href="produit.php?id=<?= $item['article_id'] ?>">
                        <?php if ($item['image'] && file_exists("uploads/produits/{$item['image']}")): ?>
                            <img src="uploads/produits/<?= sanitize($item['image']) ?>" alt="<?= sanitize($item['nom']) ?>">
                        <?php else: ?>
                            <div style="width: 100px; height: 100px; background: var(--bg-secondary); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-box-seam" style="color: var(--text-tertiary);"></i>
                            </div>
                        <?php endif; ?>
                    </a>
                    <div class="cart-item-info">
                        <h3><a href="produit.php?id=<?= $item['article_id'] ?>" style="color: inherit; text-decoration: none;"><?= sanitize($item['nom']) ?></a></h3>
                        <p class="caption">Vendu par <?= sanitize($item['auteur_nom']) ?></p>
                        <p class="price"><?= formatPrice($item['prix']) ?></p>
                    </div>
                    <div class="cart-item-actions">
                        <div class="quantity-selector">
                            <button class="cart-qty-btn" data-id="<?= $item['id'] ?>" data-action="decrease" type="button">−</button>
                            <input type="text" value="<?= $item['quantite'] ?>" readonly>
                            <button class="cart-qty-btn" data-id="<?= $item['id'] ?>" data-action="increase" type="button">+</button>
                        </div>
                        <button class="btn-icon sm cart-delete-btn" data-id="<?= $item['id'] ?>" style="background: rgba(255,69,58,0.1); color: var(--danger); border: none; cursor: pointer;">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary -->
            <div class="cart-summary fade-in">
                <h3>Résumé</h3>
                <div class="cart-summary-row">
                    <span>Sous-total</span>
                    <span><?= formatPrice($subtotal) ?></span>
                </div>
                <div class="cart-summary-row">
                    <span>Livraison</span>
                    <span style="color: var(--success);">Gratuit</span>
                </div>
                <div class="cart-summary-row total">
                    <span>Total</span>
                    <span><?= formatPrice($subtotal) ?></span>
                </div>

                <?php if (isLoggedIn()): ?>
                    <?php $balance = getUserBalance(); ?>
                    <div style="padding: 12px 0; font-size: 14px; color: var(--text-secondary); border-top: 1px solid rgba(0,0,0,0.06); margin-top: 8px;">
                        Votre solde : <strong style="color: <?= $balance >= $subtotal ? 'var(--success)' : 'var(--danger)' ?>;"><?= formatPrice($balance) ?></strong>
                    </div>
                    <?php if ($balance >= $subtotal): ?>
                        <a href="checkout.php" class="btn btn-primary">Commander</a>
                    <?php else: ?>
                        <p style="font-size: 14px; color: var(--danger); text-align: center; margin-top: 12px;">
                            Solde insuffisant. <a href="compte.php?tab=balance">Recharger</a>
                        </p>
                        <a href="checkout.php" class="btn btn-primary" style="opacity: 0.5; pointer-events: none;">Commander</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="connexion.php" class="btn btn-primary">Se connecter pour commander</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div style="height: 80px;"></div>

<?php require_once 'includes/footer.php'; ?>
